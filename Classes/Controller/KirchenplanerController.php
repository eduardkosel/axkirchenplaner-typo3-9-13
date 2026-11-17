<?php

namespace Axist\AxKirchenplaner\Controller;

use Axist\AxKirchenplaner\Api\KirchenplanerApi;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class KirchenplanerController extends ActionController
{
    public function showAction()
    {
        $request = $this->request instanceof ServerRequestInterface ? $this->request : ($GLOBALS['TYPO3_REQUEST'] ?? null);
        $normalizedParams = $request ? $request->getAttribute('normalizedParams') : null;
        $baseUrl = $normalizedParams ? $normalizedParams->getSiteUrl() : GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        $contentObject = $request ? $request->getAttribute('currentContentObject') : null;
        if ($contentObject === null && method_exists($this->configurationManager, 'getContentObject')) {
            $contentObject = $this->configurationManager->getContentObject();
        }
        $contentData = $contentObject->data ?? [];
        $contentUid = (int)($contentData['uid'] ?? 0);
        $queryParams = $request ? $request->getQueryParams() : $_GET;

        $page = $this->settings['pageconfig'] ?? [];
        $query = $this->settings['query'] ?? [];
        $search = $this->settings['search'] ?? [];
        $api = GeneralUtility::makeInstance(KirchenplanerApi::class);
        $asset = 'EXT:axkirchenplaner/Resources/Public/Angular/app.js';
        $appUrl = method_exists(PathUtility::class, 'getPublicResourceWebPath')
            ? PathUtility::getPublicResourceWebPath($asset)
            : PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($asset));
        $publicResources = substr($appUrl, 0, -strlen('/Angular/app.js'));

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $this->addAssets($pageRenderer, $publicResources, $page['framework'] ?? '');

        $templateName = ($page['seitentyp'] ?? '') === 'details' ? 'defaultDetail' : 'defaultListing';
        $htmlTemplate = $this->getFileUrl($page['templatedatei'] ?? '', 'templatedatei', $contentUid, $baseUrl)
            ?? $publicResources . '/Angular/templates/' . $templateName . '.html';
        $customCss = $this->getFileUrl($page['cssdatei'] ?? '', 'cssdatei', $contentUid, $baseUrl);
        if ($customCss !== null) {
            $pageRenderer->addCssFile($customCss, 'stylesheet', 'all', '', false, false);
        }

        $detailsPage = '';
        if (!empty($page['detailseite'])) {
            $detailsPage = $this->uriBuilder->reset()
                ->setTargetPageUid((int)$page['detailseite'])
                ->setCreateAbsoluteUri(true)
                ->build();
        }

        $searchCommunities = [];
        if (!empty($search['overridegemeinden'])) {
            foreach (explode(',', $search['overridegemeinden']) as $entry) {
                [$id, $name] = array_pad(explode('|', $entry, 2), 2, '');
                if ($id !== '' && $name !== '') {
                    $searchCommunities[] = ['id' => $id, 'name' => $name];
                }
            }
        } else {
            $searchCommunities = $this->getSearchOptions($api, $search['gemeinden'] ?? '', 'organisationen', true);
        }

        $config = [
            'apiKey' => $api->getKey(),
            'apiUrl' => $api->getBaseUrl(),
            'baseUrl' => $baseUrl,
            'pageId' => (string)($this->getQueryValue($queryParams, 'p_id') ?: ($contentData['pid'] ?? '')),
            'userGroup' => $api->getUserGroup(),
            'topics' => (string)($query['rubriken'] ?? ''),
            'locations' => (string)($query['orte'] ?? ''),
            'targetAudience' => (string)($query['zielgruppen'] ?? ''),
            'htmlTemplate' => $htmlTemplate,
            'modalTemplate' => $publicResources . '/Angular/Controller/kirchenPlanerModal.html',
            'detailLinkType' => (string)($page['detaillinktyp'] ?? 'modal'),
            'detailsPage' => $detailsPage,
            'groupItems' => (string)($page['datumgruppieren'] ?? 'nein'),
            'borderColors' => (string)($page['randfarben'] ?? ''),
            'showPagination' => (string)($page['paginierungzeigen'] ?? ''),
            'format' => (string)($query['format'] ?? ''),
            'startDate' => $this->formatDate($query['startdatum'] ?? ''),
            'endDate' => $this->formatDate($query['enddatum'] ?? ''),
            'daysToFetch' => (string)($query['abruftage'] ?? '10'),
            'communities' => (string)(($query['overridegemeinden'] ?? '') ?: ($query['gemeinden'] ?? '')),
            'includeSubCommunities' => (string)($query['unterorganisationen'] ?? ''),
            'itemsPerPage' => (string)($page['eintraegeproseite'] ?? '10'),
            'holidays' => (string)($page['feiertage'] ?? ''),
            'additionalItems' => (string)($page['zusatztermine'] ?? ''),
            'items' => (string)($query['termine'] ?? ''),
            'detailItem' => $this->getQueryValue($queryParams, 'v_id'),
            'displaySearch' => (string)($search['anzeigen'] ?? ''),
            'searchWidth' => (string)($search['breite'] ?? ''),
            'searchTextSearch' => (string)($search['textsuche'] ?? ''),
            'searchTextSearchValue' => $this->getQueryValue($queryParams, 's'),
            'searchCalendar' => (string)($search['kalender'] ?? ''),
            'searchPast' => (string)($search['vergangenheit'] ?? ''),
            'searchCategories' => $this->getSearchOptions($api, $search['kategorien'] ?? '', 'themen'),
            'searchCategoryValue' => $this->getQueryValue($queryParams, 'kategorie'),
            'searchTargetAudiences' => $this->getSearchOptions($api, $search['zielgruppen'] ?? '', 'zielgruppen'),
            'searchTargetAudienceValue' => $this->getQueryValue($queryParams, 'zielgruppe'),
            'searchLocations' => $this->getSearchOptions($api, $search['orte'] ?? '', 'raeume', true),
            'searchLocationValue' => $this->getQueryValue($queryParams, 'ort'),
            'searchCommunities' => $searchCommunities,
            'searchCommunityValue' => $this->getQueryValue($queryParams, 'gemeinde'),
        ];

        $this->view->assign('uid', $contentUid);
        $this->view->assign('configJson', json_encode(
            $config,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
        ));

        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse();
        }

        return $this->view->render();
    }

    private function addAssets(PageRenderer $pageRenderer, string $publicResources, string $framework): void
    {
        foreach (['FontAwesome/css/font-awesome.min.css', 'Bootstrap4/css/glyphicons.css', 'Css/kirchenplaner.css'] as $file) {
            $pageRenderer->addCssFile($publicResources . '/' . $file, 'stylesheet', 'all', '', false, false);
        }

        $scripts = [
            'libs/angular.min.js',
            'libs/angular-locale_de-de.js',
            'libs/angular-ui-bootstrap.min.js',
            'libs/angular-filter.min.js',
            'libs/angular-animate.min.js',
            'libs/angular-scroll.min.js',
            'libs/underscore.min.js',
            'app.js',
            'Controller/kirchenPlaner.js',
            'Controller/kirchenPlanerModal.js',
        ];
        foreach ($scripts as $file) {
            $pageRenderer->addJsFile($publicResources . '/Angular/' . $file, 'text/javascript', false, false);
        }

        if ($framework === 'bootstrap3' || $framework === 'bootstrap4') {
            $directory = ucfirst($framework);
            $pageRenderer->addCssFile($publicResources . '/' . $directory . '/css/bootstrap.min.css', 'stylesheet', 'all', '', false, true);
            $pageRenderer->addJsFile($publicResources . '/' . $directory . '/js/bootstrap.min.js', 'text/javascript', false, false);
        }
    }

    private function getFileUrl($value, string $fieldName, int $contentUid, string $baseUrl): ?string
    {
        if (!preg_match('/^(?:sys_file_reference_)?([1-9]\d*)$/D', (string)$value, $matches)) {
            return null;
        }
        $uid = (int)$matches[1];
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $file = null;
        try {
            $reference = $resourceFactory->getFileReferenceObject($uid);
            $properties = $reference->getReferenceProperties();
            if (
                $properties['tablenames'] === 'tt_content'
                && $properties['fieldname'] === $fieldName
                && (int)$properties['uid_foreign'] === $contentUid
            ) {
                $file = $reference;
            }
        } catch (ResourceDoesNotExistException $exception) {
            // A legacy record may already contain the file UID.
        }

        if ($file === null && strpos((string)$value, 'sys_file_reference_') !== 0) {
            try {
                $file = $resourceFactory->getFileObject($uid);
            } catch (ResourceDoesNotExistException $exception) {
                return null;
            }
        }

        $url = $file ? $file->getPublicUrl() : null;
        if ($url && !preg_match('~^(?:/|[a-z][a-z0-9+.-]*:)~i', $url)) {
            $url = rtrim($baseUrl, '/') . '/' . $url;
        }
        return $url ?: null;
    }

    private function getSearchOptions(KirchenplanerApi $api, string $selection, string $endpoint, bool $requiresKey = false): array
    {
        if ($selection === '') {
            return [];
        }

        $selected = explode(',', $selection);
        return array_values(array_filter($api->getOptions($endpoint, $requiresKey), static function (array $option) use ($selected) {
            return in_array($option['id'], $selected, true);
        }));
    }

    private function getQueryValue(array $params, string $name): string
    {
        $value = $params[$name] ?? '';
        return is_scalar($value) ? (string)$value : '';
    }

    private function formatDate($value): string
    {
        if ($value === '' || $value === null || $value === '0' || $value === 0) {
            return '';
        }
        if (is_numeric($value)) {
            return date('Y-m-d', (int)$value);
        }
        return preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) ? $value : '';
    }
}

<?php

use Axist\AxKirchenplaner\Controller\KirchenplanerController;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || defined('TYPO3_MODE') || die();

(static function () {
    $majorVersion = defined('TYPO3_branch') ? (int)constant('TYPO3_branch') : (new Typo3Version())->getMajorVersion();
    $extensionName = $majorVersion === 9 ? 'Axist.AxKirchenplaner' : 'AxKirchenplaner';
    $controller = $majorVersion === 9 ? 'Kirchenplaner' : KirchenplanerController::class;

    ExtensionUtility::configurePlugin(
        $extensionName,
        'Termine',
        [$controller => 'show'],
        [$controller => 'show'],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    if ($majorVersion < 12) {
        GeneralUtility::makeInstance(IconRegistry::class)->registerIcon(
            'kirchenplaner',
            BitmapIconProvider::class,
            ['source' => 'EXT:axkirchenplaner/Resources/Public/Icons/Kirchenplaner.png']
        );
        ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:axkirchenplaner/Configuration/PageTS/Mod/ContentElements/Kirchenplaner.tsconfig">'
        );
    }
})();

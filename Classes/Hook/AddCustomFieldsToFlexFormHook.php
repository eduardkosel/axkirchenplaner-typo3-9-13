<?php

namespace Axist\AxKirchenplaner\Hook;

use Axist\AxKirchenplaner\Api\KirchenplanerApi;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AddCustomFieldsToFlexFormHook
{
    public function addTopics(array &$config): void
    {
        $this->addOptions($config, 'themen');
    }

    public function addTargetAudiences(array &$config): void
    {
        $this->addOptions($config, 'zielgruppen');
    }

    public function addLocations(array &$config): void
    {
        $this->addOptions($config, 'raeume', true);
    }

    public function addCommunities(array &$config): void
    {
        $this->addOptions($config, 'organisationen', true);
    }

    private function addOptions(array &$config, string $endpoint, bool $requiresKey = false): void
    {
        $api = GeneralUtility::makeInstance(KirchenplanerApi::class);
        $useNamedItems = !defined('TYPO3_branch') && (new Typo3Version())->getMajorVersion() >= 12;
        $config['items'] = $config['items'] ?? [];
        foreach ($api->getOptions($endpoint, $requiresKey) as $option) {
            $value = $option['id'];
            $label = $requiresKey ? $option['name'] . ' (' . $value . ')' : $option['name'];
            $config['items'][] = $useNamedItems ? ['label' => $label, 'value' => $value] : [$label, $value];
        }
    }
}

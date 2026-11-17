<?php

namespace Axist\AxKirchenplaner\Api;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class KirchenplanerApi
{
    private $configuration;

    public function __construct()
    {
        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->configuration = $configuration->get('axkirchenplaner');
    }

    public function getBaseUrl(): string
    {
        return rtrim((string)($this->configuration['apiUrl'] ?? ''), '/');
    }

    public function getKey(): string
    {
        return (string)($this->configuration['apiKey'] ?? '');
    }

    public function getUserGroup(): string
    {
        return explode('|', $this->configuration['userGroup'] ?? '', 2)[0];
    }

    public function getOptions(string $endpoint, bool $requiresKey = false): array
    {
        $url = $this->getBaseUrl();
        if ($url === '' || ($requiresKey && $this->getKey() === '')) {
            return [];
        }

        $url .= '/api/' . $endpoint;
        if ($requiresKey) {
            $payload = json_encode(['key' => $this->getKey()]);
            if ($payload === false) {
                return [];
            }
            $url .= '/' . rawurlencode($payload);
        }

        try {
            $response = GeneralUtility::makeInstance(RequestFactory::class)->request($url, 'GET', ['timeout' => 5]);
        } catch (\Exception $exception) {
            return [];
        }
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $body = (string)$response->getBody();
        $rows = json_decode($body, true);
        if (!is_array($rows)) {
            $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
            $rows = json_decode($body, true);
        }
        if (!is_array($rows)) {
            $rows = [];
            foreach (preg_split('/\R/', $body) ?: [] as $line) {
                $rows[] = explode('|', trim($line), 2);
            }
        }

        $options = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row[0], $row[1]) || !is_scalar($row[0]) || !is_scalar($row[1])) {
                continue;
            }
            $id = trim((string)$row[1]);
            if ($id !== '') {
                $options[] = [
                    'id' => $id,
                    'name' => html_entity_decode(trim((string)$row[0]), ENT_QUOTES, 'UTF-8'),
                ];
            }
        }
        return $options;
    }
}

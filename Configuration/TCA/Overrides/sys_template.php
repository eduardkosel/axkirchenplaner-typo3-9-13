<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || defined('TYPO3_MODE') || die();

ExtensionManagementUtility::addStaticFile('axkirchenplaner', 'Configuration/TypoScript', 'Kirchenplaner');

<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || defined('TYPO3_MODE') || die();

(static function () {
    $contentType = 'axkirchenplaner_termine';

    ExtensionManagementUtility::addPlugin(
        ['Kirchenplaner Termine', $contentType, 'kirchenplaner'],
        'CType',
        'axkirchenplaner'
    );

    $GLOBALS['TCA']['tt_content']['types'][$contentType]['showitem'] =
        '--palette--;;general,header,pi_flexform,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,--palette--;;hidden,--palette--;;access';
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType] = 'kirchenplaner';

    $flexForm = !defined('TYPO3_branch') && (new Typo3Version())->getMajorVersion() >= 12 ? 'setupModern.xml' : 'setup.xml';
    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:axkirchenplaner/Configuration/FlexForms/' . $flexForm,
        $contentType
    );
})();

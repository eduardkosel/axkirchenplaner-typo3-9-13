# Kirchenplaner für TYPO3 9 bis 13

Die Extension bindet Termine aus der Kirchenplaner-API als TYPO3-Inhaltselement ein. Version **2.0.3** unterstützt TYPO3 9 bis 13. Die benötigte PHP-Version richtet sich nach der verwendeten TYPO3-Version.

## Installation mit Composer

Führe die Befehle im TYPO3-Projektverzeichnis aus. Für das private GitHub-Repository braucht Composer einen GitHub-Zugang mit Leserechten. Die [Composer-Dokumentation zur Authentifizierung](https://getcomposer.org/doc/articles/authentication-for-private-packages.md#github-oauth) erklärt die Anmeldung.

```bash
composer config repositories.axkirchenplaner-typo3-9-13 vcs https://github.com/eduardkosel/axkirchenplaner-typo3-9-13.git
composer require axist/axkirchenplaner:2.0.3
```

Für TYPO3 9.5.0 ist die Classic-Installation geprüft. Der TYPO3-Installer dieser Version setzt Composer 1 voraus.

## Installation per ZIP im Classic-Modus

1. Lade [axkirchenplaner_2.0.3.zip](https://github.com/eduardkosel/axkirchenplaner-typo3-9-13/releases/download/v2.0.3/axkirchenplaner_2.0.3.zip) herunter. Du brauchst GitHub-Zugriff auf das Repository.
2. Lade das ZIP im TYPO3-Backend im Modul **Erweiterungen** hoch und aktiviere **Kirchenplaner**.

## Einrichtung

1. Öffne die Extension-Konfiguration und hinterlege den API-Schlüssel für Kirchenplaner.
2. Binde das statische TypoScript-Template **Kirchenplaner** im TypoScript-Template der Website ein.
3. Füge auf der gewünschten Seite das Inhaltselement **Kirchenplaner** hinzu und stelle dort Darstellung und Filter ein.

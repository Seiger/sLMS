---
id: getting-started
title: "Erste Schritte"
sidebar_position: 2
---

# Erste Schritte

Wechseln Sie bei einer installierten Evolution-CMS-Instanz in das Verzeichnis `core`. Installieren Sie den Entwicklungszweig und führen Sie die sLMS-Schemamigration aus:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

Führen Sie beim Aktualisieren einer vorhandenen Installation den Migrationsbefehl erneut aus. Die Migration ergänzt fehlende sLMS-Tabellen und erhält vorhandene Daten.

Öffnen Sie **Module → Kurse** im Manager. Zum Anlegen eines Kurses wählen Sie **Kurse**.

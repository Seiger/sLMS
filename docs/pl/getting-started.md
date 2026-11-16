---
id: getting-started
title: "Pierwsze kroki"
sidebar_position: 2
---

# Pierwsze kroki

W zainstalowanym Evolution CMS przejdź do katalogu `core`. Zainstaluj gałąź rozwojową i uruchom migracje pakietu:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

W panelu administracyjnym otwórz **Moduły → sLMS**. Aby dodać kurs, wybierz **Kursy**.

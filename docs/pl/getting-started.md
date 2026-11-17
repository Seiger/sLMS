---
id: getting-started
title: "Pierwsze kroki"
sidebar_position: 2
---

# Pierwsze kroki

W zainstalowanym Evolution CMS przejdź do katalogu `core`. Zainstaluj gałąź rozwojową i uruchom migrację schematu sLMS:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

Podczas aktualizacji istniejącej instalacji uruchom ponownie polecenie migracji. Doda ono brakujące tabele sLMS i zachowa istniejące dane.

W panelu administracyjnym otwórz **Moduły → Kursy**. Aby dodać kurs, wybierz **Kursy**.

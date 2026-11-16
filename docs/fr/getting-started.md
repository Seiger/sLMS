---
id: getting-started
title: "Premiers pas"
sidebar_position: 2
---

# Premiers pas

Ouvrez le répertoire `core` d’une installation Evolution CMS. Installez la branche de développement et appliquez les migrations du paquet :

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

Dans le gestionnaire, ouvrez **Modules → sLMS**. Sélectionnez **Cours** pour ajouter un cours.

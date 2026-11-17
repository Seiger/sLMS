---
id: getting-started
title: "Premiers pas"
sidebar_position: 2
---

# Premiers pas

Ouvrez le répertoire `core` d’une installation Evolution CMS. Installez la branche de développement et appliquez la migration du schéma sLMS :

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

Lors de la mise à jour d’une installation existante, relancez la commande de migration. Elle crée les tables sLMS manquantes et conserve les données existantes.

Dans le gestionnaire, ouvrez **Modules → sLMS**. Sélectionnez **Cours** pour ajouter un cours.

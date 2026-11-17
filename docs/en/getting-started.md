---
id: getting-started
title: "Getting started"
sidebar_position: 2
---

# Getting started

Open the `core` directory of an installed Evolution CMS site. Install the development branch and run the sLMS schema migration:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

When updating an existing installation, run the migration command again. It creates any missing sLMS tables and preserves existing data.

Open **Modules → sLMS** in the manager. To add a course, select **Courses**.

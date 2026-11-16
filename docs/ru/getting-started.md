---
id: getting-started
title: "Начало работы"
sidebar_position: 2
---

# Начало работы

В установленной Evolution CMS откройте каталог `core`. Установите ветку разработки и примените миграции пакета:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

Откройте в менеджере **Модули → sLMS**. Чтобы добавить курс, перейдите на вкладку **Курсы**.

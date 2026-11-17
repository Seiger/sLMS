---
id: getting-started
title: "Начало работы"
sidebar_position: 2
---

# Начало работы

В установленной Evolution CMS откройте каталог `core`. Установите ветку разработки и примените миграцию схемы sLMS:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

При обновлении установленного пакета снова выполните команду миграции. Она создаст недостающие таблицы sLMS и сохранит существующие данные.

Откройте в менеджере **Модули → sLMS**. Чтобы добавить курс, перейдите на вкладку **Курсы**.

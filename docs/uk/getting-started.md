---
id: getting-started
title: "Початок роботи"
sidebar_position: 2
---

# Початок роботи

У встановленому Evolution CMS відкрий каталог `core`. Встанови поточну гілку пакета та застосуй міграцію схеми sLMS:

```sh
php artisan package:installrequire seiger/slms dev-main
php artisan migrate --path=vendor/seiger/slms/database/migrations
```

Під час оновлення встановленого пакета виконай команду міграції повторно. Вона створить відсутні таблиці sLMS і збереже наявні дані.

У менеджері відкрий **Модулі → sLMS**. Для додавання курсу перейди на вкладку **Курси**.

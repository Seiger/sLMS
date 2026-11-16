---
id: reference
title: "Referenz"
sidebar_position: 6
---

# Referenz

Der Katalog hat drei Ebenen. Ein `Course` (`s_lms_courses`) enthält `Module` (`s_lms_modules`), die `Lesson`-Datensätze (`s_lms_lessons`) enthalten. Position und ID bestimmen die Reihenfolge der Module und Lektionen.

Der Titel ist erforderlich. Der Slug ist im gesamten Katalog eindeutig und verwendet lateinische Kleinbuchstaben, Ziffern und Bindestriche zwischen Wörtern. Die optionale Ressourcen-ID muss auf eine vorhandene, nicht gelöschte Evo-Ressource verweisen. Neue Kurse sind standardmäßig Entwürfe.

Modul-Slugs sind innerhalb eines Kurses eindeutig, Lektions-Slugs innerhalb eines Moduls. Beim Umsortieren bleiben IDs und Zuordnungen erhalten.

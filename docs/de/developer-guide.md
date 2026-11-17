---
id: developer-guide
title: "Entwicklerleitfaden"
sidebar_position: 5
---

# Entwicklerleitfaden

`Seiger\sLMS\sLMSServiceProvider` registriert Migrationen und Manager-Modul. Das Panel `slms.module-panel` nutzt Evo-UI-Komponenten. Das Preset `slms.courses` definiert Kurstabelle und Formular.

Die Paketübersetzungen liegen in `lang/<locale>/global.php`. Pflegen Sie diese Seiten mit den Funktionen und prüfen Sie das Paket mit PHP 8.4, 8.5 und 8.6.

## Zugriff für Lernende

Die Route `slms.assessment.entry` erwartet die Modul-ID im Parameter `module`. Das Paket prüft die Evo-Websitzung und setzt einen veröffentlichten Kurs und ein veröffentlichtes Modul voraus. Für zusätzliche Zugriffsregeln binden Sie Ihre Implementierung von `Seiger\sLMS\Support\AssessmentAccess` im Container. `slms.attempts.pass_percent` erwartet eine Ganzzahl zwischen 0 und 100 und gilt für neue Testversionen.

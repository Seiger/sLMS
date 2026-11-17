---
id: developer-guide
title: "Przewodnik dla programistów"
sidebar_position: 5
---

# Przewodnik dla programistów

`Seiger\sLMS\sLMSServiceProvider` rejestruje migracje i moduł panelu. Panel `slms.module-panel` używa komponentów Evo UI. Preset `slms.courses` definiuje tabelę i formularz kursów.

Tłumaczenia pakietu znajdują się w `lang/<locale>/global.php`. Aktualizuj dokumentację razem z kodem i sprawdzaj pakiet na PHP 8.4, 8.5 i 8.6.

## Dostęp uczestników

Trasa `slms.assessment.entry` przyjmuje ID modułu w parametrze `module`. Pakiet sprawdza sesję webową Evo oraz publikację kursu i modułu. Aby dodać reguły dostępu, powiąż w kontenerze własną implementację `Seiger\sLMS\Support\AssessmentAccess`. `slms.attempts.pass_percent` przyjmuje liczbę całkowitą od 0 do 100 i dotyczy nowych wersji testu.

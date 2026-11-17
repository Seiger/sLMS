---
id: developer-guide
title: "Guide de développement"
sidebar_position: 5
---

# Guide de développement

`Seiger\sLMS\sLMSServiceProvider` enregistre les migrations et le module du gestionnaire. Le panneau `slms.module-panel` utilise les composants Evo UI. Le préréglage `slms.courses` définit le tableau et le formulaire des cours.

Les traductions du paquet se trouvent dans `lang/<locale>/global.php`. Mettez à jour ces pages au fil des changements et vérifiez le paquet sous PHP 8.4, 8.5 et 8.6.

## Accès des apprenants

La route `slms.assessment.entry` reçoit l’identifiant du module dans le paramètre `module`. Le paquet vérifie la session web Evo et exige un cours et un module publiés. Pour ajouter des règles d’accès, liez votre implémentation de `Seiger\sLMS\Support\AssessmentAccess` dans le conteneur. `slms.attempts.pass_percent` accepte un entier de 0 à 100 et s’applique aux nouvelles versions du test.

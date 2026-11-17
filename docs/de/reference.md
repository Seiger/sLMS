---
id: reference
title: "Referenz"
sidebar_position: 6
---

# Referenz

Der Katalog hat drei Ebenen. Ein `Course` (`s_lms_courses`) enthält `Module` (`s_lms_modules`), die `Lesson`-Datensätze (`s_lms_lessons`) enthalten. Position und ID bestimmen die Reihenfolge der Module und Lektionen.

Der Titel ist erforderlich. Der Slug ist im gesamten Katalog eindeutig und verwendet lateinische Kleinbuchstaben, Ziffern und Bindestriche zwischen Wörtern. Die optionale Ressourcen-ID muss auf eine vorhandene, nicht gelöschte Evo-Ressource verweisen. Neue Kurse sind standardmäßig Entwürfe.

Modul-Slugs sind innerhalb eines Kurses eindeutig, Lektions-Slugs innerhalb eines Moduls. Beim Umsortieren bleiben IDs und Zuordnungen erhalten.

Jedes Modul kann einen bearbeitbaren Test `Assessment` (`s_lms_assessments`) haben. Der Test wird beim Speichern der ersten Frage angelegt. Fragen (`AssessmentQuestion`, `s_lms_assessment_questions`) und Antwortoptionen (`AssessmentOption`, `s_lms_assessment_options`) speichern ihre Reihenfolge. Jede Option hat eine Kennzeichnung für die richtige Antwort.

`AssessmentVersion` (`s_lms_assessment_versions`) speichert Fragen, Optionen, Lösungsschlüssel und Bestehensgrenze. `AssessmentAttempt` (`s_lms_assessment_attempts`) enthält den Webnutzer, die gewählten Antworten, die Abgabezeit und das Ergebnis. Für eine Frage gibt es einen Punkt, wenn die Auswahl genau den richtigen Optionen entspricht. Der Prozentwert wird aufgerundet. Standardmäßig liegt die Bestehensgrenze bei 70%.

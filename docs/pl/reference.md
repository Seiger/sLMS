---
id: reference
title: "Dokumentacja referencyjna"
sidebar_position: 6
---

# Dokumentacja referencyjna

Katalog ma trzy poziomy. `Course` (`s_lms_courses`) zawiera rekordy `Module` (`s_lms_modules`), które zawierają rekordy `Lesson` (`s_lms_lessons`). Moduły i lekcje są porządkowane według pozycji i ID.

Tytuł jest wymagany. Slug jest unikatowy wśród kursów i składa się z małych liter łacińskich, cyfr i łączników między słowami. Opcjonalny ID zasobu musi wskazywać istniejący, nieusunięty zasób Evo. Nowy kurs jest domyślnie szkicem.

Slug modułu jest unikalny w obrębie kursu, a slug lekcji w obrębie modułu. Zmiana kolejności zachowuje identyfikatory i powiązania pozycji.

Każdy moduł może mieć jeden edytowalny test `Assessment` (`s_lms_assessments`). Zapis pierwszego pytania tworzy test. Pytania (`AssessmentQuestion`, `s_lms_assessment_questions`) i warianty (`AssessmentOption`, `s_lms_assessment_options`) zachowują kolejność. Poprawność jest zapisywana osobno dla każdego wariantu.

`AssessmentVersion` (`s_lms_assessment_versions`) przechowuje pytania, warianty, klucz i próg zaliczenia. `AssessmentAttempt` (`s_lms_assessment_attempts`) zapisuje użytkownika webowego, wybrane odpowiedzi, czas przesłania i wynik. Za pytanie przyznawany jest punkt, jeśli wybrane odpowiedzi dokładnie odpowiadają zestawowi poprawnych. Procent zaokrągla się w górę. Domyślny próg zaliczenia to 70%.

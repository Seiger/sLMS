---
id: reference
title: "Référence"
sidebar_position: 6
---

# Référence

Le catalogue comporte trois niveaux. Un `Course` (`s_lms_courses`) contient des `Module` (`s_lms_modules`), qui contiennent des `Lesson` (`s_lms_lessons`). Les modules et les leçons sont ordonnés par position et par ID.

Le titre est obligatoire. Le slug est unique parmi les cours et utilise des lettres latines minuscules, des chiffres et des traits d’union entre les mots. L’ID de ressource est facultatif et doit désigner une ressource Evo existante et non supprimée. Un nouveau cours est un brouillon par défaut.

Le slug d’un module est unique dans son cours et celui d’une leçon dans son module. Le réordonnancement préserve les identifiants et les liens de parenté.

Chaque module peut avoir un test modifiable `Assessment` (`s_lms_assessments`). Le premier enregistrement d’une question crée le test. Les questions (`AssessmentQuestion`, `s_lms_assessment_questions`) et les choix (`AssessmentOption`, `s_lms_assessment_options`) conservent leur ordre. Chaque choix indique s’il constitue une bonne réponse.

---
id: reference
title: "Reference"
sidebar_position: 6
---

# Reference

The catalogue has three levels. `Course` (`s_lms_courses`) contains `Module` records (`s_lms_modules`), which contain `Lesson` records (`s_lms_lessons`). Modules and lessons are ordered by position and ID.

A course title is required. The slug is unique across courses and uses lowercase Latin letters, digits and hyphens between words. The resource ID is optional and must point to an existing, non-deleted Evo resource. A new course starts as a draft.

Module slugs are unique within a course, and lesson slugs are unique within a module. Reordering preserves record IDs and parent relationships.

Each module can have one editable `Assessment` (`s_lms_assessments`). Saving the first question creates the assessment. Questions (`AssessmentQuestion`, `s_lms_assessment_questions`) and options (`AssessmentOption`, `s_lms_assessment_options`) store their order. Correctness is set on each option.

`AssessmentVersion` (`s_lms_assessment_versions`) captures questions, options, the answer key, and the passing threshold. `AssessmentAttempt` (`s_lms_assessment_attempts`) stores the web user, selected answers, submission time, and score. A question earns one point when the selected set exactly matches the correct options. The percentage rounds up. The default passing score is 70%.

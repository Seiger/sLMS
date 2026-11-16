---
id: reference
title: "Reference"
sidebar_position: 6
---

# Reference

The catalogue has three levels. `Course` (`s_lms_courses`) contains `Module` records (`s_lms_modules`), which contain `Lesson` records (`s_lms_lessons`). Modules and lessons are ordered by position and ID.

A course title is required. The slug is unique across courses and uses lowercase Latin letters, digits and hyphens between words. The resource ID is optional and must point to an existing, non-deleted Evo resource. A new course starts as a draft.

Module slugs are unique within a course, and lesson slugs are unique within a module. Reordering preserves record IDs and parent relationships.

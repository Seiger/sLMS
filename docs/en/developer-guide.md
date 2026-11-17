---
id: developer-guide
title: "Developer guide"
sidebar_position: 5
---

# Developer guide

`Seiger\sLMS\sLMSServiceProvider` registers the migrations and the manager module. The `slms.module-panel` panel uses Evo UI components. The `slms.courses` preset defines the course table and form.

Package translations live in `lang/<locale>/global.php`. Update these documents alongside feature changes and verify the package on PHP 8.4, 8.5 and 8.6.

## Learner access

The `slms.assessment.entry` route takes the module ID as its `module` parameter. The package checks the Evo web session and requires a published course and module. Bind your own implementation of `Seiger\sLMS\Support\AssessmentAccess` in the container to add access rules. `slms.attempts.pass_percent` accepts an integer from 0 to 100 and applies to new assessment versions.

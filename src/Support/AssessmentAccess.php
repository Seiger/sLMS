<?php

namespace Seiger\sLMS\Support;

use Seiger\sLMS\Models\Module;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Check whether a web learner may access a module's assessment.
 *
 * @since 0.1.0
 */
class AssessmentAccess
{
    /**
     * Require a web account and a published course and module.
     *
     * Sites can extend this check with their course access policy.
     *
     * @since 0.1.0
     */
    public function authorize(Module $module, int $webUserId): void
    {
        if ($webUserId < 1 || !$module->published || !$module->course?->published) {
            throw new NotFoundHttpException();
        }
    }
}

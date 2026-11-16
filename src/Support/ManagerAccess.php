<?php

namespace Seiger\sLMS\Support;

use EvolutionCMS\Support\ModuleAccess;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Apply the Evo manager permissions to the course table and its Livewire actions.
 *
 * @since 0.1.0
 */
class ManagerAccess
{
    /**
     * Check module access and, for writes, the manager's content editing permission.
     *
     * @since 0.1.0
     */
    public static function allows(bool $write = false): bool
    {
        if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true
            || empty($_SESSION['mgrValidated']) || empty($_SESSION['mgrInternalKey'])) {
            return false;
        }

        $core = evo();
        $roleId = (int) ($_SESSION['mgrRole'] ?? 0);
        $module = $core->modulesFromFile[md5('sLMS')] ?? null;

        return $roleId > 0
            && (int) $core->getLoginUserID('mgr') > 0
            && $core->hasPermission('exec_module', 'mgr')
            && $module !== null
            && ModuleAccess::canRunFileModule($roleId, $module)
            && (!$write || $core->hasPermission('save_document', 'mgr'));
    }

    /**
     * Stop the request when the manager cannot perform the requested course action.
     *
     * @since 0.1.0
     */
    public static function authorize(bool $write = false): void
    {
        if (!self::allows($write)) {
            throw new AccessDeniedHttpException();
        }
    }
}

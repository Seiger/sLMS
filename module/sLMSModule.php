<?php

use Seiger\sLMS\Support\ManagerAccess;

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    http_response_code(404);
    return;
}

ManagerAccess::authorize();

echo view('sLMS::index')->render();

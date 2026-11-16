<?php

use Seiger\sLMS\Tables\CoursesTableData;

return [
    'key' => 'slms.courses',
    'provider' => CoursesTableData::class,
    'per_page' => 30,
    'per_page_options' => [15, 30, 50, 100],
    'views' => ['table', 'list'],
    'default_view' => 'table',
    'default_sort' => 'id',
    'default_direction' => 'desc',
    'search' => ['enabled' => true],
    'filters' => [
        [
            'state' => 'availability',
            'type' => 'segmented',
            'label' => 'sLMS::global.courses.status',
            'options' => [
                ['value' => 'all', 'icon' => 'list', 'label' => 'sLMS::global.courses.all'],
                ['value' => 'published', 'icon' => 'eye', 'label' => 'sLMS::global.courses.published'],
                ['value' => 'draft', 'icon' => 'eye-off', 'label' => 'sLMS::global.overview.drafts'],
            ],
        ],
    ],
    'columns' => [
        ['key' => 'id', 'type' => 'text', 'label' => 'ID', 'class' => 'evo-ui-table__number-column evo-ui-table__id-column', 'sortable' => true, 'default_direction' => 'desc'],
        ['key' => 'title', 'type' => 'text', 'label' => 'sLMS::global.courses.title', 'class' => 'evo-ui-table__title-column', 'sortable' => true],
        ['key' => 'slug', 'type' => 'text', 'label' => 'sLMS::global.courses.slug', 'sortable' => true],
        ['key' => 'status', 'type' => 'badge', 'label' => 'sLMS::global.courses.status', 'sortable' => true],
    ],
    'list' => [
        'media' => false,
        'title' => 'title',
        'subtitle' => 'slug',
        'meta' => ['id', 'status'],
    ],
    'actions' => [
        [
            'key' => 'create',
            'type' => 'wire',
            'method' => 'openCreateModal',
            'attributes_provider' => 'managementAttributes',
            'icon' => 'plus',
            'label' => 'sLMS::global.courses.create',
            'tone' => 'success',
            'icon_only' => true,
        ],
        [
            'key' => 'edit',
            'type' => 'wire',
            'method' => 'openEditModal',
            'attributes_provider' => 'managementAttributes',
            'icon' => 'edit',
            'label' => 'evo::global.action_edit',
            'tone' => 'primary',
            'icon_only' => true,
            'selection' => 'single',
        ],
    ],
    'row_actions' => [
        [
            'key' => 'modules',
            'type' => 'wire',
            'method' => 'openStructure',
            'argument' => 'id',
            'icon' => 'stack-2',
            'label' => 'sLMS::global.overview.modules',
        ],
        [
            'key' => 'edit',
            'type' => 'wire',
            'method' => 'openEditModal',
            'argument' => 'id',
            'icon' => 'edit',
            'label' => 'evo::global.action_edit',
        ],
    ],
    'modal' => [
        'enabled' => true,
        'row_dblclick' => false,
        'title_create' => 'sLMS::global.courses.create',
        'title_edit' => 'sLMS::global.courses.edit',
        'submit_label' => 'evo::global.action_save',
        'icon' => 'book',
        'fields' => [
            ['name' => 'title', 'type' => 'text', 'label' => 'sLMS::global.courses.title', 'rules' => ['required']],
            [
                'name' => 'slug',
                'type' => 'text',
                'label' => 'sLMS::global.courses.slug',
                'help' => 'sLMS::global.courses.slug_form_help',
            ],
            ['name' => 'description', 'type' => 'textarea', 'label' => 'sLMS::global.courses.description'],
            [
                'name' => 'resource_id',
                'type' => 'number',
                'label' => 'sLMS::global.courses.resource',
                'help' => 'sLMS::global.courses.resource_help',
                'min' => 1,
            ],
            ['name' => 'published', 'type' => 'checkbox', 'label' => 'sLMS::global.courses.published'],
        ],
    ],
];

<?php

use Seiger\sLMS\Tables\CurriculumTableData;

$table = static function (string $kind, string $body, string $bodyLabel, string $icon, array $navigation = []): array {
    $create = 'sLMS::global.curriculum.create_' . $kind;
    $edit = 'sLMS::global.curriculum.edit_' . $kind;

    return [
        'key' => 'slms.' . $kind . 's',
        'provider' => CurriculumTableData::class,
        'per_page' => 30,
        'per_page_options' => [15, 30, 50, 100],
        'views' => ['table'],
        'search' => ['enabled' => false],
        'reorder' => ['enabled' => true],
        'columns' => [
            ['key' => 'position', 'type' => 'position', 'label' => 'sLMS::global.curriculum.position'],
            ['key' => 'title', 'type' => 'text', 'label' => 'sLMS::global.courses.title', 'class' => 'evo-ui-table__title-column'],
            ['key' => 'slug', 'type' => 'text', 'label' => 'sLMS::global.courses.slug'],
            ['key' => 'status', 'type' => 'badge', 'label' => 'sLMS::global.courses.status'],
        ],
        'actions' => [
            [
                'key' => 'create',
                'type' => 'wire',
                'method' => 'openCreateModal',
                'attributes_provider' => 'managementAttributes',
                'icon' => 'plus',
                'label' => $create,
                'tone' => 'success',
                'icon_only' => true,
            ],
        ],
        'row_actions' => [
            ...$navigation,
            ['key' => 'edit', 'type' => 'wire', 'method' => 'openEditModal', 'argument' => 'id', 'icon' => 'edit', 'label' => $edit],
        ],
        'modal' => [
            'enabled' => true,
            'row_dblclick' => false,
            'title_create' => $create,
            'title_edit' => $edit,
            'submit_label' => 'evo::global.action_save',
            'icon' => $icon,
            'fields' => [
                ['name' => 'title', 'type' => 'text', 'label' => 'sLMS::global.courses.title', 'rules' => ['required']],
                ['name' => 'slug', 'type' => 'text', 'label' => 'sLMS::global.courses.slug', 'help' => 'sLMS::global.curriculum.slug_help'],
                ['name' => $body, 'type' => 'textarea', 'label' => $bodyLabel, 'rows' => $kind === 'lesson' ? 8 : 4],
                ['name' => 'published', 'type' => 'checkbox', 'label' => 'sLMS::global.courses.published'],
            ],
        ],
    ];
};

return [
    'modules' => ['table' => $table('module', 'description', 'sLMS::global.courses.description', 'stack-2', [
        [
            'key' => 'lessons',
            'type' => 'wire',
            'method' => 'openStructure',
            'argument' => 'id',
            'icon' => 'file-text',
            'label' => 'sLMS::global.overview.lessons',
        ],
    ])],
    'lessons' => ['table' => $table('lesson', 'content', 'sLMS::global.curriculum.content', 'file-text')],
];

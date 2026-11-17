<?php

use Seiger\sLMS\Tables\AssessmentTableData;

return [
    'key' => 'slms.assessment',
    'provider' => AssessmentTableData::class,
    'per_page' => 30,
    'per_page_options' => [15, 30, 50, 100],
    'views' => ['table'],
    'search' => ['enabled' => false],
    'columns' => [
        ['key' => 'position', 'type' => 'text', 'label' => 'sLMS::global.curriculum.position'],
        ['key' => 'prompt', 'type' => 'text', 'label' => 'sLMS::global.assessment.prompt', 'class' => 'evo-ui-table__title-column'],
        ['key' => 'options_count', 'type' => 'text', 'label' => 'sLMS::global.assessment.options'],
    ],
    'actions' => [
        [
            'key' => 'create',
            'type' => 'wire',
            'method' => 'openCreateModal',
            'attributes_provider' => 'managementAttributes',
            'icon' => 'plus',
            'label' => 'sLMS::global.assessment.create_question',
            'tone' => 'success',
            'icon_only' => true,
        ],
    ],
    'row_actions' => [
        [
            'key' => 'edit',
            'type' => 'wire',
            'method' => 'openEditModal',
            'argument' => 'id',
            'icon' => 'edit',
            'label' => 'sLMS::global.assessment.edit_question',
        ],
    ],
    'modal' => [
        'enabled' => true,
        'row_dblclick' => false,
        'title_create' => 'sLMS::global.assessment.create_question',
        'title_edit' => 'sLMS::global.assessment.edit_question',
        'submit_label' => 'evo::global.action_save',
        'icon' => 'clipboard-check',
        'fields' => [
            ['name' => 'prompt', 'type' => 'textarea', 'label' => 'sLMS::global.assessment.prompt', 'rules' => ['required']],
            [
                'name' => 'options',
                'type' => 'repeater',
                'label' => 'sLMS::global.assessment.options',
                'help' => 'sLMS::global.assessment.options_help',
                'add_label' => 'sLMS::global.assessment.add_option',
                'default_item' => ['text' => '', 'is_correct' => false],
                'fields' => [
                    ['name' => 'text', 'type' => 'textarea', 'rows' => 2, 'span' => 'grow', 'label' => 'sLMS::global.assessment.option_text'],
                    ['name' => 'is_correct', 'type' => 'checkbox', 'span' => 'compact', 'label' => 'sLMS::global.assessment.correct'],
                ],
            ],
        ],
    ],
];

<?php

return [
    'column_name' => [
        'order' => 'order',
        'parent' => 'parent_id',
        'depth' => 'depth',
    ],
    'default_parent_id' => -1,
    'default_children_key_name' => 'children',

    'register' => [
        /**
         * Register filament-tree.css
         */
        'default_css' => true,
    ]
];
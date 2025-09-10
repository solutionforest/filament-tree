<?php

return [
    /**
     * Tree model fields
     */
    'column_name' => [
        'order' => 'order',
        'parent' => 'parent_id',
        'title' => 'title',
    ],
    
    /**
     * Tree model default parent key
     */
    'default_parent_id' => -1,
    
    /**
     * Tree model default children key name
     */
    'default_children_key_name' => 'children',
    
    /**
     * NEW: Enable Laravel policy authorization for tree actions
     * When enabled:
     * - Actions automatically check policies based on ability mapping
     * - Unauthorized actions are completely hidden (not rendered in DOM)
     * - Works with any Laravel authorization system (Gates, Policies, Spatie, etc.)
     */
    'enable_policy_authorization' => false,
    
    /**
     * NEW: Policy abilities mapping for tree actions
     * Maps action names to policy method names
     * Customize this to match your policy method naming conventions
     */
    'policy_abilities' => [
        'create' => 'create',
        'createChild' => 'create',
        'edit' => 'update', 
        'view' => 'view',
        'delete' => 'delete',
        'restore' => 'restore',
        'forceDelete' => 'forceDelete',
    ],
    
    /**
     * NEW: TreeResource configuration
     */
    'resources' => [
        'enabled' => true,
        'namespace' => 'App\\Filament\\Resources',
        'path' => app_path('Filament/Resources'),
    ],
];

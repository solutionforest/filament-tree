@props([
    'key',
    'depth',
    'label' => null,
    'groupKey' => null,
    'hasChildren' => false,
])

<div class="filament-forms-tree-component-option-node flex items-center gap-1" 
    data-treenode="{{ $key }}"
    data-treenode-group="{{ $groupKey }}"
    data-treenode-depth="{{ $depth }}"
>
    @if ($hasChildren)
        <x-filament::icon-button 
            type="button" 
            color="secondary"
            size="xs"
            x-on:click="toggleNodeExpand('{{ $key }}')"
        >
            <x-slot name="icon">
                <x-heroicon-o-minus class="w-4 h-4" x-show="isNodeExpanded('{{ $key }}')" />
                <x-heroicon-o-plus class="w-4 h-4" x-show="!isNodeExpanded('{{ $key }}')" />
            </x-slot>
        </x-filament::icon-button>
    @endif

    <label @class([
        'filament-forms-tree-component-option-label',
        'ml-4' => !$hasChildren,
    ])>
        <x-filament::input.checkbox 
            type="checkbox"
            x-model="state"
            value="{{ $key }}" 
            class="tree-checkbox"
            x-on:change="treeNodeCheckboxToggleEvent"
        />
        <span @class([
            'filament-forms-tree-component-option-label-text text-sm font-medium ',
        ])>
            {{ $label ?? $key }}
        </span>
    </label>
</div>
@props(['record', 'containerKey', 'tree'])
@php
    /** @var $record \Illuminate\Database\Eloquent\Model */
    /** @var $containerKey string */
    /** @var $tree \SolutionForest\FilamentTree\Components\Tree */

    $title = $record->getAttributeValue('title');
    $icon = $record->getAttributeValue('icon');

    $recordKey = $tree->getRecordKey($record);
    $parentKey = $record->parent ? $tree->getRecordKey($record->parent) : null;

    $children = $record->children;

    $actions = $tree->getActions();
@endphp

<li @class([
        'filament-tree-row dd-item',
        'mb-2',
    ])
    data-id="{{ $recordKey }}">
    <div @class([
            'dd-handle',
            'border rounded-lg px-4 py-3', 
            'flex items-center justify-between cursor-move',
            'dark:bg-gray-900' => config('forms.dark_mode'),
        ])
    >
        <div class="flex">
            <div class="w-4 mr-1">
                @if ($icon)
                    <x-dynamic-component :component="$icon" class="w-4 h-4" />
                @endif
            </div>

            <span>
                {{ $title }}
            </span>
        </div>
    </div>

    @if (count($actions))
        <div class="ml-auto flex items-center gap-4 flex-wrap">
            <x-filament-tree::actions :actions="$actions" :record="$record" />
        </div>
    @endif

    @if ($children)
        <x-filament-tree::tree.list :records="$children" :containerKey="$containerKey" :tree="$tree" />
    @endif
</li>
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

<li class="filament-tree-row dd-item" data-id="{{ $recordKey }}">
    <div @class([
            'bg-white rounded-lg border border-gray-300 dd-handle', 
            'mb-2',
            'flex w-full items-center ',
            'dark:bg-gray-900' => config('filament.dark_mode'),
        ])>

        <button type="button" class="h-full flex items-center bg-gray-50 rounded-lg border-r border-gray-300 px-px">
            <svg class="text-gray-400 w-4 h-4 -mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
            </svg>
            <svg class="text-gray-400 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
            </svg>
        </button>

        <div class="dd-title dd-nodrag ml-2 flex">
            <div class="w-4 mr-1">
                @if ($icon)
                    <x-dynamic-component :component="$icon" class="w-4 h-4" />
                @endif
            </div>

            <span>
                {{ $title }}
            </span>
        </div>

        @if (count($actions))
            <div class="dd-nodrag ml-auto">
                <x-filament-tree::actions :actions="$actions" :record="$record" />
            </div>
        @endif
    </div>
    @if ($children)
        <x-filament-tree::tree.list :records="$children" :containerKey="$containerKey" :tree="$tree" />
    @endif
</li>
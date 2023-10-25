@props(['record', 'containerKey', 'tree', 'title' => null, 'icon' => null])
@php
    /** @var $record \Illuminate\Database\Eloquent\Model */
    /** @var $containerKey string */
    /** @var $tree \SolutionForest\FilamentTree\Components\Tree */

    $recordKey = $tree->getRecordKey($record);
    $parentKey = $tree->getParentKey($record);

    $children = $record->children;
    $collapsed = $this->getNodeCollapsedState($record);

    $actions = $tree->getActions();
@endphp

<li class="filament-tree-row dd-item" data-id="{{ $recordKey }}">
    <div wire:loading.remove.delay
        wire:target="{{ implode(',', \SolutionForest\FilamentTree\Components\Tree::LOADING_TARGETS) }}"
        @class([
            'bg-white rounded-lg border border-gray-300 dd-handle',
            'mb-2',
            'flex w-full items-center ',
            'dark:bg-gray-900' => config('filament.dark_mode'),
        ])>

        <button type="button"
            class="h-full flex items-center bg-gray-50 rounded-lg border-r rtl:border-l border-gray-300 px-px">
            <x-heroicon-o-dots-vertical class="text-gray-400 w-4 h-4 -mr-2 rtl:mr-0" />
            <x-heroicon-o-dots-vertical class="text-gray-400 w-4 h-4 rtl:-mr-2" />
        </button>

        <div class="dd-content dd-nodrag ml-2 rtl:mr-2 rtl:ml-0 flex gap-1">
            <div class="w-4">
                @if ($icon)
                    <x-dynamic-component :component="$icon" class="w-4 h-4" />
                @endif
            </div>

            <span>
                {{ $title }}
            </span>

            <div @class(['dd-item-btns', 'hidden' => !count($children)])>
                <button data-action="expand" @class(['hidden' => !$collapsed])>
                    <x-heroicon-o-chevron-down class="text-gray-400 w-4 h-4" />
                </button>
                <button data-action="collapse" @class(['hidden' => $collapsed])>
                    <x-heroicon-o-chevron-up class="text-gray-400 w-4 h-4" />
                </button>
            </div>
        </div>

        @if (count($actions))
            <div class="dd-nodrag ml-auto rtl:ml-0 rtl:mr-auto">
                <x-filament-tree::actions :actions="$actions" :record="$record" />
            </div>
        @endif
    </div>
    @if (count($children))
        <x-filament-tree::tree.list :records="$children" :containerKey="$containerKey" :tree="$tree" :collapsed="$collapsed" />
    @endif
    <div class="rounded-lg border border-gray-300 mb-2 w-full px-4 py-4 animate-pulse hidden"
        wire:loading.class.remove.delay="hidden"
        wire:target="{{ implode(',', \SolutionForest\FilamentTree\Components\Tree::LOADING_TARGETS) }}">
        <div class="h-4 bg-gray-300 rounded-md"></div>
    </div>
</li>

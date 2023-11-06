@php use Illuminate\Database\Eloquent\Model; @endphp
@php use Filament\Facades\Filament; @endphp
@php use SolutionForest\FilamentTree\Components\Tree; @endphp
@props(['record', 'containerKey', 'tree', 'title' => null, 'icon' => null])
@php
    /** @var $record Model */
    /** @var $containerKey string */
    /** @var $tree Tree */

    $recordKey = $tree->getRecordKey($record);
    $parentKey = $tree->getParentKey($record);

    $children = $record->children;
    $collapsed = $this->getNodeCollapsedState($record);

    $actions = $tree->getActions();
@endphp

<li class="filament-tree-row dd-item" data-id="{{ $recordKey }}">
    <div wire:loading.remove.delay
        wire:target="{{ implode(',', Tree::LOADING_TARGETS) }}"
        @class([
            'rounded-lg border dd-handle h-10',
            'mb-2',
            'flex w-full items-center',
            'border-gray-300 bg-white dark:border-white/10 dark:bg-gray-900',
        ])>

        <button type="button" @class([
            'h-full flex items-center',
            'rounded-l-lg border-r rtl:rounded-l rtl:border-r-0 rtl:border-l px-px',
            'bg-gray-50 border-gray-300 dark:bg-white/5 dark:border-white/10',
        ])>
            <x-heroicon-m-ellipsis-vertical class="text-gray-400 dark:text-gray-500 w-4 h-4 -mr-2 rtl:mr-0 rtl:-ml-2"/>
            <x-heroicon-m-ellipsis-vertical class="text-gray-400 dark:text-gray-500 w-4 h-4"/>
        </button>

        <div class="dd-content dd-nodrag flex gap-1">
            @if ($icon)
                <div class="w-4">
                    <x-dynamic-component :component="$icon" class="w-4 h-4"/>
                </div>
            @endif

            <span @class([
                'ml-4 rtl:mr-4' => !$icon,
                'font-semibold'
            ])>
                {{ $title }}
            </span>

            <div @class(['dd-item-btns', 'hidden' => !count($children), 'flex items-center justify-center pl-3'])>
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
         wire:target="{{ implode(',', Tree::LOADING_TARGETS) }}">
        <div class="h-4 bg-gray-300 rounded-md"></div>
    </div>
</li>

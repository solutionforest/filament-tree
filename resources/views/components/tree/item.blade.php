@php use Illuminate\Database\Eloquent\Model; @endphp
@php use Filament\Facades\Filament; @endphp
@php use SolutionForest\FilamentTree\Components\Tree; @endphp
@props(['record', 'containerKey', 'tree', 'title' => null, 'icon' => null, 'description' => null])
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
        class="dd-handle"
    >

        <button type="button">
            <x-heroicon-m-ellipsis-vertical/>
            <x-heroicon-m-ellipsis-vertical/>
        </button>

        <div class="dd-content dd-nodrag">
            <x-filament-tree::tree.item-display :record="$record" :title="$title" :icon="$icon" :description="$description"/>
            <div class="dd-item-btns">
                <button data-action="expand" @class(['hidden' => !$collapsed])>
                    <x-heroicon-o-chevron-down />
                </button>
                <button data-action="collapse" @class(['hidden' => $collapsed])>
                    <x-heroicon-o-chevron-up />
                </button>
            </div>
        </div>

        @if (count($actions))
            <div class="fi-tree-actions-ctn dd-nodrag ml-auto">
                <x-filament-tree::actions :actions="$actions" :record="$record" />
            </div>
        @endif
    </div>
    @if (count($children))
        <x-filament-tree::tree.list :records="$children" :containerKey="$containerKey" :tree="$tree" :collapsed="$collapsed" />
    @endif
    <div class="loading-indicator"
         wire:loading.class.remove.delay="hidden"
         wire:target="{{ implode(',', Tree::LOADING_TARGETS) }}"
    ></div>
</li>

@php
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? 1;
    $records = collect($this->getRootLayerRecords() ?? []);
    $toolbarActions = $tree->getToolbarActions() ?? [];
@endphp

<div class="filament-tree-component"
    wire:disabled="updateTree"
    {{-- x-ignore --}}
    ax-load
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-tree-component', 'solution-forest/filament-tree') }}"
    x-data="treeNestableComponent({
        containerKey: {{ $containerKey }},
        maxDepth: {{ $maxDepth }}
    })">
    <x-filament::section>
        <x-slot name="heading">
            {{ ($this->displayTreeTitle() ?? false) ? $this->getTreeTitle() : null }}
        </x-slot>
        <menu class="nestable-menu" id="nestable-menu">
            <div class="toolbar-btns main">
                <div class="btn-group">
                    <x-filament::button color="gray" tag="button" data-action="expand-all" x-on:click="expandAll()" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                        {{ __('filament-tree::filament-tree.button.expand_all') }}
                    </x-filament::button>
                    <x-filament::button color="gray" tag="button" data-action="collapse-all" x-on:click="collapseAll()" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                        {{ __('filament-tree::filament-tree.button.collapse_all') }}
                    </x-filament::button>
                </div>
                <div class="btn-group">
                    <x-filament::button tag="button" data-action="save" x-on:click="save()" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                        <x-filament::loading-indicator class="h-4 w-4" wire:loading wire:target="updateTree"/>
                        <span wire:loading.remove wire:target="updateTree">
                            {{ __('filament-tree::filament-tree.button.save') }}
                        </span>
                    </x-filament::button>
                </div>
            </div>

            @if (is_array($toolbarActions) && count($toolbarActions))
                <x-filament::actions 
                    class="toolbar-btns"
                    :actions="$toolbarActions"
                />
            @endif
        </menu>
        <div class="filament-tree dd" id="{{ $containerKey }}" x-ref="treeContainer">
            <x-filament-tree::tree.list :records="$records" :containerKey="$containerKey" :tree="$tree"/>
        </div>
    </x-filament::section>
</div>
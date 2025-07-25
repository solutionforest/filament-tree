@php
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? 1;
    $records = collect($this->getRootLayerRecords() ?? []);

@endphp

<div wire:disabled="updateTree"
    x-ignore
    ax-load
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-tree-component', 'solution-forest/filament-tree') }}"
    x-data="treeNestableComponent({
        containerKey: {{ $containerKey }},
        maxDepth: {{ $maxDepth }}
    })">
    <x-filament::section :heading="($this->displayTreeTitle() ?? false) ? $this->getTreeTitle() : null">
        <menu class="flex gap-2 mb-4" id="nestable-menu">
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
        </menu>
        <div class="filament-tree dd" id="{{ $containerKey }}" x-ref="treeContainer">
            <x-filament-tree::tree.list :records="$records" :containerKey="$containerKey" :tree="$tree"/>
        </div>
    </x-filament::section>
</div>

<form wire:submit.prevent="callMountedTreeAction">
    @php
        $action = $this->getMountedTreeAction();
    @endphp

    <x-filament::modal
        :alignment="$action?->getModalAlignment()"
        :close-button="$action?->hasModalCloseButton()"
        :close-by-clicking-away="$action?->isModalClosedByClickingAway()"
        :description="$action?->getModalDescription()"
        display-classes="block"
        :footer-actions="$action?->getVisibleModalFooterActions()"
        :footer-actions-alignment="$action?->getModalFooterActionsAlignment()"
        :heading="$action?->getModalHeading()"
        :icon="$action?->getModalIcon()"
        :icon-color="$action?->getModalIconColor()"
        :id="$this->getId() . '-tree-action'"
        :slide-over="$action?->isModalSlideOver()"
        :sticky-footer="$action?->isModalFooterSticky()"
        :sticky-header="$action?->isModalHeaderSticky()"
        :visible="filled($action)"
        :width="$action?->getModalWidth()"
        :wire:key="$action ? $this->getId() . '.tree.actions.' . $action->getName() . '.modal' : null"
        x-on:closed-form-component-action-modal.window="if (($event.detail.id === '{{ $this->getId() }}') && $wire.mountedTreeActions.length) open()"
        x-on:modal-closed.stop="
            const mountedTreeActionShouldOpenModal = {{ \Illuminate\Support\Js::from($action && $this->mountedTreeActionShouldOpenModal()) }}

            if (! mountedTreeActionShouldOpenModal) {
                return
            }

            if ($wire.mountedFormComponentActions.length) {
                return
            }

            $wire.unmountTreeAction(false)
        "
        x-on:opened-form-component-action-modal.window="if ($event.detail.id === '{{ $this->getId() }}') close()"
    >
        @if ($action)
            {{ $action->getModalContent() }}

            @if (count(($infolist = $action->getInfolist())?->getComponents() ?? []))
                {{ $infolist }}
            @elseif ($this->mountedTreeActionHasForm())
                {{ $this->getMountedTreeActionForm() }}
            @endif

            {{ $action->getModalContentFooter() }}
        @endif
    </x-filament::modal>
</form>

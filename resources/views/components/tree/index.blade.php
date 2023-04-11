@php
    $containerKey = 'filament_tree_container_' . $this->id;
    $maxDepth = $getMaxDepth() ?? 1;
    $records = collect($this->getRootLayerRecords() ?? []);

@endphp

@include('filament-tree::tree.scripts', ['containerKey' => $containerKey, $maxDepth => $maxDepth])

<div wire:disabled="updateTree">
    <x-filament::card :heading="($this->displayTreeTitle() ?? false) ? $this->getTreeTitle() : null">
        <menu class="flex gap-2" id="nestable-menu">
            <div class="btn-group">
                <x-filament::button tag="button" data-action="expand-all" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                    {{ __('filament-tree::filament-tree.button.expand_all') }}
                </x-filament::button>
                <x-filament::button tag="button" data-action="collapse-all" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                    {{ __('filament-tree::filament-tree.button.collapse_all') }}
                </x-filament::button>
            </div>
            <div class="btn-group">
                <x-filament::button tag="button" data-action="save" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                    <x-filament-support::loading-indicator class="h-4 w-4" wire:loading wire:target="updateTree" />
                    <span wire:loading.remove wire:target="updateTree">
                        {{ __('filament-tree::filament-tree.button.save') }}
                    </span>
                    
                </x-filament::button>
            </div>
        </menu>
        <div class="filament-tree dd" id="{{ $containerKey }}">
            <x-filament-tree::tree.list :records="$records" :containerKey="$containerKey" :tree="$tree" />
        </div>
    </x-filament::card>
</div>

<form wire:submit.prevent="callMountedTreeAction">
    @php
        $action = $this->getMountedTreeAction();
        $heading = $action ? $action->getModalHeading() : null;
        $subheading = $action ? $action->getModalSubheading() : null;
        $modalContent = $action ? $action->getModalContent() : null;
        $modalFooter = $action ? $action->getModalFooter() : null;
        $modalActions = $action ? $action->getModalActions() : null;
    @endphp
    <x-filament-tree::modal
        :id="$this->id . '-tree-action'"
        :wire:key="$action ? $this->id . '.tree.actions.' . $action->getName() . '.modal' : null"
        :visible="filled($action)"
        :width="$action?->getModalWidth()"
        :slide-over="$action?->isModalSlideOver()"
        :close-by-clicking-away="$action?->isModalClosedByClickingAway()"
        display-classes="block"
        x-init="livewire = $wire.__instance"
        x-on:modal-closed.stop="
            if ('mountedTreeAction' in livewire?.serverMemo.data) {
                livewire.set('mountedTreeAction', null)
            }

            if ('mountedTreeActionRecord' in livewire?.serverMemo.data) {
                livewire.set('mountedTreeActionRecord', null)
            }
        "
    >
        @if ($action?->isModalCentered())
            @if ($heading)
                <x-slot name="heading">
                    {{ $heading }}
                </x-slot>
            @endif

            @if ($subheading)
                <x-slot name="subheading">
                    {{ $subheading }}
                </x-slot>
            @endif
        @else
            <x-slot name="header">
                @if ($heading)
                    <x-filament-support::modal.heading>
                        {{ $heading }}
                    </x-filament-support::modal.heading>
                @endif

                @if ($subheading)
                    <x-filament-support::modal.subheading>
                        {{ $subheading }}
                    </x-filament-support::modal.subheading>
                @endif
            </x-slot>
        @endif
        {{ $modalContent }}

        @if ($action?->hasFormSchema())
            {{ $getMountedActionForm() }}
        @endif

        {{ $modalFooter}}

        @if (count($modalActions ?? []))
            <x-slot name="footer">
                <x-filament-support::modal.actions :full-width="$action?->isModalCentered()">
                    @foreach ($modalActions as $modalAction)
                        {{ $modalAction }}
                    @endforeach
                </x-filament-support::modal.actions>
            </x-slot>
        @endif
    </x-filament-tree::modal>
</form>
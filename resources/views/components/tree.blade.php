@php
    $containerKey = 'filament_tree_container_' . $this->id;
    $maxDepth = $tree->getMaxDepth() ?? 1;
    $records = collect($this->getRootLayerRecords() ?? []);

@endphp

@include('filament-tree::tree-assets')
<script>
    $(document).ready(function () {
        $('#{{ $containerKey }}').nestable({
            group: {{ $containerKey }},
            maxDepth: {{ $maxDepth }}
        });
        $('#nestable-menu [data-action="expand-all"]').on('click', function () {
            $('.dd').nestable('expandAll');
        });
        $('#nestable-menu [data-action="collapse-all"]').on('click', function () {
            $('.dd').nestable('collapseAll');
        });
        $('#nestable-menu [data-action="save"]').on('click', async function (e) {
            let value = $('#{{ $containerKey }}').nestable('serialize');
            let result = await @this.updateTree(value);
            if (result['reload'] === true) {
                console.log('Reload Menu');
                window.location.reload();
            }
        });
    });
</script>

<div wire:disabled="updateTree">
    <x-filament::card>
        <menu class="flex gap-2" id="nestable-menu">
            <div class="btn-group">
                <x-filament::button tag="button" data-action="expand-all">
                    {{ __('filament-tree::filament-tree.button.expand_all') }}
                </x-filament::button>
                <x-filament::button tag="button" data-action="collapse-all">
                    {{ __('filament-tree::filament-tree.button.collapse_all') }}
                </x-filament::button>
            </div>
            <div class="btn-group">
                <x-filament::button tag="button" data-action="save">
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

@php
    $action = $this->getMountedTreeAction();
@endphp
@if ($action)
@php
    ray([
        'action' => $action,
        'heading' => $action->getModalHeading(),
        'subheading' => $action->getModalSubheading(),
        'modalContent' => $action->getModalContent(),
    ]);
@endphp
    <x-filament-tree::modal
        :id="$this->id . '-tree-action'"
        :wire:key="$action ? $this->id . '.tree.actions.' . $action->getName() . '.modal' : null"
        {{-- :visible="filled($action)" --}}
        {{-- :width="$action?->getModalWidth()" --}}
        {{-- :slide-over="$action?->isModalSlideOver()" --}}
        {{-- :close-by-clicking-away="$action?->isModalClosedByClickingAway()" --}}
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
        @if ($action->isModalCentered())
            @if ($heading = $action->getModalHeading())
                <x-slot name="heading">
                    {{ $heading }}
                </x-slot>
            @endif

            @if ($subheading = $action->getModalSubheading())
                <x-slot name="subheading">
                    {{ $subheading }}
                </x-slot>
            @endif
        @else
            <x-slot name="header">
                @if ($heading = $action->getModalHeading())
                    <x-tables::modal.heading>
                        {{ $heading }}
                    </x-tables::modal.heading>
                @endif

                @if ($subheading = $action->getModalSubheading())
                    <x-tables::modal.subheading>
                        {{ $subheading }}
                    </x-tables::modal.subheading>
                @endif
            </x-slot>
        @endif
        {{ $action->getModalContent() }}

        @if ($action->hasFormSchema())
            {{ $this->getMountedActionForm() }}
        @endif

        <span>Test</span>

        {{ $action->getModalFooter() }}

        @if (count($action->getModalActions()))
            <x-slot name="footer">
                TODO:: Footer action
                {{-- <x-tables::modal.actions :full-width="$action->isModalCentered()">
                    @foreach ($action->getModalActions() as $modalAction)
                        {{ $modalAction }}
                    @endforeach
                </x-tables::modal.actions> --}}
            </x-slot>
        @endif
    </x-filament-tree::modal>

@endif
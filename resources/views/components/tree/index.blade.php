@php
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? 1;
    $records = collect($this->getRootLayerRecords() ?? []);
@endphp

<div wire:disabled="updateTree"
     x-data="{
        init: function () {
            const nestedTreeElement = $('#{{ $containerKey }}');
            nestedTreeElement.nestable({
                group: {{ $containerKey }},
                maxDepth: {{ $maxDepth }},
                expandBtnHTML: '',
                collapseBtnHTML: '',
            });
            const nestedTree = nestedTreeElement.data('nestable');

            function expandItem(item) {
                item.removeClass(nestedTree.options.collapsedClass);
                item.children('.dd-content').find('[data-action=expand]').addClass('hidden');
                item.children('.dd-content').find('[data-action=collapse]').removeClass('hidden');
                item.children(nestedTree.options.listNodeName).removeClass('hidden');
            }

            function collapseItem(item) {
                item.addClass(nestedTree.options.collapsedClass);
                item.children('.dd-content').find('[data-action=collapse]').addClass('hidden');
                item.children('.dd-content').find('[data-action=expand]').removeClass('hidden');
                item.children(nestedTree.options.listNodeName).addClass('hidden');
            }

            // Custom expand / collapse buttons
            $('#{{ $containerKey }} .dd-item').on('click', '.dd-item-btns button', function (e) {
                const target = $(e.currentTarget);
                const item = target.closest('li');

                target.data('action') === 'collapse' ? collapseItem(item) : expandItem(item);
            });

            // Expand / collapse all buttons
            $('#nestable-menu [data-action=expand-all]').on('click', function () {
                nestedTree.el.find(nestedTree.options.itemNodeName).each((idx, item) => expandItem($(item)));
            });
            $('#nestable-menu [data-action=collapse-all]').on('click', function () {
                nestedTree.el.find(nestedTree.options.itemNodeName).each((idx, item) => collapseItem($(item)));
            });

            // Update expand / collapse button visibility on tree update
            nestedTreeElement.on('change', function () {
                $('.dd-item-btns').each(function () {
                    const childList = $(this).closest('li').children('ol');
                    childList.length ? $(this).removeClass('hidden') : $(this).addClass('hidden');
                });
            });

            $('#nestable-menu [data-action=save]').on('click', async function (e) {
                let value = $('#{{ $containerKey }}').nestable('serialize');
                let result = await @this.updateTree(value);
                if (result['reload'] === true) {
                    window.location.reload();
                }
            });
        }
    }">
    <x-filament::section :heading="($this->displayTreeTitle() ?? false) ? $this->getTreeTitle() : null">
        <menu class="flex gap-2 mb-4" id="nestable-menu">
            <div class="btn-group">
                <x-filament::button color="gray" tag="button" data-action="expand-all" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                    {{ __('filament-tree::filament-tree.button.expand_all') }}
                </x-filament::button>
                <x-filament::button color="gray" tag="button" data-action="collapse-all" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                    {{ __('filament-tree::filament-tree.button.collapse_all') }}
                </x-filament::button>
            </div>
            <div class="btn-group">
                <x-filament::button tag="button" data-action="save" wire:loading.attr="disabled" wire:loading.class="cursor-wait opacity-70">
                    <x-filament::loading-indicator class="h-4 w-4" wire:loading wire:target="updateTree"/>
                    <span wire:loading.remove wire:target="updateTree">
                        {{ __('filament-tree::filament-tree.button.save') }}
                    </span>

                </x-filament::button>
            </div>
        </menu>
        <div class="filament-tree dd" id="{{ $containerKey }}">
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

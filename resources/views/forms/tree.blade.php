@php
    $statePath = $getStatePath();
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div {{ 
            $attributes
                ->merge($getExtraAttributes())
                ->class([
                    'filament-forms-tree-component py-2 px-5 border rounded-xl shadow-sm',
                    'bg-white dark:bg-gray-500/10 border-gray-300 dark:border-gray-600',
                ]) 
        }}
        wire:ignore 
        x-data="{

            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},

            checkboxOptions: [],

            expanded: [],

            areAllCheckboxesChecked: false,

            areAllExpanded: false,

            isNodeExpanded: function (key) {
                if (this.areAllExpanded) {
                    return true;
                }

                return Array.from(this.expanded).includes(key);
            },

            toggleNodeExpand: function (key) {

                const expandedKeys = Array.from(this.expanded);

                // If previous state of 'expand_all' button is true, then add other keys into 'collapsed'
                if (this.areAllExpanded === true) {
                    this.expanded = Array.from(this.checkboxOptions.map(checkbox => checkbox?.getAttribute('value'))).filter(value => value != key);
                }
                // Collapse requesting key if already expanded it before
                else if (expandedKeys.includes(key)) {
                    this.expanded = expandedKeys.filter(c => c !== key)
                } 
                // Expand requesting key
                else {
                    this.expanded.push(key);
                }

                // Init state for 'expand_all' button
                this.areAllExpanded = false;
            },

            toggleExpandAll: function (isExpand) {
                this.areAllExpanded = Boolean(isExpand);
                if (this.areAllExpanded) {
                    this.expanded = Array.from(this.checkboxOptions.map(checkbox => checkbox?.getAttribute('value')));
                } else {
                    this.initExpandedState();
                }
            },

            treeNodeCheckboxToggleEvent: function (event) {

                const currentCheckbox = event.target;
                if (!currentCheckbox) return;

                const ctn = currentCheckbox.closest('.filament-forms-tree-component-option-node[data-treenode-group]');
                if (!ctn) return;

                if (currentCheckbox.checked === false && currentCheckbox.indeterminate === false) {

                    const currentKey = ctn.getAttribute('data-treenode');
                    if (currentKey) {
                        // All its children
                        const children = $root.querySelectorAll(`.filament-forms-tree-component-option-node[data-treenode-group='${currentKey}'] input[type=checkbox]`);
                        const checkedChildren = Array.from(children).filter(item => item.checked);
                        const indeterminateChildren = Array.from(children).filter(item => item.indeterminate);

                        // If any children checked/ indeterminate, set currentCheckbox as 'indeterminate'
                        if (checkedChildren.length > 0 || indeterminateChildren.length > 0) {
                            currentCheckbox.indeterminate = true;
                        }
                    }
                }
                
                const parentKey = ctn.getAttribute('data-treenode-group');
                if (parentKey) {
                    const parentCheckbox = $root.querySelector(`.filament-forms-tree-component-option-node[data-treenode='${parentKey}'] input[type=checkbox]`);

                    // Skip set 'Indeterminate' status if parentCheckbox is 'checked' 
                    if (parentCheckbox && parentCheckbox.checked == false)  {

                        // All the parent's children.
                        const siblingsAndSelf = $root.querySelectorAll(`.filament-forms-tree-component-option-node[data-treenode-group='${parentKey}'] input[type=checkbox]`);
                        const checkedSiblingsAndSelf = Array.from(siblingsAndSelf).filter(item => item.checked);
                        const indeterminateSiblingsAndSelf = Array.from(siblingsAndSelf).filter(item => item.indeterminate);

                        let isDispatchParentUpdateEvent = false;
                        // If any siblings checked/indeterminate, set parentCheckbox as 'indeterminate'
                        if (checkedSiblingsAndSelf.length > 0 || indeterminateSiblingsAndSelf.length > 0) {
                            parentCheckbox.indeterminate = true;
                            isDispatchParentUpdateEvent = true;
                        } else {
                            parentCheckbox.indeterminate = false;
                            isDispatchParentUpdateEvent = true;
                        }

                        if (isDispatchParentUpdateEvent) {
                            parentCheckbox.dispatchEvent(new Event('change'))
                        }
                    }
                }
            },

            toggleSelectAllCheckboxes: function () {
                let state = ! this.areAllCheckboxesChecked

                this.checkboxOptions.forEach((checkbox) => {
                    checkbox.checked = state
                    checkbox.indeterminate = false;

                    checkbox.dispatchEvent(new Event('change'))
                })

                this.areAllCheckboxesChecked = state
            },

            initAllCheckboxesAreChecked: function () {
                const allCheckboxes = this.checkboxOptions;

                this.areAllCheckboxesChecked = allCheckboxes.length === allCheckboxes.filter((checkbox) => checkbox.checked == true).length
            },

            initExpandedState: function () {
                this.expanded = [];
            },

            initCheckboxOptions: function () {
                this.checkboxOptions = Array.from($root.querySelectorAll('.filament-forms-tree-component-option-label input[type=checkbox]'));
            },

            init: function () {

                this.$nextTick(() => { 
                    this.initCheckboxOptions();
                    this.initAllCheckboxesAreChecked();
                    this.initExpandedState();
                });

                this.$watch('state', (value) => {
                    this.initAllCheckboxesAreChecked();
                });
            }
        }">

        <div
            x-cloak
            class="flex gap-2 mb-2"
        >
            <x-filament::link
                tag="button"
                size="sm"
                x-on:click="toggleSelectAllCheckboxes()"
            >
                <span x-show="areAllCheckboxesChecked">
                {{ __('filament-tree::filament-tree.components.tree.buttons.deselect_all.label') }}
                </span>
                <span x-show="!areAllCheckboxesChecked">
                    {{ __('filament-tree::filament-tree.components.tree.buttons.select_all.label') }}
                </span>
            </x-filament::link>

            <x-filament::icon-button
                size="sm"
                icon="heroicon-o-plus"
                color="secondary"
                label="{{ __('filament-tree::filament-tree.components.tree.buttons.expand_all.label') }}"
                x-on:click="toggleExpandAll(true)"
                
            />

            <x-filament::icon-button
                size="sm"
                icon="heroicon-o-minus"
                color="secondary"
                label="{{ __('filament-tree::filament-tree.components.tree.buttons.collapse_all.label') }}"
                x-on:click="toggleExpandAll(false)"
            />
        </div>

        
        <div>
            @foreach($getOptions() as $node)
                <x-filament-tree::forms.tree.group :node="$node"/>
            @endforeach
        </div>
    </div>
</x-dynamic-component>

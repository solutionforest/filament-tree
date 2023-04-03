<x-filament-tree::actions.action
    :action="$action"
    component="filament::button"
    :outlined="$isOutlined()"
    :icon-position="$getIconPosition()"
    class="filament-tree-button-action"
>
    {{ $getLabel() }}
</x-filament-tree::actions.action>


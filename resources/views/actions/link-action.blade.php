<x-filament-tree::actions.action
    :action="$action"
    component="filament::link"
    :icon-position="$getIconPosition()"
    class="filament-tree-link-action"
>
    {{ $getLabel() }}
</x-filament-tree::actions.action>

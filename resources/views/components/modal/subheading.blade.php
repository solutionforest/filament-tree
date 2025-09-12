<x-dynamic-component 
    component="filament::modal.description" 
    :attributes="\Filament\Support\prepare_inherited_attributes($attributes)"
>
    {{ $slot }}
</x-dynamic-component>
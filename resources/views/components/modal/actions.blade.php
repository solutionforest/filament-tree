<x-dynamic-component 
    component="filament::actions" 
    :actions="$actions ?? []"
    :alignment="$alignment ?? null"
    :fullWidth="$fullWidth ?? false"
    :attributes="\Filament\Support\prepare_inherited_attributes($attributes)"
>
    {{ $slot }}
</x-dynamic-component>
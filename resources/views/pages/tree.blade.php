@php
    $columns = [
        'default' => 1,
    ];
@endphp
<x-filament::page class="filament-tree-page">
    <x-filament-support::grid 
        :default="$columns['default']"
        class="gap-4"
    >
        <x-filament-support::grid.column>
            {{ $this->tree }}
        </x-filament-support::grid.column>

    </x-filament-support::grid>
</x-filament::page>
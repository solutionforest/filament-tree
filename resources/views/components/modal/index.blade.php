@captureSlots([
    'actions',
    'content',
    'footer',
    'header',
    'heading',
    'subheading',
    'trigger',
])

<x-filament-support::modal
    :attributes="\Filament\Support\prepare_inherited_attributes($attributes)->merge($slots)"
    :dark-mode="config('filament.dark_mode')"
    heading-component="filament-tree::modal.heading"
    {{-- hr-component="tables::hr" --}}
    subheading-component="filament-tree::modal.subheading"
>
    {{ $slot }}
</x-filament-support::modal>

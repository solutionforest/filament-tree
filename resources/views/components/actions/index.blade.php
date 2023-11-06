@props([
    'actions',
    'alignment' => null,
    'record' => null,
    'wrap' => false,
])

@php
    use Filament\Support\Enums\Alignment;

    $actions = array_filter(
        $actions,
        function ($action) use ($record): bool {

            if (! $action instanceof \SolutionForest\FilamentTree\Actions\Modal\Action) {
                $action->record($record);
            }
            
            return $action->isVisible();
        },
    );
@endphp

<div
    {{
        $attributes->class([
            'fi-tree-actions flex shrink-0 items-center gap-3',
            'flex-wrap' => $wrap,
            'sm:flex-nowrap' => $wrap === '-sm',
            match ($alignment) {
                Alignment::Center, 'center' => 'justify-center',
                Alignment::Start, Alignment::Left, 'start', 'left' => 'justify-start',
                'start md:end' => 'justify-start md:justify-end',
                default => 'justify-end',
            },
        ])
    }}
>
    <x-filament-actions::actions :actions="$actions"/>
</div>
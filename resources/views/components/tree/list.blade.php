@props(['records', 'containerKey', 'tree'])
<ol class="filament-tree-list dd-list">
    @foreach ($records ?? [] as $record)
        <x-filament-tree::tree.item :record="$record" :containerKey="$containerKey" :tree="$tree" />
    @endforeach
</ol>
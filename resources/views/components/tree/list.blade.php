@props(['records', 'containerKey', 'tree'])
<ol class="filament-tree-list dd-list">
    @foreach ($records ?? [] as $record)
        @php
            $title = $this->getTreeRecordTitle($record);
            $icon = $this->getTreeRecordIcon($record);
        @endphp
        <x-filament-tree::tree.item 
            :record="$record" 
            :containerKey="$containerKey" 
            :tree="$tree" 
            :title="$title" 
            :icon="$icon"
        />
    @endforeach
</ol>
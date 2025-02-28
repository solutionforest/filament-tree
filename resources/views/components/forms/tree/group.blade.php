@props([
    'groupKey' => null,
    'depth' => 0,
    'node' => [],
])

@php
    $key = $node['id'] ?? null;
    $label = $node['label'] ?? $key;
    $children = $node['children'] ?? [];

    $hasChildren = count($children) > 0;
@endphp

<div role="treeitem">
    <x-filament-tree::forms.tree.item 
        :key="$key"
        :depth="$depth"
        :label="$label"
        :group-key="$groupKey"
        :has-children="$hasChildren"
    />

    @if (count($children) > 0)
        <div class="w-full overflow-hidden" 
            role="group"
            x-show="isNodeExpanded('{{ $key }}')"
        >
            <div 
                class="ml-4"
                role="group"
            >
                @foreach ($children as $index => $item)
                    <x-filament-tree::forms.tree.group
                        :node="$item"
                        :depth="$depth + 1"
                        :group-key="$key"
                    />
                @endforeach
            </div>
        </div>
    @endif
</div>
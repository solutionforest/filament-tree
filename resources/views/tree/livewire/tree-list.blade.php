@php
use \Illuminate\Support\Arr;
/** @var $tree \SolutionForest\FilamentTree\Components\Tree */
@endphp
@php
    $maxDepth = $this->maxDepth ?? 1;
    $depthIndex = 1;
@endphp

<menu class="flex gap-2">
    <div class="btn-group">
        <x-filament::button 
            tag="button"
            x-on:click="$dispatch('expandAll')"
            >
            {{ __('filament-tree::filament-tree.button.expand_all') }}
        </x-filament::button>
        <x-filament::button 
            tag="button"
            x-on:click="$dispatch('collapseAll')"
            >
            {{ __('filament-tree::filament-tree.button.collapse_all') }}
        </x-filament::button>
    </div>
    <div class="btn-group">
        
        <x-filament::button 
            tag="button" 
            wire:loading.attr="disabled"
            wire:loading.class.delay="opacity-70 cursor-wait"
            wire:click="updateTree"
            >
            {{-- x-on:click="$dispatch('updateTree')"> --}}
            <span wire:loading.remove wire:target="updateTree">
                {{ __('filament-tree::filament-tree.button.save') }}
            </span>
            {{-- <span wire:loading>
                <x-filament-support::loading-indicator class="w-4 h-4" />
            </span> --}}
        </x-filament::button>
    </div>
</menu>
<div class="filament-tree dd">
    <x-filament-tree::tree.list wire:sortable="updateTree" :records="$records ?? []" :containerKey="$containerKey" firstDepthOnly="true" />
    
</div>
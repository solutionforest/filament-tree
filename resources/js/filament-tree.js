import treeNestableComponent from './components/filament-tree-component';

document.addEventListener('alpine:init', () => {
    Alpine.data('treeNestableComponent', treeNestableComponent);
});
<?php

namespace SolutionForest\FilamentTree\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Resources\Pages\CreateTreeRecord;
use SolutionForest\FilamentTree\Resources\Pages\EditTreeRecord;
use SolutionForest\FilamentTree\Resources\Pages\ListTreeRecords;
use SolutionForest\FilamentTree\Resources\Pages\ViewTreeRecord;

abstract class TreeResource extends Resource
{
    /**
     * Configure the tree structure
     * This replaces the table() method functionality
     */
    abstract public static function tree(Tree $tree): Tree;

    /**
     * Dummy implementation to satisfy Resource interface
     * This is NEVER called because our custom pages don't use it
     */
    public static function table(Table $table): Table
    {
        // Check if this is being called from a tree page context
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $isFromTreePage = collect($backtrace)->contains(function ($frame) {
            return isset($frame['class']) && 
                   str_contains($frame['class'], 'ListTreeRecords');
        });

        // In development, throw error if accidentally called (but not from tree pages)
        if (config('app.debug') && !$isFromTreePage) {
            throw new \LogicException(
                'TreeResource uses tree() method instead of table(). ' .
                'This method should never be called. ' .
                'If you see this error, the tree pages are not properly configured.'
            );
        }

        // Return empty table to satisfy type system
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    /**
     * Use tree-specific pages instead of standard resource pages
     */
    public static function getPages(): array
    {
        return [
            'index' => ListTreeRecords::route('/'),
            'create' => CreateTreeRecord::route('/create'),
            'edit' => EditTreeRecord::route('/{record}/edit'),
            'view' => ViewTreeRecord::route('/{record}'),
        ];
    }

    /**
     * Get tree actions (replaces table row actions)
     */
    public static function getTreeActions(): array
    {
        return [];
    }

    /**
     * Get tree header actions (create button, etc.)
     */
    public static function getTreeHeaderActions(): array  
    {
        return [];
    }

    /**
     * Get tree bulk actions
     */
    public static function getTreeBulkActions(): array
    {
        return [];
    }

    /**
     * Determine if this resource uses a tree structure
     */
    public static function isTreeResource(): bool
    {
        return true;
    }

    /**
     * Get the tree model's parent column name
     */
    public static function getTreeParentColumn(): string
    {
        return config('filament-tree.column_name.parent', 'parent_id');
    }

    /**
     * Get the tree model's order column name
     */
    public static function getTreeOrderColumn(): string
    {
        return config('filament-tree.column_name.order', 'order');
    }

    /**
     * Get the tree model's title column name
     */
    public static function getTreeTitleColumn(): string
    {
        return config('filament-tree.column_name.title', 'title');
    }

    /**
     * Get the tree model's children key name
     */
    public static function getTreeChildrenKeyName(): string
    {
        return config('filament-tree.default_children_key_name', 'children');
    }

    /**
     * Get the default parent ID for root level items
     */
    public static function getTreeDefaultParentId(): int|string|null
    {
        return config('filament-tree.default_parent_id', -1);
    }
}
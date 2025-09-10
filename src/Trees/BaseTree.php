<?php

namespace SolutionForest\FilamentTree\Trees;

use SolutionForest\FilamentTree\Components\Tree;

abstract class BaseTree
{
    // Action configuration properties with defaults
    protected bool $hasCreateAction = true;
    protected bool $hasAddChildAction = true;
    protected bool $hasEditAction = true;
    protected bool $hasViewAction = true;
    protected bool $hasDeleteAction = true;

    /**
     * Configure the tree structure
     */
    public static function tree(Tree $tree): Tree
    {
        return $tree;
    }

    /**
     * Get tree actions (appears on each tree node) based on configured flags
     */
    public static function getTreeActions(): array
    {
        $instance = new static();
        $actions = [];

        // Check if we're in a resource context
        $isResourceContext = static::isResourceContext();
        
        if ($isResourceContext) {
            $model = static::getResource()::getModel();

            if ($instance->hasAddChildAction()) {
                $actions[] = \SolutionForest\FilamentTree\Actions\Action::make('createChild')
                    ->iconButton()
                    ->icon('heroicon-m-plus')
                    ->tooltip('Add Child')
                    ->url(fn ($record) => static::getResource()::getUrl('create', ['parent_id' => $record->id]))
                    ->model($model);
            }

            if ($instance->hasEditAction()) {
                $actions[] = \SolutionForest\FilamentTree\Actions\Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-m-pencil-square')
                    ->tooltip('Edit')
                    ->url(fn ($record) => static::getResource()::getUrl('edit', ['record' => $record]))
                    ->model($model);
            }

            if ($instance->hasViewAction()) {
                $actions[] = \SolutionForest\FilamentTree\Actions\Action::make('view')
                    ->iconButton()
                    ->icon('heroicon-m-eye')
                    ->tooltip('View')
                    ->url(fn ($record) => static::getResource()::getUrl('view', ['record' => $record]))
                    ->model($model);
            }
        }

        // Delete action works in both contexts (resource and non-resource)
        if ($instance->hasDeleteAction()) {
            $actions[] = \SolutionForest\FilamentTree\Actions\Action::make('delete')
                ->iconButton()
                ->icon('heroicon-m-trash')
                ->tooltip('Delete')
                ->requiresConfirmation()
                ->action(fn ($record) => $record->delete())
                ->model($isResourceContext ? static::getResource()::getModel() : null);
        }

        return $actions;
    }

    /**
     * Get header actions (create root level items) based on configured flags
     */
    public static function getTreeHeaderActions(): array
    {
        $instance = new static();
        $actions = [];

        // Only show create action in resource context
        if ($instance->hasCreateAction() && static::isResourceContext()) {
            $model = static::getResource()::getModel();
            $actions[] = \SolutionForest\FilamentTree\Actions\Action::make('create')
                ->label('Create ' . static::getResource()::getModelLabel())
                ->url(static::getResource()::getUrl('create'))
                ->model($model);
        }

        return $actions;
    }

    /**
     * Get the associated resource class
     * Must be implemented by child classes
     */
    abstract public static function getResource(): string;

    /**
     * Check if we're in a resource context (vs original tree page/widget context)
     */
    protected static function isResourceContext(): bool
    {
        try {
            $resourceClass = static::getResource();
            
            // Check if the resource class exists and has the required methods
            if (!class_exists($resourceClass)) {
                return false;
            }

            // Check if resource has getUrl method (indicating it's a proper Filament resource)
            if (!method_exists($resourceClass, 'getUrl')) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            // If anything goes wrong, assume non-resource context
            return false;
        }
    }

    // Action configuration methods
    protected function hasCreateAction(): bool
    {
        return $this->hasCreateAction;
    }

    protected function hasAddChildAction(): bool
    {
        return $this->hasAddChildAction;
    }

    protected function hasEditAction(): bool
    {
        return $this->hasEditAction;
    }

    protected function hasViewAction(): bool
    {
        return $this->hasViewAction;
    }

    protected function hasDeleteAction(): bool
    {
        return $this->hasDeleteAction;
    }
}
<?php

namespace SolutionForest\FilamentTree\Concern;

use Filament\Actions\Action as FilamentActionsAction;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Component;
use SolutionForest\FilamentTree\Actions;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;
use SolutionForest\FilamentTree\Components\Tree;

trait TreePageTrait
{
    use InteractWithTree;

    // protected string $view = 'filament-tree::pages.tree';

    // protected static string $viewIdentifier = 'tree';

    protected static string $model;

    protected static int $maxDepth = 999;

    // Action configuration properties with defaults
    protected bool $hasCreateAction = true;
    protected bool $hasAddChildAction = false;
    protected bool $hasEditAction = true;
    protected bool $hasViewAction = false;
    protected bool $hasDeleteAction = false;

    public static function tree(Tree $tree): Tree
    {
        return $tree;
    }

    public static function getMaxDepth(): int
    {
        return static::$maxDepth;
    }

    protected function model(string $model): static
    {
        static::$model = $model;

        return $this;
    }

    public function getModel(): string
    {
        return static::$model ?? class_basename(static::class);
    }

    protected function hasCreateAction(): bool
    {
        return $this->hasCreateAction;
    }

    protected function hasAddChildAction(): bool
    {
        return $this->hasAddChildAction;
    }

    protected function hasDeleteAction(): bool
    {
        return $this->hasDeleteAction;
    }

    protected function hasEditAction(): bool
    {
        return $this->hasEditAction;
    }

    protected function hasViewAction(): bool
    {
        return $this->hasViewAction;
    }

    protected function getCreateAction(): CreateAction
    {
        return $this->configureCreateAction(CreateAction::make());
    }

    protected function getAddChildAction(): Actions\Action
    {
        return $this->configureAddChildAction(Actions\Action::make('create_child'));
    }

    protected function getDeleteAction(): DeleteAction
    {
        return DeleteAction::make();
    }

    protected function getEditAction(): EditAction
    {
        return EditAction::make();
    }

    protected function getViewAction(): ViewAction
    {
        return ViewAction::make();
    }

    /**
     * @deprecated Version 3.x.x
     */
    protected function configureAction(FilamentActionsAction $action): void
    {
        match (true) {
            $action instanceof CreateAction => $this->configureCreateAction($action),
            default => null,
        };
    }

    protected function configureTreeAction(Actions\Action $action): void
    {
        match (true) {
            $action instanceof DeleteAction => $this->configureDeleteAction($action),
            $action instanceof EditAction => $this->configureEditAction($action),
            $action instanceof ViewAction => $this->configureViewAction($action),
            $action->getName() === 'create_child' => $this->configureAddChildAction($action),
            default => null,
        };
    }

    protected function configureCreateAction(CreateAction $action): CreateAction
    {
        $action->livewire($this);

        $schema = $this->getCreateFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->schema($schema);

        $action->model($this->getModel());

        $this->afterConfiguredCreateAction($action);

        return $action;
    }

    protected function configureAddChildAction(Actions\Action $action): Actions\Action
    {
        $action->tree($this->getCachedTree());

        $action->iconButton()
            ->icon('heroicon-m-plus')
            ->tooltip('Add Child');

        // Only configure URL navigation if we're in a resource context
        if (method_exists(static::class, 'getResource')) {
            // Configure the action to navigate to create page with parent_id
            $action->url(fn ($record) => static::getResource()::getUrl('create', ['parent_id' => $record->id]));
        } else {
            // For non-resource contexts (original tree pages), use modal forms
            $schema = $this->getCreateFormSchema();
            if (empty($schema)) {
                $schema = $this->getFormSchema();
            }
            
            $action->schema($schema)
                ->model($this->getModel())
                ->livewire($this)
                ->fillForm(function ($record): array {
                    // Pre-fill the form with parent_id set to current record
                    return ['parent_id' => $record->id];
                })
                ->action(function (array $data, $record): void {
                    $modelClass = $this->getModel();
                    
                    // Set parent_id from the current record (the one being acted upon)
                    $data['parent_id'] = $record->id;
                    
                    // Set order to be last among siblings
                    $maxOrder = $modelClass::where('parent_id', $record->id)->max('order') ?? 0;
                    $data['order'] = $maxOrder + 1;
                    
                    // Create the new record
                    $modelClass::create($data);
                    
                    // Refresh the tree
                    $this->dispatch('refreshTree');
                });
        }

        $this->afterConfiguredAddChildAction($action);

        return $action;
    }

    protected function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        $action->tree($this->getCachedTree());

        $action->iconButton();

        $this->afterConfiguredDeleteAction($action);

        return $action;
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        $action->tree($this->getCachedTree());

        $action->iconButton();

        $schema = $this->getEditFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->schema($schema);

        $action->model($this->getModel());

        $action->mutateFormDataBeforeSaveUsing(fn (array $data) => $this->mutateFormDataBeforeSave($data));

        $this->afterConfiguredEditAction($action);

        return $action;
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        $action->tree($this->getCachedTree());

        $action->iconButton();

        $schema = $this->getViewFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->schema($schema);

        $isInfoList = count(array_filter($schema, fn ($component) => $component instanceof Component)) > 0;

        if ($isInfoList) {
            $action->schema($schema);
        }

        $action->model($this->getModel());

        $this->afterConfiguredViewAction($action);

        return $action;
    }

    protected function afterConfiguredCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }

    protected function afterConfiguredAddChildAction(Actions\Action $action): Actions\Action
    {
        return $action;
    }

    protected function afterConfiguredDeleteAction(DeleteAction $action): DeleteAction
    {
        return $action;
    }

    protected function afterConfiguredEditAction(EditAction $action): EditAction
    {
        return $action;
    }

    protected function afterConfiguredViewAction(ViewAction $action): ViewAction
    {
        return $action;
    }

    protected function getFormSchema(): array
    {
        return [];
    }

    protected function getCreateFormSchema(): array
    {
        return [];
    }

    protected function getViewFormSchema(): array
    {
        return [];
    }

    protected function getEditFormSchema(): array
    {
        return [];
    }

    protected function getTreeActions(): array
    {
        return array_merge(
            ($this->hasAddChildAction() ? [$this->getAddChildAction()] : []),
            ($this->hasEditAction() ? [$this->getEditAction()] : []),
            ($this->hasViewAction() ? [$this->getViewAction()] : []),
            ($this->hasDeleteAction() ? [$this->getDeleteAction()] : []),
        );
    }

    protected function getActions(): array
    {
        return array_merge(
            ($this->hasCreateAction() ? [$this->getCreateAction()] : []),
        );
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    // protected function callHook(string $hook): void
    // {
    //     if (! method_exists($this, $hook)) {
    //         return;
    //     }

    //     $this->{$hook}();
    // }
}

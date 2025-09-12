<?php

namespace SolutionForest\FilamentTree\Concern;

use Filament\Actions\Action as FilamentActionsAction;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Component;
use SolutionForest\FilamentTree\Actions\Action;
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
        return true;
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getCreateAction(): CreateAction
    {
        return $this->configureCreateAction(CreateAction::make());
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

    protected function configureTreeAction(Action|FilamentActionsAction $action): void
    {
        match (true) {
            $action instanceof DeleteAction => $this->configureDeleteAction($action),
            $action instanceof EditAction => $this->configureEditAction($action),
            $action instanceof ViewAction => $this->configureViewAction($action),
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

    protected function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        // $action->tree($this->getCachedTree());

        $action->iconButton()->icon(fn () => $action->getGroupedIcon());

        $this->afterConfiguredDeleteAction($action);

        return $action;
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        // $action->tree($this->getCachedTree());

        $action->iconButton()->icon(fn () => $action->getGroupedIcon());

        $schema = $this->getEditFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->schema($schema);

        $action->model($this->getModel());

        // $action->mutateFormDataBeforeSaveUsing(fn (array $data) => $this->mutateFormDataBeforeSave($data));

        $this->afterConfiguredEditAction($action);

        return $action;
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        // $action->tree($this->getCachedTree());

        $action->iconButton()->icon(fn () => $action->getGroupedIcon());

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

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     return $data;
    // }

    // protected function callHook(string $hook): void
    // {
    //     if (! method_exists($this, $hook)) {
    //         return;
    //     }

    //     $this->{$hook}();
    // }
}

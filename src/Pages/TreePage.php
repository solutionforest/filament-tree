<?php

namespace SolutionForest\FilamentTree\Pages;

use Filament\Actions\Action as FilamentActionsAction;
use Filament\Actions\CreateAction;
use Filament\Infolists\Components\Component as InfolistsComponent;
use Filament\Pages\Actions\Action as PagesAction;
use Filament\Pages\Page;
use SolutionForest\FilamentTree\Actions;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Concern\InteractWithTree;
use SolutionForest\FilamentTree\Contract\HasTree;

abstract class TreePage extends Page implements HasTree
{
    use InteractWithTree;

    protected static string $view = 'filament-tree::pages.tree';

    protected static string $viewIdentifier = 'tree';

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
        return CreateAction::make();
    }

    protected function getDeleteAction(): Actions\DeleteAction
    {
        return Actions\DeleteAction::make();
    }

    protected function getEditAction(): Actions\EditAction
    {
        return Actions\EditAction::make();
    }

    protected function getViewAction(): Actions\ViewAction
    {
        return Actions\ViewAction::make();
    }

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
            $action instanceof Actions\DeleteAction => $this->configureDeleteAction($action),
            $action instanceof Actions\EditAction => $this->configureEditAction($action),
            $action instanceof Actions\ViewAction => $this->configureViewAction($action),
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

        $action->form($schema);

        $action->model($this->getModel());

        $this->afterConfiguredCreateAction($action);

        return $action;
    }

    protected function configureDeleteAction(Actions\DeleteAction $action): Actions\DeleteAction
    {
        $action->tree($this->getCachedTree());

        $action->iconButton();

        $this->afterConfiguredDeleteAction($action);

        return $action;
    }

    protected function configureEditAction(Actions\EditAction $action): Actions\EditAction
    {
        $action->tree($this->getCachedTree());

        $action->iconButton();

        $schema = $this->getEditFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($schema);

        $action->model($this->getModel());

        $action->mutateFormDataBeforeSaveUsing(fn (array $data) => $this->mutateFormDataBeforeSave($data));

        $this->afterConfiguredEditAction($action);

        return $action;
    }

    protected function configureViewAction(Actions\ViewAction $action): Actions\ViewAction
    {
        $action->tree($this->getCachedTree());

        $action->iconButton();

        $schema = $this->getViewFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($this->getFormSchema());

        $isInfoList = count(array_filter($schema, fn ($component) => $component instanceof InfolistsComponent)) > 0;

        if ($isInfoList) {
            $action->infolist($schema);
        }

        $action->model($this->getModel());

        $this->afterConfiguredViewAction($action);

        return $action;
    }

    protected function afterConfiguredCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }

    protected function afterConfiguredDeleteAction(Actions\DeleteAction $action): Actions\DeleteAction
    {
        return $action;
    }

    protected function afterConfiguredEditAction(Actions\EditAction $action): Actions\EditAction
    {
        return $action;
    }

    protected function afterConfiguredViewAction(Actions\ViewAction $action): Actions\ViewAction
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function callHook(string $hook): void
    {
        if (! method_exists($this, $hook)) {
            return;
        }

        $this->{$hook}();
    }
}

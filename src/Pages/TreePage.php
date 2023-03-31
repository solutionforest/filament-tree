<?php

namespace SolutionForest\FilamentTree\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use SolutionForest\FilamentTree\Actions;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Concern;
use SolutionForest\FilamentTree\Contract\HasTree;

abstract class TreePage extends Page implements HasTree, HasForms
{
    use Concern\InteractWithTree;

    protected static string $view = 'filament-tree::pages.tree';

    protected static string $viewIdentifier = 'tree';

    protected static string $model;

    protected function tree(Tree $tree): Tree
    {
        return $tree;
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

    protected function actions(): array
    {
        return array_merge(
            ($this->hasEditAction() ? [$this->getEditAction()] : []),
            ($this->hasViewAction() ? [$this->getViewAction()] : []),
            ($this->hasDeleteAction() ? [$this->getDeleteAction()] : []),
        );
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
        return true;
    }

    protected function getDeleteAction()
    {
        return Actions\DeleteAction::make();
    }

    protected function getEditAction()
    {
        return Actions\EditAction::make();
    }

    protected function getViewAction()
    {
        return Actions\ViewAction::make();
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

    protected function configureDeleteAction(Actions\DeleteAction $action): Actions\DeleteAction
    {
        return $action;
    }

    protected function configureEditAction(Actions\EditAction $action): Actions\EditAction
    {
        $schema = $this->getEditFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($schema);

        return $action;
    }

    protected function configureViewAction(Actions\ViewAction $action): Actions\ViewAction
    {
        $schema = $this->getViewFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($schema);

        return $action;
    }

    protected function getFormSchema(): array
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
        return $this->actions();
    }
}

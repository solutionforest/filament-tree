<?php

namespace SolutionForest\FilamentTree\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\Page as BasePage;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;
use SolutionForest\FilamentTree\Concern\TreePageTrait;
use SolutionForest\FilamentTree\Contract\HasTree;

abstract class TreePage extends BasePage implements HasTree
{
    use TreePageTrait {
        TreePageTrait::getViewFormSchema as protected traitGetViewFormSchema;
        TreePageTrait::configureDeleteAction as protected traitConfigureDeleteAction;
        TreePageTrait::configureEditAction as protected traitConfigureEditAction;
        TreePageTrait::configureViewAction as protected traitConfigureViewAction;
        TreePageTrait::configureCreateAction as protected traitConfigureCreateAction;
    }

    protected string $view = 'filament-tree::pages.tree';

    protected function getFormSchema(): array
    {
        return static::getResource()::form(Schema::make($this))->getComponents();
    }

    protected function getViewFormSchema(): array
    {
        $resource = static::getResource();
        try {
            if (method_exists($resource, 'infolist')) {
                $infolistSchema = $resource::infolist(Schema::make($this))->getComponents();
                if ($infolistSchema && count($infolistSchema) > 0) {
                    return $infolistSchema;
                }
            }
        } catch (\Throwable $th) {
            //
        }

        return $this->traitGetViewFormSchema();
    }

    /**
     * @return array<NavigationItem | NavigationGroup>
     */
    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    protected function configureCreateAction(CreateAction $action): CreateAction
    {
        return $this->traitConfigureCreateAction($action)
            ->authorize(static::getResource()::canCreate())
            ->modelLabel($this->getModelLabel());
    }

    protected function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        return $this->traitConfigureDeleteAction($action)
            ->authorize(fn (Model $record): bool => static::getResource()::canDelete($record));
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        return $this->traitConfigureViewAction($action)
            ->authorize(fn (Model $record): bool => static::getResource()::canView($record));
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        return $this->traitConfigureEditAction($action)
            ->authorize(fn (Model $record): bool => static::getResource()::canEdit($record));
    }

    protected function hasDeleteAction(): bool
    {
        return true;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    public function getModel(): string
    {
        return static::getResource()::getModel();
    }
}

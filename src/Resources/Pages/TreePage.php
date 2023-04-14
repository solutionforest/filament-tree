<?php

namespace SolutionForest\FilamentTree\Resources\Pages;

use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\Concerns\UsesResourceForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;
use SolutionForest\FilamentTree\Pages\TreePage as BasePage;

abstract class TreePage extends BasePage
{
    use UsesResourceForm;

    protected static ?string $breadcrumb = null;

    protected static string $resource;

    protected function getFormSchema(): array
    {
        return $this->getResourceForm(columns: 2)->getSchema();
    }

    public static function route(string $path): array
    {
        return [
            'class' => static::class,
            'route' => $path,
        ];
    }

    public function getBreadcrumb(): ?string
    {
        return static::$breadcrumb ?? static::getTitle();
    }

    protected function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [$resource::getUrl() => $resource::getBreadcrumb()],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    public static function authorizeResourceAccess(): void
    {
        abort_unless(static::getResource()::canViewAny(), 403);
    }
    
    protected function configureCreateAction(CreateAction $action): CreateAction
    {
        return parent::configureCreateAction($action)
            ->authorize(static::getResource()::canCreate())
            ->modelLabel($this->getModelLabel());
    }

    protected function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        return parent::configureDeleteAction($action)
            ->authorize(fn (Model $record): bool => static::getResource()::canDelete($record));
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        return parent::configureViewAction($action)
            ->authorize(fn (Model $record): bool => static::getResource()::canView($record));
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        return parent::configureEditAction($action)
            ->authorize(fn (Model $record): bool => static::getResource()::canEdit($record));
    }

    public function getModel(): string
    {
        return static::getResource()::getModel();
    }

    public static function getResource(): string
    {
        return static::$resource;
    }

    protected function getTitle(): string
    {
        return static::$title ?? Str::headline(static::getResource()::getPluralModelLabel());
    }
    
    public function getTableRecordTitle(Model $record): string
    {
        return static::getResource()::getRecordTitle($record);
    }

    public function getModelLabel(): string
    {
        return static::getResource()::getModelLabel();
    }

    public function getPluralModelLabel(): string
    {
        return static::getResource()::getPluralModelLabel();
    }

    public function getTableModelLabel(): string
    {
        return $this->getModelLabel();
    }

    public function getTablePluralModelLabel(): string
    {
        return $this->getPluralModelLabel();
    }

    protected function callHook(string $hook): void
    {
        if (! method_exists($this, $hook)) {
            return;
        }

        $this->{$hook}();
    }
}

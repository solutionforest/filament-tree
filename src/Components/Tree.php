<?php

namespace SolutionForest\FilamentTree\Components;

use Filament\Actions\ActionGroup as FilamentActionsActionGroup;
use Filament\Support\Components\ViewComponent;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Actions\ActionGroup;
use SolutionForest\FilamentTree\Concern\BelongsToLivewire;
use SolutionForest\FilamentTree\Contract\HasTree;
use SolutionForest\FilamentTree\Support\Utils;

class Tree extends ViewComponent
{
    use BelongsToLivewire;

    protected string $view = 'filament-tree::components.tree.index';

    protected string $viewIdentifier = 'tree';

    protected int $maxDepth = 999;

    protected array $actions = [];

    protected array $toolbarActions = [];

    public const LOADING_TARGETS = ['activeLocale'];

    public function __construct(HasTree $livewire)
    {
        $this->livewire($livewire);
    }

    public static function make(HasTree $livewire): static
    {
        $result = app(static::class, ['livewire' => $livewire]);

        $result->configure();

        return $result;
    }

    public function maxDepth(int $maxDepth): static
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function toolbarActions(array $actions): static
    {
        $this->toolbarActions = $actions;

        return $this;
    }

    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getAction($name)
    {
        foreach ($this->actions as $action) {

            if ($action instanceof FilamentActionsActionGroup || $action instanceof ActionGroup) {
                if ($groupedAction = collect($action->getFlatActions())->get($name)) {
                    return $groupedAction;
                } else {
                    continue;
                }
            }

            if ($action->getName() === $name) {
                return $action;
            }
        }

        return null;
    }

    public function getToolbarActions(): array
    {
        return $this->toolbarActions;
    }

    public function getToolbarAction($name)
    {
        foreach ($this->toolbarActions as $action) {

            if ($action instanceof FilamentActionsActionGroup || $action instanceof ActionGroup) {
                if ($groupedAction = collect($action->getFlatActions())->get($name)) {
                    return $groupedAction;
                } else {
                    continue;
                }
            }

            if ($action->getName() === $name) {
                return $action;
            }
        }

        return null;
    }

    public function getModel(): string
    {
        return $this->getLivewire()->getModel();
    }

    public function getRecordKey(?Model $record): ?string
    {
        if (! $record) {
            return null;
        }

        return $record->getAttributeValue($record->getKeyName());
    }

    public function getParentKey(?Model $record): ?string
    {
        if (! $record) {
            return null;
        }

        return $record->getAttributeValue((method_exists($record, 'determineParentKey') ? $record->determineParentColumnName() : Utils::parentColumnName()));
    }
}

<?php

namespace SolutionForest\FilamentTree\Components;

use Filament\Forms\ComponentContainer;
use Filament\Support\Components\ViewComponent;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\BelongsToLivewire;
use SolutionForest\FilamentTree\Contract\HasTree;

class Tree extends ViewComponent
{
    use BelongsToLivewire;

    protected string $view = 'filament-tree::components.tree.index';

    protected string $viewIdentifier = 'tree';

    protected int $maxDepth = 999;

    protected array $actions = [];

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

    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getModel(): string
    {
        return $this->getLivewire()->getModel();
    }

    public function getRecordKey(Model $record): string
    {
        return $record->getAttributeValue($record->getKeyName());
    }

    public function getMountedActionForm(): ?ComponentContainer
    {
        return $this->getLivewire()->getMountedTreeActionForm();
    }
}

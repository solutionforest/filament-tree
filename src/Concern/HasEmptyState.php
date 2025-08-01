<?php

namespace SolutionForest\FilamentTree\Concern;

use Closure;
use Illuminate\Contracts\View\View;
use SolutionForest\FilamentTree\Actions\Action;

trait HasEmptyState
{
    protected array $cachedTreeEmptyStateActions;

    public function cacheTreeEmptyStateActions(): void
    {
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureTreeAction']),
            fn (): array => $this->getTreeEmptyStateActions(),
        );

        $this->cachedTreeEmptyStateActions = [];

        foreach ($actions as $action) {
            $action->tree($this->getCachedTree());

            $this->cachedTreeEmptyStateActions[$action->getName()] = $action;
        }
    }

    public function getCachedTreeEmptyStateActions(): array
    {
        return $this->cachedTreeEmptyStateActions;
    }

    public function getCachedTreeEmptyStateAction(string $name): ?Action
    {
        return $this->getCachedTreeEmptyStateActions()[$name] ?? null;
    }

    protected function getTreeEmptyState(): ?View
    {
        return null;
    }

    protected function getTreeEmptyStateActions(): array
    {
        return [];
    }

    protected function getTreeEmptyStateDescription(): ?string
    {
        return null;
    }

    protected function getTreeEmptyStateHeading(): ?string
    {
        return null;
    }

    protected function getTreeEmptyStateIcon(): ?string
    {
        return null;
    }
}

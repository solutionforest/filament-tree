<?php

namespace SolutionForest\FilamentTree\Concern;

use Filament\Actions\Action as FilamentActionsAction;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Actions\Action;
use SolutionForest\FilamentTree\Actions\ActionGroup;
use SolutionForest\FilamentTree\Contract\HasTree;

trait HasActions
{
    /**
     * @var array<string, Action>
     */
    protected array $cachedTreeToolbarActions = [];

    /**
     * @var array<string, Action>
     */
    protected array $cachedTreeActions = [];

    public function cacheHasActions(): void
    {
        $this->cachedTreeActions = [];
        $this->cachedTreeToolbarActions = [];

        $configuringTreeActions = function ($actions, $extraArgs = []) {

            $configureResolvedAction = function ($action) use ($extraArgs) {

                if (! empty($extraArgs) && method_exists($action, 'arguments')) {
                    $action = $action->arguments($extraArgs);
                }

                // Set tree on action
                if (method_exists($action, 'tree')) {
                    $action = $action->tree($this->getCachedTree());
                }
                // Set livewire on action
                else {
                    $action = $action->livewire($this);
                }

                return $action;
            };

            return collect($actions)
                ->whereInstanceOf([
                    ActionGroup::class,
                    Action::class,
                    FilamentActionsAction::class,
                ])
                ->flatMap(function (ActionGroup|Action|FilamentActionsAction $action) {
                    if ($action instanceof ActionGroup) {
                        return $action->getFlatActions();
                    }

                    return [$action];
                })
                // Configure action
                ->map(function ($action) {
                    $this->configureTreeAction($action);

                    return $action;
                })
                // Key by action name (resolve used)
                ->mapWithKeys(fn (Action|FilamentActionsAction $action) => [
                    $action->getName() => $configureResolvedAction($action),
                ])
                ->all();
        };

        $this->cachedTreeActions = $configuringTreeActions($this->getCachedTree()->getActions());
        $this->cachedTreeToolbarActions = $configuringTreeActions($this->getCachedTree()->getToolbarActions(), ['treeToolbar' => true]);

    }

    protected function resolveAction(array $action, array $parentActions): ?FilamentActionsAction
    {
        if ($this instanceof HasTree) {

            $resolvedAction = null;

            if (
                filled($action['context']['tree'] ?? null) ||
                filled($action['arguments']['treeToolbar'] ?? null)
            ) {

                if (! isset($action['name']) || empty($action['name'])) {
                    throw new ActionNotResolvableException('Action name is not specified.');
                }

                if (($action['arguments']['treeToolbar'] ?? false) === true) {
                    $resolvedAction = $this->cachedTreeToolbarActions[$action['name']] ?? null;
                } else {
                    $resolvedAction = $this->cachedTreeActions[$action['name']] ?? null;
                }

                if ($resolvedAction) {

                    if (filled($action['context']['recordKey'] ?? null)) {
                        $record = $this->getTreeRecord($action['context']['recordKey']);

                        $resolvedAction->getRootGroup()?->record($record) ?? $resolvedAction->record($record);
                    }

                    return $resolvedAction;
                }
            }

        }

        return parent::resolveAction($action, $parentActions);
    }

    protected function configureTreeAction(Action|FilamentActionsAction $action): void {}

    /**
     * @deprecated Use `callMountedAction()` instead.
     */
    public function callMountedTreeAction(?string $arguments = null)
    {
        return $this->callMountedAction($arguments);
    }

    /**
     * @deprecated Version 3.x.x
     */
    public function mountedTreeActionRecord($record): void {}

    public function mountTreeAction(string $name, ?string $record = null, array $arguments = [])
    {
        return $this->mountAction($name, $arguments, context: [
            'tree' => true,
            'recordKey' => $record,
        ]);
    }

    /**
     * @deprecated Use `mountedActionShouldOpenModal()` instead.
     */
    public function mountedTreeActionShouldOpenModal(?Action $mountedAction = null): bool
    {
        return $this->mountedActionShouldOpenModal($mountedAction);
    }

    public function getCachedTreeActions(): array
    {
        return $this->cachedTreeActions;
    }

    public function getCachedTreeToolbarActions(): array
    {
        return $this->cachedTreeToolbarActions;
    }

    /**
     * @deprecated Use `getMountedAction()` instead.
     */
    public function getMountedTreeAction(?int $actionNestingIndex = null): ?Action
    {
        return $this->getMountedAction($actionNestingIndex);
    }

    /**
     * @deprecated Use `mountedActionHasSchema()` instead.
     */
    public function mountedTreeActionHasForm(?Action $mountedAction = null): bool
    {
        return $this->mountedActionHasSchema($mountedAction);
    }

    /**
     * @deprecated Use `($mountedAction = $this->getMountedAction()) ? [$this->getMountedActionSchemaName() => $this->getMountedActionSchema(0, $mountedAction)] : []` instead.
     */
    protected function getHasActionsForms(): array
    {
        return ($mountedAction = $this->getMountedAction()) ? [$this->getMountedActionSchemaName() => $this->getMountedActionSchema(0, $mountedAction)] : [];
    }

    /**
     * @deprecated Use `array_pop($this->mountedActions)` instead.
     */
    protected function popMountedTreeAction(): ?string
    {
        return array_pop($this->mountedActions);
    }

    /**
     * @deprecated Version 3.x.x
     */
    protected function resetMountedTreeActionProperties(): void {}

    /**
     * @deprecated Use `unmountAction()` instead.
     */
    public function unmountTreeAction(bool $shouldCancelParentActions = true): void
    {
        $this->unmountAction($shouldCancelParentActions);
    }

    protected function cacheMountedTreeActionForm(): void {}

    /**
     * @deprecated Use `getMountedActionSchema()` instead.
     */
    protected function getMountedTreeActionForm(?int $actionNestingIndex = null, ?Action $mountedAction = null): ?Schema
    {
        return $this->getMountedActionSchema($actionNestingIndex, $mountedAction);
    }

    /**
     * @deprecated Use `getMountedAction()?->getRecord()?->getKey()` instead.
     */
    public function getMountedTreeActionRecordKey(): int|string|null
    {
        return $this->getMountedAction()?->getRecord()?->getKey() ?? null;
    }

    /**
     * @deprecated Use `getMountedAction()?->getRecord()` instead.
     */
    public function getMountedTreeActionRecord(): ?Model
    {
        return $this->getMountedAction()?->getRecord();
    }

    /**
     * @param  string | array<string>  $name
     */
    public function getCachedTreeAction(string|array $name): ?Action
    {
        if (is_string($name) && str($name)->contains('.')) {
            $name = explode('.', $name);
        }

        if (is_array($name)) {
            $firstName = array_shift($name);

            $name = $firstName;
        }

        return $this->findTreeAction($name)?->record($this->getMountedTreeActionRecord());
    }

    protected function findTreeAction(string $name): ?Action
    {
        $actions = $this->getCachedTreeActions();

        $action = $actions[$name] ?? null;

        if ($action) {
            return $action;
        }

        foreach ($actions as $action) {
            if (! $action instanceof ActionGroup) {
                continue;
            }

            $groupedAction = $action->getActions()[$name] ?? null;

            if (! $groupedAction) {
                continue;
            }

            return $groupedAction;
        }

        return null;
    }

    /**
     * @deprecated Version 3.x.x
     */
    protected function closeTreeActionModal(): void {}

    /**
     * @deprecated Version 3.x.x
     */
    protected function openTreeActionModal(): void {}

    /**
     * Action for each record
     */
    protected function getTreeActions(): array
    {
        return [];
    }

    protected function getTreeToolbarActions(): array
    {
        return [];
    }

    protected function getTreeActionsPosition(): ?string
    {
        return null;
    }
}

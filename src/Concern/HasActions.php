<?php

namespace SolutionForest\FilamentTree\Concern;

use Closure;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Cancel;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Actions\Action;
use SolutionForest\FilamentTree\Actions\ActionGroup;

/**
 * @property Form $mountedTreeActionForm
 */
trait HasActions
{
    /**
     * @var array<string> | null
     */
    public ?array $mountedTreeAction = [];

    /**
     * @var array<string, array<string, mixed>> | null
     */
    public ?array $mountedTreeActionData = [];

    public int | string | null $mountedTreeActionRecord = null;

    protected array $cachedTreeActions;

    protected ?Model $cachedMountedTreeActionRecord = null;

    protected int | string | null $cachedMountedTreeActionRecordKey = null;

    public function cacheTreeActions(): void
    {
        $this->cachedTreeActions = [];

        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureTreeAction']),
            fn (): array => $this->getTreeActions(),
        );

        foreach ($actions as $index => $action) {
            if ($action instanceof ActionGroup) {
                foreach ($action->getActions() as $groupedAction) {
                    $groupedAction->tree($this->getCachedTree());
                }

                $this->cachedTreeActions[$index] = $action;

                continue;
            }

            $action->tree($this->getCachedTree());

            $this->cachedTreeActions[$action->getName()] = $action;
        }
    }

    protected function configureTreeAction(Action $action): void
    {
    }

    public function callMountedTreeAction(?string $arguments = null)
    {
        $action = $this->getMountedTreeAction();

        if (! $action) {
            return null;
        }

        if (filled($this->mountedTreeActionRecord) && ($action->getRecord() === null)) {
            return null;
        }

        if ($action->isDisabled()) {
            return null;
        }

        $action->arguments($arguments ? json_decode($arguments, associative: true) : []);

        $form = $this->getMountedTreeActionForm();

        $result = null;

        try {
            if ($this->mountedTreeActionHasForm()) {
                $action->callBeforeFormValidated();

                $action->formData($form->getState());

                $action->callAfterFormValidated();
            }

            $action->callBefore();

            $result = $action->call([
                'form' => $form,
            ]);

            $result = $action->callAfter() ?? $result;
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
        }

        $action->resetArguments();
        $action->resetFormData();

        $this->unmountTreeAction();

        return $result;
    }

    public function mountedTreeActionRecord($record): void
    {
        $this->mountedTreeActionRecord = $record;
    }

    public function mountTreeAction(string $name, ?string $record = null)
    {
        $this->mountedTreeAction[] = $name;
        $this->mountedTreeActionData[] = [];

        if (count($this->mountedTreeAction) === 1) {
            $this->mountedTreeActionRecord($record);
        }

        $action = $this->getMountedTreeAction();

        if (! $action) {
            $this->unmountTreeAction();

            return null;
        }

        if (filled($record) && ($action->getRecord() === null)) {
            return;
        }

        if ($action->isDisabled()) {
            return;
        }

        $this->cacheMountedTreeActionForm();

        try {
            $hasForm = $this->mountedTreeActionHasForm();

            if ($hasForm) {
                $action->callBeforeFormFilled();
            }

            $action->mount([
                'form' => $this->getMountedTreeActionForm(),
            ]);

            if ($hasForm) {
                $action->callAfterFormFilled();
            }
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
            $this->unmountTreeAction(shouldCancelParentActions: false);

            return null;
        }

        if (! $this->mountedTreeActionShouldOpenModal()) {
            return $this->callMountedTreeAction();
        }

        $this->resetErrorBag();

        $this->openTreeActionModal();

        return null;
    }

    public function mountedTreeActionShouldOpenModal(): bool
    {
        $action = $this->getMountedTreeAction();

        if ($action->isModalHidden()) {
            return false;
        }

        return $action->getModalDescription() ||
            $action->getModalContent() ||
            $action->getModalContentFooter() ||
            $action->getInfolist() ||
            $this->mountedTreeActionHasForm();
    }

    public function getCachedTreeActions(): array
    {
        return $this->cachedTreeActions;
    }

    public function getMountedTreeAction(): ?Action
    {
        if (! count($this->mountedTreeAction ?? [])) {
            return null;
        }

        return $this->getCachedTreeAction($this->mountedTreeAction) ?? $this->getCachedTreeEmptyStateAction($this->mountedTreeAction);
    }

    public function mountedTreeActionHasForm(): bool
    {
        return (bool) count($this->getMountedTreeActionForm()?->getComponents() ?? []);
    }

    protected function getHasActionsForms(): array
    {
        return [
            'mountedTreeActionData' => $this->getMountedTreeActionForm(),
        ];
    }

    protected function popMountedTreeAction(): ?string
    {
        try {
            return array_pop($this->mountedTreeAction);
        } finally {
            array_pop($this->mountedTreeActionData);
        }
    }

    protected function resetMountedTreeActionProperties(): void
    {
        $this->mountedTreeAction = [];
        $this->mountedTreeActionData = [];
    }

    public function unmountTreeAction(bool $shouldCancelParentActions = true): void
    {
        $action = $this->getMountedTreeAction();

        if (! ($shouldCancelParentActions && $action)) {
            $this->popMountedTreeAction();
        } elseif ($action->shouldCancelAllParentActions()) {
            $this->resetMountedTreeActionProperties();
        } else {
            $parentActionToCancelTo = $action->getParentActionToCancelTo();

            while (true) {
                $recentlyClosedParentAction = $this->popMountedTreeAction();

                if (
                    blank($parentActionToCancelTo) ||
                    ($recentlyClosedParentAction === $parentActionToCancelTo)
                ) {
                    break;
                }
            }
        }

        if (! count($this->mountedTreeAction)) {
            $this->closeTreeActionModal();

            $action?->record(null);
            $this->mountedTreeActionRecord(null);

            return;
        }

        $this->cacheMountedTreeActionForm();

        $this->resetErrorBag();

        $this->openTreeActionModal();
    }

    protected function cacheMountedTreeActionForm(): void
    {
        $this->cacheForm(
            'mountedTreeActionForm',
            fn () => $this->getMountedTreeActionForm(),
        );
    }

    public function getMountedTreeActionForm()
    {
        $action = $this->getMountedTreeAction();

        if (! $action) {
            return null;
        }

        if ((! $this->isCachingForms) && $this->hasCachedForm('mountedTreeActionForm')) {
            return $this->getCachedForm('mountedTreeActionForm');
        }

        return $action->getForm(
            $this->makeForm()
                ->model($this->getMountedTreeActionRecord() ?? $this->getTreeQuery()->getModel()::class)
                ->statePath('mountedTreeActionData.' . array_key_last($this->mountedTreeActionData))
                ->operation(implode('.', $this->mountedTreeAction)),
        );
    }

    public function getMountedTreeActionRecordKey(): int | string | null
    {
        return $this->mountedTreeActionRecord;
    }

    public function getMountedTreeActionRecord(): ?Model
    {
        $recordKey = $this->getMountedTreeActionRecordKey();

        if ($this->cachedMountedTreeActionRecord && ($this->cachedMountedTreeActionRecordKey === $recordKey)) {
            return $this->cachedMountedTreeActionRecord;
        }

        $this->cachedMountedTreeActionRecordKey = $recordKey;

        return $this->cachedMountedTreeActionRecord = $this->getTreeRecord($recordKey);
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

    protected function closeTreeActionModal(): void
    {
        $this->dispatch('close-modal', id: "{$this->getId()}-tree-action");
    }

    protected function openTreeActionModal(): void
    {
        $this->dispatch('open-modal', id: "{$this->getId()}-tree-action");
    }

    /**
     * Action for each record
     */
    protected function getTreeActions(): array
    {
        return [];
    }

    protected function getTreeActionsPosition(): ?string
    {
        return null;
    }
}

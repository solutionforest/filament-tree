<?php

namespace SolutionForest\FilamentTree\Concern;

use Closure;
use Filament\Support\Exceptions\Cancel;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Actions\Action;
use SolutionForest\FilamentTree\Actions\ActionGroup;

trait HasActions
{
    public $mountedTreeAction = null;

    public $mountedTreeActionData = [];

    public $mountedTreeActionRecord = null;

    protected array $cachedTreeActions;

    protected ?Model $cachedMountedTreeActionRecord = null;

    protected $cachedMountedTreeActionRecordKey = null;

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
            return;
        }

        if (filled($this->mountedTreeActionRecord) && ($action->getRecord() === null)) {
            return;
        }

        if ($action->isDisabled()) {
            return;
        }

        $action->arguments($arguments ? json_decode($arguments, associative: true) : []);

        $form = $this->getMountedTreeActionForm();

        $result = null;

        try {
            if ($action->hasForm()) {
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
            return;
        } catch (Cancel $exception) {
        }

        if (filled($this->redirectTo)) {
            return $result;
        }

        $this->mountedTreeAction = null;

        $action->record(null);
        $this->mountedTreeActionRecord(null);

        $action->resetArguments();
        $action->resetFormData();

        $this->dispatchBrowserEvent('close-modal', [
            'id' => "{$this->id}-tree-action",
        ]);

        return $result;
    }

    public function mountedTreeActionRecord($record): void
    {
        $this->mountedTreeActionRecord = $record;
    }

    public function mountTreeAction(string $name, ?string $record = null)
    {
        $this->mountedTreeAction = $name;
        $this->mountedTreeActionRecord($record);

        $action = $this->getMountedTreeAction();

        if (! $action) {
            return;
        }

        if (filled($record) && ($action->getRecord() === null)) {
            return;
        }

        if ($action->isDisabled()) {
            return;
        }

        $this->cacheForm(
            'mountedTreeActionForm',
            fn () => $this->getMountedTreeActionForm(),
        );

        try {
            if ($action->hasForm()) {
                $action->callBeforeFormFilled();
            }

            $action->mount([
                'form' => $this->getMountedTreeActionForm(),
            ]);

            if ($action->hasForm()) {
                $action->callAfterFormFilled();
            }
        } catch (Halt $exception) {
            return;
        } catch (Cancel $exception) {
            $this->mountedTreeAction = null;
            $this->mountedTreeActionRecord(null);

            return;
        }

        if (! $action->shouldOpenModal()) {
            return $this->callMountedTreeAction();
        }

        $this->resetErrorBag();

        $this->dispatchBrowserEvent('open-modal', [
            'id' => "{$this->id}-tree-action",
        ]);
    }

    public function getCachedTreeActions(): array
    {
        return $this->cachedTreeActions;
    }

    public function getMountedTreeAction(): ?Action
    {
        if (! $this->mountedTreeAction) {
            return null;
        }

        return $this->getCachedTreeAction($this->mountedTreeAction) ?? $this->getCachedTreeEmptyStateAction($this->mountedTreeAction);
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

        return $this->makeForm()
            ->schema($action->getFormSchema())
            ->model($this->getMountedTreeActionRecord() ?? $this->getTreeQuery()->getModel()::class)
            ->statePath('mountedTreeActionData')
            ->context($this->mountedTreeAction);
    }

    public function getMountedTreeActionRecordKey()
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

    public function getCachedTreeAction(string $name): ?Action
    {
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

    protected function getTreeActions(): array
    {
        return [];
    }

    protected function getTreeActionsPosition(): ?string
    {
        return null;
    }
}

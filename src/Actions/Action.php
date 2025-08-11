<?php

namespace SolutionForest\FilamentTree\Actions;

use Filament\Actions\Action as BaseAction;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\Actions\HasTree;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

class Action extends BaseAction implements HasTree
{
    use BelongsToTree;

    public function getLivewireClickHandler(): ?string
    {
        if (! $this->isLivewireClickHandlerEnabled()) {
            return null;
        }

        if (is_string($this->action)) {
            return $this->action;
        }

        if ($record = $this->getRecord()) {
            $recordKey = $this->getLivewire()->getRecordKey($record);

            return "mountTreeAction('{$this->getName()}', '{$recordKey}')";
        }

        return "mountTreeAction('{$this->getName()}')";
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'tree' => [$this->getTree()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    public function getRecordTitle(?Model $record = null): string
    {
        $record ??= $this->getRecord();

        return $this->getCustomRecordTitle($record) ?? $this->getLivewire()->getTreeRecordTitle($record);
    }

    public function getRecordTitleAttribute(): ?string
    {
        return $this->getCustomRecordTitleAttribute() ?? $this->getTree()->getRecordTitleAttribute();
    }

    public function getModelLabel(): string
    {
        return $this->getCustomModelLabel() ?? $this->getTree()->getModelLabel();
    }

    public function getPluralModelLabel(): string
    {
        return $this->getCustomPluralModelLabel() ?? $this->getTree()->getPluralModelLabel();
    }

    public function getModel(bool $withDefault = true): string
    {
        return $this->getCustomModel() ?? $this->getLivewire()->getModel();
    }

    public function prepareModalAction(BaseAction $action): BaseAction
    {
        $action = parent::prepareModalAction($action);

        if (! $action instanceof Action) {
            return $action;
        }

        return $action
            ->tree($this->getTree())
            ->record($this->getRecord());
    }
}

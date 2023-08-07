<?php

namespace SolutionForest\FilamentTree\Actions;


use Filament\Actions\Concerns\HasMountableArguments;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\Groupable;
use Filament\Actions\Contracts\HasRecord;
use Filament\Actions\MountableAction as BaseAction;
use Filament\Actions\StaticAction;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\Actions\HasTree;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

class Action extends BaseAction implements Groupable, HasRecord, HasTree
{
    use BelongsToTree;
    use HasMountableArguments;
    use InteractsWithRecord;

    public const BUTTON_VIEW = 'filament-tree::actions.button-action';

    public const GROUPED_VIEW = 'filament-tree::actions.grouped-action';

    public const ICON_BUTTON_VIEW = 'filament-tree::actions.icon-button-action';

    public const LINK_VIEW = 'filament-tree::actions.link-action';

    public function getLivewireCallMountedActionName(): string
    {
        return 'callMountedTreeAction';
    }

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
            'model' => [$this->getModel()],
            'record' => [$this->getRecord()],
            'tree' => [$this->getTree()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        $record = $this->getRecord();

        if (! $record) {
            return parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
        }

        return match ($parameterType) {
            Model::class, $record::class => [$record],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
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

    public function getModel(): string
    {
        return $this->getCustomModel() ?? $this->getLivewire()->getModel();
    }

    public function prepareModalAction(StaticAction $action): StaticAction
    {
        $action = parent::prepareModalAction($action);

        if (! $action instanceof Action) {
            return $action;
        }

        return $action
            ->tree($this->getTree())
            ->record($this->getRecord());
    }

    protected function getDefaultEvaluationParameters(): array
    {
        return collect(['record', 'model', 'tree'])
            ->flip()
            ->map(fn ($v, $name) => $this->resolveDefaultClosureDependencyForEvaluationByName($name)[0] ?? null)
            ->toArray();
    }
}

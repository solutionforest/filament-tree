<?php

namespace SolutionForest\FilamentTree\Concern\Actions;

use Filament\Actions\Action as BaseAction;
use Illuminate\Support\Js;
use SolutionForest\FilamentTree\Actions\Action;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

trait TreeActionTrait
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

        $arguments = Js::from($this->getArguments());

        if ($record = $this->getRecord()) {
            $recordKey = $this->getLivewire()->getRecordKey($record);

            return "mountTreeAction('{$this->getName()}', '{$recordKey}', {$arguments})";
        }

        return "mountTreeAction('{$this->getName()}', null, {$arguments})";
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

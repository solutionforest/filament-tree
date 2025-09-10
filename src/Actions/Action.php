<?php

namespace SolutionForest\FilamentTree\Actions;

use Closure;
use Filament\Actions\Action as BaseAction;
use Filament\Actions\Concerns\CanBeAuthorized;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\Actions\HasTree;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

class Action extends BaseAction implements HasTree
{
    use BelongsToTree;
    use CanBeAuthorized;
    
    /**
     * Override tree method to configure policy authorization after tree is set
     */
    public function tree(\SolutionForest\FilamentTree\Components\Tree $tree): static
    {
        $this->tree = $tree;
        
        // Now that tree is set, configure policy authorization
        $this->configureAutomaticPolicyAuthorization();
        
        return $this;
    }
    
    // NEW: Hook system for ALL tree actions
    protected ?Closure $beforeActionHook = null;
    protected ?Closure $afterActionHook = null;
    protected ?Closure $mutateRecordUsing = null;
    protected ?Closure $mutateFormDataUsing = null;

    // Custom model and label properties
    protected Closure|string|null $customModel = null;
    protected Closure|string|null $customModelLabel = null;
    protected Closure|string|null $customPluralModelLabel = null;
    protected Closure|string|null $customRecordTitle = null;
    protected Closure|string|null $customRecordTitleAttribute = null;

    /**
     * Override to ensure complete invisibility when unauthorized
     * Not just disabled or CSS hidden - completely not rendered
     */
    public function isVisible(): bool
    {
        // Skip authorization if tree isn't initialized yet (during header action filtering)
        if (!$this->getTree()) {
            return parent::isVisible();
        }

        // Check parent visibility first
        if (! parent::isVisible()) {
            return false;
        }

        // If policy authorization is disabled, use standard visibility
        if (! config('filament-tree.enable_policy_authorization', false)) {
            return true;
        }

        // Check policy authorization - action won't render if unauthorized
        return $this->isAuthorized();
    }

    /**
     * Automatically configure policy authorization based on action name
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Only auto-configure if feature is enabled
        if (! config('filament-tree.enable_policy_authorization', false)) {
            return;
        }

        $this->configureAutomaticPolicyAuthorization();
    }

    /**
     * Map action names to policy abilities using config
     */
    protected function configureAutomaticPolicyAuthorization(): void
    {
        // Skip if tree is not initialized yet
        if (!$this->getTree()) {
            return;
        }

        $actionName = $this->getName();
        $policyAbilities = config('filament-tree.policy_abilities', []);

        if (isset($policyAbilities[$actionName])) {
            $ability = $policyAbilities[$actionName];
            
            // For create actions, we need the model class, not a record
            if (in_array($actionName, ['create', 'createChild'])) {
                $model = $this->getModel();
                if ($model) {
                    $this->authorize($ability, $model);
                }
            } else {
                // For other actions, use the record
                $this->authorize($ability);
            }
        }
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
            'tree' => [$this->getTree()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    // Custom property getters and setters
    public function model(Closure|string|null $model): static
    {
        $this->customModel = $model;
        return $this;
    }

    public function getCustomModel(): ?string
    {
        if ($this->customModel instanceof Closure) {
            return $this->evaluate($this->customModel);
        }
        
        return $this->customModel;
    }

    public function modelLabel(Closure|string|null $label): static
    {
        $this->customModelLabel = $label;
        return $this;
    }

    public function getCustomModelLabel(): ?string
    {
        if ($this->customModelLabel instanceof Closure) {
            return $this->evaluate($this->customModelLabel);
        }
        
        return $this->customModelLabel;
    }

    public function pluralModelLabel(Closure|string|null $label): static
    {
        $this->customPluralModelLabel = $label;
        return $this;
    }

    public function getCustomPluralModelLabel(): ?string
    {
        if ($this->customPluralModelLabel instanceof Closure) {
            return $this->evaluate($this->customPluralModelLabel);
        }
        
        return $this->customPluralModelLabel;
    }

    public function recordTitle(Closure|string|null $title): static
    {
        $this->customRecordTitle = $title;
        return $this;
    }

    public function getCustomRecordTitle($record = null): ?string
    {
        if ($this->customRecordTitle instanceof Closure) {
            return $this->evaluate($this->customRecordTitle, ['record' => $record]);
        }
        
        return $this->customRecordTitle;
    }

    public function recordTitleAttribute(Closure|string|null $attribute): static
    {
        $this->customRecordTitleAttribute = $attribute;
        return $this;
    }

    public function getCustomRecordTitleAttribute(): ?string
    {
        if ($this->customRecordTitleAttribute instanceof Closure) {
            return $this->evaluate($this->customRecordTitleAttribute);
        }
        
        return $this->customRecordTitleAttribute;
    }

    public function getRecordTitle(?Model $record = null): string
    {
        $record ??= $this->getRecord();
        $livewire = $this->getLivewire();

        return $this->getCustomRecordTitle($record) ?? ($livewire ? $livewire->getTreeRecordTitle($record) : '');
    }

    public function getRecordTitleAttribute(): ?string
    {
        if ($customAttribute = $this->getCustomRecordTitleAttribute()) {
            return $customAttribute;
        }
        
        $livewire = $this->getLivewire();
        if ($livewire && method_exists($livewire, 'getResource')) {
            $resource = $livewire->getResource();
            if ($resource && method_exists($resource, 'getRecordTitleAttribute')) {
                return $resource::getRecordTitleAttribute();
            }
        }
        
        return 'name';
    }

    public function getModelLabel(): string
    {
        if ($customLabel = $this->getCustomModelLabel()) {
            return $customLabel;
        }
        
        $livewire = $this->getLivewire();
        if ($livewire && method_exists($livewire, 'getResource')) {
            $resource = $livewire->getResource();
            if ($resource && method_exists($resource, 'getModelLabel')) {
                return $resource::getModelLabel();
            }
        }
        
        $model = $this->getModel();
        if ($model) {
            return class_basename($model);
        }
        
        return 'Record';
    }

    public function getPluralModelLabel(): string
    {
        if ($customLabel = $this->getCustomPluralModelLabel()) {
            return $customLabel;
        }
        
        $livewire = $this->getLivewire();
        if ($livewire && method_exists($livewire, 'getResource')) {
            $resource = $livewire->getResource();
            if ($resource && method_exists($resource, 'getPluralModelLabel')) {
                return $resource::getPluralModelLabel();
            }
        }
        
        return $this->getModelLabel() . 's';
    }

    public function getModel(bool $withDefault = true): ?string
    {
        $livewire = $this->getLivewire();
        if (!$livewire) {
            return null;
        }
        
        return $this->getCustomModel() ?? $livewire->getModel();
    }

    public function prepareModalAction(BaseAction $action): BaseAction
    {
        $action = parent::prepareModalAction($action);

        if (! $action instanceof Action) {
            return $action;
        }

        $tree = $this->getTree();
        if ($tree) {
            $action = $action->tree($tree);
        }
        
        return $action->record($this->getRecord());
    }

    /**
     * Execute before the action runs
     * Useful for preparing data, setting defaults, validation
     */
    public function beforeAction(?Closure $callback): static
    {
        $this->beforeActionHook = $callback;
        return $this;
    }
    
    /**
     * Execute after the action completes
     * Useful for cleanup, logging, side effects
     */
    public function afterAction(?Closure $callback): static
    {
        $this->afterActionHook = $callback;
        return $this;
    }
    
    /**
     * Mutate the record before using it in the action
     * Useful for setting relationships, defaults, etc.
     */
    public function mutateRecordUsing(?Closure $callback): static
    {
        $this->mutateRecordUsing = $callback;
        return $this;
    }
    
    /**
     * Mutate form data before processing
     * Useful for adding parent IDs, calculated fields, etc.
     */
    public function mutateFormDataUsing(?Closure $callback): static
    {
        $this->mutateFormDataUsing = $callback;
        return $this;
    }
    
    /**
     * Execute the before action hook if set
     */
    protected function callBeforeActionHook(Model $record = null, array $data = []): array
    {
        if ($this->beforeActionHook) {
            $result = $this->evaluate($this->beforeActionHook, [
                'record' => $record,
                'data' => $data,
                'tree' => $this->getTree(),
            ]);
            
            return is_array($result) ? $result : $data;
        }
        
        return $data;
    }
    
    /**
     * Execute the after action hook if set
     */
    protected function callAfterActionHook(Model $record = null, array $data = [], Model $result = null): ?Model
    {
        if ($this->afterActionHook) {
            $hookResult = $this->evaluate($this->afterActionHook, [
                'record' => $record,
                'data' => $data,
                'result' => $result,
                'tree' => $this->getTree(),
            ]);
            
            return $hookResult instanceof Model ? $hookResult : $result;
        }
        
        return $result;
    }
    
    /**
     * Mutate record if callback is set
     */
    protected function getMutatedRecord(Model $record = null): ?Model
    {
        if ($this->mutateRecordUsing && $record) {
            $result = $this->evaluate($this->mutateRecordUsing, [
                'record' => $record,
                'tree' => $this->getTree(),
            ]);
            
            return $result instanceof Model ? $result : $record;
        }
        
        return $record;
    }
    
    /**
     * Mutate form data if callback is set
     */
    protected function getMutatedFormData(array $data = []): array
    {
        if ($this->mutateFormDataUsing) {
            $result = $this->evaluate($this->mutateFormDataUsing, [
                'data' => $data,
                'record' => $this->getRecord(),
                'tree' => $this->getTree(),
            ]);
            
            return is_array($result) ? $result : $data;
        }
        
        return $data;
    }
}

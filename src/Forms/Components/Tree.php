<?php

namespace SolutionForest\FilamentTree\Forms\Components;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Concerns\BelongsToModel;
use Filament\Forms\Components\Concerns\HasState;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class Tree extends Field
{
    use BelongsToModel;
    use HasState;

    protected string $view = 'filament-tree::forms.tree';

    protected ?array $nodes = null;

    protected string | Closure | null $keyColumn = null;

    protected string | Closure | null $titleColumn = null;

    protected string | Closure | null $childrenColumn = null;

    protected ?Collection $cachedExistingRecords = null;

    protected string | Closure | null $relationship = null;

    protected ?Closure $modifyRelationshipQueryUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(static function (Tree $component, $state) {
            if (is_array($state)) {
                return;
            }

            $component->state([]);
        });

        $this->dehydrateStateUsing(static function (Tree $component, $state) {
            if (! is_array($state)) {
                $state = [];
            }
            return $component->formatNodeState($state, $component->getOptions());
        });
    }

    public function getState()
    {
        $state = parent::getState();

        if (is_array($state)) {
            return $this->getNodeState($state);
        } else {
            try {
                return json_decode($state);
            } catch (\Exception $e) {
                return [];
            }
        }
    }

    public function getNodes(): array
    {
        return $this->nodes ?? [];
    }

    public function getKeyColumn(): ?string
    {
        return $this->evaluate($this->keyColumn);
    }

    public function getTitleColumn(): ?string
    {
        return $this->evaluate($this->titleColumn);
    }

    public function getChildrenColumn(): ?string
    {
        return $this->evaluate($this->childrenColumn);
    }

    public function getNodeLabel(string $uuid): ?string
    {
        return data_get($this->getChildComponentContainer($uuid)->getRawState(), $this->getTitleColumn() ?? 'title');
    }

    public function getOptions(): array
    {
        return $this->getNodeOptions($this->getNodes());
    }

    public function getRelationship(): BelongsToMany | null
    {
        if (! $this->hasRelationship()) {
            return null;
        }

        return $this->getModelInstance()->{$this->getRelationshipName()}();
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    public function getCachedExistingRecords(): Collection
    {
        if ($this->cachedExistingRecords) {
            return $this->cachedExistingRecords;
        }

        $relationship = $this->getRelationship();
        $relationshipQuery = $relationship->getQuery();

        if ($relationship instanceof BelongsToMany) {
            $relationshipQuery->select([
                $relationship->getTable() . '.*',
                $relationshipQuery->getModel()->getTable() . '.*',
            ]);
        }

        if ($this->modifyRelationshipQueryUsing) {
            $relationshipQuery = $this->evaluate($this->modifyRelationshipQueryUsing, [
                'query' => $relationshipQuery,
            ]) ?? $relationshipQuery;
        }

        $relatedKeyName = $relationship->getRelated()->getKeyName();

        return $this->cachedExistingRecords = $relationshipQuery->get()->mapWithKeys(
            fn (Model $item): array => [strval($item[$relatedKeyName]) => $item],
        );
    }

    public function nodes(array|Arrayable $nodes): static
    {
        if ($nodes instanceof Arrayable) {
            $this->nodes = $nodes->toArray();
        } else {
            $this->nodes = $nodes;
        }

        return $this;
    }

    public function keyColumn(string $keyColumn): static
    {
        $this->keyColumn = $keyColumn;

        return $this;
    }

    public function titleColumn(string $titleColumn): static
    {
        $this->titleColumn = $titleColumn;

        return $this;
    }

    public function childrenColumn(string $childrenColumn): static
    {
        $this->childrenColumn = $childrenColumn;

        return $this;
    }

    public function relationship(string | Closure $relationshipName, ?Closure $callback = null): static
    {
        $this->relationship = $relationshipName;
        $this->modifyRelationshipQueryUsing = $callback;

        $this->afterStateHydrated(null);

        $this->loadStateFromRelationshipsUsing(static function (Tree $component, $state): void {
            $component->clearCachedExistingRecords();

            $component->fillFromRelationship();
        });

        $this->saveRelationshipsUsing(static function (Tree $component, Model $record, $state) {
            if (! is_array($state)) {
                $state = [];
            }

            $relationship = $component->getRelationship();

            $existingRecords = $component->getCachedExistingRecords();
            $existingRecordKeys = $existingRecords->pluck($relationship->getRelated()->getKeyName())->toArray();

            $recordsToDetach = collect($existingRecordKeys)->filter(fn ($keyToDetach) => !in_array($keyToDetach, $state));

            $recordsToAttach = collect($state)->filter(fn ($keyToAttach) => !in_array($keyToAttach, $existingRecordKeys));

            if ($relationship instanceof BelongsToMany) {
                $relationship->detach($recordsToDetach);
                $relationship->attach($recordsToAttach);
            } 
        });

        return $this;
    }

    public function clearCachedExistingRecords(): void
    {
        $this->cachedExistingRecords = null;
    }

    protected function getRelatedModel(): string
    {
        return $this->getRelationship()->getModel()::class;
    }

    public function hasRelationship(): bool
    {
        return filled($this->getRelationshipName());
    }

    public function fillFromRelationship(): void
    {
        $this->state(
            array_keys($this->getStateFromRelatedRecords($this->getCachedExistingRecords())),
        );
    }

    protected function getStateFromRelatedRecords(Collection $records): array
    {
        if (! $records->count()) {
            return [];
        }

        $activeLocale = $this->getLivewire()->getActiveFormLocale();

        return $records
            ->map(function (Model $record) use ($activeLocale): array {
                $state = $record->attributesToArray();

                if ($activeLocale && method_exists($record, 'getTranslatableAttributes') && method_exists($record, 'getTranslation')) {
                    foreach ($record->getTranslatableAttributes() as $attribute) {
                        $state[$attribute] = $record->getTranslation($attribute, $activeLocale);
                    }
                }

                return $state;
            })
            ->toArray();
    }

    private function getNodeOptions(array $options): array
    {
        return collect($options)
            ->keyBy(fn ($item) => strval(data_get($item, $this->getKeyColumn() ?? 'id')))
            ->map(fn (array $item) => [
                'label' => data_get($item, $this->getTitleColumn() ?? 'title'),
                'children' => $this->getNodeOptions(data_get($item, $this->getChildrenColumn() ?? 'children') ?? []),
            ])
            ->toArray();
    }

    private function getNodeState(array $state): array
    {
        $final = [];

        foreach ($state as $key => $childOrKey) {
            if (is_array($childOrKey)) {
                $final = array_merge($final, [$key], $this->getNodeState($childOrKey));
            } else {
                $final = array_merge($final, [$childOrKey]);
            }
        }

        return $final;
    }

    private function formatNodeState(array $state, array $options): array
    {
        $final = [];

        foreach ($options as $value => $item) {
            if (in_array($value, $state)) {
                $final[strval($value)] = $this->formatNodeState($state, data_get($item, 'children', []));
            }
        }

        return $final;
    }
}

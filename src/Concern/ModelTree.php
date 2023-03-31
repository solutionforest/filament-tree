<?php

namespace SolutionForest\FilamentTree\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Support\Utils;

trait ModelTree
{

    /**
     * Boot the bootable traits on the model.
     */
    public static function bootModelTree()
    {
        static::saving(function(Model $model) {
            if (empty($model->{$model->determineParentColumnName()}) || $model->{$model->determineParentColumnName()} === 0) {
                $model->{$model->determineParentColumnName()} = Utils::defaultParentId();
            }
        });

        static::saved(function(Model $model) {
            // TODO: Clear cache
            \Illuminate\Support\Facades\Cache::clear();
        });

        // Delete children
        static::deleting(function (Model $model) {
            static::buildSortQuery()
                ->where($model->determineParentColumnName(), $model->getKey())
                ->get()
                ->each
                ->delete();
        });
    }

    public function children()
    {
        return $this->hasMany(static::class, $this->determineParentColumnName())->with('children')->orderBy($this->determineOrderColumnName());
    }

    public function isRoot(): bool
    {
        return $this->getAttributeValue($this->determineParentColumnName()) === Utils::defaultParentId();
    }

    public function setHighestOrderNumber(): void
    {
        $this->{$this->determineOrderColumnName()} = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->where($this->determineParentColumnName(), $this->{$this->determineParentColumnName()})->max($this->determineOrderColumnName());
    }

    public function getLowestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->where($this->determineParentColumnName(), $this->{$this->determineParentColumnName()})->min($this->determineOrderColumnName());
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy($this->determineParentColumnName(), 'asc')->orderBy($this->determineOrderColumnName(), $direction);
    }

    public function determineOrderColumnName() : string
    {
        return Utils::orderColumnName();
    }

    public function determineParentColumnName() : string
    {
        return Utils::parentColumnName();
    }

    /**
     * Format all nodes as tree.
     * 
     * @param array|\Illuminate\Support\Collection|null $nodes
     */
    public function toTree($nodes = null): array
    {
        if ($nodes === null) {
            $nodes = static::allNodes();
        }

        return Utils::buildNestedArray(
            nodes: $nodes,
            parentId: Utils::defaultParentId(),
            primaryKeyName: $this->getKeyName(),
            parentKeyName: $this->determineParentColumnName()
        );
    }

    /**
     * @return static[]|\Illuminate\Support\Collection
     */
    public static function allNodes()
    {
        return static::buildSortQuery()->get();
    }

    public static function buildSortQuery(): Builder
    {
        return static::query()->ordered();
    }
}

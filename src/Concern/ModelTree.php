<?php

namespace SolutionForest\FilamentTree\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
            if (empty($model->{$model->determineOrderColumnName()}) || $model->{$model->determineOrderColumnName()} === 0) {
                $model->setHighestOrderNumber();
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

    public function determineTitleColumnName() : string
    {
        return 'title';
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
     * Get select array options.
     */
    public static function selectArray(?int $maxDepth = null): array
    {
        $result = [];

        $model = new static();

        [$primaryKeyName, $titleKeyName, $parentKeyName, $childrenKeyName] = [
            $model->getKeyName(),
            $model->determineTitleColumnName(),
            $model->determineParentColumnName(),
            Utils::defaultChildrenKeyName(),
        ];

        $nodes = Utils::buildNestedArray(
            nodes: static::allNodes(),
            parentId: Utils::defaultParentId(),
            primaryKeyName: $primaryKeyName,
            parentKeyName: $parentKeyName,
            childrenKeyName: $childrenKeyName
        );

        $result[Utils::defaultParentId()] = __('filament-access-management::filament-access-management.field.menu.root');

        foreach ($nodes as $node) {
            static::buildSelectArrayItem($result, $node, $primaryKeyName, $titleKeyName, $childrenKeyName, 1, $maxDepth);
        }

        return $result;
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

    private static function buildSelectArrayItem(array &$final, array $item, string $primaryKeyName, string $titleKeyName, string $childrenKeyName, int $depth, ?int $maxDepth = null): void
    {
        if (! isset($item[$primaryKeyName])) {
            throw new \InvalidArgumentException("Unset '{$primaryKeyName}' primary key.");
        }

        if ($maxDepth && $depth > $maxDepth) {
            return;
        }

        $key = $item[$primaryKeyName];
        $title = isset($item[$titleKeyName])? $item[$titleKeyName] : $item[$primaryKeyName];
        $final[$key] = Str::padLeft($title, (str($title)->length() + ($depth * 3)), '-');

        if (count($item[$childrenKeyName])) {
            foreach ($item[$childrenKeyName] as $child) {
                static::buildSelectArrayItem($final, $child, $primaryKeyName, $titleKeyName, $childrenKeyName, $depth + 1, $maxDepth);
            }
        }
    }
}

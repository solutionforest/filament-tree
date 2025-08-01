<?php

namespace SolutionForest\FilamentTree\Support;

use Illuminate\Support\Collection;

class Utils
{
    public static function orderColumnName(): string
    {
        return config('filament-tree.column_name.order', 'order');
    }

    public static function parentColumnName(): string
    {
        return config('filament-tree.column_name.parent', 'parent_id');
    }

    /**
     * @deprecated Since v1.1.0
     */
    public static function depthColumnName(): string
    {
        return config('filament-tree.column_name.depth', 'depth');
    }

    public static function titleColumnName(): string
    {
        return config('filament-tree.column_name.title', 'title');
    }

    public static function defaultParentId(): ?int
    {
        return config('filament-tree.default_parent_id', -1);
    }

    public static function defaultChildrenKeyName(): string
    {
        return strval(config('filament-tree.default_children_key', 'children'));
    }

    /**
     * @param  array|Collection  $nodes
     */
    public static function buildNestedArray(
        $nodes = [],
        int|string|null $parentId = null,
        ?string $primaryKeyName = null,
        ?string $parentKeyName = null,
        ?string $childrenKeyName = null): array
    {
        $branch = [];
        $parentId = is_numeric($parentId) ? intval($parentId) : $parentId;
        // if (blank($parentId)) {
        //     $parentId = self::defaultParentId();
        // }
        $primaryKeyName = $primaryKeyName ?: 'id';
        $parentKeyName = $parentKeyName ?: static::parentColumnName();
        $childrenKeyName = $childrenKeyName ?: static::defaultChildrenKeyName();

        $nodeGroups = collect($nodes)->groupBy(fn ($node) => $node[$parentKeyName])->sortKeys();
        foreach ($nodeGroups as $pk => $nodeGroup) {
            $pk = is_numeric($pk) ? intval($pk) : $pk;
            if (
                ($pk === $parentId)
                // Allow parentId is nullable or negative number
                // https://github.com/solutionforest/filament-tree/issues/28
                || (($pk === '' || $pk <= 0) && $parentId <= 0)
            ) {
                foreach ($nodeGroup as $node) {
                    $node = collect($node)->toArray();

                    array_push($branch, array_merge($node, [
                        // children
                        $childrenKeyName => static::buildNestedArray(
                            nodes: $nodes,
                            // children's parent id
                            parentId: $node[$primaryKeyName],
                            primaryKeyName: $primaryKeyName,
                            parentKeyName: $parentKeyName,
                            childrenKeyName: $childrenKeyName
                        ),
                    ]));
                }
            }
        }

        return $branch;
    }
}

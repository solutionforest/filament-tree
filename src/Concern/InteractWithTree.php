<?php

namespace SolutionForest\FilamentTree\Concern;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Concern;
use SolutionForest\FilamentTree\Support\Utils;

trait InteractWithTree
{
    use Concern\HasActions;
    use Concern\HasRecords;
    use Concern\HasEmptyState;

    protected bool $hasMounted = false;

    protected Tree $tree;

    public function bootedInteractWithTree()
    {
        $this->tree = $this->getTree();

        $this->tree = $this->getTree()->configureUsing(
            Closure::fromCallable([$this, 'tree']),
            fn () => $this->tree($this->tree),
        );

        $this->cacheTreeActions();
        $this->cacheTreeEmptyStateActions();

        $this->tree->actions(array_values($this->getCachedTreeActions()));

        if ($this->hasMounted) {
            return;
        }

        $this->hasMounted = true;
    }

    public function mountInteractsWithTree(): void
    {
    }

    protected function getCachedTree(): Tree
    {
        return $this->tree;
    }

    protected function getTree(): Tree
    {
        return Tree::make($this);
    }

    protected function getInteractsWithTreeForms(): array
    {
        return $this->getTreeForms();
    }

    protected function getTreeForms(): array
    {
        ray(__METHOD__, $this);
        return [
            'mountedTreeActionForm' => $this->getMountedTreeActionForm(),
        ];
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }
        return $record->getAttributeValue('title');
    }

    /**
     * Update the tree list.
     */
    public function updateTree(?array $list = null): array
    {
        $needReload = false;
        if ($list) {
            $tree = $this->getCachedTree();

            $records = $tree->getRecords()->keyBy(fn ($record) => $record->getAttributeValue($record->getKeyName()));

            $unnestedArr = [];
            $this->unnestArray($unnestedArr, $list, Utils::defaultParentId());
            collect($unnestedArr)
                ->map(fn (array $data, $id) => ['data' => $data, 'model' => $records->get($id)])
                ->filter(fn (array $arr) => !is_null($arr['model']))
                ->each(function (array $arr) {
                    $model = $arr['model'];
                    [$newParentId, $newOrder] = [$arr['data']['parent_id'], $arr['data']['order']];
                    if ($model instanceof Model) {
                        $parentColumnName = method_exists($model, 'determineParentColumnName') ? $model->determineParentColumnName() : Utils::parentColumnName();
                        $orderColumnName = method_exists($model, 'determineOrderColumnName') ? $model->determineOrderColumnName() : Utils::orderColumnName();

                        $model->{$parentColumnName} = $newParentId;
                        $model->{$orderColumnName} = $newOrder;
                        if ($model->isDirty([$parentColumnName, $orderColumnName])) {
                            $model->save();

                            $needReload = true;
                        }
                    }
                });
        }

        return ['reload' => $needReload];
    }

    public function getRecordKey(?Model $record): ?string
    {
        return $record ? $this->getCachedTree()->getRecordKey($record) : null;
    }

    /**
     * Unnesting the tree array.
     */
    private function unnestArray(array &$result, array $current, $parent): void
    {
        foreach($current as $index => $item) {
            $key = data_get($item, 'id');
            $result[$key] = [
                'parent_id' => $parent,
                'order' => $index + 1,
            ];
            if (isset($item['children']) && count($item['children'])) {
                $this->unnestArray($result, $item['children'], $key);
            }
        }
    }
}

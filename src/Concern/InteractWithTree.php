<?php

namespace SolutionForest\FilamentTree\Concern;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Support\Utils;

trait InteractWithTree
{
    use HasActions;
    use HasEmptyState;
    use HasHeading;
    use HasRecords;

    // protected bool $hasMounted = false;

    protected Tree $tree;

    public function bootInteractWithTree()
    {
        $this->tree = Tree::configureUsing(
            Closure::fromCallable([static::class, 'tree']),
            fn (): Tree => $this->makeTree()
                ->maxDepth(static::getMaxDepth())
        );

        // Fill actions and toolbar actions if not set in the tree configurator
        if (empty($this->tree->getActions())) {
            $this->tree->actions($this->getTreeActions());
        }
        if (empty($this->tree->getToolbarActions())) {
            $this->tree->toolbarActions($this->getTreeToolbarActions());
        }

        // $this->cacheTreeActions();

        // if ($this->hasMounted) {
        //     return;
        // }

        // $this->hasMounted = true;
    }

    public function mountInteractsWithTree(): void {}

    protected function getCachedTree(): Tree
    {
        return $this->tree;
    }

    /**
     * @deprecated Use makeTree() instead.
     */
    protected function getTree(): Tree
    {
        return $this->makeTree();
    }

    protected function makeTree(): Tree
    {
        return Tree::make($this);
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        return $record->{(method_exists($record, 'determineTitleColumnName') ? $record->determineTitleColumnName() : 'title')};
    }

    public function getTreeRecordDescription(?Model $record = null): string|HtmlString|null
    {
        return null;
    }

    public function getTreeRecordIcon(?Model $record = null): ?string
    {
        if (! $record) {
            return null;
        }

        return $record->{(method_exists($record, 'determineIconColumnName') ? $record->determineIconColumnName() : 'icon')};
    }

    public function getRecordKey(?Model $record): ?string
    {
        return $this->getCachedTree()->getRecordKey($record);
    }

    public function getParentKey(?Model $record): ?string
    {
        return $this->getCachedTree()->getParentKey($record);
    }

    public function getNodeCollapsedState(?Model $record = null): bool
    {
        return false;
    }

    public function getTreeRootLevelKey(): null|string|int
    {
        return Utils::defaultParentId();
    }

    /**
     * Update the tree list.
     */
    public function updateTree(?array $list = null): array
    {
        $needReload = false;
        if ($list) {
            $records = $this->getRecords()->keyBy(fn ($record) => $record->getAttributeValue($record->getKeyName()));

            $unnestedArr = [];
            $defaultParentId = $this->getTreeRootLevelKey();

            $this->unnestArray($unnestedArr, $list, $defaultParentId);

            $unnestedArrData = collect($unnestedArr)
                ->map(fn (array $data, $id) => ['data' => $data, 'model' => $records->get($id)])
                ->filter(fn (array $arr) => ! is_null($arr['model']));
            foreach ($unnestedArrData as $arr) {
                $model = $arr['model'];
                [$newParentId, $newOrder] = [$arr['data']['parent_id'], $arr['data']['order']];
                if ($model instanceof Model) {
                    $parentColumnName = method_exists($model, 'determineParentColumnName') ? $model->determineParentColumnName() : Utils::parentColumnName();
                    $orderColumnName = method_exists($model, 'determineOrderColumnName') ? $model->determineOrderColumnName() : Utils::orderColumnName();
                    $newParentId = $newParentId === $defaultParentId && method_exists($model, 'defaultParentKey') ? $model::defaultParentKey() : $newParentId;

                    $model->{$parentColumnName} = $newParentId;
                    $model->{$orderColumnName} = $newOrder;
                    if ($model->isDirty([$parentColumnName, $orderColumnName])) {
                        $model->save();

                        $needReload = true;
                    }
                }
            }
        }
        if ($needReload) {

            Notification::make()
                ->success()
                ->title(__('filament-actions::edit.single.modal.actions.save.label'))
                ->send();

            // Reload data
            $this->dispatch('refreshTree');
        }

        return ['reload' => $needReload];
    }

    /**
     * Unnesting the tree array.
     */
    private function unnestArray(array &$result, array $current, $parent): void
    {
        foreach ($current as $index => $item) {
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

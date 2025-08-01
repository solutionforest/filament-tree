<?php

namespace SolutionForest\FilamentTree\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use SolutionForest\FilamentTree\Support\Utils;

trait HasRecords
{
    protected ?Collection $records = null;

    #[On('refreshTree')]
    public function getRecords(): ?Collection
    {
        if ($this->records) {
            return $this->records;
        }

        return $this->records = $this->getSortedQuery()->get();
    }

    public function getTreeRecord(?string $key): ?Model
    {
        return $this->resolveTreeRecord($key);
    }

    protected function resolveTreeRecord(?string $key): ?Model
    {
        if ($key === null) {
            return null;
        }

        return $this->getSortedQuery()->find($key);
    }

    public function getRootLayerRecords(): \Illuminate\Support\Collection
    {
        return collect($this->getRecords() ?? [])
            ->filter(function (Model $record) {
                if (method_exists($record, 'isRoot')) {
                    return $record->isRoot();
                }
                if (method_exists($record, 'determineParentColumnName')) {
                    return $record->getAttributeValue($record->determineParentColumnName()) == Utils::defaultParentId();
                }

                return $record->getAttributeValue('parent') === Utils::defaultParentId();
            });
    }

    protected function getSortedQuery(): Builder
    {
        $query = $this->getWithRelationQuery();
        if (method_exists($this->getModel(), 'scopeOrdered')) {
            return $this->getWithRelationQuery()->ordered();
        }

        return $query;
    }

    protected function getWithRelationQuery(): Builder
    {
        $query = $this->getTreeQuery();
        if (method_exists($this->getModel(), 'children') && $this->getModel()::has('children')) {
            return $query->with('children');
        }

        return $query;
    }

    protected function getTreeQuery(): Builder
    {
        return $this->getModel()::query();
    }
}

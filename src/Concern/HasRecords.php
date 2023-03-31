<?php

namespace SolutionForest\FilamentTree\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait HasRecords
{
    protected Collection | null $records = null;

    public function getRecords(): Collection | null
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
            ->filter(fn ($record) =>$record->isRoot());
    }

    protected function getSortedQuery(): Builder
    {
        return $this->getWithRelationQuery()->ordered();
    }

    protected function getWithRelationQuery(): Builder
    {
        return $this->getTreeQuery()->with('children');
    }

    protected function getTreeQuery(): Builder
    {
        return $this->getModel()::query();
    }
}

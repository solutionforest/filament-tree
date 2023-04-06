<?php

namespace SolutionForest\FilamentTree\Concern;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait HasTranslatableRecords
{
    use HasRecords {
        HasRecords::getRecords as traitGetRecords;
        HasRecords::resolveTreeRecord as traitResolveTreeRecord;
    }

    public function getRecords(): Collection | null
    {
        $records = $this->traitGetRecords();
        if ($records) {
            foreach ($records as $record) {
                $this->updateModelTranslation($record);
            }
        }
        return $records;
    }

    protected function resolveTreeRecord(?string $key): ?Model
    {
        $record = $this->traitResolveTreeRecord($key);

        $this->updateModelTranslation($record);
        
        return $record;
    }

    private function updateModelTranslation(?Model $record = null): void
    {
        if ($record) {
            if (method_exists($record, 'setLocale') && $activeLocale = $this->getActiveLocale()) {
                $record->setLocale($activeLocale);
            }

            // relationships
            foreach ($record->getRelations() as $relationKey => $item) {
                if (is_array($item) || $item instanceof Arrayable) {
                    foreach ($item as $relationRecord) {
                        $this->updateModelTranslation($relationRecord);
                    }

                } else if (! empty($item)) {

                    $this->updateModelTranslation($item);
                }
            }
        }
    }
}

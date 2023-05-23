<?php

namespace SolutionForest\FilamentTree\Concern\TreeRecords;

use Filament\Pages\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Actions;
use SolutionForest\FilamentTree\Concern\TreeRecords\HasActiveLocaleSwitcher;
use SolutionForest\FilamentTree\Concern\HasTranslatableRecords;

trait Translatable
{
    use HasActiveLocaleSwitcher;
    use HasTranslatableRecords;

    public function mountTranslatable(): void
    {
        $this->setActiveLocale();
    }

    public function getActiveLocale(): ?string
    {
        return $this->activeLocale;
    }

    protected function setActiveLocale(): void
    {
        $this->activeLocale = method_exists($this, 'getDefaultTranslatableLocale')
            ? $this->getDefaultTranslatableLocale()
            : app()->getLocale();
    }

    public function hydrateTranslatable()
    {
        //
    }

    public function dehydrateTranslatable()
    {
        //
    }

    protected function afterConfiguredCreateAction(CreateAction $action): CreateAction
    {
        /** @var CreateAction */
        $action = parent::afterConfiguredCreateAction($action);

        if (method_exists($action, 'using')) {
            $model = $action->getModel();
            $action->using(function (array $data) use ($model) {
                if (method_exists($model, 'getTranslatableAttributes')) {
                    foreach (app($model)->getTranslatableAttributes() as $attr) {
                        $data[$attr] = array_merge(
                            [$this->getActiveLocale() => $data[$attr]],
                            $this->getActiveLocale() !== app()->getFallbackLocale() ? [app()->getFallbackLocale() => $data[$attr]] : [],
                        );
                    }
                }
                return $model::create($data);
            });
        }

        return $action;
    }

    protected function afterConfiguredEditAction(Actions\EditAction $action): Actions\EditAction
    {
        /** @var Actions\EditAction */
        $action = parent::afterConfiguredEditAction($action);

        $action->mutateRecordDataUsing(function (array $data, Model $record) {
            return $this->mutateRecordData($data, $record);
        });

        if (method_exists($action, 'using')) {
            $action->using(function (array $data, Model $record) use ($action) {

                $data = $action->evaluate($action->getMutateFormDataBeforeSave(), ['data' => $data]);
                
                $record->fill($data);
                if (method_exists($record, 'setTranslation') &&
                    method_exists($record, 'getTranslatableAttributes')
                ) {
                    foreach ($record->getTranslatableAttributes() as $attr) {
                        $record->setTranslation($attr, $this->getActiveLocale(), $data[$attr]);
                    }
                }
                $record->save();
            });
        }

        return $action;
    }

    protected function afterConfiguredViewAction(Actions\ViewAction $action): Actions\ViewAction
    {
        /** @var Actions\ViewAction */
        $action = parent::afterConfiguredViewAction($action);

        $action->mutateRecordDataUsing(function (array $data, Model $record) {
            return $this->mutateRecordData($data, $record);
        });

        return $action;
    }

    private function mutateRecordData(array $data, Model $record): array
    {
        if (method_exists($record, 'getTranslatableAttributes')) {
            foreach ($record->getTranslatableAttributes() as $attr) {
                $data[$attr] = $record->getAttributeValue($attr);
            }
        }
        return $data;
    }
}

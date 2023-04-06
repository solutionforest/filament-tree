<?php

namespace SolutionForest\FilamentTree\Concern;

use Illuminate\Database\Eloquent\Model;

trait SupportTranslation
{
    protected static function handleTranslatable(array &$final, string $modelClass): void
    {
        $model = app($modelClass);
        if (! $model instanceof Model) {
            return;
        }
        foreach ($final as $key => $value) {
            if (! (
                method_exists($modelClass, 'isTranslatableAttribute') &&
                method_exists($modelClass, 'setTranslations') &&
                method_exists($modelClass, 'getTranslationWithFallback')
            )) {
                continue;
            }
            $model = app($modelClass);

            if (! $model->isTranslatableAttribute($key)) {
                continue;
            }
            if (is_array($value)) {
                $model->setTranslations($key, $value);
                $final[$key] = $model->getTranslationWithFallback($key, app()->getLocale());
            }
        }
            
    }
}

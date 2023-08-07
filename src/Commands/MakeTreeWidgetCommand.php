<?php

namespace SolutionForest\FilamentTree\Commands;

use Closure;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MakeTreeWidgetCommand extends Command
{
    use CanManipulateFiles;
    use CanValidateInput;

    protected $description = 'Creates a Filament tree widget class.';

    protected $signature = 'make:filament-tree-widget {name?} {model?} {--R|resource=} {--F|force}';

    public function handle(): int
    {
        $path = config('filament.widgets.path', app_path('Filament/Widgets/'));
        $resourcePath = config('filament.resources.path', app_path('Filament/Resources/'));
        $namespace = config('filament.widgets.namespace', 'App\\Filament\\Widgets');
        $resourceNamespace = config('filament.resources.namespace', 'App\\Filament\\Resources');

        $resource = null;
        $resourceClass = null;

        $resourceInput = $this->option('resource') ?? $this->ask('(Optional) Resource (e.g. `ProductCategoryResource`)');

        if ($resourceInput !== null) {
            $resource = (string) Str::of($resourceInput)
                ->studly()
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->replace('/', '\\');

            if (! Str::of($resource)->endsWith('Resource')) {
                $resource .= 'Resource';
            }

            $resourceClass = (string) Str::of($resource)
                ->afterLast('\\');
        }

        $widget =  Str::of(strval($this->argument('name') ?? $this->askRequired('Name (e.g. `ProductCategory`)', 'name')))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');
        $widgetClass = (string) Str::of($widget)->afterLast('\\');
        $widgetNamespace = Str::of($widget)->contains('\\') ?
            (string) Str::of($widget)->beforeLast('\\') :
            '';

        $path = (string) Str::of($widget)
            ->prepend('/')
            ->prepend($resource === null ? $path : "{$resourcePath}\\{$resource}\\Widgets\\")
            ->replace('\\', '/')
            ->replace('//', '/')
            ->append('.php');

        $model = (string) Str::of($this->argument('model') ?? $this->askRequired('Model (e.g. `ProductCategory`)', 'model'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');
        $modelClass = (string) Str::of($model)->afterLast('\\');

        if (! $this->option('force') && $this->checkForCollision([$path])) {
            return static::INVALID;
        }

        $this->copyStubToApp('TreeWidget', $path, [
            'class' => $widgetClass,
            'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            'model' => $model == $widgetClass ? "{$model} as TreeWidgetModel" : $model,
            'modelClass' => $modelClass == $widgetClass ? "TreeWidgetModel" : $modelClass,
        ]);

        $this->info("Successfully created {$widget} !");

        if ($resource !== null) {
            $this->info("Make sure to register the widget in `{$resourceClass}::getWidgets()`, and then again in `getHeaderWidgets()` or `getFooterWidgets()` of any `{$resourceClass}` page.");
        }

        return static::SUCCESS;
    }
    
    protected function askRequired(string $question, string $field, ?string $default = null): string
    {
        return $this->validateInput(fn () => $this->ask($question, $default), $field, ['required']);
    }

    protected function validateInput(Closure $callback, string $field, array $rules, ?Closure $onError = null): string
    {
        $input = $callback();

        $validator = Validator::make(
            [$field => $input],
            [$field => $rules],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            if ($onError) {
                $onError($validator);
            }

            $input = $this->validateInput($callback, $field, $rules);
        }

        return $input;
    }
}

<?php

namespace SolutionForest\FilamentTree\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MakeTreeWidgetCommand extends Command
{
    use CanManipulateFiles;
    use CanValidateInput;

    protected $description = 'Creates a Filament tree widget class.';

    protected $signature = 'make:filament-tree-widget {name?} {model?}';

    public function handle(): int
    {
        $path = config('filament.widgets.path', app_path('Filament/Widgets/'));
        $namespace = config('filament.widgets.namespace', 'App\\Filament\\Widgets');

        $widget =  Str::of(strval($this->argument('name') ?? $this->askRequired('Name (e.g. `MemberDetails`)', 'name')))
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
            ->prepend($path)
            ->replace('\\', '/')
            ->replace('//', '/')
            ->append('.php');

        $model = (string) Str::of($this->argument('model') ?? $this->askRequired('Model (e.g. `Menu`)', 'model'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');
        $modelClass = (string) Str::of($model)->afterLast('\\');

        $this->copyStubToApp('TreeWidget', $path, [
            'class' => $widgetClass,
            'namespace' => $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            'model' => $model,
            'modelClass' => $modelClass,
        ]);

        $this->info("Successfully created {$widget}!");

        return static::SUCCESS;
    }
}

<?php

namespace SolutionForest\FilamentTree\Commands;

use Closure;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MakeTreePageCommand extends Command
{
    use CanManipulateFiles;
    
    protected $signature = "make:filament-tree-page {name?} {--model=} {--R|resource=} {--F|force}";

    public $description = 'Creates a Filament tree page class';

    protected ?string $resourceClass = null;
    protected string $page;
    protected string $pageClass;

    public function handle(): int
    {
        $this->page =  Str::of(strval($this->argument('name') ?? $this->askRequired('Name (e.g. `Users`)', 'name')))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        $this->pageClass = (string) Str::of($this->page)->afterLast('\\');

        $this->askResourceClass();

        if (! $this->createPage()) {
            return static::INVALID;
        }

        $this->info("Successfully created {$this->page} !");

        return static::SUCCESS;
    }

    protected function createPage(): bool
    {
        $path = config('filament.pages.path', app_path('Filament/Pages/'));
        $resourcePath = config('filament.resources.path', app_path('Filament/Resources/'));

        $namespace = config('filament.pages.namespace', 'App\\Filament\\Pages');
        $resourceNamespace = config('filament.resources.namespace', 'App\\Filament\\Resources');
        $pageNamespace = Str::of($this->page)->contains('\\') ?
            (string) Str::of($this->page)->beforeLast('\\') :
            '';
        
        $resourceClass = $this->resourceClass;
        $stub = $this->getStub();

        $path = (string) Str::of($this->pageClass)
            ->prepend('/')
            ->prepend($resourceClass === null ? $path : "{$resourcePath}\\{$resourceClass}\\Pages\\")
            ->replace('\\', '/')
            ->replace('//', '/')
            ->append('.php');

        
        if (! $this->option('force') && $this->checkForCollision([$path])) {
            return false;
        }

        if ($resourceClass === null) {
            $model = (string) Str::of($this->argument('name') ?? $this->askRequired('Model (e.g. `User`)', 'name'))
                ->studly()
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->studly()
                ->replace('/', '\\');
    
            if (blank($model)) {
                $model = $this->pageClass;
            }
    
            $modelClass = (string) Str::of($model)->afterLast('\\');

            $this->copyStubToApp($stub, $path, [
                'class' => $this->pageClass,
                'namespace' => $namespace . ($pageNamespace !== '' ? "\\{$pageNamespace}" : ''),
                'modelClass' => $modelClass == $this->pageClass ? 'TreePageModel' : $modelClass,
                'model' => $model == $this->pageClass ? "{$model} as TreePageModel" : $model,
            ]);
        } else {
            $this->copyStubToApp($stub, $path, [
                'namespace' => "{$resourceNamespace}\\{$resourceClass}\\Pages" . ($pageNamespace !== '' ? "\\{$pageNamespace}" : ''),
                'resource' => $this->resourceClass == $this->pageClass ? "{$resourceNamespace}\\{$resourceClass} as TreePageResource" : "{$resourceNamespace}\\{$resourceClass}",
                'class' => $this->pageClass,
                'resourceClass' => $resourceClass == $this->pageClass ? "TreePageResource" : $resourceClass,
            ]);
        }

        return true;
    }

    protected function getStub(): string
    {
        return $this->resourceClass ? 'TreeResourcePage' : 'TreePage';
    }

    protected function askResourceClass(): void
    {
        $resourceInput = $this->option('resource') ?? $this->ask('(Optional) Resource (e.g. `UserResource`)');

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

            $this->resourceClass = (string) Str::of($resource)
                ->afterLast('\\');
        }
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

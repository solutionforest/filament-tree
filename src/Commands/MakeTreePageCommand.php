<?php

namespace SolutionForest\FilamentTree\Commands;

use Filament\Support\Commands\Concerns\CanAskForRelatedModel;
use Filament\Support\Commands\Concerns\CanAskForRelatedResource;
use Filament\Support\Commands\Concerns\CanAskForResource;
use Filament\Support\Commands\Concerns\CanAskForViewLocation;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\HasCluster;
use Filament\Support\Commands\Concerns\HasClusterPagesLocation;
use Filament\Support\Commands\Concerns\HasPanel;
use Filament\Support\Commands\Concerns\HasResourcesLocation;
use Filament\Support\Commands\Exceptions\FailureCommandOutput;
use Filament\Support\Commands\FileGenerators\Concerns\CanCheckFileGenerationFlags;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ReflectionClass;
use SolutionForest\FilamentTree\Commands\FileGenerators\ResourceTreePageClassGenerator;
use SolutionForest\FilamentTree\Commands\FileGenerators\TreePageClassGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Filament\Support\discover_app_classes;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

#[AsCommand(
    name: 'make:filament-tree-page',
    description: 'Creates a Filament tree page class',
)]
class MakeTreePageCommand extends Command
{
    use CanAskForRelatedModel;
    use CanAskForRelatedResource;
    use CanAskForResource;
    use CanAskForViewLocation;
    use CanCheckFileGenerationFlags;
    use CanManipulateFiles;
    use HasCluster;
    use HasClusterPagesLocation;
    use HasPanel;
    use HasResourcesLocation;

    /**
     * @var class-string
     */
    protected string $fqn;

    protected string $fqnEnd;

    /**
     * @var class-string<Model>
     */
    protected string $modelFqn;

    protected string $modelFqnEnd;

    protected bool $hasResource;

    /**
     * @var ?class-string
     */
    protected ?string $resourceFqn = null;

    protected string $pagesNamespace;

    protected string $pagesDirectory;

    public static bool $shouldCheckModelsForSoftDeletes = true;

    protected function configure()
    {
        $this->addArgument(
            name: 'name',
            mode: InputArgument::OPTIONAL,
            description: 'The name of the page, optionally prefixed with directories (e.g. `Categories`)',
        );

        $this
            ->addOption(
                name: 'model',
                shortcut: 'M',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The model class for the page (e.g. `Category`)',
            )
            ->addOption(
                name: 'model-namespace',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'The namespace of the model class, [App\\Models] by default',
            )
            ->addOption(
                name: 'resource',
                shortcut: 'R',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The resource class for the page (e.g. `CategoryResource`)',
            )
            ->addOption(
                name: 'resource-namespace',
                shortcut: null,
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The namespace of the resource class, such as [App\\Filament\\Resources]',
            )
            ->addOption(
                name: 'panel',
                shortcut: 'P',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The panel to use for the page (e.g. `admin`)',
            )
            ->addOption(
                name: 'cluster',
                shortcut: 'C',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The cluster to use for the page (e.g. `CategoryCluster`)',
            )
            ->addOption(
                name: 'force',
                shortcut: 'F',
                mode: InputOption::VALUE_NONE,
                description: 'Overwrite the contents of the files if they already exist',
            );
    }

    public function handle(): int
    {
        try {
            $this->configureFqnEnd();
            $this->configureModel();
            $this->configurePanel(question: 'Which panel would you like to create this page in?');
            $this->configureHasResource();
            $this->configureCluster();
            $this->configureResource();
            // $this->configureResourcePageType();
            $this->configurePagesLocation();

            $this->configureLocation();

            $this->createCustomPage();
            // $this->createPage();
            $this->createResourceCustomPage();
            // $this->createResourceCreatePage();
            // $this->createResourceEditPage();
            // $this->createResourceViewPage();
            // $this->createResourceManageRelatedRecordsPage();
            // $this->createView();

            // if (! $this->createPage()) {
            //     return static::FAILURE;
            // }

        } catch (FailureCommandOutput) {
            return static::FAILURE;
        } catch (\Throwable $e) {
            $this->components->error("Failed to create Filament tree page [{$this->fqn}]: {$e->getMessage()}");

            return static::FAILURE;
        }

        $this->components->info("Filament tree page [{$this->fqn}] created successfully.");
        // $this->components->info("Successfully created {$this->fqnEnd} !");

        if (filled($this->resourceFqn)) {
            $this->components->info("Make sure to register the page in [{$this->resourceFqn}::getPages()].");
        } elseif (empty($this->panel->getPageNamespaces())) {
            $this->components->info('Make sure to register the page with [pages()] or discover it with [discoverPages()] in the panel service provider.');
        }

        // $this->page =  Str::of(strval($this->argument('name') ?? $this->askRequired('Name (e.g. `Users`)', 'name')))
        //     ->trim('/')
        //     ->trim('\\')
        //     ->trim(' ')
        //     ->replace('/', '\\');

        // $this->pageClass = (string) Str::of($this->page)->afterLast('\\');

        return static::SUCCESS;
    }

    protected function configureFqnEnd(): void
    {
        $this->fqnEnd = (string) str($this->argument('name') ?? text(
            label: 'What is the page name?',
            placeholder: 'CategoryTree',
            required: true,
        ))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');
    }

    protected function configureModel(): void
    {
        if ($this->option('model')) {
            $this->modelFqnEnd = (string) str($this->option('model'))
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->studly()
                ->replace('/', '\\');

            $modelNamespace = $this->option('model-namespace') ?? 'App\\Models';

            $this->modelFqn = "{$modelNamespace}\\{$this->modelFqnEnd}";
        } else {
            $modelFqns = discover_app_classes(parentClass: Model::class);

            $this->modelFqn = suggest(
                label: 'What is the model?',
                options: function (string $search) use ($modelFqns): array {
                    $search = str($search)->trim()->replace(['\\', '/'], '');

                    if (blank($search)) {
                        return $modelFqns;
                    }

                    return array_filter(
                        $modelFqns,
                        fn (string $class): bool => str($class)->replace(['\\', '/'], '')->contains($search, ignoreCase: true),
                    );
                },
                placeholder: 'App\\Models\\ProductCategory',
                required: true,
            );

            $this->modelFqnEnd = class_basename($this->modelFqn);
        }

        // if ($this->option('model')) {
        //     $this->callSilently('make:model', [
        //         'name' => $this->modelFqn,
        //     ]);
        // }
    }

    protected function configureHasResource(): void
    {
        $this->hasResource = $this->option('resource') || confirm(
            label: 'Would you like to create this page in a resource?',
            default: false,
        );
    }

    protected function configureCluster(): void
    {
        if ($this->hasResource) {
            $this->configureClusterFqn(
                initialQuestion: 'Is the resource in a cluster?',
                question: 'Which cluster is the resource in?',
            );
        } else {
            $this->configureClusterFqn(
                initialQuestion: 'Would you like to create this page in a cluster?',
                question: 'Which cluster would you like to create this page in?',
            );
        }

        if (blank($this->clusterFqn)) {
            return;
        }

        $this->configureClusterPagesLocation();
        $this->configureClusterResourcesLocation();
    }

    protected function configureResource(): void
    {
        if (! $this->hasResource) {
            return;
        }

        $this->configureResourcesLocation(question: 'Which namespace would you like to search for resources in?');

        $this->resourceFqn = $this->askForResource(
            question: 'Which resource would you like to create this page in?',
            initialResource: $this->option('resource'),
        );

        $pluralResourceBasenameBeforeResource = (string) str($this->resourceFqn)
            ->classBasename()
            ->beforeLast('Resource')
            ->plural();

        $resourceNamespacePartBeforeBasename = (string) str($this->resourceFqn)
            ->beforeLast('\\')
            ->classBasename();

        if ($pluralResourceBasenameBeforeResource === $resourceNamespacePartBeforeBasename) {
            $this->pagesNamespace = (string) str($this->resourceFqn)
                ->beforeLast('\\')
                ->append('\\Pages');
            $this->pagesDirectory = (string) str((new ReflectionClass($this->resourceFqn))->getFileName())
                ->beforeLast(DIRECTORY_SEPARATOR)
                ->append('/Pages');

            return;
        }

        $this->pagesNamespace = "{$this->resourceFqn}\\Pages";
        $this->pagesDirectory = (string) str((new ReflectionClass($this->resourceFqn))->getFileName())
            ->beforeLast('.')
            ->append('/Pages');
    }

    protected function configurePagesLocation(): void
    {
        if (filled($this->resourceFqn)) {
            return;
        }

        if (filled($this->clusterFqn)) {
            return;
        }

        $directories = $this->panel->getPageDirectories();
        $namespaces = $this->panel->getPageNamespaces();

        foreach ($directories as $index => $directory) {
            if (str($directory)->startsWith(base_path('vendor'))) {
                unset($directories[$index]);
                unset($namespaces[$index]);
            }
        }

        if (count($namespaces) < 2) {
            $this->pagesNamespace = (Arr::first($namespaces) ?? 'App\\Filament\\Pages');
            $this->pagesDirectory = (Arr::first($directories) ?? app_path('Filament/Pages/'));

            return;
        }

        $keyedNamespaces = array_combine(
            $namespaces,
            $namespaces,
        );

        $this->pagesNamespace = search(
            label: 'Which namespace would you like to create this page in?',
            options: function (?string $search) use ($keyedNamespaces): array {
                if (blank($search)) {
                    return $keyedNamespaces;
                }

                $search = str($search)->trim()->replace(['\\', '/'], '');

                return array_filter($keyedNamespaces, fn (string $namespace): bool => str($namespace)->replace(['\\', '/'], '')->contains($search, ignoreCase: true));
            },
        );
        $this->pagesDirectory = $directories[array_search($this->pagesNamespace, $namespaces)];
    }

    protected function configureLocation(): void
    {
        $this->fqn = $this->pagesNamespace.'\\'.$this->fqnEnd;

        // if ((! $this->hasResource)) {
        //     $componentLocations = FilamentCli::getComponentLocations();

        //     $matchingComponentLocationNamespaces = collect($componentLocations)
        //         ->keys()
        //         ->filter(fn (string $namespace): bool => str($this->fqn)->startsWith($namespace));

        //     [
        //         $this->view,
        //         $this->viewPath,
        //     ] = $this->askForViewLocation(
        //         view: str($this->fqn)
        //             ->whenContains(
        //                 'Filament\\',
        //                 fn (Stringable $fqn) => $fqn->after('Filament\\')->prepend('Filament\\'),
        //                 fn (Stringable $fqn) => $fqn->replaceFirst('App\\', ''),
        //             )
        //             ->replace('\\', '/')
        //             ->explode('/')
        //             ->map(Str::kebab(...))
        //             ->implode('.'),
        //         question: 'Where would you like to create the Blade view for the page?',
        //         defaultNamespace: (count($matchingComponentLocationNamespaces) === 1)
        //             ? $componentLocations[Arr::first($matchingComponentLocationNamespaces)]['viewNamespace'] ?? null
        //             : null,
        //     );
        // }
    }

    protected function createCustomPage(): void
    {
        if ($this->hasResource) {
            return;
        }

        $path = (string) str("{$this->pagesDirectory}\\{$this->fqnEnd}.php")
            ->replace('\\', '/')
            ->replace('//', '/');

        if (! $this->option('force') && $this->checkForCollision($path)) {
            throw new FailureCommandOutput;
        }

        $this->writeFile($path, app(TreePageClassGenerator::class, [
            'fqn' => $this->fqn,
            'modelFqn' => $this->modelFqn,
            'clusterFqn' => $this->clusterFqn,
        ]));
    }

    protected function createResourceCustomPage(): void
    {
        if (! $this->hasResource) {
            return;
        }

        $path = (string) str("{$this->pagesDirectory}\\{$this->fqnEnd}.php")
            ->replace('\\', '/')
            ->replace('//', '/');

        if (! $this->option('force') && $this->checkForCollision($path)) {
            throw new FailureCommandOutput;
        }

        info('Most pages in a resource, such as the default Edit or View pages, have a record ID in their URL.');

        $this->writeFile($path, app(ResourceTreePageClassGenerator::class, [
            'fqn' => $this->fqn,
            'resourceFqn' => $this->resourceFqn,
        ]));
    }
}

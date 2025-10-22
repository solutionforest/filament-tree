<?php

namespace SolutionForest\FilamentTree\Commands;

use Filament\Support\Commands\Concerns\CanAskForLivewireComponentLocation;
use Filament\Support\Commands\Concerns\CanAskForResource;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\HasCluster;
use Filament\Support\Commands\Concerns\HasPanel;
use Filament\Support\Commands\Concerns\HasResourcesLocation;
use Filament\Support\Commands\Exceptions\FailureCommandOutput;
use Filament\Support\Commands\FileGenerators\Concerns\CanCheckFileGenerationFlags;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ReflectionClass;
use SolutionForest\FilamentTree\Commands\FileGenerators\TreeWidgetClassGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Filament\Support\discover_app_classes;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

#[AsCommand(
    name: 'make:filament-tree-widget',
    description: 'Creates a Filament tree widget class.',
)]
class MakeTreeWidgetCommand extends Command
{
    use CanAskForLivewireComponentLocation;
    use CanAskForResource;
    use CanCheckFileGenerationFlags;
    use CanManipulateFiles;
    use HasCluster;
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

    protected string $widgetsNamespace;

    protected string $widgetsDirectory;

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
                description: 'The model class for the widget (e.g. `Category`)',
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
                description: 'The resource to create the widget in',
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
                description: 'The panel to create the widget in',
            )
            ->addOption(
                name: 'cluster',
                shortcut: 'C',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The cluster that the resource belongs to',
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
            $this->configurePanel(
                question: 'Which panel would you like to create this widget in?',
                initialQuestion: 'Would you like to create this widget in a panel?',
            );
            $this->configureHasResource();
            $this->configureCluster();
            $this->configureResource();
            $this->configureWidgetsLocation();

            $this->configureLocation();

            $this->createCustomWidget();
        } catch (FailureCommandOutput) {
            return static::FAILURE;
        } catch (\Throwable $e) {
            $this->components->error("Failed to create Filament tree widget [{$this->fqn}]: {$e->getMessage()}");

            return static::FAILURE;
        }

        $this->components->info("Filament tree widget [{$this->fqn}] created successfully.");

        if (filled($this->resourceFqn)) {
            $this->components->info("Make sure to register the widget in [{$this->resourceFqn}::getWidgets()], and add it to a page in the resource.");
        } elseif ($this->panel && empty($this->panel->getWidgetNamespaces())) {
            $this->components->info('Make sure to register the widget with [widgets()] or discover it with [discoverWidgets()] in the panel service provider.');
        }

        return static::SUCCESS;
    }

    protected function configureFqnEnd(): void
    {
        $this->fqnEnd = (string) str($this->argument('name') ?? text(
            label: 'What is the widget name?',
            placeholder: 'ProductCategory',
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
        if (! $this->panel) {
            $this->hasResource = false;

            return;
        }

        $this->hasResource = $this->option('resource') || confirm(
            label: 'Would you like to create this widget in a resource?',
            default: false,
        );
    }

    protected function configureCluster(): void
    {
        if (! $this->hasResource) {
            return;
        }

        $this->configureClusterFqn(
            initialQuestion: 'Is the resource in a cluster?',
            question: 'Which cluster is the resource in?',
        );

        if (blank($this->clusterFqn)) {
            return;
        }

        $this->configureClusterResourcesLocation();
    }

    protected function configureResource(): void
    {
        if (! $this->hasResource) {
            return;
        }

        $this->configureResourcesLocation(question: 'Which namespace would you like to search for resources in?');

        $this->resourceFqn = $this->askForResource(
            question: 'Which resource would you like to create this widget in?',
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
            $this->widgetsNamespace = (string) str($this->resourceFqn)
                ->beforeLast('\\')
                ->append('\\Widgets');
            $this->widgetsDirectory = (string) str((new ReflectionClass($this->resourceFqn))->getFileName())
                ->beforeLast(DIRECTORY_SEPARATOR)
                ->append('/Widgets');

            return;
        }

        $this->widgetsNamespace = "{$this->resourceFqn}\\Widgets";
        $this->widgetsDirectory = (string) str((new ReflectionClass($this->resourceFqn))->getFileName())
            ->beforeLast('.')
            ->append('/Widgets');
    }

    protected function configureWidgetsLocation(): void
    {
        if (filled($this->resourceFqn)) {
            return;
        }

        if (! $this->panel) {
            [
                $this->widgetsNamespace,
                $this->widgetsDirectory,
            ] = $this->askForLivewireComponentLocation(
                question: 'Where would you like to create the widget?',
            );

            return;
        }

        $directories = $this->panel->getWidgetDirectories();
        $namespaces = $this->panel->getWidgetNamespaces();

        foreach ($directories as $index => $directory) {
            if (str($directory)->startsWith(base_path('vendor'))) {
                unset($directories[$index]);
                unset($namespaces[$index]);
            }
        }

        if (count($namespaces) < 2) {
            $this->widgetsNamespace = (Arr::first($namespaces) ?? 'App\\Filament\\Widgets');
            $this->widgetsDirectory = (Arr::first($directories) ?? app_path('Filament/Widgets/'));

            return;
        }

        $keyedNamespaces = array_combine(
            $namespaces,
            $namespaces,
        );

        $this->widgetsNamespace = search(
            label: 'Which namespace would you like to create this widget in?',
            options: function (?string $search) use ($keyedNamespaces): array {
                if (blank($search)) {
                    return $keyedNamespaces;
                }

                $search = str($search)->trim()->replace(['\\', '/'], '');

                return array_filter($keyedNamespaces, fn (string $namespace): bool => str($namespace)->replace(['\\', '/'], '')->contains($search, ignoreCase: true));
            },
        );
        $this->widgetsDirectory = $directories[array_search($this->widgetsNamespace, $namespaces)];
    }

    protected function configureLocation(): void
    {
        $this->fqn = $this->widgetsNamespace.'\\'.$this->fqnEnd;
    }

    protected function createCustomWidget(): void
    {
        $path = (string) str("{$this->widgetsDirectory}\\{$this->fqnEnd}.php")
            ->replace('\\', '/')
            ->replace('//', '/');

        if (! $this->option('force') && $this->checkForCollision($path)) {
            throw new FailureCommandOutput;
        }

        $this->writeFile($path, app(TreeWidgetClassGenerator::class, [
            'fqn' => $this->fqn,
            'modelFqn' => $this->modelFqn,
        ]));
    }
}

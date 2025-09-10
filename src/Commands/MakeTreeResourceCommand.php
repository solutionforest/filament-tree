<?php

namespace SolutionForest\FilamentTree\Commands;

use Filament\Support\Commands\Concerns\CanAskForRelatedModel;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\HasPanel;
use Filament\Support\Commands\Concerns\HasResourcesLocation;
use Filament\Support\Commands\FileGenerators\Concerns\CanCheckFileGenerationFlags;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;
use SolutionForest\FilamentTree\Commands\FileGenerators\TreeResourceClassGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

#[AsCommand(
    name: 'make:filament-tree-resource',
    description: 'Creates a Filament tree resource class',
)]
class MakeTreeResourceCommand extends Command
{
    use CanAskForRelatedModel;
    use CanCheckFileGenerationFlags;
    use CanManipulateFiles;
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

    protected string $resourcesNamespace;

    protected string $resourcesDirectory;

    protected ?string $clusterFqn = null;

    protected bool $includeViewPages = false;

    protected bool $includeSoftDeletes = false;

    public static bool $shouldCheckModelsForSoftDeletes = true;

    protected function configure()
    {
        $this->addArgument(
            name: 'name',
            mode: InputArgument::OPTIONAL,
            description: 'The name of the resource, optionally prefixed with directories (e.g. `CategoryResource`)',
        );

        $this
            ->addOption(
                name: 'model',
                shortcut: 'M',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The model class for the resource (e.g. `Category`)',
            )
            ->addOption(
                name: 'model-namespace',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'The namespace of the model class, [App\\Models] by default',
                default: 'App\\Models',
            )
            ->addOption(
                name: 'panel',
                shortcut: 'P',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The panel to use for the resource (e.g. `admin`)',
            )
            ->addOption(
                name: 'view',
                shortcut: null,
                mode: InputOption::VALUE_NONE,
                description: 'Generate view pages in addition to create/edit',
            )
            ->addOption(
                name: 'soft-deletes',
                shortcut: null,
                mode: InputOption::VALUE_NONE,
                description: 'Include soft delete support',
            )
            ->addOption(
                name: 'force',
                shortcut: 'F',
                mode: InputOption::VALUE_NONE,
                description: 'Overwrite the contents of the files if they already exist',
            )
            ->addOption(
                name: 'generate-form',
                shortcut: null,
                mode: InputOption::VALUE_NONE,
                description: 'Generate form components based on model attributes',
            );
    }

    public function handle(): int
    {
        try {
            $this->configureFqnEnd();
            $this->configureModel();
            $this->configurePanel(question: 'Which panel would you like to create this resource in?');
            $this->configureResourcesLocation(question: 'Where would you like to create the resource?');
            $this->configureLocation();
            $this->configureOptions();

            $this->createResource();
            $this->createPages();

            $this->info("Successfully created TreeResource [{$this->fqn}]!");

            return static::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return static::FAILURE;
        }
    }

    protected function configureFqnEnd(): void
    {
        $name = $this->argument('name') ?? text(
            label: 'What is the resource name?',
            placeholder: 'CategoryResource',
            hint: 'The resource class name (e.g. CategoryResource)',
            required: true,
        );

        if (!str_ends_with($name, 'Resource')) {
            $name .= 'Resource';
        }

        $this->fqnEnd = (string) Str::of($name)
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        if (blank($this->fqnEnd)) {
            throw new \InvalidArgumentException('Resource name cannot be empty');
        }
    }

    protected function configureModel(): void
    {
        $modelName = $this->option('model') ?? text(
            label: 'What is the model name?',
            placeholder: 'Category',
            hint: 'The Eloquent model class name',
            default: str_replace('Resource', '', $this->fqnEnd),
        );

        $modelNamespace = $this->option('model-namespace') ?? 'App\\Models';

        $this->modelFqn = "{$modelNamespace}\\{$modelName}";
        $this->modelFqnEnd = $modelName;

        // Validate model exists
        if (!class_exists($this->modelFqn)) {
            $createModel = confirm(
                label: "Model [{$this->modelFqn}] does not exist. Would you like to continue anyway?",
                default: false,
            );

            if (!$createModel) {
                throw new \InvalidArgumentException("Model [{$this->modelFqn}] does not exist");
            }
        } else {
            $this->validateTreeModel();
        }
    }

    protected function validateTreeModel(): void
    {
        $reflection = new ReflectionClass($this->modelFqn);
        $model = $reflection->newInstance();

        // Check for required tree columns in fillable or database
        $requiredColumns = ['parent_id', 'order'];
        $fillableColumns = $model->getFillable();
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $fillableColumns)) {
                $this->warn("Model [{$this->modelFqn}] should include '{$column}' in fillable attributes for tree functionality");
            }
        }

        // Check for title column
        $titleColumns = ['title', 'name', 'label'];
        $hasTitleColumn = false;
        foreach ($titleColumns as $column) {
            if (in_array($column, $fillableColumns)) {
                $hasTitleColumn = true;
                break;
            }
        }

        if (!$hasTitleColumn) {
            $this->warn("Model [{$this->modelFqn}] should have a title column (name, title, or label) for tree display");
        }
    }

    protected function configureLocation(): void
    {
        $this->fqn = $this->resourcesNamespace . '\\' . $this->fqnEnd;
        $this->info("Resource will be created at: {$this->fqn}");
    }

    protected function configureOptions(): void
    {
        $this->includeViewPages = $this->option('view');
        $this->includeSoftDeletes = $this->option('soft-deletes');

        if ($this->includeSoftDeletes && class_exists($this->modelFqn)) {
            $model = new $this->modelFqn();
            if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model))) {
                $this->warn("Soft deletes support was requested but model [{$this->modelFqn}] does not use SoftDeletes trait");
            }
        }
    }

    protected function createResource(): void
    {
        // Create v4 directory structure: Resources/ModelPlural/
        $pluralModel = $this->getPluralModelLabel();
        $resourceDir = $this->getResourcesDirectory() . '/' . $pluralModel;
        $path = $resourceDir . '/' . $this->fqnEnd . '.php';

        $this->checkForCollision($path);

        // Create main resource file
        $this->createMainResource($path, $resourceDir, $pluralModel);
        
        // Create schema file
        $this->createSchemaFile($resourceDir, $pluralModel);
        
        // Create tree file  
        $this->createTreeFile($resourceDir, $pluralModel);
    }

    protected function createMainResource(string $path, string $resourceDir, string $pluralModel): void
    {
        $stubPath = __DIR__ . '/../../stubs/filament-tree/tree-resource.stub';
        
        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$stubPath}");
        }

        $stub = file_get_contents($stubPath);
        $modelNamespace = $this->extractNamespace($this->modelFqn);
        $modelClass = class_basename($this->modelFqn);
        
        $schemaClass = $this->getModelLabel() . 'Form';
        $treeClass = $pluralModel . 'Tree';
        
        $content = str_replace([
            '{{ namespace }}',
            '{{ modelNamespace }}',
            '{{ model }}',
            '{{ pluralModel }}',
            '{{ class }}',
            '{{ navigationIcon }}',
            '{{ navigationLabel }}',
            '{{ modelLabel }}',
            '{{ pluralModelLabel }}',
            '{{ schemaNamespace }}',
            '{{ schemaClass }}',
            '{{ treeNamespace }}',
            '{{ treeClass }}',
            '{{ viewPage }}',
            '{{ customMethods }}',
        ], [
            $this->resourcesNamespace . '\\' . $pluralModel,
            $modelNamespace,
            $modelClass,
            $pluralModel,
            $this->fqnEnd,
            'heroicon-o-rectangle-stack',
            $this->getPluralModelLabel(),
            $this->getModelLabel(),
            $this->getPluralModelLabel(),
            $this->resourcesNamespace . '\\' . $pluralModel . '\\Schemas',
            $schemaClass,
            $this->resourcesNamespace . '\\' . $pluralModel . '\\Trees',
            $treeClass,
            $this->includeViewPages ? "\n            'view' => Pages\\View{$modelClass}::route('/{record}')," : '',
            '',
        ], $stub);

        if (!is_dir($resourceDir)) {
            mkdir($resourceDir, 0755, true);
        }

        $this->writeFile($path, $content);
    }

    protected function createSchemaFile(string $resourceDir, string $pluralModel): void
    {
        $schemaDir = $resourceDir . '/Schemas';
        $schemaClass = $this->getModelLabel() . 'Form';
        $schemaPath = $schemaDir . '/' . $schemaClass . '.php';
        
        $stubPath = __DIR__ . '/../../stubs/filament-tree/tree-form-schema.stub';
        
        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Schema stub file not found: {$stubPath}");
        }

        $stub = file_get_contents($stubPath);
        $formComponents = $this->generateFormComponents();
        $modelNamespace = $this->extractNamespace($this->modelFqn);
        $modelClass = class_basename($this->modelFqn);
        
        $content = str_replace([
            '{{ namespace }}',
            '{{ modelNamespace }}',
            '{{ model }}',
            '{{ class }}',
            '{{ formComponents }}',
        ], [
            $this->resourcesNamespace . '\\' . $pluralModel . '\\Schemas',
            $modelNamespace,
            $modelClass,
            $schemaClass,
            $formComponents,
        ], $stub);

        if (!is_dir($schemaDir)) {
            mkdir($schemaDir, 0755, true);
        }

        $this->writeFile($schemaPath, $content);
    }

    protected function createTreeFile(string $resourceDir, string $pluralModel): void
    {
        $treeDir = $resourceDir . '/Trees';
        $treeClass = $pluralModel . 'Tree';
        $treePath = $treeDir . '/' . $treeClass . '.php';
        
        $stubPath = __DIR__ . '/../../stubs/filament-tree/tree-tree-schema.stub';
        
        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Tree stub file not found: {$stubPath}");
        }

        $stub = file_get_contents($stubPath);
        $modelNamespace = $this->extractNamespace($this->modelFqn);
        $modelClass = class_basename($this->modelFqn);
        
        $resourceClass = $this->resourcesNamespace . '\\' . $pluralModel . '\\' . $this->fqnEnd;
        
        $content = str_replace([
            '{{ namespace }}',
            '{{ modelNamespace }}',
            '{{ model }}',
            '{{ class }}',
            '{{ resourceClass }}',
            '{{ maxDepth }}',
        ], [
            $this->resourcesNamespace . '\\' . $pluralModel . '\\Trees',
            $modelNamespace,
            $modelClass,
            $treeClass,
            $resourceClass,
            '10',
        ], $stub);

        if (!is_dir($treeDir)) {
            mkdir($treeDir, 0755, true);
        }

        $this->writeFile($treePath, $content);
    }

    protected function generateFormComponents(): string
    {
        // Always add tree management fields
        $components = [
            "Hidden::make('parent_id')",
            "Hidden::make('order')",
        ];

        // Only generate form components if the option is enabled
        if ($this->option('generate-form')) {
            $generatedComponents = [];

            if (class_exists($this->modelFqn)) {
                $model = new $this->modelFqn();
                $fillable = $model->getFillable();
                
                foreach ($fillable as $attribute) {
                    $component = $this->generateFormComponent($attribute);
                    if ($component) {
                        $generatedComponents[] = $component;
                    }
                }
            }

            // Add default components if none were generated
            if (empty($generatedComponents)) {
                $generatedComponents = [
                    "TextInput::make('name')\n                ->required()\n                ->maxLength(255)",
                    "Textarea::make('description')\n                ->rows(3)\n                ->columnSpanFull()",
                ];
            }

            // Merge generated components with tree management fields
            $components = array_merge($generatedComponents, $components);
        }

        return "\n            " . implode(",\n            ", $components) . ",\n        ";
    }

    protected function generateFormComponent(string $attribute): ?string
    {
        // Skip tree management columns
        if (in_array($attribute, ['parent_id', 'order', 'id', 'created_at', 'updated_at', 'deleted_at'])) {
            return null;
        }

        // Generate components based on attribute names
        return match (true) {
            str_contains($attribute, 'email') => "TextInput::make('{$attribute}')\n                ->email()\n                ->maxLength(255)",
            str_contains($attribute, 'password') => "TextInput::make('{$attribute}')\n                ->password()\n                ->maxLength(255)",
            str_contains($attribute, 'phone') => "TextInput::make('{$attribute}')\n                ->tel()\n                ->maxLength(50)",
            str_contains($attribute, 'url') || str_contains($attribute, 'link') => "TextInput::make('{$attribute}')\n                ->url()\n                ->maxLength(255)",
            $attribute === 'name' || $attribute === 'title' => "TextInput::make('{$attribute}')\n                ->required()\n                ->maxLength(255)",
            str_contains($attribute, 'description') || str_contains($attribute, 'content') || str_contains($attribute, 'notes') => "Textarea::make('{$attribute}')\n                ->rows(3)\n                ->columnSpanFull()",
            str_contains($attribute, 'status') || str_contains($attribute, 'type') => "Select::make('{$attribute}')\n                ->options([\n                    // Add your options here\n                ])",
            str_contains($attribute, '_id') && $attribute !== 'parent_id' => "Select::make('{$attribute}')\n                ->relationship('" . str_replace('_id', '', $attribute) . "', 'name')",
            default => "TextInput::make('{$attribute}')\n                ->maxLength(255)",
        };
    }

    protected function extractNamespace(string $fqn): string
    {
        $parts = explode('\\', $fqn);
        array_pop($parts); // Remove class name
        return implode('\\', $parts);
    }

    protected function getPluralModelLabel(): string
    {
        return Str::plural(class_basename($this->modelFqn));
    }

    protected function getModelLabel(): string
    {
        return class_basename($this->modelFqn);
    }

    protected function checkForCollision(string $path): void
    {
        if (!$this->option('force') && $this->checkForFilamentCollision($path)) {
            throw new \InvalidArgumentException("Resource [{$this->fqn}] already exists. Use --force to overwrite.");
        }
    }

    protected function getResourcesDirectory(): string
    {
        return $this->resourcesDirectory;
    }

    protected function checkForFilamentCollision(string $path): bool
    {
        return file_exists($path);
    }

    protected function createPages(): void
    {
        $pluralModel = $this->getPluralModelLabel();
        $pagesDirectory = $this->getResourcesDirectory() . '/' . $pluralModel . '/Pages';
        $pagesNamespace = $this->resourcesNamespace . '\\' . $pluralModel . '\\Pages';

        $this->createPage('List', $pagesDirectory, $pagesNamespace);
        $this->createPage('Create', $pagesDirectory, $pagesNamespace);
        $this->createPage('Edit', $pagesDirectory, $pagesNamespace);
        
        if ($this->includeViewPages) {
            $this->createPage('View', $pagesDirectory, $pagesNamespace);
        }
    }

    protected function createPage(string $type, string $directory, string $namespace): void
    {
        // Follow Filament naming convention: ListModels (plural), CreateModel, EditModel, ViewModel (singular)
        $pageName = $type . ($type === 'List' ? $this->getPluralModelLabel() : $this->modelFqnEnd);
        $stubPath = __DIR__ . '/../../stubs/filament-tree/tree-resource-' . strtolower($type) . '-page.stub';
        
        if (!file_exists($stubPath)) {
            return;
        }

        $content = file_get_contents($stubPath);
        $pluralModel = $this->getPluralModelLabel();
        
        $content = str_replace([
            '{{ namespace }}',
            '{{ resourceNamespace }}',
            '{{ resource }}',
            '{{ model }}',
        ], [
            $namespace,
            $this->resourcesNamespace . '\\' . $pluralModel,
            $this->fqnEnd,
            ($type === 'List' ? $this->getPluralModelLabel() : $this->modelFqnEnd),
        ], $content);

        $path = $directory . '/' . $pageName . '.php';
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->writeFile($path, $content);
    }
}
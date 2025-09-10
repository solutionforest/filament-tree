<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators;

use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Illuminate\Database\Eloquent\Model;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use SolutionForest\FilamentTree\Resources\TreeResource;

class TreeResourceClassGenerator extends ClassGenerator
{
    /**
     * @param  class-string<Model>  $modelFqn
     */
    final public function __construct(
        protected string $fqn,
        protected string $model,
        protected bool $includeViewPages = false,
        protected bool $includeSoftDeletes = false,
    ) {}

    public function getFqn(): string
    {
        return $this->fqn;
    }

    public function getNamespace(): string
    {
        return $this->extractNamespace($this->getFqn());
    }

    /**
     * @return array<string>
     */
    public function getImports(): array
    {
        return [
            $this->model,
            TreeResource::class,
            'Filament\\Schemas\\Schema',
            'Filament\\Schemas\\Components\\TextInput',
            'Filament\\Schemas\\Components\\Textarea', 
            'Filament\\Schemas\\Components\\Select',
            'Filament\\Schemas\\Components\\Hidden',
            'SolutionForest\\FilamentTree\\Components\\Tree',
            'SolutionForest\\FilamentTree\\Actions\\CreateAction',
            'SolutionForest\\FilamentTree\\Actions\\CreateChildAction',
            'SolutionForest\\FilamentTree\\Actions\\EditAction',
            'SolutionForest\\FilamentTree\\Actions\\DeleteAction',
            ...($this->includeViewPages ? ['SolutionForest\\FilamentTree\\Actions\\ViewAction'] : []),
            ...($this->includeSoftDeletes ? ['SolutionForest\\FilamentTree\\Actions\\RestoreAction'] : []),
        ];
    }

    public function getBasename(): string
    {
        return class_basename($this->getFqn());
    }

    public function getExtends(): string
    {
        return TreeResource::class;
    }

    public function getBody(): string
    {
        $modelBasename = class_basename($this->model);
        $modelVariable = lcfirst($modelBasename);

        return <<<PHP
<?php

namespace {$this->getNamespace()};

use {$this->model};
use SolutionForest\FilamentTree\Resources\TreeResource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Hidden;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Actions\CreateAction;
use SolutionForest\FilamentTree\Actions\CreateChildAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\DeleteAction;{$this->getViewImport()}{$this->getSoftDeleteImports()}

class {$this->getBasename()} extends TreeResource
{
    protected static ?string \$model = {$modelBasename}::class;

    protected static ?string \$navigationIcon = 'heroicon-o-folder-tree';

    protected static ?string \$navigationLabel = '{$this->getPluralModelName()}';

    protected static ?string \$modelLabel = '{$modelBasename}';

    protected static ?string \$pluralModelLabel = '{$this->getPluralModelName()}';

    protected static ?int \$navigationSort = null;

    /**
     * Configure the tree structure
     */
    public static function tree(Tree \$tree): Tree
    {
        return \$tree
            ->title('{$this->getTitleColumn()}')
            ->maxDepth(5)
            ->collapsible()
            ->searchable();
    }

    /**
     * Define the form schema for create/edit actions
     */
    public static function form(Schema \$schema): Schema
    {
        return \$schema->components([{$this->generateFormComponents()}
        ]);
    }

    /**
     * Get tree actions (appears on each tree node)
     */
    public static function getTreeActions(): array
    {
        return [
            CreateChildAction::make(),
            EditAction::make(),{$this->getViewAction()}
            DeleteAction::make(),{$this->getSoftDeleteActions()}
        ];
    }

    /**
     * Get header actions (create root level items)
     */
    public static function getTreeHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
PHP;
    }

    protected function getViewImport(): string
    {
        return $this->includeViewPages ? "\nuse SolutionForest\\FilamentTree\\Actions\\ViewAction;" : '';
    }

    protected function getSoftDeleteImports(): string
    {
        return $this->includeSoftDeletes ? "\nuse SolutionForest\\FilamentTree\\Actions\\RestoreAction;" : '';
    }

    protected function getViewAction(): string
    {
        return $this->includeViewPages ? "\n            ViewAction::make()," : '';
    }

    protected function getSoftDeleteActions(): string
    {
        return $this->includeSoftDeletes ? "\n            RestoreAction::make()," : '';
    }

    protected function getPluralModelName(): string
    {
        $modelName = class_basename($this->model);
        return str($modelName)->plural()->title()->toString();
    }

    protected function getTitleColumn(): string
    {
        // Try to detect common title column names
        $possibleColumns = ['name', 'title', 'label'];
        
        if (class_exists($this->model)) {
            $model = new $this->model();
            $fillable = $model->getFillable();
            
            foreach ($possibleColumns as $column) {
                if (in_array($column, $fillable)) {
                    return $column;
                }
            }
        }

        // Default to 'name' if we can't detect
        return 'name';
    }

    protected function generateFormComponents(): string
    {
        $components = [];
        
        // Try to generate form components based on model fillable attributes
        if (class_exists($this->model)) {
            $model = new $this->model();
            $fillable = $model->getFillable();
            
            foreach ($fillable as $attribute) {
                $component = $this->generateFormComponent($attribute);
                if ($component) {
                    $components[] = $component;
                }
            }
        }

        // Add default components if none were generated
        if (empty($components)) {
            $components = [
                "TextInput::make('name')\n                ->required()\n                ->maxLength(255)",
                "Textarea::make('description')\n                ->rows(3)\n                ->columnSpanFull()",
            ];
        }

        // Always add tree management fields
        $components[] = "Hidden::make('parent_id')";
        $components[] = "Hidden::make('order')";

        return "\n            " . implode(",\n            ", $components) . "\n        ";
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
}
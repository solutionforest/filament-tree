# Make Filament Tree Resource Command

## Overview

The `make:filament-tree-resource` command generates a complete TreeResource class with all necessary components for managing hierarchical data structures in Filament v4. This command works similarly to `make:filament-resource` but creates resources specifically designed for tree structures using the new Filament v4 directory structure with separated Schema and Tree configuration classes.

## Command Signature

```bash
php artisan make:filament-tree-resource {name} {--model=} {--view} {--soft-deletes} {--force} {--panel=}
```

## Parameters

### Required Arguments
- `name` - The name of the resource (e.g., `CategoryResource`, `LocationResource`)

### Options
- `--model=` - Specify the model class to use (e.g., `--model=Category`)
- `--view` - Generate view pages in addition to create/edit
- `--soft-deletes` - Include soft delete support
- `--force` - Overwrite existing files
- `--panel=` - Specify the panel name (default: `admin`)

## Generated Directory Structure (Filament v4)

The command follows Filament v4 conventions and generates:

```
app/Filament/Resources/
└── {PluralModel}/                    # e.g., Categories/
    ├── {Model}Resource.php           # Main resource file
    ├── Pages/                        # Page classes directory
    │   ├── List{Model}.php
    │   ├── Create{Model}.php
    │   ├── Edit{Model}.php
    │   └── View{Model}.php           # Optional, with --view flag
    ├── Schemas/                      # Form schemas directory
    │   └── {Model}Form.php           # Form schema class
    └── Trees/                        # Tree configurations directory
        └── {PluralModel}Tree.php     # Tree configuration class
```

## Generated Files

The command generates the following files:

### 1. Main TreeResource Class
**Location**: `app/Filament/Resources/{PluralModel}/{Model}Resource.php`

```php
<?php

namespace App\Filament\Resources\{PluralModel};

use App\Models\{Model};
use SolutionForest\FilamentTree\Resources\TreeResource;
use App\Filament\Resources\{PluralModel}\Schemas\{Model}Form;
use App\Filament\Resources\{PluralModel}\Trees\{PluralModel}Tree;
use SolutionForest\FilamentTree\Components\Tree;
use Filament\Schemas\Schema;

class {Model}Resource extends TreeResource
{
    protected static ?string $model = {Model}::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-folder-tree';
    protected static ?string $navigationLabel = '{PluralModel}';
    protected static ?string $modelLabel = '{Model}';
    protected static ?string $pluralModelLabel = '{PluralModel}';
    protected static ?int $navigationSort = null;

    /**
     * Configure the tree structure
     */
    public static function tree(Tree $tree): Tree
    {
        return {PluralModel}Tree::tree($tree);
    }

    /**
     * Define the form schema for create/edit actions
     */
    public static function form(Schema $schema): Schema
    {
        return {Model}Form::form($schema);
    }

    /**
     * Get tree actions (appears on each tree node)
     */
    public static function getTreeActions(): array
    {
        return {PluralModel}Tree::getTreeActions();
    }

    /**
     * Get header actions (create root level items)
     */
    public static function getTreeHeaderActions(): array
    {
        return {PluralModel}Tree::getTreeHeaderActions();
    }
}
```

### 2. Form Schema Class  
**Location**: `app/Filament/Resources/{PluralModel}/Schemas/{Model}Form.php`

```php
<?php

namespace App\Filament\Resources\{PluralModel}\Schemas;

use App\Models\{Model};
use Filament\Schemas\Schema;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Hidden;

class {Model}Form
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            // ... other intelligently generated fields
            Hidden::make('parent_id'),
            Hidden::make('order')
        ]);
    }
}
```

### 3. Tree Configuration Class
**Location**: `app/Filament/Resources/{PluralModel}/Trees/{PluralModel}Tree.php`

```php
<?php

namespace App\Filament\Resources\{PluralModel}\Trees;

use App\Models\{Model};
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Actions\CreateAction;
use SolutionForest\FilamentTree\Actions\CreateChildAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\DeleteAction;

class {PluralModel}Tree
{
    /**
     * Configure the tree structure
     */
    public static function tree(Tree $tree): Tree
    {
        return $tree
            ->title('name')
            ->maxDepth(10)
            ->collapsible()
            ->searchable();
    }

    /**
     * Get tree actions (appears on each tree node)
     */
    public static function getTreeActions(): array
    {
        return [
            CreateChildAction::make(),
            EditAction::make(),
            DeleteAction::make(),
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
    
    public static function tree(Tree $tree): Tree
    {
        return $tree
            ->title('name')
            ->maxDepth(5)
            ->collapsible()
            ->searchable();
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Form components will be generated based on model
        ]);
    }
    
    public static function getTreeActions(): array
    {
        return [
            CreateChildAction::make(),
            EditAction::make(),
            ViewAction::make(), // Only if --view flag is used
            DeleteAction::make(),
        ];
    }
    
    public static function getTreeHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

### 2. Resource Pages (if not using TreeResource's default pages)

The command can optionally generate custom resource pages:

**Location**: `app/Filament/Resources/{Name}Resource/Pages/`

- `List{Name}.php` - Custom list page with tree
- `Create{Name}.php` - Create page
- `Edit{Name}.php` - Edit page  
- `View{Name}.php` - View page (if `--view` flag used)

### 3. Publishable Stubs

The command uses publishable stub files that can be customized:

**Stub Location**: `stubs/filament-tree/`

- `tree-resource.stub` - Main TreeResource template
- `tree-resource-list-page.stub` - List page template
- `tree-resource-create-page.stub` - Create page template
- `tree-resource-edit-page.stub` - Edit page template
- `tree-resource-view-page.stub` - View page template

## Usage Examples

### Basic Usage

```bash
php artisan make:filament-tree-resource CategoryResource
```

Generates a basic TreeResource for categories.

### With Model Specification

```bash
php artisan make:filament-tree-resource LocationResource --model=Location
```

Generates a TreeResource specifically for the Location model.

### With View Pages

```bash
php artisan make:filament-tree-resource CategoryResource --view
```

Includes ViewAction and generates view pages.

### With Soft Deletes

```bash
php artisan make:filament-tree-resource CategoryResource --soft-deletes
```

Includes restore functionality for soft-deleted records.

### Complete Example

```bash
php artisan make:filament-tree-resource LocationResource --model=Location --view --soft-deletes
```

## Model Requirements

The target model must have the following attributes for tree functionality:

### Required Database Columns
- `parent_id` (integer, default: -1) - Parent relationship
- `order` (integer, default: 0) - Sort order within siblings
- `title` or `name` (string) - Display title

### Model Relationships

```php
class Location extends Model
{
    protected $fillable = ['name', 'parent_id', 'order', 'description'];
    
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id')->orderBy('order');
    }
}
```

## Command Features

### 1. Intelligent Form Generation
- Detects model fillable attributes
- Auto-generates appropriate form components
- Includes Hidden fields for `parent_id` and `order`
- Adds validation based on model rules

### 2. Policy Integration
- Detects existing model policies
- Configures policy-based authorization if available
- Sets up ability mapping automatically

### 3. Relationship Detection
- Identifies parent-child relationships
- Configures tree title column automatically
- Sets up proper navigation labels

### 4. Customizable Stubs
- All templates are publishable and customizable
- Support for custom form layouts
- Configurable action sets
- Template variables for dynamic content

## Publishing and Customizing Stubs

### Publish Stubs

```bash
php artisan vendor:publish --tag="filament-tree-stubs"
```

This publishes stub files to `stubs/filament-tree/` in your project.

### Available Stub Variables

- `{{ class }}` - Resource class name
- `{{ model }}` - Model class name
- `{{ modelVariable }}` - Model variable name (camelCase)
- `{{ title }}` - Human readable title
- `{{ navigationIcon }}` - Icon for navigation
- `{{ namespace }}` - Resource namespace
- `{{ modelNamespace }}` - Model namespace

### Customizing Generated Resources

After publishing stubs, you can:

1. **Modify Form Components**: Customize the form schema generation
2. **Add Custom Actions**: Include additional tree actions
3. **Configure Tree Settings**: Adjust default tree configuration
4. **Set Navigation Options**: Customize navigation labels and icons

## Advanced Configuration

### Custom Tree Configuration

The generated resource can be configured with advanced tree options:

```php
public static function tree(Tree $tree): Tree
{
    return $tree
        ->title('name')
        ->description('description') // Optional description column
        ->maxDepth(5)
        ->collapsible()
        ->searchable()
        ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
        ->emptyStateHeading('No items found')
        ->emptyStateDescription('Get started by creating a new item.');
}
```

### Custom Actions with Hooks

```php
public static function getTreeActions(): array
{
    return [
        CreateChildAction::make()
            ->beforeAction(function ($record, $data) {
                // Custom logic before creating child
                $data['created_by'] = auth()->id();
                return $data;
            })
            ->afterAction(function ($record, $data, $result) {
                // Log the creation
                activity()->performedOn($result)->log('Created child item');
                return $result;
            }),
        
        EditAction::make(),
        DeleteAction::make()
            ->beforeAction(function ($record) {
                if ($record->children()->count() > 0) {
                    throw new \Exception('Cannot delete item with children');
                }
                return true;
            }),
    ];
}
```

## Error Handling

The command includes comprehensive error handling:

### Model Validation
- Verifies model exists and is accessible
- Checks for required tree columns
- Validates model relationships

### File Conflicts
- Detects existing resource files
- Provides `--force` option to overwrite
- Shows clear conflict resolution options

### Dependency Checks
- Ensures Filament Tree package is installed
- Validates required traits and interfaces
- Checks panel configuration

## Testing the Generated Resource

After generating a TreeResource, you can test it by:

1. **Database Setup**: Ensure your model's table has the required tree columns
2. **Seed Data**: Create test data with parent-child relationships
3. **Navigation**: Verify the resource appears in Filament navigation
4. **Tree Operations**: Test create, edit, delete, and reordering operations
5. **Policy Authorization**: Verify authorization works correctly

## Integration with Existing Resources

If you already have a standard Filament Resource, you can:

1. **Generate New TreeResource**: Create alongside existing resource
2. **Migration Path**: Copy form schemas and customizations
3. **Navigation Management**: Hide standard resource, show tree resource
4. **Data Compatibility**: Ensure tree columns exist in existing data

## Best Practices

### 1. Naming Conventions
- Use singular form for resource names (e.g., `CategoryResource`, not `CategoriesResource`)
- Match model naming exactly
- Use descriptive navigation labels

### 2. Tree Structure Design
- Keep maximum depth reasonable (typically 3-5 levels)
- Consider performance with large datasets
- Plan for tree reorganization needs

### 3. Form Design
- Include essential fields in tree creation
- Use validation to prevent circular references
- Provide clear field labels and help text

### 4. Action Configuration
- Choose appropriate actions for your use case
- Implement safety checks in delete actions
- Provide meaningful success/error messages

### 5. Policy Integration
- Define clear authorization rules
- Test different user permission levels
- Handle unauthorized access gracefully

## Troubleshooting

### Common Issues

1. **Model not found**: Ensure model exists and is in correct namespace
2. **Missing tree columns**: Run migration to add required columns
3. **Permission denied**: Check file permissions in target directories
4. **Resource conflicts**: Use `--force` flag or choose different name

### Debug Mode

Run command with `-vvv` for verbose output:

```bash
php artisan make:filament-tree-resource CategoryResource -vvv
```

This shows detailed information about file generation and any errors encountered.
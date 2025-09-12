# Filament Tree Plugin - AI Coding Guide

This is a Laravel package that provides hierarchical tree management for Filament Admin panels, enabling drag-and-drop tree structures for models like menus and categories.

## Architecture Overview

**Trait-Based Design**: The plugin heavily uses traits to add tree functionality to models and pages:

- `ModelTree` trait: Adds tree behavior to Eloquent models (parent-child relationships, ordering)
- `TreePageTrait` trait: Adds tree page functionality to Filament resources
- `InteractWithTree` trait: Core tree interaction logic for widgets and pages

**Key Components**:

- `Tree` component: Main UI component handling tree rendering and drag-drop
- Actions: `CreateAction`, `EditAction`, `DeleteAction`, `ViewAction` for tree items
- Commands: Artisan generators for tree pages and widgets

## Model Conventions

**Required Database Structure**:

```php
// Critical: parent_id MUST default to -1 (not null)
$table->integer('parent_id')->default(-1)->index();
$table->integer('order')->default(0);
$table->string('title'); // or any display field
```

**Model Setup Pattern**:

```php
use SolutionForest\FilamentTree\Concern\ModelTree;

class Category extends Model
{
    use ModelTree;

    protected $fillable = ["parent_id", "title", "order"];
    protected $casts = ['parent_id' => 'int'];
}
```

## Page/Widget Creation Patterns

**Use Artisan Generators**:

- `php artisan make:filament-tree-page CategoryTree --resource=Category`
- `php artisan make:filament-tree-widget CategoryWidget`

**Action Configuration**: Override these methods to customize tree item actions:

```php
protected function configureEditAction(EditAction $action): EditAction
{
    return $action->slideOver()->modalHeading('Edit Category');
}
```

## Critical Configuration

**Config Structure** (`config/filament-tree.php`):

- Column name mappings for `order`, `parent`, `title` fields
- Default parent ID (-1 is required convention)
- Children key name for relationships

**Asset Registration**: After installation, run `php artisan filament:assets` to register CSS/JS assets.

## Build & Development

**Frontend Build**:

- `npm run dev` - Watch mode for styles and scripts
- `npm run build` - Production build with minification
- Uses esbuild for JavaScript, TailwindCSS for styles

**Quality Tools**:

- `composer analyse` - PHPStan static analysis
- `composer lint` - Laravel Pint code formatting
- `composer test` - Pest testing framework

## Common Patterns

**Tree Depth Control**: Set `protected static int $maxDepth = 2;` in widgets/pages

**Custom Record Display**:

```php
public function getTreeRecordTitle(?Model $record = null): string
{
    return "[{$record->id}] {$record->title}";
}
```

**Action Visibility**: Control which actions appear using:

```php
protected function hasEditAction(): bool { return true; }
protected function hasDeleteAction(): bool { return false; }
```

**Tree Record Icons**: Customize icons for tree items:

```php
public function getTreeRecordIcon(?Model $record = null): ?string
{
    if ($record->parent_id != -1) {
        return null; // no icon for child records
    }

    return match ($record->title) {
        'Categories' => 'heroicon-o-tag',
        'Products' => 'heroicon-o-shopping-bag',
        'Settings' => 'heroicon-o-cog',
        default => 'heroicon-o-folder',
    };
}
```

**Toolbar Actions**: Add global actions displayed above the tree:

```php
protected function getTreeToolbarActions(): array
{
    return [
        CreateAction::make(),
        ExportAction::make(),
        ImportAction::make(),
    ];
}
```

> **Note**: Toolbar actions are only supported in version 3.1.0 and later.

## Key File Locations

- Models: Add `ModelTree` trait to any hierarchical model
- Pages: Extend from classes in `src/Pages/` or use trait
- Widgets: Extend `SolutionForest\FilamentTree\Widgets\Tree`
- Views: Located in `resources/views/` (customizable)
- Assets: `resources/css/` and `resources/js/` (built to `resources/dist/`)

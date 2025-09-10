> [!IMPORTANT]
> Please note that we will only be updating to version 2.x or 3.x, excluding any bug fixes.

> [!NOTE]
> **Enhanced Fork**: This is an enhanced fork of the original Filament Tree plugin. We've built upon the excellent work of the original authors to add policy authorization, dedicated tree resources that replace table views, and enhanced Filament v4 compatibility. This fork was developed to meet specific requirements - replacing tables with trees in some resources and applying Laravel policies - and we found it more beneficial to enhance the existing plugin rather than start from scratch. We've tried our best to make the component behave exactly like the original until the new features are activated, maintaining full backward compatibility. Until the original developers accept these enhancements (if they choose to), this should be treated as a beta version developed with these specific use cases in mind. We invite anyone interested to help us debug and improve these changes.

# Filament Tree

Filament Tree is a plugin for Filament Admin that creates a model management page with a heritage tree structure view. This plugin can be used to create menus and more.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)

This plugin creates model management page with heritage tree structure view for Filament Admin. It could be used to create menu, etc.

Demo site : https://filament-cms-website-demo.solutionforest.net/admin

Demo username : demo@solutionforest.net

Demo password : 12345678
Auto Reset every hour.

## Supported Filament versions

| Filament Version | Plugin Version |
| ---------------- | -------------- |
| v3               | 2.x.x          |
| v4               | 3.x.x          |

## Installation

To install the package, run the following command:

```bash
composer require solution-forest/filament-tree
```

> **Important: Need to publish assets after version 2.x**

```bash
php artisan filament:assets
```

> **Note: Add plugin Blade files to your custom theme `tailwind.config.js` for dark mode.**
>
> To set up your own custom theme, you can visit the [official instruction page](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) on the Filament website.

Add the plugin's views and css to your `theme.css` file.

```css
@import '<path-to-vendor>/solution-forest/filament-tree/resources/css/jquery.nestable.css';
@import '<path-to-vendor>/solution-forest/filament-tree/resources/css/button.css';
@import '<path-to-vendor>/solution-forest/filament-tree/resources/css/custom-nestable-item.css';
@source '<path-to-vendor>/solution-forest/filament-tree/resources/**/*.blade.php';
```

Then, publish the config file using:

```bash
php artisan vendor:publish --tag="filament-tree-config"
```

You can set your preferred options by adding the following code to your `config/filament-tree.php` file:

```php
<?php

return [
    /**
     * Tree model fields
     */
    'column_name' => [
        'order' => 'order',
        'parent' => 'parent_id',
        'title' => 'title',
    ],
    /**
     * Tree model default parent key
     */
    'default_parent_id' => -1,
    /**
     * Tree model default children key name
     */
    'default_children_key_name' => 'children',
];

```

![Using tree widget on page](https://github.com/user-attachments/assets/1f09d707-b6ac-4d68-9009-55dade707c43)

## Usage

### Prepare the database and model

To use Filament Tree, follow these table structure conventions:

> **Tip: The `parent_id` field must always default to -1!!!**

```
Schema::create('product_categories', function (Blueprint $table) {
    $table->id();
    $table->integer('parent_id')->default(-1);
    $table->integer('order')->default(0)->index();
    $table->string('title');
    $table->timestamps();
});
```

This plugin provides a convenient method called `treeColumns()` that you can use to add the required columns for the tree structure to your table more easily. Here's an example:

```
Schema::create('product_categories', function (Blueprint $table) {
    $table->id();
    $table->treeColumns();
    $table->timestamps();
});
```

This will automatically add the required columns for the tree structure to your table.

The above table structure contains three required fields: `parent_id`, `order`, `title`, and other fields do not have any requirements.

The corresponding model is `app/Models/ProductCategory.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;

class ProductCategory extends Model
{
    use ModelTree;

    protected $fillable = ["parent_id", "title", "order"];

    protected $casts = [
        'parent_id' => 'int'
    ];

    protected $table = 'product_categories';
}
```

The field names of the three fields `parent_id`, `order`, and `title` in the table structure can also be modified:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;

class ProductCategory extends Model
{
    use ModelTree;

    protected $fillable = ["parent_id", "title", "order"];

    protected $table = 'product_categories';

    // Default if you need to override

    // public function determineOrderColumnName(): string
    // {
    //     return "order";
    // }

    // public function determineParentColumnName(): string
    // {
    //     return "parent_id";
    // }

    // public function determineTitleColumnName(): string
    // {
    //     return 'title';
    // }

    // public static function defaultParentKey()
    // {
    //     return -1;
    // }

    // public static function defaultChildrenKeyName(): string
    // {
    //     return "children";
    // }

}

```

### Widget

Filament provides a powerful feature that allows you to display widgets inside pages, below the header and above the footer. This can be useful for adding additional functionality to your resource pages.

To create a Tree Widget and apply it to a resource page, you can follow these steps:

#### 1. Creating a Filament Resource Page

To create a resources page, run the following command:

```
php artisan make:filament-resource ProductCategory
```

#### 2. Create Tree Widget

Prepare the filament-tree Widget and show it in Resource page.

```php
php artisan make:filament-tree-widget ProductCategoryWidget
```

Now you can see the Widget in Filament Folder

```php
<?php

namespace App\Filament\Widgets;

use App\Models\ProductCategory as ModelsProductCategory;
use App\Filament\Widgets;
use Filament\Forms\Components\TextInput;
use SolutionForest\FilamentTree\Widgets\Tree as BaseWidget;

class ProductCategoryWidget extends BaseWidget
{
    protected static string $model = ModelsProductCategory::class;

    // you can customize the maximum depth of your tree
    protected static int $maxDepth = 2;

    protected ?string $treeTitle = 'ProductCategory';

    protected bool $enableTreeTitle = true;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title'),
        ];
    }
}
```

#### 3. Displaying a widget on a resource page

Once you have created the widget, modify the `getHeaderWidgets()` or `getFooterWidgets()` methods of the resource page to show the tree view:

```php
<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use App\Filament\Widgets\ProductCategory;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductCategory::class
        ];
    }
}
```

### Resources

Filament allows you to create a custom pages for resources, you also can create a tree page that display hierarchical data.

#### Create a Page

To create a tree page for resource, you can use:

```
php artisan make:filament-tree-page ProductCategoryTree --resource=ProductCategory
```

#### Register a Page to the resource

You must register the tree page to a route in the static `getPages()` methods of your resource. For example:

```php
public static function getPages(): array
{
    return [
        // ...
        'tree-list' => Pages\ProductCategoryTree::route('/tree-list'),
    ];
}
```

#### Actions

Define the available "actions" for the tree page using the `getActions()` and `getTreeActions()` methods of your page class.

The `getActions()` method defines actions that are displayed next to the page's heading:

```php
    use Filament\Actions\CreateAction;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
            // SAMPLE CODE, CAN DELETE
            //\Filament\Pages\Actions\Action::make('sampleAction'),
        ];
    }
```

The `getTreeActions()` method defines the actions that are displayed for each record in the tree. For example:

```php
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;

protected function getTreeActions(): array
{
    return [
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
    ];
}

```

Alternatively, you can use the `hasDeleteAction()`, `hasEditAction()`, and `hasViewAction()` methods to customize each action individually.

```php
protected function hasDeleteAction(): bool
{
    return false;
}

protected function hasEditAction(): bool
{
    return true;
}

protected function hasViewAction(): bool
{
    return false;
}
```

#### Record ICON

To customize the prefix icon for each record in a tree page, you can use the `getTreeRecordIcon()` method in your tree page class. This method should return a string that represents the name of the icon you want to use for the record. For example:

```php
public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
{
    if ($record->parent_id != -1) {
        return null; // no icon for child records
    }

    return match ($record->title) {
        'Top' => 'heroicon-o-arrow-up',
        'Bottom' => 'heroicon-o-arrow-down',
        'Shoes' => 'heroicon-o-shopping-bag',
        'Accessories' => 'heroicon-o-briefcase',
        default => null, // no icon for other records
    };
}
```

![Item record on tree](https://github.com/user-attachments/assets/f5d2fcb8-f366-47e9-956d-6b81c8edd2ac)

#### Node collapsed state

You can customize a collapsed state of the node. If you would like to show your tree initially collapsed you can use:

```php
public function getNodeCollapsedState(?\Illuminate\Database\Eloquent\Model $record = null): bool
{
    // All tree nodes will be collapsed by default.
    return true;
}
```

#### Record Title

To customize the ttile for each record in a tree page, you can use the `getTreeRecordTitle()` method in your tree page class. This method should return a string that represents the name of the icon you want to use for the record. For example:

```php
public function getTreeRecordTitle(?\Illuminate\Database\Eloquent\Model $record = null): string
{
    if (! $record) {
        return '';
    }
    $id = $record->getKey();
    $title = $record->{(method_exists($record, 'determineTitleColumnName') ? $record->determineTitleColumnName() : 'title')};
    return "[{$id}] {$title}";
}
```

#### Configuring Tree Item Actions

You can customize the behavior and appearance of tree item actions (Delete, Edit, and View) by overriding the configuration methods in your widget or page class. Each action type has its own configuration method:

##### Configure Delete Action

Override the `configureDeleteAction()` method to customize the delete action:

```php
protected function configureDeleteAction(DeleteAction $action): DeleteAction
{
    $action
        ->label('Remove Item')
        ->icon('heroicon-o-trash')
        ->color('danger')
        ->requiresConfirmation()
        ->modalHeading('Delete Category')
        ->modalDescription('Are you sure you want to delete this category? This action cannot be undone.')
        ->modalSubmitActionLabel('Yes, delete it');

    return $action;
}
```

##### Configure Edit Action

Override the `configureEditAction()` method to customize the edit action:

```php
protected function configureEditAction(EditAction $action): EditAction
{
    $action
        ->label('Edit Item')
        ->icon('heroicon-o-pencil')
        ->color('primary')
        ->modalHeading('Edit Category')
        ->modalSubmitActionLabel('Save Changes')
        ->slideOver();

    return $action;
}
```

##### Configure View Action

Override the `configureViewAction()` method to customize the view action:

```php
protected function configureViewAction(ViewAction $action): ViewAction
{
    $action
        ->label('View Details')
        ->icon('heroicon-o-eye')
        ->color('secondary')
        ->modalHeading('Category Details')
        ->modalWidth('2xl')
        ->slideOver();

    return $action;
}
```

##### Example: Complete Action Configuration

Here's a complete example showing how to configure all three actions in a tree widget:

```php
<?php

namespace App\Filament\Widgets;

use App\Models\ProductCategory;
use Filament\Forms\Components\TextInput;
use SolutionForest\FilamentTree\Actions\DeleteAction;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;
use SolutionForest\FilamentTree\Widgets\Tree as BaseWidget;

class ProductCategoryWidget extends BaseWidget
{
    protected static string $model = ProductCategory::class;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title')->required(),
        ];
    }

    protected function hasDeleteAction(): bool
    {
        return true;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return true;
    }

    protected function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        $action
            ->requiresConfirmation()
            ->modalDescription('This will permanently delete the category and all its subcategories.');

        return $action;
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        $action
            ->slideOver()
            ->modalWidth('md');

        return $action;
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        $action
            ->slideOver()
            ->disabled(fn ($record) => $record->parent_id === -1); // Disable for root items

        return $action;
    }
}
```

### Pages

This plugin enables you to create tree pages in the admin panel. To create a tree page for a model, use the `make:filament-tree-page` command. For example, to create a tree page for the ProductCategory model, you can run:

#### Create a Page

> **Tip: Note that you should make sure the model contains the required columns or already uses the `ModelTree` trait**

```php
php artisan make:filament-tree-page ProductCategory --model=ProductCategory
```

#### Actions, Widgets and Icon for each record

Once you've created the tree page, you can customize the available actions, widgets, and icon for each record. You can use the same methods as for resource pages. See the [Resource Page](#resources) for more information on how to customize actions, widgets, and icons.

### Translation

Suggest used with Spatie Translatable [https://github.com/lara-zeus/translatable](https://github.com/lara-zeus/spatie-translatable) Plugin.

1. Ensure your model already apply translatable setup. (Refence on https://spatie.be/docs/laravel-translatable/v6/installation-setup)

```php
use Filament\Actions\LocaleSwitcher;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;
    use TreeModel;

    protected $translatable = [
        'title',
    ];
}
```

2. You need to add the necessary trait and `LocaleSwitcher` header action to your tree page:

```php
use App\Models\Category as TreePageModel;
use SolutionForest\FilamentTree\Concern\TreeRecords\Translatable;
use SolutionForest\FilamentTree\Pages\TreePage as BasePage;

class Category extends BasePage
{
    use Translatable;

    protected static string $model = TreePageModel::class;

    public function getTranslatableLocales(): array
    {
        return ['en', 'fr'];
    }

    protected function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
```

### Publishing Views

To publish the views, use:

```bash
php artisan vendor:publish --tag="filament-tree-views"
```

### Publishing Translations

To publish the translations, use:

```bash
php artisan vendor:publish --tag="filament-tree-translations"
```

## 🆕 Enhanced Features

This enhanced fork includes powerful new features that extend the capabilities of Filament Tree:

### 1. Tree Resource Generation Command

Create complete tree resources that replace table views with hierarchical tree interfaces:

#### Command: `make:filament-tree-resource`

```bash
# Create a complete tree resource
php artisan make:filament-tree-resource Location --model=Location

# Include form generation (optional, off by default)
php artisan make:filament-tree-resource Category --model=Category --generate-form
```

#### Generated Structure (Filament v4 Pattern)

The command creates a complete resource following Filament v4's folder-based organization:

```
app/Filament/Resources/Locations/
├── LocationResource.php           # Main resource class
├── Pages/
│   ├── ListLocations.php         # Tree view page (replaces table)
│   ├── CreateLocation.php        # Create page with parent_id support
│   ├── EditLocation.php          # Edit page
│   └── ViewLocation.php          # View page  
├── Schemas/
│   └── LocationForm.php          # Reusable form schema
└── Trees/
    └── LocationsTree.php         # Tree configuration and actions
```

#### Key Differences from Original Approaches

**Tree Resource vs Resource with Tree Widget:**
- Creates a **dedicated tree resource** (not a regular resource + tree widget)
- The tree view **completely replaces** the table view
- Designed specifically for hierarchical data management

**Page-Based Navigation (No Modals):**
- Uses dedicated pages for Create, Edit, List, and View operations
- Matches standard Filament resource behavior
- Better UX for complex forms and workflows
- Supports deep linking and browser navigation

**Enhanced Capabilities:**
- **Relation Manager Support**: Add ContactsRelationManager, etc. (not supported in widget/old integration)
- **Custom Record Actions**: Full support for custom actions on tree records
- **Policy Integration**: Complete Laravel policy authorization
- **Parent ID Auto-Population**: Child creation automatically sets parent relationships

#### Usage Example

```php
// Generated LocationResource.php
class LocationResource extends TreeResource
{
    protected static ?string $model = Location::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    public static function getRelations(): array
    {
        return [
            ContactsRelationManager::class,
            // Add more relation managers
        ];
    }
}

// Generated LocationsTree.php  
class LocationsTree extends BaseTree
{
    public static function tree(Tree $tree): Tree
    {
        return $tree->maxDepth(4);
    }
    
    public static function getResource(): string
    {
        return LocationResource::class;
    }
    
    // Action configuration (all enabled by default)
    // protected bool $hasCreateAction = true;
    // protected bool $hasAddChildAction = true; 
    // protected bool $hasEditAction = true;
    // protected bool $hasViewAction = true;
    // protected bool $hasDeleteAction = true;
}
```

### 2. Laravel Policy Authorization

Automatically hide unauthorized actions based on Laravel policies:

```php
// Enable in config/filament-tree.php
'enable_policy_authorization' => true,
```

**Features:**
- Actions are **completely hidden** when unauthorized (not just disabled)
- Tree reordering disabled when user lacks update permissions
- Works with any Laravel authorization system (Gates, Policies, Spatie)
- Configurable ability mapping

**Policy Methods Supported:**
```php
class LocationPolicy
{
    public function viewAny(User $user): bool
    public function create(User $user): bool  
    public function update(User $user, Location $location): bool
    public function delete(User $user, Location $location): bool
}
```

### 3. Enhanced Action System

#### CreateChildAction with Auto Parent-Child Relationships

```php
use SolutionForest\FilamentTree\Actions\CreateChildAction;

protected function getTreeActions(): array
{
    return [
        CreateChildAction::make()
            ->beforeAction(function ($record, $data) {
                // Customize data before creation
                $data['type'] = match($record->type) {
                    'country' => 'state',
                    'state' => 'city',
                    default => 'district'
                };
                return $data;
            })
            ->afterAction(function ($record, $data, $result) {
                // Log, notify, or perform side effects
                activity()->performedOn($result)->log("Created child under {$record->name}");
                return $result;
            }),
    ];
}
```

#### Action Hooks System

All tree actions now support powerful before/after hooks:

```php
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\DeleteAction;

protected function getTreeActions(): array
{
    return [
        EditAction::make()
            ->beforeAction(function ($record, $data) {
                // Validate, prepare, or log before action
                return $data;
            })
            ->afterAction(function ($record, $data, $result) {
                // Clear cache, send notifications, log changes
                return $result;
            }),
            
        DeleteAction::make()
            ->beforeAction(function ($record) {
                // Safety checks
                if ($record->children()->count() > 0) {
                    throw new \Exception('Cannot delete record with children');
                }
                return true;
            }),
    ];
}
```

### 4. Enhanced Configuration

The config file now includes additional options:

```php
return [
    // Existing options...
    
    /**
     * Enable Laravel policy authorization
     */
    'enable_policy_authorization' => false,
    
    /**
     * Policy abilities mapping
     */
    'policy_abilities' => [
        'create' => 'create',
        'createChild' => 'create',
        'edit' => 'update',
        'view' => 'view',
        'delete' => 'delete',
    ],
    
    /**
     * TreeResource configuration
     */
    'resources' => [
        'enabled' => true,
        'namespace' => 'App\\Filament\\Resources',
        'path' => app_path('Filament/Resources'),
    ],
];
```

### 5. Backward Compatibility

All original functionality remains fully supported:

- **`make:filament-tree-page`**: Creates standalone tree pages with modal forms
- **`make:filament-tree-widget`**: Creates tree widgets for dashboards
- **Resource Integration**: Original "create in resource" option still works

The enhanced features are additive and don't break existing implementations.

## Testing

To run the tests, run:

```bash
composer test
```

## Changelog

See the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

See [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email info+package@solutionforest.net instead of using the issue tracker.

## Credits

- [Carly]
- [All Contributors](../../contributors)

## License

Filament Tree is open-sourced software licensed under the [MIT license](LICENSE.md).

<p align="center"><a href="https://solutionforest.com" target="_blank"><img src="https://github.com/solutionforest/.github/blob/main/docs/images/sf.png?raw=true" width="200"></a></p>

## About Solution Forest

[Solution Forest](https://solutionforest.com) Web development agency based in Hong Kong. We help customers to solve their problems. We Love Open Soruces.

We have built a collection of best-in-class products:

- [VantagoAds](https://vantagoads.com): A self manage Ads Server, Simplify Your Advertising Strategy.
- [GatherPro.events](https://gatherpro.events): A Event Photos management tools, Streamline Your Event Photos.
- [Website CMS Management](https://filamentphp.com/plugins/solution-forest-cms-website): Website CMS Management - Filament CMS Plugin
- [Filaletter](https://filaletter.solutionforest.net): Filaletter - Filament Newsletter Plugin

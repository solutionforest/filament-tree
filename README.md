
# Filament Tree

Filament Tree is a plugin for Filament Admin that creates a model management page with a heritage tree structure view. This plugin can be used to create menus and more.


[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)

This plugin creates model management page with heritage tree structure view for Filament Admin. It could be used to create menu, etc.

Demo site : https://filament-cms-website-demo.solutionforest.net/admin

Demo username : demo@solutionforest.net

Demo password : 12345678
Auto Reset every hour.


## Installation

To install the package, run the following command:

```bash
composer require solution-forest/filament-tree
```

> **Important: Need to publish assets after version 2.x**
```bash
php artisan filament:assets
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
![Screenshot](https://github.com/solutionforest/filament-tree/assets/68211972/d4bc8d33-3448-4cf5-837e-14116e28b4b5)

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
``` php
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

``` php
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
    use Filament\Pages\Actions\CreateAction;

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
use Filament\Pages\Actions\Action;

protected function getTreeActions(): array
{
    return [
        Actions\ViewAction::make(),
        Actions\EditAction::make(),
        Actions\DeleteAction::make(),
    ];
}

```

Alternatively, you can use the `hasDeleteAction()`, `hasEditAction()`, and `hasViewAction()` methods to customize each action individually.

``` php
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
    // default null
    return 'heroicon-o-cake';
}
```
![tree-icon](https://github.com/solutionforest/filament-tree/assets/68525320/6a1ef719-9029-4e91-a20a-515a514c4326)

#### Node collapsed state
You can customize a collapsed state of the node. If you would like to show your tree initially collapsed you can use:

```php
public function getNodeCollapsedState(?\Illuminate\Database\Eloquent\Model $record = null): bool
{
    // All tree nodes will be collapsed by default.
    return true;
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
Once you've created the tree page, you can customize the available actions, widgets, and icon for each record. You can use the same methods as for resource pages. See the [Resource Page](#resources)  for more information on how to customize actions, widgets, and icons.



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

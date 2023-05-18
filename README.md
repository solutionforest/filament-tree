
# Filament Tree

Filament Tree is a plugin for Filament Admin that creates a model management page with a heritage tree structure view. This plugin can be used to create menus and more.


[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)

This plugin creates model management page with heritage tree structure view for Filament Admin. It could be used to create menu, etc.

Demo site : https://filament-cms-website-demo.solutionforest.net/

Demo username : demo@solutionforest.net

Demo password : 12345678
Auto Reset every hour.


## Installation

To install the package, run the following command:

```bash
composer require solution-forest/filament-tree
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

### Creating a Filament Resource Page

To create a resources page, run the following command:

```bash
php artisan make:filament-resource ProductCategory
```

Next, prepare the database and model.

### Table Structure and Model

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

### Create Tree Widget

Prepare the filament-tree Widget and show it in Resource page.

```bash
php artisan make:filament-tree-widget ProductCategoryWidget
```
Now you can see the Widget in Filament Folder
```
<?php

namespace App\Filament\Widgets;

use App\Models\ProductCategory as ModelsProductCategory;
use App\Filament\Widgets;
use SolutionForest\FilamentTree\Widgets\Tree as BaseWidget;

class ProductCategoryWidget extends BaseWidget
{
    protected static string $model = ModelsProductCategory::class;

    protected static int $maxDepth = 2;

    protected ?string $treeTitle = 'ProductCategory';

    protected bool $enableTreeTitle = true;
}
```


### Resource Page 

Once you have created the widget, modify the resource page to show the tree view:

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

### Publishing Configuration

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

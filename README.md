# Filament Tree

[![Latest Version on Packagist](https://img.shields.io/packagist/v/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/solution-forest/filament-tree/run-tests?label=tests)](https://github.com/solution-forest/filament-tree/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/solution-forest/filament-tree/Check%20&%20fix%20styling?label=code%20style)](https://github.com/solution-forest/filament-tree/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/solution-forest/filament-tree.svg?style=flat-square)](https://packagist.org/packages/solution-forest/filament-tree)

This plugin creates model management page with heritage tree structure view for Filament Admin. It could be used to create menu, etc.

## Installation

You can install the package via composer:

```bash
composer require solution-forest/filament-tree
```

Publish the config file with:
```bash
php artisan vendor:publish --tag="filament-tree-config"
```
Set your preferred options:
```php
<?php

return [
    /**
     * Tree model fields
     */
    'column_name' => [
        'order' => 'order',
        'parent' => 'parent_id',
        'depth' => 'depth',
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

## Usage

You can create tree page via command:
```php
php artisan make:filament-tree-page
```

This is an example of the tree page:
``` bash
use SolutionForest\FilamentTree\Pages\TreePage as BasePage;

class DumpTreePage extends BasePage
{

    public static function getMaxDepth(): int
    {
        return 2;
    }

    /**
     * Must be implemented
     */
    public function getModel(): string
    {
        //
    }
    
    protected function getFormSchema(): array
    {
        return [
            //
        ];
    }
}
```

Control the maximum depth of the tree by `getMaxDepth()` method.

Each tree should be assigned a model, for example: 
```php
public function getModel(): string
{
    return Menu::class;
}
```

The model assigned should use the ModelTree Concern:
```php
use SolutionForest\FilamentTree\Concern\ModelTree;

class Menu extends Model
{
    use ModelTree;
}
```

Optionally, you can publish the views and translations using:
```bash
php artisan vendor:publish --tag="filament-tree-views"

php artisan vendor:publish --tag="filament-tree-translations"
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Carly](https://github.com/n/a)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

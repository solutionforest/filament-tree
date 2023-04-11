<?php

namespace SolutionForest\FilamentTree;

use Filament\PluginServiceProvider;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;

class FilamentTreeServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-tree';

    protected array $styles = [
        'filament-tree-min' => __DIR__ . '/../resources/dist/filament-tree.css',
    ];

    protected array $scripts = [
        'https://code.jquery.com/jquery-3.6.1.slim.min.js',
        'filament-tree-min' => __DIR__ . '/../resources/dist/filament-tree.js',
    ];

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasCommands([
                Commands\MakeTreePageCommand::class,
                Commands\MakeTreeWidgetCommand::class,
            ]);
    }
}

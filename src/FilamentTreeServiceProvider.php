<?php

namespace SolutionForest\FilamentTree;

use Filament\PluginServiceProvider;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;

class FilamentTreeServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-tree';

    protected array $styles = [
        'filament-tree-styles' => __DIR__ . '/../resources/dist/filament-tree.css',
    ];
    protected array $beforeCoreScripts = [
        'filament-tree-scripts' => __DIR__ . '/../resources/dist/filament-tree.js',
    ];

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasTranslations();
    }
}

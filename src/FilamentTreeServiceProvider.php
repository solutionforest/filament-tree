<?php

namespace SolutionForest\FilamentTree;

use Filament\PluginServiceProvider;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;

class FilamentTreeServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-tree';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasTranslations();
    }
}

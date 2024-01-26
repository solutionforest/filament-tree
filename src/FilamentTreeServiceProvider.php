<?php

namespace SolutionForest\FilamentTree;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Schema\Blueprint;
use SolutionForest\FilamentTree\Macros\BlueprintMarcos;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTreeServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-tree';
    
    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasTranslations()
            ->hasCommands([
                Commands\MakeTreePageCommand::class,
                Commands\MakeTreeWidgetCommand::class,
            ]);
    }

    public function boot()
    {
        parent::boot();

        $this->registerBlueprintMacros();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filament-tree-min', __DIR__ . '/../resources/dist/filament-tree.css'),
        ], 'solution-forest/filament-tree');
        
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => "<script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>",
        );

        $js = asset('js/solution-forest/filament-tree/filament-tree-min.js');
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => "<script src=\"$js\"></script>",
        );
    }

    protected function registerBlueprintMacros()
    {
        Blueprint::mixin(new BlueprintMarcos);
    }
}

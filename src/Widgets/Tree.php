<?php

namespace SolutionForest\FilamentTree\Widgets;

use Filament\Widgets\Widget;
use SolutionForest\FilamentTree\Components\Tree as TreeComponent;
use SolutionForest\FilamentTree\Concern;
use SolutionForest\FilamentTree\Contract\HasTree;

class Tree extends Widget implements HasTree
{
    use Concern\InteractWithTree;

    protected static string $view = 'filament-tree::widgets.tree';

    protected int | string | array $columnSpan = 'full';

    protected static string $model;

    protected static int $maxDepth = 2;

    public static function getMaxDepth(): int
    {
        return static::$maxDepth;
    }

    public static function tree(TreeComponent $tree): TreeComponent
    {
        return $tree;
    }

    public function getModel(): string
    {
        return static::$model ?? class_basename(static::class);
    }
}

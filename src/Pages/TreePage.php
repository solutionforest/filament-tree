<?php

namespace SolutionForest\FilamentTree\Pages;

use Filament\Pages\Page;
use SolutionForest\FilamentTree\Concern\TreePageTrait;
use SolutionForest\FilamentTree\Contract\HasTree;

abstract class TreePage extends Page implements HasTree
{
    use TreePageTrait;

    protected string $view = 'filament-tree::pages.tree';
}

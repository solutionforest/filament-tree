<?php

namespace SolutionForest\FilamentTree\Concern\Actions;

use SolutionForest\FilamentTree\Components\Tree;

interface HasTree
{
    public function tree(Tree $tree): static;
}

<?php

namespace SolutionForest\FilamentTree\Concern;

use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Contract\HasTree;

trait BelongsToTree
{
    protected Tree $tree;

    public function tree(Tree $tree): static
    {
        $this->tree = $tree;

        return $this;
    }

    public function getTree(): Tree
    {
        return $this->tree;
    }

    public function getLivewire(): HasTree
    {
        return $this->getTree()->getLivewire();
    }
}

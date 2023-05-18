<?php

namespace SolutionForest\FilamentTree\Macros;

use Illuminate\Database\Schema\Blueprint;
use SolutionForest\FilamentTree\Support\Utils;

/**
 * @see Blueprint
 */
class BlueprintMarcos
{
    public function treeColumns()
    {
        return function (string $titleType = 'string') {
            $this->{$titleType}(Utils::titleColumnName());
            $this->integer(Utils::parentColumnName())->default(Utils::defaultParentId());
            $this->integer(Utils::orderColumnName())->default(0)->index();
        };
    }
}

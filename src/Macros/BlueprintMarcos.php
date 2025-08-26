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
        return function (string $titleType = 'string', string $parentType = 'integer') {
            $this->{$titleType}(Utils::titleColumnName());
            
            if ($parentType === 'uuid') {
                $this->uuid(Utils::parentColumnName())->nullable()->index();
            } elseif ($parentType === 'string') {
                $this->string(Utils::parentColumnName())->nullable()->index();
            } else {
                // Default to integer for backward compatibility
                $this->integer(Utils::parentColumnName())->default(Utils::defaultParentId())->index();
            }
            
            $this->integer(Utils::orderColumnName())->default(0);
        };
    }
}

<?php

namespace SolutionForest\FilamentTree\Actions\Modal;

use Filament\Actions\StaticAction;
use SolutionForest\FilamentTree\Concern\Actions\HasTree;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

/**
 * @deprecated Use `\Filament\Actions\StaticAction` instead.
 */
class Action extends StaticAction implements HasTree
{
    use BelongsToTree;
}

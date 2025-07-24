<?php

namespace SolutionForest\FilamentTree\Actions\Modal;

use SolutionForest\FilamentTree\Concern\Actions\HasTree;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

/**
 * @deprecated Use `\Filament\Actions\StaticAction` instead.
 */
class Action extends \Filament\Actions\Action implements HasTree
{
    use BelongsToTree;
}

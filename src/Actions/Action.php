<?php

namespace SolutionForest\FilamentTree\Actions;

use Filament\Actions\Action as BaseAction;
use SolutionForest\FilamentTree\Concern\Actions\HasTree;
use SolutionForest\FilamentTree\Concern\Actions\TreeActionTrait;
use SolutionForest\FilamentTree\Concern\BelongsToTree;

class Action extends BaseAction implements HasTree
{
    use BelongsToTree;
    use TreeActionTrait;
}

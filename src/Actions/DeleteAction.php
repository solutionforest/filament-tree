<?php

namespace SolutionForest\FilamentTree\Actions;

use Filament\Actions\DeleteAction as BaseDeleteAction;
use SolutionForest\FilamentTree\Concern\Actions\TreeActionTrait;

class DeleteAction extends BaseDeleteAction
{
    use TreeActionTrait;
}

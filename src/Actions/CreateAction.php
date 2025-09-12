<?php

namespace SolutionForest\FilamentTree\Actions;

use Filament\Actions\CreateAction as BaseCreateAction;
use SolutionForest\FilamentTree\Concern\Actions\TreeActionTrait;

class CreateAction extends BaseCreateAction
{
    use TreeActionTrait;
}

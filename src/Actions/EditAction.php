<?php

namespace SolutionForest\FilamentTree\Actions;

use Filament\Actions\EditAction as BaseEditAction;
use SolutionForest\FilamentTree\Concern\Actions\TreeActionTrait;

class EditAction extends BaseEditAction
{
    use TreeActionTrait;
}

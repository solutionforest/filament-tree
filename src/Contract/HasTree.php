<?php

namespace SolutionForest\FilamentTree\Contract;

use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Components\Tree;

interface HasTree
{
    public static function tree(Tree $tree): Tree;

    public function getModel(): string;

    public function updateTree(?array $list = null): array;

    public function getTreeRecordTitle(?Model $record = null): string;

    public function getRecordKey(?Model $record): ?string;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver;
}

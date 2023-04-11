<?php

namespace SolutionForest\FilamentTree\Concern;

trait HasHeading
{
    protected ?string $treeTitle = null;

    protected bool $enableTreeTitle = false;

    public function treeTitle(string $treeTitle): static
    {
        $this->treeTitle = $treeTitle;

        return $this;
    }

    public function enableTreeTitle(bool $condition): static
    {
        $this->enableTreeTitle = $condition;

        return $this;
    }

    public function getTreeTitle(): ?string
    {
        return $this->treeTitle;
    }

    public function displayTreeTitle(): bool
    {
        return $this->enableTreeTitle;
    }
}

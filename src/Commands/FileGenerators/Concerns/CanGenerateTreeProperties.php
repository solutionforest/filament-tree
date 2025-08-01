<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators\Concerns;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Property;

trait CanGenerateTreeProperties
{
    protected function addTreePropertiesToClass(ClassType $class): void
    {
        $properties['maxDepth'] = $class->addProperty('maxDepth', 2)
            ->setProtected()
            ->setStatic()
            ->setType('int');
        $this->configureTreeProperties($properties);
    }

    /**
     * @param  array<string,Property>  $property
     */
    protected function configureTreeProperties(array $property): void {}
}

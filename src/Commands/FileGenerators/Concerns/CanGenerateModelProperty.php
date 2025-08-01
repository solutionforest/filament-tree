<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators\Concerns;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Property;

trait CanGenerateModelProperty
{
    protected function addModelPropertyToClass(ClassType $class): void
    {
        $property = $class->addProperty('model', new Literal("{$this->getModelFqcnEnd()}::class"))
            ->setProtected()
            ->setStatic()
            ->setType('string');
        $this->configureModelProperty($property);
    }

    protected function configureModelProperty(Property $property): void {}

    public function getModelBasename(): string
    {
        return class_basename($this->getModelFqn());
    }

    public function getModelForImport(): array
    {
        return ($this->getModelBasename() === $this->getBasename()) ? [$this->getModelFqn() => 'TreeModel'] : [$this->getModelFqn()];
    }

    public function getModelFqcnEnd(): string
    {
        return ($this->getModelBasename() === $this->getBasename()) ? 'TreeModel' : $this->simplifyFqn($this->getModelFqn());
    }
}

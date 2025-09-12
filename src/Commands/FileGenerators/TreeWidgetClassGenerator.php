<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators;

use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Illuminate\Database\Eloquent\Model;
use Nette\PhpGenerator\ClassType;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateModelProperty;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateTreeMethods;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateTreeProperties;
use SolutionForest\FilamentTree\Widgets\Tree;

class TreeWidgetClassGenerator extends ClassGenerator
{
    use CanGenerateModelProperty;
    use CanGenerateTreeMethods;
    use CanGenerateTreeProperties;

    /**
     * @param  class-string<Model>  $modelFqn
     */
    final public function __construct(
        protected string $fqn,
        protected string $modelFqn,
    ) {}

    public function getNamespace(): string
    {
        return $this->extractNamespace($this->getFqn());
    }

    /**
     * @return array<string>
     */
    public function getImports(): array
    {
        $extends = $this->getExtends();
        $extendsBasename = class_basename($extends);

        return [
            ...(($extendsBasename === $this->getBasename()) ? [$extends => "Base{$extendsBasename}"] : [$extends]),
            ...$this->getModelForImport(),
        ];
    }

    public function getBasename(): string
    {
        return class_basename($this->getFqn());
    }

    public function getExtends(): string
    {
        return Tree::class;
    }

    protected function addPropertiesToClass(ClassType $class): void
    {
        $this->addModelPropertyToClass($class);
        $this->addTreePropertiesToClass($class);
        $class->addProperty('treeTitle', $this->getBasename())
            ->setProtected()
            ->setType('?string');
        $class->addProperty('enableTreeTitle', true)
            ->setProtected()
            ->setType('bool');
    }

    protected function addMethodsToClass(ClassType $class): void
    {
        $this->addGetFormSchemaMethodToClass($class);
        $this->addGetViewFormSchemaMethodToClass($class);
        $this->addGetTreeToolbarActionsMethodToClass($class, true);
    }

    public function generate(): string
    {
        return $this->appendCommentedMethodsForWidget(parent::generate());
    }

    public function getFqn(): string
    {
        return $this->fqn;
    }

    /**
     * @return class-string<Model>
     */
    public function getModelFqn(): string
    {
        return $this->modelFqn;
    }
}

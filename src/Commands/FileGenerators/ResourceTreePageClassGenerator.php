<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators;

use Filament\Commands\FileGenerators\Resources\Pages\Concerns\CanGenerateResourceProperty;
use Filament\Resources\Resource;
use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Nette\PhpGenerator\ClassType;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateTreeMethods;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateTreeProperties;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;

class ResourceTreePageClassGenerator extends ClassGenerator
{
    use CanGenerateResourceProperty;
    use CanGenerateTreeMethods;
    use CanGenerateTreeProperties;

    /**
     * @param  class-string<resource>  $resourceFqn
     */
    final public function __construct(
        protected string $fqn,
        protected string $resourceFqn,
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
            $this->getResourceFqn(),
            ...(($extendsBasename === $this->getBasename()) ? [$extends => 'BasePage'] : [$extends]),
        ];
    }

    public function getBasename(): string
    {
        return class_basename($this->getFqn());
    }

    public function getExtends(): string
    {
        return TreePage::class;
    }

    protected function addPropertiesToClass(ClassType $class): void
    {
        $this->addResourcePropertyToClass($class);
        $this->addTreePropertiesToClass($class);

    }

    protected function addMethodsToClass(ClassType $class): void
    {
        $this->addGetTreeToolbarActionsMethodToClass($class);
        $this->addGetTreeActionsMethodToClass($class);
        $this->addHasDeleteActionMethodToClass($class);
        $this->addHasEditActionMethodToClass($class);
        $this->addHasViewActionMethodToClass($class);
        $this->addGetHeaderWidgetsMethodToClass($class);
        $this->addGetFooterWidgetsMethodToClass($class);
    }

    public function generate(): string
    {
        return $this->appendCommentedMethodsToPage(parent::generate());
    }

    public function getFqn(): string
    {
        return $this->fqn;
    }

    /**
     * @return class-string
     */
    public function getResourceFqn(): string
    {
        return $this->resourceFqn;
    }
}

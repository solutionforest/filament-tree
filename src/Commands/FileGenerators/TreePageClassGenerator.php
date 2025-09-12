<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators;

use Filament\Clusters\Cluster;
use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Illuminate\Database\Eloquent\Model;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Property;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateModelProperty;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateTreeMethods;
use SolutionForest\FilamentTree\Commands\FileGenerators\Concerns\CanGenerateTreeProperties;
use SolutionForest\FilamentTree\Pages\TreePage;

class TreePageClassGenerator extends ClassGenerator
{
    use CanGenerateModelProperty;
    use CanGenerateTreeMethods;
    use CanGenerateTreeProperties;

    /**
     * @param  class-string<Model>  $modelFqn
     * @param  ?class-string<Cluster>  $clusterFqn
     */
    final public function __construct(
        protected string $fqn,
        protected ?string $modelFqn,
        protected ?string $clusterFqn,
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
            ...(($extendsBasename === $this->getBasename()) ? [$extends => 'BasePage'] : [$extends]),
            ...$this->getModelForImport(),
            ...($this->hasCluster() ? (($this->getClusterBasename() === 'Page') ? [$this->getClusterFqn() => 'PageCluster'] : [$this->getClusterFqn()]) : []),
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
        $this->addModelPropertyToClass($class);
        $this->addNavigationIconPropertyToClass($class);
        $this->addClusterPropertyToClass($class);
        $this->addTreePropertiesToClass($class);
    }

    protected function addMethodsToClass(ClassType $class): void
    {
        $this->addGetTreeToolbarActionsMethodToClass($class);
        $this->addGetTreeActionsMethodToClass($class);
        $this->addGetFormSchemaMethodToClass($class);
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

    protected function addNavigationIconPropertyToClass(ClassType $class): void
    {
        $property = $class->addProperty('navigationIcon', 'heroicon-o-document-text')
            ->setProtected()
            ->setStatic()
            ->setType('\BackedEnum|string|null');
        $this->configureNavigationIconProperty($property);
    }

    protected function configureNavigationIconProperty(Property $property): void {}

    protected function addClusterPropertyToClass(ClassType $class): void
    {
        if (! $this->hasCluster()) {
            return;
        }

        $property = $class->addProperty('cluster', new Literal("{$this->simplifyFqn($this->getClusterFqn())}::class"))
            ->setProtected()
            ->setStatic()
            ->setType('?string');
        $this->configureClusterProperty($property);
    }

    protected function configureClusterProperty(Property $property): void {}

    public function getFqn(): string
    {
        return $this->fqn;
    }

    /**
     * @return ?class-string<Cluster>
     */
    public function getClusterFqn(): ?string
    {
        return $this->clusterFqn;
    }

    public function getClusterBasename(): string
    {
        return class_basename($this->getClusterFqn());
    }

    public function hasCluster(): bool
    {
        return filled($this->getClusterFqn());
    }

    /**
     * @return class-string<Model>
     */
    public function getModelFqn(): string
    {
        return $this->modelFqn;
    }
}

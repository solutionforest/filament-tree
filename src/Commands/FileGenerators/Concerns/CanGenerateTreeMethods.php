<?php

namespace SolutionForest\FilamentTree\Commands\FileGenerators\Concerns;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;

trait CanGenerateTreeMethods
{
    protected function addGetTreeActionsMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('getActions')
            ->setProtected()
            ->setReturnType('array')
            ->setBody(<<<PHP
            return [
                \$this->getCreateAction(),
                // SAMPLE CODE, CAN DELETE
                //\Filament\Pages\Actions\Action::make('sampleAction'),
            ];
            PHP);

        $this->configureGetTreeActionsMethod($method);
    }

    protected function configureGetTreeActionsMethod(Method $method): void {}

    protected function addGetTreeToolbarActionsMethodToClass(ClassType $class, bool $preset = false): void
    {
        $body = $preset ? <<<PHP
            return [
                \SolutionForest\FilamentTree\Actions\CreateAction::make(),
            ];
            PHP : <<<'PHP'
            return [];
            PHP;

        $method = $class->addMethod('getTreeToolbarActions')
            ->setProtected()
            ->setReturnType('array')
            ->setBody($body);

        $this->configureGetTreeToolbarActionsMethod($method);
    }

    protected function configureGetTreeToolbarActionsMethod(Method $method): void {}

    protected function addGetFormSchemaMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('getFormSchema')
            ->setProtected()
            ->setReturnType('array')
            ->setBody(<<<'PHP'
            return [
                //
            ];
            PHP);

        $this->configureGetFormSchemaMethod($method);
    }

    protected function configureGetFormSchemaMethod(Method $method): void {}

    protected function addGetViewFormSchemaMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('getViewFormSchema')
            ->setProtected()
            ->setReturnType('array')
            ->setBody(<<<'PHP'
            return [
                // INFOLIST, CAN DELETE
            ];
            PHP);

        $this->configureGetViewFormSchemaMethod($method);
    }

    protected function configureGetViewFormSchemaMethod(Method $method): void {}

    protected function addHasDeleteActionMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('hasDeleteAction')
            ->setProtected()
            ->setReturnType('bool')
            ->setBody(<<<'PHP'
                return false;
            PHP);

        $this->configureHasDeleteActionMethod($method);
    }

    protected function configureHasDeleteActionMethod(Method $method): void {}

    protected function addHasEditActionMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('hasEditAction')
            ->setProtected()
            ->setReturnType('bool')
            ->setBody(<<<'PHP'
                return true;
            PHP);

        $this->configureHasEditActionMethod($method);
    }

    protected function configureHasEditActionMethod(Method $method): void {}

    protected function addHasViewActionMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('hasViewAction')
            ->setProtected()
            ->setReturnType('bool')
            ->setBody(<<<'PHP'
                return false;
            PHP);

        $this->configureHasViewActionMethod($method);
    }

    protected function configureHasViewActionMethod(Method $method): void {}

    protected function addGetHeaderWidgetsMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('getHeaderWidgets')
            ->setProtected()
            ->setReturnType('array')
            ->setBody(<<<'PHP'
                return [];
            PHP);

        $this->configureHeaderWidgetsMethod($method);
    }

    protected function configureHeaderWidgetsMethod(Method $method): void {}

    protected function addGetFooterWidgetsMethodToClass(ClassType $class): void
    {
        $method = $class->addMethod('getFooterWidgets')
            ->setProtected()
            ->setReturnType('array')
            ->setBody(<<<'PHP'
                return [];
            PHP);

        $this->configureFooterWidgetsMethod($method);
    }

    protected function configureFooterWidgetsMethod(Method $method): void {}

    protected function getCommentedMethodsForPage(): string
    {
        return <<<PHP

            // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
            // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model \$record = null): ?string
            // {
            //     return null;
            // }
        
        PHP;
    }

    protected function getCommentedMethodsForWidget(): string
    {
        return <<<PHP

            // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
            // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model \$record = null): ?string
            // {
            //     return null;
            // }

            // CUSTOMIZE ACTION OF EACH RECORD, CAN DELETE 
            // protected function getTreeActions(): array
            // {
            //     return [
            //         Action::make('helloWorld')
            //             ->action(function () {
            //                 Notification::make()->success()->title('Hello World')->send();
            //             }),
            //         // ViewAction::make(),
            //         // EditAction::make(),
            //         ActionGroup::make([
            //             
            //             ViewAction::make(),
            //             EditAction::make(),
            //         ]),
            //         DeleteAction::make(),
            //     ];
            // }
            // OR OVERRIDE FOLLOWING METHODS
            //protected function hasDeleteAction(): bool
            //{
            //    return true;
            //}
            //protected function hasEditAction(): bool
            //{
            //    return true;
            //}
            //protected function hasViewAction(): bool
            //{
            //    return true;
            //}
        
        PHP;
    }

    protected function appendCommentedMethodsToPage($contents): string
    {
        // Add the commented-out method before the closing brace
        $commentedMethod = $this->getCommentedMethodsForPage();

        // Insert before the last closing brace
        $lastBracePos = strrpos($contents, '}');
        if ($lastBracePos !== false) {
            $contents = substr_replace($contents, $commentedMethod, $lastBracePos, 0);
        }

        return $contents;
    }

    protected function appendCommentedMethodsForWidget($contents): string
    {
        // Add the commented-out method before the closing brace
        $commentedMethod = $this->getCommentedMethodsForWidget();

        // Insert before the last closing brace
        $lastBracePos = strrpos($contents, '}');
        if ($lastBracePos !== false) {
            $contents = substr_replace($contents, $commentedMethod, $lastBracePos, 0);
        }

        return $contents;
    }
}

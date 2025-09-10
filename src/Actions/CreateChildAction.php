<?php

namespace SolutionForest\FilamentTree\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Components\Tree;

class CreateChildAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'createChild';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-tree::actions.create-child.label'));

        $this->modalHeading(fn (): string => __('filament-tree::actions.create-child.modal.heading', [
            'label' => $this->getModelLabel(),
            'parent' => $this->getRecordTitle()
        ]));

        $this->modalSubmitActionLabel(__('filament-tree::actions.create-child.modal.actions.create.label'));

        $this->successNotificationTitle(__('filament-tree::actions.create-child.notifications.created.title'));

        $this->icon('heroicon-m-plus-circle');

        // Auto-configure parent relationship for tree structure
        $this->mutateFormDataUsing(function (array $data) {
            return $this->autoConfigureParentRelationship($data);
        });

        $this->action(function (): void {
            $this->process(function (array $data, Tree $tree) {
                // Execute before hook
                $data = $this->callBeforeActionHook($this->getRecord(), $data);
                
                // Apply form data mutations
                $data = $this->getMutatedFormData($data);

                // Create the record
                $result = $this->createChildRecord($data, $tree);

                // Execute after hook
                $result = $this->callAfterActionHook($this->getRecord(), $data, $result);

                return $result;
            });

            $this->success();
        });
    }

    /**
     * Automatically configure parent relationship and order
     */
    protected function autoConfigureParentRelationship(array $data): array
    {
        $parentRecord = $this->getRecord();
        $parentColumn = config('filament-tree.column_name.parent', 'parent_id');
        $orderColumn = config('filament-tree.column_name.order', 'order');

        // Set parent ID
        $data[$parentColumn] = $parentRecord->getKey();

        // Set order if not provided
        if (!isset($data[$orderColumn])) {
            $modelClass = $this->getModel();
            $maxOrder = $modelClass::where($parentColumn, $parentRecord->getKey())
                ->max($orderColumn) ?? 0;
            $data[$orderColumn] = $maxOrder + 1;
        }

        return $data;
    }

    /**
     * Create the child record
     */
    protected function createChildRecord(array $data, Tree $tree): Model
    {
        // Handle translatable content if enabled
        if ($translatableContentDriver = $tree->makeFilamentTranslatableContentDriver()) {
            return $translatableContentDriver->makeRecord($this->getModel(), $data);
        }
        
        // Standard record creation
        $modelClass = $this->getModel();
        return $modelClass::create($data);
    }
    
    /**
     * Set custom label for specific child types
     */
    public function childType(string $type): static
    {
        $this->label(__("filament-tree::actions.create-child.{$type}.label"));
        
        $this->modalHeading(fn (): string => __("filament-tree::actions.create-child.{$type}.modal.heading", [
            'parent' => $this->getRecordTitle()
        ]));
        
        return $this;
    }
}
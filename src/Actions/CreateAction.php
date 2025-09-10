<?php

namespace SolutionForest\FilamentTree\Actions;

use Closure;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Components\Tree;

class CreateAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-tree::actions.create.label'));

        $this->modalHeading(fn (): string => __('filament-tree::actions.create.modal.heading', [
            'label' => $this->getModelLabel()
        ]));

        $this->modalSubmitActionLabel(__('filament-tree::actions.create.modal.actions.create.label'));

        $this->successNotificationTitle(__('filament-tree::actions.create.notifications.created.title'));

        $this->icon('heroicon-m-plus');

        // Auto-configure as root level (no parent)
        $this->mutateFormDataUsing(function (array $data) {
            return $this->autoConfigureRootLevel($data);
        });

        $this->action(function (): void {
            $this->process(function (array $data, Tree $tree) {
                // Execute before hook
                $data = $this->callBeforeActionHook(null, $data);

                // Apply form data mutations
                $data = $this->getMutatedFormData($data);

                // Create the record
                $result = $this->createRecord($data, $tree);

                // Execute after hook
                $result = $this->callAfterActionHook(null, $data, $result);

                return $result;
            });

            $this->success();
        });
    }

    /**
     * Automatically configure as root level record
     */
    protected function autoConfigureRootLevel(array $data): array
    {
        $parentColumn = config('filament-tree.column_name.parent', 'parent_id');
        $orderColumn = config('filament-tree.column_name.order', 'order');

        // Set as root level if not provided
        if (!isset($data[$parentColumn])) {
            $data[$parentColumn] = config('filament-tree.default_parent_id', -1);
        }

        // Set order if not provided
        if (!isset($data[$orderColumn])) {
            $modelClass = $this->getModel();
            $maxOrder = $modelClass::where($parentColumn, $data[$parentColumn])
                ->max($orderColumn) ?? 0;
            $data[$orderColumn] = $maxOrder + 1;
        }

        return $data;
    }

    /**
     * Create the record
     */
    protected function createRecord(array $data, Tree $tree): Model
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
     * Set parent for this create action (useful for creating under specific node)
     */
    public function parent($parentId): static
    {
        $this->mutateFormDataUsing(function (array $data) use ($parentId) {
            $parentColumn = config('filament-tree.column_name.parent', 'parent_id');
            $data[$parentColumn] = $parentId;
            return $data;
        });

        return $this;
    }
}

<?php

namespace SolutionForest\FilamentTree\Actions;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;

class DeleteAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'delete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::delete.single.label'));

        $this->modalHeading(fn (): string => __('filament-actions::delete.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('filament-actions::delete.single.modal.actions.delete.label'));

        $this->successNotificationTitle(__('filament-actions::delete.single.notifications.deleted.title'));

        $this->color('danger');

        $this->icon('heroicon-m-trash');

        $this->requiresConfirmation();

        $this->modalSubheading(function (Model $record) {
            if (collect($record->children)->isNotEmpty()) {
                return __('filament-tree::filament-tree.actions.delete.confirmation.with_children');

            } else {
                return __('filament-actions::modal.confirmation');

            }
        });

        $this->modalIcon('heroicon-o-trash');

        $this->hidden(static function (Model $record): bool {
            if (! method_exists($record, 'trashed')) {
                return false;
            }

            return $record->trashed();
        });

        $this->action(function (): void {
            $record = $this->getRecord();
            
            // Execute before hook (useful for cascade checks, cleanup)
            $this->callBeforeActionHook($record);
            
            // Apply record mutations if needed
            $record = $this->getMutatedRecord($record);

            // Delete the record
            $result = $this->process(static fn (Model $record) => $record->delete());

            if (! $result) {
                $this->failure();
                return;
            }

            // Execute after hook (useful for cleanup, logging)
            $this->callAfterActionHook($record, [], $record);

            $this->success();
        });
    }
}

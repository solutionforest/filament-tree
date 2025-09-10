<?php

namespace SolutionForest\FilamentTree\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Components\Tree;

class EditAction extends Action
{
    use CanCustomizeProcess;

    protected ?Closure $mutateRecordDataUsing = null;

    protected ?Closure $mutateFormDataBeforeSaveUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'edit';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::edit.single.label'));

        $this->modalHeading(fn (): string => __('filament-actions::edit.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('filament-actions::edit.single.modal.actions.save.label'));

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->icon('heroicon-m-pencil-square');

        $this->fillForm(function (Model $record, Tree $tree): array {
            if ($translatableContentDriver = $tree->makeFilamentTranslatableContentDriver()) {
                $data = $translatableContentDriver->getRecordAttributesToArray($record);
            } else {
                $data = $record->attributesToArray();
            }

            if ($this->mutateRecordDataUsing) {
                $data = $this->evaluate($this->mutateRecordDataUsing, ['data' => $data, 'record' => $record]);
            }

            return $data;
        });

        $this->action(function (): void {
            $this->process(function (array $data, Model $record, Tree $tree) {
                // Execute before hook
                $data = $this->callBeforeActionHook($record, $data);
                
                // Apply form data mutations
                $data = $this->getMutatedFormData($data);
                
                // Apply record mutations
                $record = $this->getMutatedRecord($record);

                // Update the record
                if ($translatableContentDriver = $tree->makeFilamentTranslatableContentDriver()) {
                    $translatableContentDriver->updateRecord($record, $data);
                } else {
                    $record->update($data);
                }

                // Execute after hook
                $record = $this->callAfterActionHook($record, $data, $record);
                
                return $record;
            });

            $this->success();
        });
    }

    public function mutateRecordDataUsing(?Closure $callback): static
    {
        $this->mutateRecordDataUsing = $callback;

        return $this;
    }

    public function mutateFormDataBeforeSaveUsing(?Closure $callback): static
    {
        $this->mutateFormDataBeforeSaveUsing = $callback;

        return $this;
    }

    public function getMutateFormDataBeforeSave(): ?Closure
    {
        return $this->mutateFormDataBeforeSaveUsing;
    }
}

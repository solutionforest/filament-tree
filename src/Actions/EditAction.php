<?php

namespace SolutionForest\FilamentTree\Actions;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

        $this->label(__('filament-support::actions/edit.single.label'));

        $this->modalHeading(fn (): string => __('filament-support::actions/edit.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->icon('heroicon-s-pencil');

        $this->iconButton();

        $this->successNotificationTitle(__('filament-support::actions/edit.single.messages.saved'));

        $this->mountUsing(function (ComponentContainer $form, Model $record): void {
            $data = $record->attributesToArray();

            if ($this->mutateRecordDataUsing) {
                $data = $this->evaluate($this->mutateRecordDataUsing, ['data' => $data, 'record' => $record]);
            }

            $form->fill($data);
        });

        $this->action(function (): void {
            $this->process(function (array $data, Model $record) {
                if ($this->mutateFormDataBeforeSaveUsing) {
                    $data = $this->evaluate($this->mutateFormDataBeforeSaveUsing, ['data' => $data]);
                }
                $record->update($data);
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

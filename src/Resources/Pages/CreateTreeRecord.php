<?php

namespace SolutionForest\FilamentTree\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTreeRecord extends CreateRecord
{
    public function mount(): void
    {
        parent::mount();
        
        // Fill form with URL parameters (e.g., parent_id from add child action)
        $this->fillFormWithUrlParams();
    }
    
    /**
     * Fill form with URL parameters after mount
     */
    protected function fillFormWithUrlParams(): void
    {
        $resource = static::getResource();
        $parentColumn = $resource::getTreeParentColumn();
        $parentId = request()->query('parent_id');
        
        if ($parentId !== null && isset($this->form)) {
            $this->form->fill([$parentColumn => $parentId]);
        }
    }

    /**
     * Handle tree-specific creation logic
     */
    protected function handleRecordCreation(array $data): Model
    {
        $resource = static::getResource();
        
        // Set default parent if not provided
        $parentColumn = $resource::getTreeParentColumn();
        if (!isset($data[$parentColumn])) {
            $data[$parentColumn] = $resource::getTreeDefaultParentId();
        }

        // Set default order if not provided
        $orderColumn = $resource::getTreeOrderColumn();
        if (!isset($data[$orderColumn])) {
            $model = $resource::getModel();
            $maxOrder = $model::where($parentColumn, $data[$parentColumn])
                ->max($orderColumn) ?? 0;
            $data[$orderColumn] = $maxOrder + 1;
        }

        return parent::handleRecordCreation($data);
    }

    /**
     * Redirect to tree index after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    /**
     * Handle tree-specific mutations before creating
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $resource = static::getResource();
        
        // Ensure required tree columns are set
        $parentColumn = $resource::getTreeParentColumn();
        $orderColumn = $resource::getTreeOrderColumn();
        
        if (!array_key_exists($parentColumn, $data)) {
            $data[$parentColumn] = $resource::getTreeDefaultParentId();
        }
        
        if (!array_key_exists($orderColumn, $data)) {
            $model = $resource::getModel();
            $data[$orderColumn] = ($model::where($parentColumn, $data[$parentColumn])->max($orderColumn) ?? 0) + 1;
        }

        return parent::mutateFormDataBeforeCreate($data);
    }
}
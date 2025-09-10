<?php

namespace SolutionForest\FilamentTree\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTreeRecord extends EditRecord
{
    /**
     * Handle tree-specific update logic
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $resource = static::getResource();
        
        // Prevent changing parent to create circular reference
        $parentColumn = $resource::getTreeParentColumn();
        if (isset($data[$parentColumn])) {
            if ($this->wouldCreateCircularReference($record, $data[$parentColumn])) {
                throw new \InvalidArgumentException(
                    'Cannot set parent: would create circular reference in tree structure.'
                );
            }
        }

        return parent::handleRecordUpdate($record, $data);
    }

    /**
     * Check if setting parent would create circular reference
     */
    protected function wouldCreateCircularReference(Model $record, $parentId): bool
    {
        if ($parentId === $record->getKey()) {
            return true;
        }

        $resource = static::getResource();
        $model = $resource::getModel();
        $parentColumn = $resource::getTreeParentColumn();

        $current = $model::find($parentId);
        $visited = [$record->getKey()]; // Prevent infinite loops

        while ($current) {
            if ($current->getKey() === $record->getKey()) {
                return true;
            }
            
            if (in_array($current->getKey(), $visited)) {
                // Circular reference in existing data, but not involving our record
                break;
            }
            
            $visited[] = $current->getKey();
            
            $currentParentId = $current->getAttribute($parentColumn);
            $current = ($currentParentId && $currentParentId !== $resource::getTreeDefaultParentId()) 
                ? $model::find($currentParentId) 
                : null;
        }

        return false;
    }

    /**
     * Redirect to tree index after update
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Handle tree-specific mutations before saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        
        // Validate parent relationship changes
        $parentColumn = $resource::getTreeParentColumn();
        if (isset($data[$parentColumn]) && $data[$parentColumn] !== $record->getAttribute($parentColumn)) {
            if ($this->wouldCreateCircularReference($record, $data[$parentColumn])) {
                unset($data[$parentColumn]); // Remove the problematic parent change
                
                session()->flash('warning', 'Parent change ignored: would create circular reference.');
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}
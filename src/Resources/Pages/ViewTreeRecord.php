<?php

namespace SolutionForest\FilamentTree\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewTreeRecord extends ViewRecord
{
    /**
     * Redirect to tree index from view
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Get header actions for view page
     */
    protected function getHeaderActions(): array
    {
        $resource = static::getResource();
        $actions = parent::getHeaderActions();
        
        // Filter based on policy if enabled
        if (config('filament-tree.enable_policy_authorization', false)) {
            $actions = array_filter($actions, function ($action) {
                return $action->isVisible();
            });
        }

        return $actions;
    }

    /**
     * Handle tree-specific data mutations for display
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $resource = static::getResource();
        $record = $this->getRecord();
        
        // Add tree-related information for display
        $parentColumn = $resource::getTreeParentColumn();
        $orderColumn = $resource::getTreeOrderColumn();
        
        // Add parent information if available
        if ($record->getAttribute($parentColumn) && $record->getAttribute($parentColumn) !== $resource::getTreeDefaultParentId()) {
            $model = $resource::getModel();
            $parent = $model::find($record->getAttribute($parentColumn));
            $data['_tree_parent_name'] = $parent ? $parent->getAttribute($resource::getTreeTitleColumn()) : 'Unknown';
        } else {
            $data['_tree_parent_name'] = 'Root Level';
        }
        
        // Add position information
        $data['_tree_order'] = $record->getAttribute($orderColumn);
        
        // Count children if relationship exists
        if (method_exists($record, $resource::getTreeChildrenKeyName())) {
            $childrenKeyName = $resource::getTreeChildrenKeyName();
            $data['_tree_children_count'] = $record->$childrenKeyName()->count();
        }

        return parent::mutateFormDataBeforeFill($data);
    }
}
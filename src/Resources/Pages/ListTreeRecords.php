<?php

namespace SolutionForest\FilamentTree\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Concern\InteractWithTree;
use SolutionForest\FilamentTree\Contract\HasTree;

class ListTreeRecords extends ListRecords implements HasTree
{
    use InteractWithTree;

    protected string $view = 'filament-tree::resources.pages.list-tree-records';

    protected ?Table $cachedTable = null;
    protected array $cachedHeaderActions = [];
    protected bool $cachedHeaderActionsProcessed = false;

    /**
     * Override to prevent calling the resource's table method
     */
    protected function makeTable(): Table
    {
        return $this->getTable();
    }

    /**
     * CRITICAL: Override to prevent table initialization
     * This prevents any table query from being executed
     */
    protected function getTableQuery(): ?Builder
    {
        return null;
    }

    /**
     * CRITICAL: Override to prevent table rendering
     * This ensures no table HTML is generated
     */
    public function getTable(): Table
    {
        // Create a table directly without going through the resource
        if (!isset($this->cachedTable)) {
            $this->cachedTable = Table::make($this)
                ->query(fn () => $this->getModel()::query()->whereRaw('1 = 0'))
                ->columns([])
                ->filters([])
                ->actions([])
                ->bulkActions([]);
        }

        return $this->cachedTable;
    }

    /**
     * CRITICAL: Override table section to return nothing
     * This removes the entire table container
     */
    protected function getTableSection(): ?View
    {
        return null;
    }

    /**
     * Get the tree component configuration
     */
    public function getTree(): Tree
    {
        $resource = static::getResource();

        $tree = $resource::tree(
            Tree::make($this)
        );

        // Set whether the user can update the tree order
        $tree->canUpdateOrder($this->canUpdateTreeOrder());

        // Only add tree actions (record-level actions), not header actions
        if (method_exists($resource, 'getTreeActions')) {
            $treeActions = $resource::getTreeActions();
            
            if (!empty($treeActions)) {
                $tree->actions($treeActions);
            }
        }

        return $tree;
    }

    /**
     * Get header actions (create button, etc.)
     */
    protected function getHeaderActions(): array
    {
        $resource = static::getResource();

        if (method_exists($resource, 'getTreeHeaderActions')) {
            $actions = $resource::getTreeHeaderActions();

            // Filter based on policy if enabled
            if (config('filament-tree.enable_policy_authorization', false)) {
                $actions = array_filter($actions, function ($action) {
                    return $action->isVisible();
                });
            }

            return $actions;
        }

        return [];
    }

    /**
     * Override content tabs to not show table-related tabs
     */
    protected function getContentTabs(): ?array
    {
        return null;
    }

    /**
     * Override to prevent table-related actions
     */
    protected function getTableActions(): array
    {
        return [];
    }

    /**
     * Override to prevent table-related bulk actions
     */
    protected function getTableBulkActions(): array
    {
        return [];
    }

    /**
     * Override to prevent table-related filters
     */
    protected function getTableFilters(): array
    {
        return [];
    }

    /**
     * Configure the tree structure - delegates to resource
     */
    public static function tree(Tree $tree): Tree
    {
        $resource = static::getResource();
        return $resource::tree($tree);
    }

    /**
     * Get the maximum depth for the tree
     */
    public static function getMaxDepth(): int
    {
        return 999; // Default max depth
    }

    /**
     * Make translatable content driver
     */
    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }
    
    /**
     * Override to cache both tree actions and header actions for different purposes
     */
    public function cacheTreeActions(): void
    {
        $this->cachedTreeActions = [];

        // Only cache header actions here - tree actions are handled separately
        $resource = static::getResource();
        $allActions = [];
        
        if (method_exists($resource, 'getTreeHeaderActions')) {
            $allActions = array_merge($allActions, $resource::getTreeHeaderActions());
        }

        $actions = \SolutionForest\FilamentTree\Actions\Action::configureUsing(
            \Closure::fromCallable([$this, 'configureTreeAction']),
            fn (): array => $allActions,
        );

        foreach ($actions as $index => $action) {
            if ($action instanceof \SolutionForest\FilamentTree\Actions\ActionGroup) {
                foreach ($action->getActions() as $groupedAction) {
                    $groupedAction->tree($this->getCachedTree());
                }

                $this->cachedTreeActions[$index] = $action;

                continue;
            }

            $action->tree($this->getCachedTree());

            $this->cachedTreeActions[$action->getName()] = $action;
        }
    }

    /**
     * Override to properly handle tree header actions with policy authorization
     */
    public function getCachedHeaderActions(): array
    {
        if (! $this->cachedHeaderActionsProcessed) {
            $this->cacheHeaderActions();
            $this->cachedHeaderActionsProcessed = true;
        }

        return $this->cachedHeaderActions;
    }


    /**
     * Override to use tree header actions with policy authorization
     */
    protected function cacheHeaderActions(): void
    {
        $this->cachedHeaderActions = [];

        $resource = static::getResource();
        
        if (method_exists($resource, 'getTreeHeaderActions')) {
            $actions = $resource::getTreeHeaderActions();
            
            // Configure actions with tree context and configure using the same system as tree actions
            $configuredActions = \SolutionForest\FilamentTree\Actions\Action::configureUsing(
                \Closure::fromCallable([$this, 'configureTreeAction']),
                fn (): array => $actions,
            );
            
            // Set tree context for each action
            foreach ($configuredActions as $action) {
                if ($action instanceof \SolutionForest\FilamentTree\Actions\Action) {
                    $action->tree($this->getCachedTree());
                }
            }
            
            // Filter by visibility (includes policy authorization)
            if (config('filament-tree.enable_policy_authorization', false)) {
                $configuredActions = array_filter($configuredActions, function ($action) {
                    return $action->isVisible();
                });
            }
            
            $this->cachedHeaderActions = $configuredActions;
        }
    }

    /**
     * Check if the current user can update/reorder tree records
     */
    public function canUpdateTreeOrder(): bool
    {
        $policyAuthEnabled = config('filament-tree.enable_policy_authorization', false);

        if (! $policyAuthEnabled) {
            return true; // Allow if policy authorization is disabled
        }

        $modelClass = static::getResource()::getModel();
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Try to authorize using Laravel's Gate/Policy system
        try {
            // Create a temporary model instance to check update permission
            $tempModel = new $modelClass();
            return $user->can('update', $tempModel);
        } catch (\Exception $e) {
            // If no policy exists or authorization fails, default to true
            return true;
        }
    }
}

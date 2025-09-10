@php
    $headerActions = $this->getCachedHeaderActions();
    $breadcrumbs = filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : [];
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
@endphp

<div class="fi-page">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_START, scopes: $this->getRenderHookScopes()) }}
    <div class="fi-page-header-main-ctn">
        @if ($header = $this->getHeader())
            {{ $header }}
        @elseif ($heading)
            <x-filament-panels::header
                :actions="$headerActions"
                :breadcrumbs="$breadcrumbs"
                :heading="$heading"
                :subheading="$subheading"
            />
        @endif
        <div class="fi-page-main">
            <div class="fi-page-content">
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_WIDGETS_BEFORE, scopes: $this->getRenderHookScopes()) }}

                {{ $this->headerWidgets }}

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER, scopes: $this->getRenderHookScopes()) }}

                {{-- Tree component - NO TABLE! --}}
                <div class="filament-tree-resource-content">
                    {{ $this->getTree() }}
                </div>

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_FOOTER_WIDGETS_BEFORE, scopes: $this->getRenderHookScopes()) }}

                {{ $this->footerWidgets }}

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_FOOTER_WIDGETS_AFTER, scopes: $this->getRenderHookScopes()) }}
            </div>
        </div>

        @if ($footer = $this->getFooter())
            {{ $footer }}
        @endif
    </div>

    <x-filament-actions::modals />

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_END, scopes: $this->getRenderHookScopes()) }}
</div>

@props(['title', 'icon', 'description', 'record'])
@php
    use Illuminate\Support\HtmlString;
@endphp

<div {{
    $attributes->merge([
        'class' => 'tree-item-display'
    ])
}}>
    @if ($icon)
        <div class="icon-ctn">
            <x-dynamic-component :component="$icon"/>
        </div>
    @endif

    <div class="item-content-ctn">
        <span class="item-title">
            {{ str($title)->sanitizeHtml()->toHtmlString() }}
        </span>
    
        @if ($description && (is_string($description) || $description instanceof HtmlString))
            @if (is_string($description))
                <span class="item-description">
                    {{ str($description)->sanitizeHtml()->toHtmlString() }}
                </span>
            @else
                {!! $description->toHtml() !!}
            @endif
            
        @endif
    </div>
    
</div>
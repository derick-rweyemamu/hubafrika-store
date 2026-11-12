@php
    $whatsappHref = $businessAssets['contact']['whatsappHref'] ?? null;
    $siteTitle = trim((string) ($businessAssets['site_name'] ?? config('app.name', 'HubAfrika')));
@endphp

@if (filled($whatsappHref))
    <a href="{{ $whatsappHref }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="{{ __('Chat with :store on WhatsApp', ['store' => $siteTitle !== '' ? $siteTitle : 'us']) }}"
       title="{{ __('Chat on WhatsApp') }}"
       class="sf-whatsapp-float print:hidden">
        @include('components.layouts.partials.storefront-footer-icon', ['name' => 'whatsapp'])
    </a>
@endif


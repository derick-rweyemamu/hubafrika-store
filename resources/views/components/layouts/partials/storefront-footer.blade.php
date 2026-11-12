@php
    // Every region of the footer is fed by the CMS "footer" menu: top level
    // items with children are the link columns, top level items without
    // children are the legal links, and social links become buttons. Column
    // headers whose links are not added yet still render as headings.
    $footerLayout = \App\Support\Storefront\FooterLayout::fromTree($storefrontMenus['footer']['tree'] ?? []);
    $footerColumns = $footerLayout->columns();
    $footerSocials = $footerLayout->social();
    $footerLegalLinks = $footerLayout->legalLinks();

    $siteName = trim((string) ($businessAssets['site_name'] ?? config('app.name')));
    $siteNameWords = preg_split('/\s+/', $siteName) ?: [$siteName];
    $siteNameLast = array_pop($siteNameWords);
    $siteNameLead = implode(' ', $siteNameWords);
    $footerLogo = $businessAssets['logoDark'] ?? $businessAssets['logo'] ?? null;

    $footerAbout = trim((string) ($businessAssets['footer_about'] ?? ''));

    if (\App\Support\RichText::plainText($footerAbout) === '') {
        $footerAbout = trim((string) ($businessAssets['site_motto'] ?? ''));
    }
@endphp

{{-- Subscribe to our newsletter section (from prototype) --}}
<section class="nl" x-data="{
    email: '{{ old('email') }}',
    loading: false,
    statusMessage: '{{ session('newsletter_status') ?? '' }}',
    errorMessage: '',
    async submitForm() {
        const trimmed = (this.email || '').trim();
        if (!trimmed) return;
        this.loading = true;
        this.errorMessage = '';
        this.statusMessage = '';
        try {
            const token = document.querySelector('meta[name=\'csrf-token\']')?.content || '{{ csrf_token() }}';
            const res = await fetch('{{ route('newsletter.subscribe') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email: trimmed, source: 'footer' })
            });
            const data = await res.json();
            if (res.ok) {
                this.statusMessage = data.message || '{{ __('Thanks for subscribing. We will keep you posted.') }}';
                this.email = '';
            } else {
                this.errorMessage = data.errors?.email?.[0] || data.message || '{{ __('Failed to subscribe. Please try again.') }}';
            }
        } catch (e) {
            this.errorMessage = '{{ __('Something went wrong. Please check your connection.') }}';
        } finally {
            this.loading = false;
        }
    }
}">
    <div class="w">
        <h3 class="st">{{ __('Subscribe to our newsletter') }}</h3>
        <p class="mut" style="margin:0">{{ __('Get the latest updates on new products and promotions.') }}</p>
        <form id="nf" method="POST" action="{{ route('newsletter.subscribe') }}" class="sf-news-form" @submit.prevent="submitForm">
            @csrf
            <label class="sr-only" for="footer-newsletter-email">{{ __('Email address') }}</label>
            <input id="footer-newsletter-email" type="email" name="email" x-model="email" value="{{ old('email') }}"
                placeholder="{{ __('Email address') }}" autocomplete="email" required class="sf-news-input" :disabled="loading">
            <button type="submit" class="btn sf-news-submit" :disabled="loading">
                <span x-show="!loading">{{ __('Subscribe') }}</span>
                <span x-show="loading" x-cloak>{{ __('Subscribing...') }}</span>
                <span class="sr-only">{{ __('Sign Up') }}</span>
            </button>
        </form>

        <p x-show="statusMessage" x-cloak class="sf-news-note" role="status" x-text="statusMessage"></p>
        <p x-show="errorMessage" x-cloak class="sf-news-error" role="alert" x-text="errorMessage"></p>

        @error('email')
            <p x-show="!errorMessage" class="sf-news-error" role="alert">{{ $message }}</p>
        @enderror

        @if (session('newsletter_status'))
            <p x-show="!statusMessage" class="sf-news-note" role="status">{{ session('newsletter_status') }}</p>
        @endif
    </div>
</section>

<footer class="sf-footer">
    <div class="sf-footer-inner">
        <div @class(['sf-footer-main', 'sf-footer-main--brand-only' => $footerColumns === []])>
            <div class="sf-footer-brand">
                <a href="{{ route('home') }}" class="sf-footer-logo" aria-label="{{ $siteName }}">
                    @if ($footerLogo !== null)
                        <img src="{{ $footerLogo }}" alt="{{ $siteName }}">
                    @else
                        <span>{{ $siteNameLead !== '' ? $siteNameLead.' ' : '' }}<b>{{ $siteNameLast }}</b></span>
                    @endif
                </a>

                @if ($footerAbout !== '')
                    <div class="sf-footer-about">{!! \App\Support\RichText::toHtml($footerAbout) !!}</div>
                @endif

                @php
                    $mergedSocials = collect($businessAssets['socials'] ?? [])
                        ->concat($footerSocials)
                        ->unique('href')
                        ->values()
                        ->all();
                @endphp

                @if ($mergedSocials !== [])
                    <div class="sf-socials">
                        @foreach ($mergedSocials as $social)
                            <a href="{{ $social['href'] }}" class="sf-social-link" target="_blank" rel="noopener noreferrer"
                                aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                                @include('components.layouts.partials.storefront-footer-icon', ['name' => $social['icon']])
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($footerColumns !== [])
                <div class="sf-footer-columns">
                    @foreach ($footerColumns as $column)
                        <nav aria-label="{{ $column['label'] }}" class="sf-footer-column">
                            @if ($column['href'] !== null)
                                <a href="{{ $column['href'] }}" class="sf-footer-heading">{{ $column['label'] }}</a>
                            @else
                                <span class="sf-footer-heading">{{ $column['label'] }}</span>
                            @endif

                            @if ($column['links'] !== [])
                                <ul class="sf-footer-links">
                                    @foreach ($column['links'] as $link)
                                        <li>
                                            @if ($link['href'] !== null)
                                                <a href="{{ $link['href'] }}"
                                                    @class(['sf-footer-link', 'has-icon' => $link['icon'] !== null])
                                                    @if ($link['external']) target="_blank" rel="noopener noreferrer" @endif>
                                                    @if ($link['icon'] !== null)
                                                        <span class="sf-footer-icon">
                                                            @include('components.layouts.partials.storefront-footer-icon', ['name' => $link['icon']])
                                                        </span>
                                                    @endif
                                                    <span>{{ $link['label'] }}</span>
                                                </a>
                                            @else
                                                <span class="sf-footer-link">{{ $link['label'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </nav>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="sf-footer-divider" role="presentation"></div>

        <div class="sf-footer-bottom">
            <p class="sf-footer-copy">{{ __('Copyright') }} &copy; {{ now()->year }} {{ $siteName }}. {{ __('All rights reserved.') }}</p>

            @if ($footerLegalLinks !== [])
                <ul class="sf-footer-legal">
                    @foreach ($footerLegalLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="sf-footer-legal-link"
                                @if ($link['external']) target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</footer>

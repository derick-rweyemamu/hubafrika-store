<?php

namespace App\Livewire\Storefront;

use App\Models\CmsPage as Page;
use App\Models\CmsPost as Post;
use App\Support\Storefront\PageBlocks;
use App\Support\Storefront\Palette;
use App\Support\Storefront\ProductGrid;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Livewire\Component;

class CmsPage extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $page = Page::query()->where('slug', $this->slug)->firstOrFail();

        return $this->renderContent($page);
    }

    protected function renderContent(Page|Post $page)
    {
        $data = $page->builderData();
        abort_unless($data['published'], 404);

        $isVisualBuilderPage = isset($data['html']);
        $hasProductGrid = $isVisualBuilderPage
            ? str_contains($data['html'], 'data-hubafrika-products')
            : collect($data['blocks'])->where('type', 'product_grid')->isNotEmpty();
        $currency = $hasProductGrid ? ProductGrid::currency() : '';
        // Legacy canvas pages carry no block settings, so they list the newest
        // products with the default limit.
        $productBlocks = $hasProductGrid && ! $isVisualBuilderPage
            ? ProductGrid::products()
            : collect();
        $pageHeight = collect($data['blocks'])
            ->max(fn (array $block): int => (int) $block['y'] + (int) $block['height']) ?? 0;
        $builderDocument = null;

        if ($isVisualBuilderPage) {
            $builderDocument = $this->builderDocument($data['html'], $data['css'] ?? '', $currency, $page->title);
        }

        return view('livewire.storefront.cms-page', [
            'page' => $page,
            'blocks' => $data['blocks'],
            'productBlocks' => $productBlocks,
            'currency' => $currency,
            'pageHeight' => max(900, $pageHeight + 40),
            'builderDocument' => $builderDocument,
        ])->layout('components.layouts.storefront', ['title' => $page->title, 'fullWidth' => $isVisualBuilderPage]);
    }

    /**
     * The sandboxed document a visual-builder page is shown in.
     *
     * The document is its own CSS island, so it loads the same stylesheet the
     * builder canvas uses and receives the storefront palette and body font as
     * custom properties.
     */
    protected function builderDocument(string $html, string $css, string $currency, string $pageTitle = ''): string
    {
        $nonce = Str::random(32);
        $stylesheet = Vite::asset('resources/css/app.css');
        $assetOrigin = parse_url($stylesheet, PHP_URL_SCHEME).'://'.parse_url($stylesheet, PHP_URL_HOST)
            .(parse_url($stylesheet, PHP_URL_PORT) ? ':'.parse_url($stylesheet, PHP_URL_PORT) : '');
        $palette = Palette::frontend();
        $bodyStyle = collect([
            '--storefront-primary' => $palette['primary'],
            '--storefront-secondary' => $palette['secondary'],
            '--storefront-accent' => $palette['accent'],
            '--storefront-bg' => $palette['bg'],
            '--storefront-surface' => $palette['surface'],
            '--storefront-text' => $palette['text'],
        ])->map(fn (string $value, string $name): string => $name.':'.$value)->implode(';');
        $contentSecurityPolicy = "default-src 'none'; script-src 'nonce-{$nonce}'; style-src 'unsafe-inline' https: {$assetOrigin}; img-src https: http: data:; font-src https: data:; connect-src 'none'; form-action 'none'; frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com; object-src 'none'; base-uri 'none'";
        $resizeScript = '<script nonce="'.$nonce.'">'
            .'const reportPageHeight=()=>window.parent.postMessage({type:"hubafrika-cms-resize",height:Math.ceil(document.documentElement.scrollHeight)},"*");'
            .'document.addEventListener("click",event=>{const link=event.target instanceof Element?event.target.closest("a[href]"):null;if(!link)return;const url=new URL(link.href,window.location.href);if(!["http:","https:"].includes(url.protocol))return;event.preventDefault();window.parent.postMessage({type:"hubafrika-cms-navigate",href:url.href,target:link.target||""},"*")});'
            .'new ResizeObserver(reportPageHeight).observe(document.documentElement);'
            .'window.addEventListener("load",reportPageHeight);reportPageHeight();'
            .'</script>';

        return '<!doctype html><html lang="en"><head>'
            .'<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<meta http-equiv="Content-Security-Policy" content="'.e($contentSecurityPolicy).'">'
            .'<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap">'
            .'<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">'
            .'<link rel="stylesheet" href="'.e($stylesheet).'">'
            .'<style>html{scroll-behavior:smooth}html,body{overflow:hidden}body{margin:0;background-color:var(--storefront-bg);color:var(--storefront-text);font-family:'.Palette::bodyFont().'}</style>'
            .'<style>'.$css.'</style></head>'
            .'<body style="'.e($bodyStyle).'">'
            .PageBlocks::renderPageHeaders(ProductGrid::render($html, $currency), $pageTitle)
            .PageBlocks::script($nonce, url()->current())
            .$resizeScript.'</body></html>';
    }
}

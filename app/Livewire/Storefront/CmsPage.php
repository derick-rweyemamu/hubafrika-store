<?php

namespace App\Livewire\Storefront;

use App\Models\CmsPage as Page;
use App\Models\CmsPost as Post;
use App\Support\Storefront\Palette;
use App\Support\Storefront\ProductGrid;
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
        // The page document is its own CSS island, so the theme colour the
        // cards print is handed to it as a custom property.
        $primary = $hasProductGrid ? Palette::primary() : '';
        // Legacy canvas pages carry no block settings, so they list the newest
        // products with the default limit.
        $productBlocks = $hasProductGrid && ! $isVisualBuilderPage
            ? ProductGrid::products()
            : collect();
        $pageHeight = collect($data['blocks'])
            ->max(fn (array $block): int => (int) $block['y'] + (int) $block['height']) ?? 0;
        $builderDocument = null;

        if ($isVisualBuilderPage) {
            $nonce = Str::random(32);
            $contentSecurityPolicy = "default-src 'none'; script-src 'nonce-{$nonce}'; style-src 'unsafe-inline' https:; img-src https: http: data:; font-src https: data:; connect-src 'none'; form-action 'none'; frame-src 'none'; object-src 'none'; base-uri 'none'";
            $resizeScript = '<script nonce="'.$nonce.'">'
                .'const reportPageHeight=()=>window.parent.postMessage({type:"hubafrika-cms-resize",height:Math.ceil(document.documentElement.scrollHeight)},"*");'
                .'document.addEventListener("click",event=>{const link=event.target instanceof Element?event.target.closest("a[href]"):null;if(!link)return;const url=new URL(link.href,window.location.href);if(!["http:","https:"].includes(url.protocol))return;event.preventDefault();window.parent.postMessage({type:"hubafrika-cms-navigate",href:url.href},"*")});'
                .'new ResizeObserver(reportPageHeight).observe(document.documentElement);'
                .'window.addEventListener("load",reportPageHeight);reportPageHeight();'
                .'</script>';
            $builderDocument = '<!doctype html><html lang="en"><head>'
                .'<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
                .'<meta http-equiv="Content-Security-Policy" content="'.e($contentSecurityPolicy).'">'
                .'<style>html{scroll-behavior:smooth}body{margin:0;font-family:Arial,sans-serif}</style>'
                .'<style>'.$data['css'].'</style></head>'
                .($primary === '' ? '<body>' : '<body style="--storefront-primary:'.e($primary).'">')
                .ProductGrid::render($data['html'], $currency)
                .$resizeScript.'</body></html>';
        }

        return view('livewire.storefront.cms-page', [
            'page' => $page,
            'blocks' => $data['blocks'],
            'productBlocks' => $productBlocks,
            'currency' => $currency,
            'pageHeight' => max(900, $pageHeight + 40),
            'builderDocument' => $builderDocument,
        ])->layout('components.layouts.storefront', ['title' => $page->title]);
    }
}

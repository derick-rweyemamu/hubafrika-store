<?php

namespace App\Support\Storefront;

/**
 * Reshapes the CMS footer menu tree into the three regions the storefront
 * footer renders.
 *
 * Top level items that hold children become link columns (the item is the
 * column title), top level items without children become the legal links
 * that sit beside the copyright notice, and any link that points at a known
 * social network is pulled out into the social button strip wherever it was
 * placed in the menu. A column header whose links have not been added yet
 * still renders as a heading, so the menu is never silently dropped.
 */
class FooterLayout
{
    /**
     * Host suffix => icon handled by the footer icon partial.
     *
     * @var array<string, string>
     */
    private const SOCIAL_NETWORKS = [
        'facebook.com' => 'facebook',
        'instagram.com' => 'instagram',
        'x.com' => 'x',
        'twitter.com' => 'x',
        'youtube.com' => 'youtube',
        'linkedin.com' => 'linkedin',
        'tiktok.com' => 'tiktok',
        'wa.me' => 'whatsapp',
        'whatsapp.com' => 'whatsapp',
    ];

    /**
     * @param  array<int, array<string, mixed>>  $tree
     */
    private function __construct(private readonly array $tree) {}

    /**
     * @param  array<int, array<string, mixed>>  $tree
     */
    public static function fromTree(array $tree): self
    {
        return new self($tree);
    }

    /**
     * Column blocks, each holding the flat list of links under its title.
     *
     * @return array<int, array{label: string, href: string|null, links: array<int, array{label: string, href: string, icon: string|null, external: bool, social: bool}>}>
     */
    public function columns(): array
    {
        $columns = [];

        foreach ($this->tree as $node) {
            $children = $node['children'] ?? [];
            $href = $this->href($node);

            // A standalone link belongs to the legal links under the columns.
            if ($children === [] && $href !== null) {
                continue;
            }

            $links = array_values(array_filter(
                $this->flatten($children),
                fn (array $link): bool => ! $link['social'],
            ));

            $columns[] = [
                'label' => (string) ($node['label'] ?? ''),
                'href' => $href,
                'links' => $links,
            ];
        }

        return $columns;
    }

    /**
     * Social buttons, de-duplicated by destination and kept in menu order.
     *
     * @return array<int, array{label: string, href: string, icon: string|null, external: bool, social: bool}>
     */
    public function social(): array
    {
        $social = array_values(array_filter(
            $this->flatten($this->tree),
            fn (array $link): bool => $link['social'],
        ));

        $seen = [];

        return array_values(array_filter($social, function (array $link) use (&$seen): bool {
            if (in_array($link['href'], $seen, true)) {
                return false;
            }

            $seen[] = $link['href'];

            return true;
        }));
    }

    /**
     * Root items without children: the short legal links under the columns.
     *
     * @return array<int, array{label: string, href: string, icon: string|null, external: bool, social: bool}>
     */
    public function legalLinks(): array
    {
        $links = [];

        foreach ($this->tree as $node) {
            if (($node['children'] ?? []) !== []) {
                continue;
            }

            $link = $this->link($node);

            if ($link['href'] !== null && ! $link['social']) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * Walk a branch depth first, so a nested group still lists every link.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array{label: string, href: string, icon: string|null, external: bool, social: bool}>
     */
    private function flatten(array $nodes): array
    {
        $links = [];

        foreach ($nodes as $node) {
            $link = $this->link($node);

            if ($link['href'] !== null) {
                $links[] = $link;
            }

            foreach ($this->flatten($node['children'] ?? []) as $child) {
                $links[] = $child;
            }
        }

        return $links;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array{label: string, href: string|null, icon: string|null, external: bool, social: bool}
     */
    private function link(array $node): array
    {
        $href = $this->href($node);
        $network = $href === null ? null : $this->network($href);

        return [
            'label' => (string) ($node['label'] ?? ''),
            'href' => $href,
            'icon' => $network ?? $this->transportIcon($href),
            'external' => $href !== null && str_starts_with($href, 'http'),
            'social' => $network !== null,
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function href(array $node): ?string
    {
        $href = $node['href'] ?? null;

        return is_string($href) && $href !== '' ? $href : null;
    }

    /**
     * Match the link host against the social networks, including subdomains.
     */
    private function network(string $href): ?string
    {
        $host = parse_url($href, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);

        foreach (self::SOCIAL_NETWORKS as $domain => $icon) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return $icon;
            }
        }

        return null;
    }

    /**
     * Phone and email links get their own icon instead of a dash bullet.
     */
    private function transportIcon(?string $href): ?string
    {
        if ($href === null) {
            return null;
        }

        return match (strtolower((string) parse_url($href, PHP_URL_SCHEME))) {
            'tel' => 'phone',
            'mailto' => 'mail',
            default => null,
        };
    }
}

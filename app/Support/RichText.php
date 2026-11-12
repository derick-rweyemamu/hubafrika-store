<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

/**
 * Sanitizes and renders rich-text HTML authored in the admin editor.
 */
class RichText
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'div',
        'strong',
        'b',
        'em',
        'i',
        'u',
        's',
        'del',
        'h2',
        'h3',
        'h4',
        'ul',
        'ol',
        'li',
        'blockquote',
        'code',
        'pre',
        'hr',
        'a',
    ];

    /**
     * @var array<string, string>
     */
    private const TAG_ALIASES = [
        'h1' => 'h2',
        'h5' => 'h4',
        'h6' => 'h4',
    ];

    /**
     * Elements removed along with all of their descendant content.
     *
     * @var list<string>
     */
    private const DROP_ELEMENTS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'applet',
        'svg',
        'math',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'option',
        'meta',
        'link',
        'base',
        'template',
        'noscript',
        'img',
    ];

    public static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if (! self::containsHtmlTag($trimmed)) {
            return $trimmed;
        }

        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML(
                '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="__rich_root">'.$trimmed.'</div></body></html>',
                LIBXML_NONET,
            );
            $document->encoding = 'UTF-8';

            $root = $document->getElementById('__rich_root');

            if (! $root instanceof DOMElement) {
                return null;
            }

            self::sanitizeChildren($root, $document);

            $output = '';

            foreach (iterator_to_array($root->childNodes) as $child) {
                $output .= (string) $document->saveHTML($child);
            }

            $output = trim($output);

            if ($output === '' || self::plainText($output) === '') {
                return null;
            }

            return $output;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }
    }

    public static function toHtml(?string $value): string
    {
        $sanitized = self::sanitize($value);

        if ($sanitized === null) {
            return '';
        }

        if (! self::containsHtmlTag($sanitized)) {
            return nl2br(e($sanitized), false);
        }

        return $sanitized;
    }

    public static function plainText(?string $value, ?int $limit = null): string
    {
        if ($value === null) {
            return '';
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $withBreaks = preg_replace('/<\/(?:p|div|h[1-6]|li|blockquote|pre)>|<br\s*\/?>/i', ' ', $trimmed) ?? $trimmed;
        $decoded = html_entity_decode(strip_tags($withBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/[\x{00A0}\s]+/u', ' ', $decoded) ?? $decoded;
        $text = trim($normalized);

        if ($text === '') {
            return '';
        }

        if ($limit !== null) {
            return Str::limit($text, $limit);
        }

        return $text;
    }

    public static function containsHtmlTag(string $value): bool
    {
        return preg_match('/<\/?[a-zA-Z][a-zA-Z0-9-]*(?:\s[^>]*)?>/', $value) === 1;
    }

    public static function isSafeUrl(string $url): bool
    {
        $clean = trim(str_replace(["\r", "\n", "\t", "\0"], '', $url));

        if ($clean === '' || str_starts_with($clean, '//')) {
            return false;
        }

        if (str_starts_with($clean, '/') || str_starts_with($clean, '#')) {
            return true;
        }

        $parts = parse_url($clean);

        if ($parts === false || empty($parts['scheme'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);

        if (in_array($scheme, ['mailto', 'tel'], true)) {
            return trim((string) ($parts['path'] ?? '')) !== '';
        }

        if (in_array($scheme, ['http', 'https'], true)) {
            return ! empty($parts['host']);
        }

        return false;
    }

    private static function sanitizeChildren(DOMNode $parent, DOMDocument $document): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_ELEMENTS, true)) {
                $parent->removeChild($child);

                continue;
            }

            self::sanitizeChildren($child, $document);

            if (isset(self::TAG_ALIASES[$tag])) {
                $replacement = $document->createElement(self::TAG_ALIASES[$tag]);

                while ($child->firstChild !== null) {
                    $replacement->appendChild($child->firstChild);
                }

                $parent->replaceChild($replacement, $child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::unwrapElement($child, $parent);

                continue;
            }

            if ($tag === 'a') {
                if (! self::sanitizeAnchor($child)) {
                    self::unwrapElement($child, $parent);
                }

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $child->removeAttribute($attribute->name);
            }
        }
    }

    private static function sanitizeAnchor(DOMElement $anchor): bool
    {
        $href = trim((string) $anchor->getAttribute('href'));
        $target = trim((string) $anchor->getAttribute('target'));
        $title = trim((string) $anchor->getAttribute('title'));

        foreach (iterator_to_array($anchor->attributes) as $attribute) {
            $anchor->removeAttribute($attribute->name);
        }

        if (! self::isSafeUrl($href)) {
            return false;
        }

        $anchor->setAttribute('href', $href);

        if ($title !== '') {
            $anchor->setAttribute('title', $title);
        }

        if ($target === '_blank') {
            $anchor->setAttribute('target', '_blank');
            $anchor->setAttribute('rel', 'noopener noreferrer');
        }

        return true;
    }

    private static function unwrapElement(DOMElement $element, DOMNode $parent): void
    {
        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}

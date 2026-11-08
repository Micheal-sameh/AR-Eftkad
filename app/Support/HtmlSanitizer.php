<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/** Allow-list HTML cleaner for owner-written rich content (no scripts, handlers or styles beyond text-align). */
class HtmlSanitizer
{
    private const TAGS = [
        'p', 'div', 'br', 'hr', 'b', 'strong', 'i', 'em', 'u', 's', 'span', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'a', 'img',
    ];

    /** Removed together with everything inside. */
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'link', 'meta'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType !== XML_TEXT_NODE) {
                    $node->removeChild($child); // comments, processing instructions...
                }

                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($child);

            if (! in_array($tag, self::TAGS, true)) {
                // Unknown tag: keep its (already cleaned) content, drop the wrapper.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $keep = [];
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = trim($attr->value);

            if ($name === 'style' && preg_match('/text-align\s*:\s*(left|right|center|justify)/i', $value, $m)) {
                $keep['style'] = 'text-align: '.strtolower($m[1]);
            } elseif ($name === 'dir' && in_array($value, ['rtl', 'ltr'], true)) {
                $keep['dir'] = $value;
            } elseif ($tag === 'a' && $name === 'href' && self::safeUrl($value, true)) {
                $keep['href'] = $value;
            } elseif ($tag === 'img' && $name === 'src' && self::safeUrl($value, false)) {
                $keep['src'] = $value;
            } elseif ($tag === 'img' && $name === 'alt') {
                $keep['alt'] = $value;
            }
        }

        foreach (iterator_to_array($el->attributes) as $attr) {
            $el->removeAttribute($attr->name);
        }
        foreach ($keep as $k => $v) {
            $el->setAttribute($k, $v);
        }

        if ($tag === 'a') {
            $el->setAttribute('target', '_blank');
            $el->setAttribute('rel', 'noopener noreferrer');
        }
        if ($tag === 'img') {
            $el->setAttribute('loading', 'lazy');
        }
    }

    private static function safeUrl(string $url, bool $allowLinks): bool
    {
        if (preg_match('#^(https?://|/(?!/))#i', $url)) {
            return true;
        }

        return $allowLinks && preg_match('#^(mailto:|tel:)#i', $url) === 1;
    }
}

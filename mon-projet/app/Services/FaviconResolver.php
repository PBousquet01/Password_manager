<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Http;
use Throwable;

class FaviconResolver
{
    public function resolve(string $websiteUrl, ?string $providedFaviconUrl = null): ?string
    {
        if ($providedFaviconUrl) {
            return $providedFaviconUrl;
        }

        $faviconUrl = $this->extractFromWebsite($websiteUrl);

        if ($faviconUrl) {
            return $faviconUrl;
        }

        return $this->fallbackUrl($websiteUrl);
    }

    private function extractFromWebsite(string $websiteUrl): ?string
    {
        try {
            $response = Http::timeout(5)->get($websiteUrl);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $this->extractFromHtml($response->body(), $websiteUrl);
    }

    private function extractFromHtml(string $html, string $websiteUrl): ?string
    {
        $document = new DOMDocument();

        libxml_use_internal_errors(true);
        $loaded = $document->loadHTML($html);
        libxml_clear_errors();

        if (! $loaded) {
            return null;
        }

        $bestIcon = null;
        $bestScore = -1;

        foreach ($document->getElementsByTagName('link') as $link) {
            $rel = strtolower($link->getAttribute('rel'));
            $href = trim($link->getAttribute('href'));

            if ($href === '' || ! str_contains($rel, 'icon')) {
                continue;
            }

            $score = $this->iconScore($rel, $link->getAttribute('sizes'));

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIcon = $this->absoluteUrl($href, $websiteUrl);
            }
        }

        return $bestIcon;
    }

    private function iconScore(string $rel, string $sizes): int
    {
        $score = str_contains($rel, 'apple-touch-icon') ? 1000 : 0;

        if (preg_match_all('/(\d+)x(\d+)/', $sizes, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $score = max($score, (int) $match[1] * (int) $match[2]);
            }
        }

        return $score;
    }

    private function absoluteUrl(string $href, string $websiteUrl): ?string
    {
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }

        $scheme = parse_url($websiteUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($websiteUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        if (str_starts_with($href, '//')) {
            return $scheme . ':' . $href;
        }

        if (str_starts_with($href, '/')) {
            return $scheme . '://' . $host . $href;
        }

        $path = parse_url($websiteUrl, PHP_URL_PATH) ?: '/';
        $directory = rtrim(str_replace(basename($path), '', $path), '/');

        return $scheme . '://' . $host . $directory . '/' . $href;
    }

    private function fallbackUrl(string $websiteUrl): ?string
    {
        $host = parse_url($websiteUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        return 'https://www.google.com/s2/favicons?domain=' . urlencode($host) . '&sz=256';
    }
}

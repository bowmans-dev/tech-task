<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class HtmlMinifier
{
    public static function minify(string $html): string
    {
        $htmlSize = strlen($html); // in bytes
        Log::info("HTML size: {$htmlSize} bytes (" . round($htmlSize / 1024, 2) . " KB)");

        // Remove line breaks, tabs, and multiple spaces
        $html = preg_replace('/\s+/', ' ', $html);

        // Remove spaces between HTML tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Trim leading/trailing whitespace
        $html = trim($html);

        $minifiedHtmlSize = strlen($html);
        Log::info("MINIFIED HTML size: {$minifiedHtmlSize} bytes (" . round($minifiedHtmlSize / 1024, 2) . " KB)");

        return $html;
    }
}

<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize, benchmark, cache_get, cache_put, validate_url};

// Simple HTTP client using cURL
if (!function_exists('ai_rewriter_http_post_json')) {
    function ai_rewriter_http_post_json($url, $headers, $payload, $timeout = 20)
    {
        if (!function_exists('curl_init')) {
            logger(''HTTP error: cURL not available'', ''error'', ''ai_rewriter.log'');
            return [false, ''PHP cURL extension is not available'', 0, null];
        }

        // Validate URL
        if (!validate_url($url)) {
            logger(''HTTP error: invalid URL='' . $url, ''error'', ''ai_rewriter.log'');
            return [false, ''Invalid URL provided'', 0, null];
        }

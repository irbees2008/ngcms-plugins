<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize, benchmark, cache_get, cache_put, validate_url};

// Simple HTTP client using cURL
if (!function_exists('ai_rewriter_http_post_json')) {
    function ai_rewriter_http_post_json($url, $headers, $payload, $timeout = 20)
    {
        if (!function_exists('curl_init')) {
            logger('HTTP error: cURL not available', 'error', 'ai_rewriter.log');
            return [false, 'PHP cURL extension is not available', 0, null];
        }

        // Validate URL
        if (!validate_url($url)) {
            logger('HTTP error: invalid URL=' . $url, 'error', 'ai_rewriter.log');
            return [false, 'Invalid URL provided', 0, null];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Content-Type: application/json'], $headers));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $t = max(5, intval($timeout));
        curl_setopt($ch, CURLOPT_TIMEOUT, $t);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min($t, 15));
        // Follow redirects off by default for APIs

        $startTime = microtime(true);
        $resp = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            logger('HTTP error: ' . $errmsg . ', code=' . $code . ', time=' . $duration . 'ms', 'error', 'ai_rewriter.log');
            return [false, 'cURL error: ' . $errmsg, $code, null];
        }

        logger('HTTP success: code=' . $code . ', time=' . $duration . 'ms, size=' . strlen($resp) . ' bytes', 'info', 'ai_rewriter.log');
        return [true, null, $code, $resp];
    }
}

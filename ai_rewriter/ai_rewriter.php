<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Simple HTTP client using cURL
if (!function_exists('ai_rewriter_http_post_json')) {
    function ai_rewriter_http_post_json($url, $headers, $payload, $timeout = 20)
    {
        if (!function_exists('curl_init')) {
            return [false, 'PHP cURL extension is not available', 0, null];
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
        $resp = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errno) {
            return [false, 'cURL error: ' . $errmsg, $code, null];
        }
        return [true, null, $code, $resp];
    }
}

// Provider: OpenAI-compatible chat API
if (!function_exists('ai_rewriter_provider_openai')) {
    function ai_rewriter_provider_openai($model, $apiKey, $apiBase, $systemPrompt, $userPrompt, $temperature = 0.7, $timeout = 20)
    {
        $base = rtrim($apiBase ?: 'https://api.openai.com/v1', '/');
        $url = $base . '/chat/completions';
        $payload = [
            'model' => $model ?: 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => $temperature,
        ];
        $headers = [
            'Authorization: Bearer ' . $apiKey,
        ];
        list($ok, $err, $code, $resp) = ai_rewriter_http_post_json($url, $headers, $payload, $timeout);
        if (!$ok) {
            return [false, $err];
        }
        $data = json_decode($resp, true);
        if (!is_array($data)) {
            return [false, 'Invalid JSON from provider'];
        }
        if (isset($data['error'])) {
            return [false, 'API error: ' . (is_array($data['error']) ? ($data['error']['message'] ?? 'unknown') : $data['error'])];
        }
        $text = $data['choices'][0]['message']['content'] ?? '';
        if (!$text) {
            return [false, 'Empty response from provider'];
        }
        return [true, $text];
    }
}

// Provider: Anthropic Messages API
if (!function_exists('ai_rewriter_provider_anthropic')) {
    function ai_rewriter_provider_anthropic($model, $apiKey, $systemPrompt, $userPrompt, $temperature = 0.7, $timeout = 20)
    {
        $url = 'https://api.anthropic.com/v1/messages';
        $payload = [
            'model' => $model ?: 'claude-3-haiku-20240307',
            'max_tokens' => 4096,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $userPrompt],
                    ],
                ],
            ],
            'temperature' => $temperature,
        ];
        $headers = [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ];
        list($ok, $err, $code, $resp) = ai_rewriter_http_post_json($url, $headers, $payload, $timeout);
        if (!$ok) {
            return [false, $err];
        }
        $data = json_decode($resp, true);
        if (!is_array($data)) {
            return [false, 'Invalid JSON from provider'];
        }
        if (isset($data['error'])) {
            return [false, 'API error: ' . (is_array($data['error']) ? ($data['error']['message'] ?? 'unknown') : $data['error'])];
        }
        $parts = $data['content'][0]['text'] ?? '';
        if (!$parts) {
            return [false, 'Empty response from provider'];
        }
        return [true, $parts];
    }
}

// Core rewrite function
if (!function_exists('ai_rewriter_rewrite')) {
    function ai_rewriter_rewrite($text)
    {
        // Load config
        pluginsLoadConfig();
        $provider = pluginGetVariable('ai_rewriter', 'provider');
        $apiKey = trim((string)pluginGetVariable('ai_rewriter', 'api_key'));
        $apiBase = trim((string)pluginGetVariable('ai_rewriter', 'api_base'));
        $model = trim((string)pluginGetVariable('ai_rewriter', 'model'));
        $orig = intval(pluginGetVariable('ai_rewriter', 'originality')); // 0..100
        $tone = trim((string)pluginGetVariable('ai_rewriter', 'tone'));
        $temperature = floatval(pluginGetVariable('ai_rewriter', 'temperature'));
        if (!$temperature) {
            $temperature = 0.7;
        }
        $timeout = intval(pluginGetVariable('ai_rewriter', 'timeout'));
        if ($timeout <= 0) {
            $timeout = 20;
        }

        if (!$provider) {
            return [true, $text]; // disabled
        }
        if (!$apiKey && in_array($provider, ['openai', 'openai_compat', 'anthropic'])) {
            return [false, 'AI Rewriter: API ключ не задан'];
        }

        // Basic model/provider sanity checks for clearer errors
        $m = mb_strtolower($model);
        if ($provider === 'anthropic') {
            if ((strpos($m, 'gpt') !== false) || (strpos($m, 'o1') !== false) || (strpos($m, 'o3') !== false)) {
                return [false, 'AI Rewriter: выбран провайдер Anthropic, но указана модель OpenAI (' . $model . '). Задайте модель Claude, например: claude-3-5-sonnet-20240620 или claude-3-haiku-20240307.'];
            }
        }
        if (in_array($provider, ['openai', 'openai_compat'])) {
            if (strpos($m, 'claude') !== false) {
                return [false, 'AI Rewriter: выбран провайдер OpenAI, но указана модель Anthropic (' . $model . '). Укажите GPT-модель, например: gpt-4o-mini.'];
            }
        }

        // Build prompts (keep structure/markup intact)
        $sys = 'Ты профессиональный редактор и копирайтер. ' .
            'Переписывай текст сохраняя смысл, факты, структуру и разметку. ' .
            'Строго сохраняй HTML, BBCode, URLы, номера, теги и спецсимволы. ' .
            'Не добавляй фактов, не удаляй важный смысл. Язык исходника сохраняй.';

        $req = 'Перепиши следующий текст с целевой уникальностью ~' . max(0, min(100, $orig ?: 60)) . '%. ' .
            ($tone ? ('Тон: ' . $tone . '. ') : '') .
            'Сохрани разметку (HTML/BBCode), заголовки, абзацы и ссылки без изменений. ' .
            'Ничего не комментируй, верни только итоговый текст.

=== ТЕКСТ ДЛЯ РЕРАЙТА ===
' . $text;

        switch ($provider) {
            case 'openai':
            case 'openai_compat':
                return ai_rewriter_provider_openai($model, $apiKey, $apiBase, $sys, $req, $temperature, $timeout);
            case 'anthropic':
                return ai_rewriter_provider_anthropic($model, $apiKey, $sys, $req, $temperature, $timeout);
            default:
                return [false, 'AI Rewriter: неизвестный провайдер'];
        }
    }
}

class AIRewriterNewsFilter extends NewsFilter
{
    // For add form UI extension (optional)
    public function addNewsForm(&$tvars)
    {
        return 1;
    }

    // For edit form UI extension (optional)
    public function editNewsForm($newsID, $SQLnews, &$tvars)
    {
        return 1;
    }

    // Hook called BEFORE adding news (can modify $SQL)
    public function addNews(&$tvars, &$SQL)
    {
        pluginsLoadConfig();
        $enabled = intval(pluginGetVariable('ai_rewriter', 'enable_on_add'));
        $force = isset($_REQUEST['ai_rewrite_now']) && ($_REQUEST['ai_rewrite_now'] == '1');
        if (!$enabled && !$force) {
            return 1;
        }

        $content = $SQL['content'] ?? '';
        if (!mb_strlen(trim($content))) {
            return 1;
        }

        // If split editor used, keep delimiter but allow full-text rewrite
        $parts = preg_split('#(<!--more(?:=.*?)?-->)#si', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $joined = $content;
        // Strategy: rewrite entire content in one go to preserve style
        list($ok, $res) = ai_rewriter_rewrite($joined);
        if ($ok) {
            $SQL['content'] = $res;
        } else {
            // Non-blocking: keep original, show admin message
            if (function_exists('msg')) {
                msg(['type' => 'error', 'text' => $res]);
            }
        }
        return 1;
    }

    // Hook called BEFORE editing news (can modify $SQL)
    public function editNews($newsID, $SQLnews, &$SQLnew, &$tvars)
    {
        pluginsLoadConfig();
        $enabled = intval(pluginGetVariable('ai_rewriter', 'enable_on_edit'));
        $force = isset($_REQUEST['ai_rewrite_now']) && ($_REQUEST['ai_rewrite_now'] == '1');
        if (!$enabled && !$force) {
            return 1;
        }

        $content = $SQLnew['content'] ?? '';
        if (!mb_strlen(trim($content))) {
            return 1;
        }

        list($ok, $res) = ai_rewriter_rewrite($content);
        if ($ok) {
            $SQLnew['content'] = $res;
        } else {
            if (function_exists('msg')) {
                msg(['type' => 'error', 'text' => $res]);
            }
        }
        return 1;
    }
}

// Register filter
register_filter('news', 'ai_rewriter', new AIRewriterNewsFilter);

// RPC: rewrite preview (no save)
function ai_rewriter_rpc_rewrite($params = null)
{
    // Text can come as POST variable or as `$params['text']`
    $text = '';
    if (is_array($params) && isset($params['text'])) {
        $text = (string)$params['text'];
    } elseif (isset($_POST['text'])) {
        $text = (string)$_POST['text'];
    }
    if (!mb_strlen(trim($text))) {
        return ['status' => 0, 'errorCode' => 100, 'errorText' => 'Пустой текст'];
    }
    list($ok, $res) = ai_rewriter_rewrite($text);
    if ($ok) {
        return ['status' => 1, 'errorCode' => 0, 'text' => $res];
    }
    return ['status' => 0, 'errorCode' => 101, 'errorText' => $res];
}

rpcRegisterFunction('ai_rewriter.rewrite', 'ai_rewriter_rpc_rewrite', true);

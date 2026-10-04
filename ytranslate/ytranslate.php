<?php
if (!defined('NGCMS')) die('HAL');

function ytranslate_languages()
{
    return array(
        'ru' => 'Русский',
        'uk' => 'Украинский',
        'be' => 'Белорусский',
        'kk' => 'Казахский',
        'uz' => 'Узбекский',
        'ky' => 'Кыргызский',
        'tg' => 'Таджикский',
        'en' => 'Английский',
        'de' => 'Немецкий',
        'zh' => 'Китайский'
    );
}

function ytranslate_http_json($url, $payload, $headers = array())
{
    global $ytranslateLastHttpError;
    $ytranslateLastHttpError = '';
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Content-Length: ' . strlen($body);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ));
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorCode = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false || $status < 200 || $status >= 300) {
            $ytranslateLastHttpError = ytranslate_http_error($status, $response, $curlErrorCode === CURLE_OPERATION_TIMEDOUT ? 'timeout' : $curlError);
            return false;
        }
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            $ytranslateLastHttpError = 'invalid JSON response';
            return false;
        }
        return $decoded;
    }
    $context = stream_context_create(array('http' => array(
        'method' => 'POST',
        'header' => implode("\r\n", $headers),
        'content' => $body,
        'timeout' => 15,
        'ignore_errors' => true
    )));
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        $ytranslateLastHttpError = 'connection error';
        return false;
    }
    $status = 200;
    if (!empty($http_response_header) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
        $status = (int)$matches[1];
    }
    $decoded = json_decode((string)$response, true);
    if ($status < 200 || $status >= 300) {
        $ytranslateLastHttpError = ytranslate_http_error($status, $response);
        return false;
    }
    if (!is_array($decoded)) {
        $ytranslateLastHttpError = 'invalid JSON response';
        return false;
    }
    return $decoded;
}

function ytranslate_http_error($status, $response, $transportError = '')
{
    if (!$status) return $transportError === 'timeout' ? 'timeout' : 'connection error';
    if ($status === 401) return 'HTTP 401 unauthorized';
    if ($status === 403) return 'HTTP 403 forbidden';
    if ($status === 429) return 'HTTP 429 rate limited';
    if ($status >= 500) return 'HTTP ' . $status . ' provider error';
    $errorResponse = json_decode((string)$response, true);
    $message = '';
    if (is_array($errorResponse) && isset($errorResponse['error'])) {
        $message = is_array($errorResponse['error']) ? (string)($errorResponse['error']['message'] ?? '') : (string)$errorResponse['error'];
    }
    $message = trim(preg_replace('/[\r\n]+/', ' ', $message));
    return 'HTTP ' . $status . ($message ? ': ' . substr($message, 0, 180) : '');
}

function ytranslate_cache_key($provider, $source, $target, $text)
{
    return 'ytranslate:' . $provider . ':' . $source . ':' . $target . ':' . hash('sha256', $text);
}

function ytranslate_cache_get($key)
{
    if (function_exists('cache_get')) return cache_get($key, null);
    if (function_exists('cacheRetrieveFile')) {
        $data = cacheRetrieveFile(md5($key) . '.cache', 2592000, 'ytranslate');
        return $data === false ? null : @unserialize($data);
    }
    return null;
}

function ytranslate_cache_put($key, $value)
{
    if (function_exists('cache_put')) return cache_put($key, $value, 43200);
    if (function_exists('cacheStoreFile')) return cacheStoreFile(md5($key) . '.cache', serialize($value), 'ytranslate');
    return false;
}

function ytranslate_libre_request($url, $source, $target, $texts, $key)
{
    $payload = array('q' => count($texts) === 1 ? reset($texts) : array_values($texts), 'source' => $source, 'target' => $target, 'format' => 'html');
    if ($key) $payload['api_key'] = $key;
    return ytranslate_http_json(rtrim($url, '/') . '/translate', $payload);
}

function ytranslate_provider($provider, $texts, $source, $target)
{
    $config = array(
        'google_key' => (string)pluginGetVariable('ytranslate', 'google_key'),
        'microsoft_key' => (string)pluginGetVariable('ytranslate', 'microsoft_key'),
        'microsoft_region' => (string)pluginGetVariable('ytranslate', 'microsoft_region'),
        'libre_url' => trim((string)pluginGetVariable('ytranslate', 'libre_url')) ?: 'https://translate.ngcms.org',
        'libre_key' => (string)pluginGetVariable('ytranslate', 'libre_key'),
        'deepl_key' => (string)pluginGetVariable('ytranslate', 'deepl_key'),
        'deepl_free' => (int)pluginGetVariable('ytranslate', 'deepl_free'),
        'yandex_key' => (string)pluginGetVariable('ytranslate', 'yandex_key'),
        'yandex_folder' => (string)pluginGetVariable('ytranslate', 'yandex_folder')
    );
    if ($provider === 'google' && $config['google_key']) {
        $url = 'https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode($config['google_key']);
        $response = ytranslate_http_json($url, array('q' => array_values($texts), 'source' => $source, 'target' => $target, 'format' => 'text'));
        if (!empty($response['data']['translations'])) {
            $result = array();
            foreach ($response['data']['translations'] as $item) $result[] = html_entity_decode($item['translatedText'], ENT_QUOTES, 'UTF-8');
            return count($result) === count($texts) ? $result : false;
        }
    }
    if ($provider === 'microsoft' && $config['microsoft_key']) {
        $url = 'https://api.cognitive.microsofttranslator.com/translate?api-version=3.0&from=' . rawurlencode($source) . '&to=' . rawurlencode($target);
        $payload = array();
        foreach ($texts as $text) $payload[] = array('Text' => $text);
        $headers = array('Ocp-Apim-Subscription-Key: ' . $config['microsoft_key']);
        if ($config['microsoft_region']) $headers[] = 'Ocp-Apim-Subscription-Region: ' . $config['microsoft_region'];
        $response = ytranslate_http_json($url, $payload, $headers);
        if (is_array($response) && count($response) === count($texts)) {
            $result = array();
            foreach ($response as $item) $result[] = isset($item['translations'][0]['text']) ? $item['translations'][0]['text'] : '';
            return in_array('', $result, true) ? false : $result;
        }
    }
    if ($provider === 'libretranslate' && $config['libre_url']) {
        $result = array();
        $missing = array();
        foreach (array_values($texts) as $index => $text) {
            $cached = ytranslate_cache_get(ytranslate_cache_key($provider, $source, $target, $text));
            if ($cached !== null && $cached !== '') $result[$index] = $cached;
            else $missing[$index] = $text;
        }
        if (!$missing) {
            ksort($result);
            return array_values($result);
        }
        $batchResponse = ytranslate_libre_request($config['libre_url'], $source, $target, array_values($missing), $config['libre_key']);
        $batchTranslations = isset($batchResponse['translatedText']) && is_array($batchResponse['translatedText']) ? $batchResponse['translatedText'] : array();
        if (count($batchTranslations) === count($missing)) {
            $position = 0;
            foreach ($missing as $index => $text) {
                $result[$index] = (string)$batchTranslations[$position++];
                ytranslate_cache_put(ytranslate_cache_key($provider, $source, $target, $text), $result[$index]);
            }
        } else {
            foreach ($missing as $index => $text) {
                $response = ytranslate_libre_request($config['libre_url'], $source, $target, array($text), $config['libre_key']);
                if (empty($response['translatedText']) || !is_string($response['translatedText'])) return false;
                $result[$index] = $response['translatedText'];
                ytranslate_cache_put(ytranslate_cache_key($provider, $source, $target, $text), $result[$index]);
            }
        }
        ksort($result);
        return count($result) === count($texts) ? array_values($result) : false;
    }
    if ($provider === 'deepl' && $config['deepl_key']) {
        $url = ($config['deepl_free'] ? 'https://api-free.deepl.com' : 'https://api.deepl.com') . '/v2/translate';
        $payload = array('text' => array_values($texts), 'source_lang' => strtoupper($source), 'target_lang' => strtoupper($target), 'tag_handling' => 'html');
        $response = ytranslate_http_json($url, $payload, array('Authorization: DeepL-Auth-Key ' . $config['deepl_key']));
        if (!empty($response['translations'])) {
            $result = array();
            foreach ($response['translations'] as $item) $result[] = $item['text'];
            return count($result) === count($texts) ? $result : false;
        }
    }
    if ($provider === 'yandex' && $config['yandex_key'] && $config['yandex_folder']) {
        $payload = array('folderId' => $config['yandex_folder'], 'texts' => array_values($texts), 'sourceLanguageCode' => $source, 'targetLanguageCode' => $target);
        $response = ytranslate_http_json('https://translate.api.cloud.yandex.net/translate/v2/translate', $payload, array('Authorization: Api-Key ' . $config['yandex_key']));
        if (!empty($response['translations'])) {
            $result = array();
            foreach ($response['translations'] as $item) $result[] = $item['text'];
            return count($result) === count($texts) ? $result : false;
        }
    }
    return false;
}

function ytranslate_provider_order()
{
    $order = array(pluginGetVariable('ytranslate', 'primary_provider'));
    foreach (array(1, 2, 3, 4) as $index) $order[] = pluginGetVariable('ytranslate', 'fallback_' . $index);
    $result = array();
    foreach ($order as $provider) {
        if ($provider && !in_array($provider, $result, true)) $result[] = $provider;
    }
    return $result;
}

function ytranslate_endpoint()
{
    if (!isset($_REQUEST['handler']) || $_REQUEST['handler'] !== 'ytranslate_translate') return;
    @header('Content-Type: application/json; charset=utf-8');
    $source = preg_replace('/[^a-z-]/', '', (string)($_REQUEST['source'] ?? 'ru'));
    $target = preg_replace('/[^a-z-]/', '', (string)($_REQUEST['target'] ?? 'en'));
    $languages = ytranslate_languages();
    $texts = isset($_REQUEST['texts']) ? json_decode((string)$_REQUEST['texts'], true) : array();
    if (!isset($languages[$source], $languages[$target]) || $source === $target || !is_array($texts) || count($texts) < 1 || count($texts) > 80) {
        echo json_encode(array('success' => false, 'error' => 'Invalid translation request'));
        exit;
    }
    $safeTexts = array();
    foreach ($texts as $text) {
        $text = trim((string)$text);
        if ($text === '' || strlen($text) > 4000) {
            echo json_encode(array('success' => false, 'error' => 'Invalid text batch'));
            exit;
        }
        $safeTexts[] = $text;
    }
    $providerErrors = array();
    foreach (ytranslate_provider_order() as $provider) {
        $translated = ytranslate_provider($provider, $safeTexts, $source, $target);
        if (is_array($translated)) {
            echo json_encode(array('success' => true, 'provider' => $provider, 'texts' => $translated), JSON_UNESCAPED_UNICODE);
            exit;
        }
        global $ytranslateLastHttpError;
        if (!empty($ytranslateLastHttpError)) $providerErrors[] = $provider . ': ' . $ytranslateLastHttpError;
    }
    echo json_encode(array('success' => false, 'error' => count($providerErrors) ? implode('; ', $providerErrors) : 'Translation providers are unavailable'));
    exit;
}

function ytranslate_country_endpoint()
{
    if (!isset($_REQUEST['handler']) || $_REQUEST['handler'] !== 'ytranslate_country') return;
    @header('Content-Type: application/json; charset=utf-8');
    $country = strtoupper((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['HTTP_X_COUNTRY'] ?? ''));
    if (!$country && function_exists('curl_init') && !empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ch = curl_init('https://ipapi.co/' . rawurlencode($_SERVER['REMOTE_ADDR']) . '/country/');
        if ($ch) {
            curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2));
            $country = strtoupper(trim((string)curl_exec($ch)));
            curl_close($ch);
        }
    }
    $map = array('RU' => 'ru', 'UA' => 'uk', 'BY' => 'be', 'KZ' => 'kk', 'UZ' => 'uz', 'KG' => 'ky', 'TJ' => 'tg', 'US' => 'en', 'GB' => 'en', 'DE' => 'de', 'AT' => 'de', 'CH' => 'de', 'CN' => 'zh', 'TW' => 'zh');
    echo json_encode(array('success' => isset($map[$country]), 'country' => $country, 'lang' => isset($map[$country]) ? $map[$country] : ''));
    exit;
}

ytranslate_endpoint();
ytranslate_country_endpoint();

function plugin_ytranslate_show($params)
{
    global $twig;
    pluginsLoadConfig();
    $position = isset($params['position']) ? $params['position'] : 'fixed';
    $theme = isset($params['theme']) ? $params['theme'] : 'light';
    $defaultLang = (string)(isset($params['default_lang']) && $params['default_lang'] ? $params['default_lang'] : (pluginGetVariable('ytranslate', 'default_lang') ?: 'ru'));
    $configuredLanguages = ytranslate_languages();
    $langs = array();
    foreach ($configuredLanguages as $code => $name) {
        if ((int)pluginGetVariable('ytranslate', 'lang_' . $code) || $code === $defaultLang) {
            $langs[$code] = array('code' => $code, 'name' => $name);
        }
    }
    $output = array(
        'position' => $position,
        'theme' => $theme,
        'default_lang' => $defaultLang,
        'langs' => $langs,
        'auto_ip' => (int)pluginGetVariable('ytranslate', 'auto_ip'),
        'endpoint' => isset($params['endpoint']) ? $params['endpoint'] : '',
        'tpl_url' => tpl_url
    );
    $tpath = locatePluginTemplates(array('translator'), 'ytranslate', pluginGetVariable('ytranslate', 'localsource'));
    $template = $twig->loadTemplate($tpath['translator'] . 'translator.tpl');
    return $template->render(array('translator' => $output));
}

twigRegisterFunction('ytranslate', 'show', 'plugin_ytranslate_show');

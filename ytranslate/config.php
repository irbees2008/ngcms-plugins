<?php
if (!defined('NGCMS')) die('HAL');
LoadPluginLang('ytranslate', 'config', '', 'ytranslate', ':');
pluginsLoadConfig();

if (!function_exists('ytranslate_admin_api_post')) {
    function ytranslate_admin_api_post($route, $payload)
    {
        $url = 'https://translate.ngcms.org/api/ytranslate/' . ltrim($route, '/');
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return array('status' => 0, 'data' => null, 'error' => 'JSON encoding failed');
        }

        $headers = array('Content-Type: application/json', 'Accept: application/json');
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => false
            ));
            $response = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => $body,
                    'timeout' => 20,
                    'ignore_errors' => true,
                    'follow_location' => 0,
                    'max_redirects' => 0
                ),
                'ssl' => array('verify_peer' => true, 'verify_peer_name' => true)
            ));
            $response = @file_get_contents($url, false, $context);
            $status = 0;
            $error = $response === false ? 'connection error' : '';
            if (!empty($http_response_header) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
                $status = (int)$matches[1];
            }
        }

        if ($response === false) {
            return array('status' => $status, 'data' => null, 'error' => $error ?: 'connection error');
        }
        $data = json_decode((string)$response, true);
        return array('status' => $status, 'data' => is_array($data) ? $data : null, 'error' => is_array($data) ? '' : 'invalid JSON response');
    }

    function ytranslate_admin_api_error($result)
    {
        if (!empty($result['data']['error'])) {
            $error = $result['data']['error'];
            if (is_array($error)) {
                return (string)(isset($error['message']) ? $error['message'] : 'API request failed');
            }
            return (string)$error;
        }
        if (!empty($result['error'])) {
            return (string)$result['error'];
        }
        return !empty($result['status']) ? 'HTTP ' . (int)$result['status'] : 'connection error';
    }

    function ytranslate_admin_new_uuid()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}

$ytranslateRegistrationNotice = '';
$ytranslateRegistrationNoticeType = 'info';
$ytranslateRegistrationActionHandled = false;
$ytranslateRegistrationAction = '';
foreach (array('ytranslate_register' => 'register', 'ytranslate_check_key' => 'check', 'ytranslate_revoke_key' => 'revoke') as $button => $action) {
    if (isset($_POST[$button])) {
        $ytranslateRegistrationAction = $action;
        $ytranslateRegistrationActionHandled = true;
        break;
    }
}

if ($ytranslateRegistrationActionHandled) {
    if (!isset($_POST['token']) || !is_string($_POST['token']) || !hash_equals((string)genUToken('admin.extra-config'), $_POST['token'])) {
        $ytranslateRegistrationNotice = $lang['ytranslate:central_csrf_failed'];
        $ytranslateRegistrationNoticeType = 'danger';
    } elseif ($ytranslateRegistrationAction === 'register') {
        $existingKey = (string)pluginGetVariable('ytranslate', 'ytranslate_api_key');
        $existingStatus = (string)pluginGetVariable('ytranslate', 'registration_status');
        if ($existingKey !== '' && $existingStatus === 'active') {
            $ytranslateRegistrationNotice = $lang['ytranslate:central_already_registered'];
            $ytranslateRegistrationNoticeType = 'warning';
        } else {
            global $config;
            $siteUrl = trim((string)(isset($config['home_url']) ? $config['home_url'] : ''));
            if ($siteUrl === '') {
                $ytranslateRegistrationNotice = $lang['ytranslate:central_missing_home_url'];
                $ytranslateRegistrationNoticeType = 'danger';
            } else {
                $installationId = (string)pluginGetVariable('ytranslate', 'installation_id');
                if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i', $installationId)) {
                    $installationId = ytranslate_admin_new_uuid();
                }
                $engineVersion = isset($config['version']) && is_scalar($config['version']) ? substr((string)$config['version'], 0, 64) : 'unknown';
                $start = ytranslate_admin_api_post('register/start', array(
                    'installation_id' => $installationId,
                    'site_url' => $siteUrl,
                    'engine' => 'NGCMS',
                    'engine_version' => $engineVersion ?: 'unknown',
                    'plugin_version' => '1.00'
                ));
                $startData = isset($start['data']) && is_array($start['data']) ? $start['data'] : array();
                if ($start['status'] !== 200 || empty($startData['success']) || empty($startData['registration_id']) || empty($startData['challenge'])) {
                    $ytranslateRegistrationNotice = $lang['ytranslate:central_register_failed'] . ' ' . ytranslate_admin_api_error($start);
                    $ytranslateRegistrationNoticeType = 'danger';
                } else {
                    $complete = ytranslate_admin_api_post('register/complete', array(
                        'registration_id' => (string)$startData['registration_id'],
                        'installation_id' => $installationId,
                        'challenge' => (string)$startData['challenge']
                    ));
                    $completeData = isset($complete['data']) && is_array($complete['data']) ? $complete['data'] : array();
                    $newKey = isset($completeData['api_key']) ? (string)$completeData['api_key'] : '';
                    if ($complete['status'] !== 200 || empty($completeData['success']) || !preg_match('/^ytr_[A-Za-z0-9_-]{40,60}$/', $newKey) || empty($completeData['site_id'])) {
                        $ytranslateRegistrationNotice = $lang['ytranslate:central_register_failed'] . ' ' . ytranslate_admin_api_error($complete);
                        $ytranslateRegistrationNoticeType = 'danger';
                    } else {
                        pluginSetVariable('ytranslate', 'installation_id', $installationId);
                        pluginSetVariable('ytranslate', 'site_id', (string)$completeData['site_id']);
                        pluginSetVariable('ytranslate', 'ytranslate_api_key', $newKey);
                        pluginSetVariable('ytranslate', 'registration_status', 'active');
                        if (pluginsSaveConfig()) {
                            $ytranslateRegistrationNotice = $lang['ytranslate:central_register_success'];
                            $ytranslateRegistrationNoticeType = 'success';
                        } else {
                            $ytranslateRegistrationNotice = $lang['ytranslate:central_save_failed'];
                            $ytranslateRegistrationNoticeType = 'danger';
                        }
                    }
                }
            }
        }
    } else {
        $registeredKey = (string)pluginGetVariable('ytranslate', 'ytranslate_api_key');
        if ($registeredKey === '') {
            $ytranslateRegistrationNotice = $lang['ytranslate:central_no_key'];
            $ytranslateRegistrationNoticeType = 'warning';
        } else {
            $route = $ytranslateRegistrationAction === 'revoke' ? 'site/revoke' : 'site/check';
            $result = ytranslate_admin_api_post($route, array('api_key' => $registeredKey));
            $resultData = isset($result['data']) && is_array($result['data']) ? $result['data'] : array();
            if ($result['status'] === 200 && !empty($resultData['success'])) {
                if ($ytranslateRegistrationAction === 'revoke') {
                    pluginSetVariable('ytranslate', 'ytranslate_api_key', '');
                    pluginSetVariable('ytranslate', 'registration_status', 'revoked');
                    $noticeKey = 'ytranslate:central_revoke_success';
                } else {
                    pluginSetVariable('ytranslate', 'registration_status', 'active');
                    if (!empty($resultData['site_id'])) {
                        pluginSetVariable('ytranslate', 'site_id', (string)$resultData['site_id']);
                    }
                    $noticeKey = 'ytranslate:central_check_success';
                }
                if (pluginsSaveConfig()) {
                    $ytranslateRegistrationNotice = $lang[$noticeKey];
                    $ytranslateRegistrationNoticeType = 'success';
                } else {
                    $ytranslateRegistrationNotice = $lang['ytranslate:central_save_failed'];
                    $ytranslateRegistrationNoticeType = 'danger';
                }
            } else {
                $noticeKey = $ytranslateRegistrationAction === 'revoke' ? 'ytranslate:central_revoke_failed' : 'ytranslate:central_check_failed';
                $ytranslateRegistrationNotice = $lang[$noticeKey] . ' ' . ytranslate_admin_api_error($result);
                $ytranslateRegistrationNoticeType = 'danger';
            }
        }
    }
}

$providers = array(
    'yandex' => $lang['ytranslate:provider_yandex'],
    'google' => $lang['ytranslate:provider_google'],
    'microsoft' => $lang['ytranslate:provider_microsoft'],
    'libretranslate' => $lang['ytranslate:provider_libretranslate'],
    'deepl' => $lang['ytranslate:provider_deepl']
);
$languages = array(
    'ru' => $lang['ytranslate:language_russian'],
    'uk' => $lang['ytranslate:language_ukrainian'],
    'be' => $lang['ytranslate:language_belarusian'],
    'kk' => $lang['ytranslate:language_kazakh'],
    'uz' => $lang['ytranslate:language_uzbek'],
    'ky' => $lang['ytranslate:language_kyrgyz'],
    'tg' => $lang['ytranslate:language_tajik'],
    'en' => $lang['ytranslate:language_english'],
    'de' => $lang['ytranslate:language_german'],
    'zh' => $lang['ytranslate:language_chinese']
);
$cfg = array();
$centralKey = (string)pluginGetVariable('ytranslate', 'ytranslate_api_key');
$centralStatus = (string)pluginGetVariable('ytranslate', 'registration_status');
$centralControls = '';
if ($ytranslateRegistrationNotice !== '') {
    $centralControls .= '<div class="alert alert-' . htmlspecialchars($ytranslateRegistrationNoticeType, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($ytranslateRegistrationNotice, ENT_QUOTES, 'UTF-8') . '</div>';
}
$centralControls .= '<p>' . htmlspecialchars($lang['ytranslate:central_help'], ENT_QUOTES, 'UTF-8') . '</p>';
if ($centralKey !== '') {
    $statusLabel = $centralStatus === 'active' ? $lang['ytranslate:central_status_active'] : $lang['ytranslate:central_status_other'];
    $centralControls .= '<p><strong>' . htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    $centralControls .= '<label>' . htmlspecialchars($lang['ytranslate:central_key_label'], ENT_QUOTES, 'UTF-8') . '</label>';
    $centralControls .= '<input type="text" class="form-control mb-2" readonly autocomplete="off" value="' . htmlspecialchars($centralKey, ENT_QUOTES, 'UTF-8') . '" />';
    $centralControls .= '<button type="submit" class="btn btn-outline-primary mr-2" name="ytranslate_check_key" value="1">' . htmlspecialchars($lang['ytranslate:central_check_button'], ENT_QUOTES, 'UTF-8') . '</button> ';
    $centralControls .= '<button type="submit" class="btn btn-outline-danger" name="ytranslate_revoke_key" value="1" onclick="return window.confirm(this.getAttribute(\'data-confirm\'))" data-confirm="' . htmlspecialchars($lang['ytranslate:central_revoke_confirm'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($lang['ytranslate:central_revoke_button'], ENT_QUOTES, 'UTF-8') . '</button>';
} else {
    if ($centralStatus === 'revoked') {
        $centralControls .= '<p><strong>' . htmlspecialchars($lang['ytranslate:central_status_revoked'], ENT_QUOTES, 'UTF-8') . '</strong></p>';
    } else {
        $centralControls .= '<p>' . htmlspecialchars($lang['ytranslate:central_status_not_registered'], ENT_QUOTES, 'UTF-8') . '</p>';
    }
    $centralControls .= '<button type="submit" class="btn btn-primary" name="ytranslate_register" value="1">' . htmlspecialchars($lang['ytranslate:central_register_button'], ENT_QUOTES, 'UTF-8') . '</button>';
}
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:central_group'], 'entries' => array(array(
    'name' => 'central_registration_control',
    'title' => $lang['ytranslate:central_group'],
    'descr' => '',
    'type' => 'manual',
    'input' => $centralControls,
    'error' => '',
    'html_flags' => '',
    'value' => ''
)));
$providerFields = array(
    array('name' => 'default_lang', 'title' => $lang['ytranslate:default_lang_title'], 'descr' => $lang['ytranslate:default_lang_descr'], 'type' => 'select', 'values' => $languages, 'value' => pluginGetVariable('ytranslate', 'default_lang') ?: 'ru'),
    array('name' => 'primary_provider', 'title' => $lang['ytranslate:primary_provider'], 'descr' => $lang['ytranslate:primary_provider_descr'], 'type' => 'select', 'values' => $providers, 'value' => pluginGetVariable('ytranslate', 'primary_provider') ?: 'google')
);
foreach (array(1, 2, 3, 4) as $index) {
    $providerFields[] = array('name' => 'fallback_' . $index, 'title' => $lang['ytranslate:fallback'] . ' ' . $index, 'descr' => $lang['ytranslate:fallback_descr'], 'type' => 'select', 'values' => array('' => $lang['ytranslate:provider_disabled']) + $providers, 'value' => pluginGetVariable('ytranslate', 'fallback_' . $index));
}
$providerFields[] = array('name' => 'google_key', 'title' => $lang['ytranslate:google_key'], 'descr' => $lang['ytranslate:google_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'google_key'));
$providerFields[] = array('name' => 'microsoft_key', 'title' => $lang['ytranslate:microsoft_key'], 'descr' => $lang['ytranslate:microsoft_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'microsoft_key'));
$providerFields[] = array('name' => 'microsoft_region', 'title' => $lang['ytranslate:microsoft_region'], 'descr' => $lang['ytranslate:microsoft_region_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'microsoft_region'));
$providerFields[] = array('name' => 'libre_url', 'title' => $lang['ytranslate:libre_url'], 'descr' => $lang['ytranslate:libre_url_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'libre_url') ?: 'https://translate.ngcms.org');
$providerFields[] = array('name' => 'libre_key', 'title' => $lang['ytranslate:libre_key'], 'descr' => $lang['ytranslate:libre_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'libre_key'));
$providerFields[] = array('name' => 'deepl_key', 'title' => $lang['ytranslate:deepl_key'], 'descr' => $lang['ytranslate:deepl_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'deepl_key'));
$providerFields[] = array('name' => 'deepl_free', 'title' => $lang['ytranslate:deepl_free'], 'descr' => $lang['ytranslate:deepl_free_descr'], 'type' => 'checkbox', 'value' => pluginGetVariable('ytranslate', 'deepl_free'));
$providerFields[] = array('name' => 'yandex_key', 'title' => $lang['ytranslate:yandex_key'], 'descr' => $lang['ytranslate:yandex_key_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'yandex_key'));
$providerFields[] = array('name' => 'yandex_folder', 'title' => $lang['ytranslate:yandex_folder'], 'descr' => $lang['ytranslate:yandex_folder_descr'], 'type' => 'input', 'value' => pluginGetVariable('ytranslate', 'yandex_folder'));
$providerFields[] = array('name' => 'auto_ip', 'title' => $lang['ytranslate:auto_ip'], 'descr' => $lang['ytranslate:auto_ip_descr'], 'type' => 'checkbox', 'value' => pluginGetVariable('ytranslate', 'auto_ip'));
$languageFields = array();
foreach ($languages as $code => $name) {
    $languageFields[] = array('name' => 'lang_' . $code, 'title' => $name, 'type' => 'checkbox', 'value' => ($code === 'ru' || pluginGetVariable('ytranslate', 'lang_' . $code)) ? '1' : '0');
}
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:group_provider'], 'entries' => $providerFields);
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:group_languages'], 'entries' => $languageFields);
$cfg[] = array('mode' => 'group', 'title' => $lang['ytranslate:group_template'], 'entries' => array(array('name' => 'localsource', 'title' => $lang['ytranslate:localsource_title'], 'descr' => $lang['ytranslate:localsource_descr'], 'type' => 'select', 'values' => array('0' => $lang['ytranslate:localsource_0'], '1' => $lang['ytranslate:localsource_1']), 'value' => pluginGetVariable('ytranslate', 'localsource'))));

if ($ytranslateRegistrationActionHandled) {
    generate_config_page('ytranslate', $cfg);
} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('ytranslate', $cfg);
    print_commit_complete('ytranslate');
} else {
    generate_config_page('ytranslate', $cfg);
}

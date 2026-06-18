<?php
// Protect against hack attempts
if (!defined('NGCMS')) {
    die('HAL');
}

require_once __DIR__ . '/functions.php';

/**
 * ВАЖНО:
 * Этот файл подключается движком глобально, поэтому:
 * - НЕЛЬЗЯ делать echo/exit без жесткой проверки что это /plugin/avtek_callback/
 * - Админку трогать нельзя
 */

// ----------------------------
// TEMPLATE TAG / helper
// ----------------------------
function plugin_avtek_callback($params = [])
{
    $formId = 0;
    $mode = 'embed';

    if (is_array($params)) {
        if (isset($params['id'])) {
            $formId = (int)$params['id'];
        } elseif (isset($params['form_id'])) {
            $formId = (int)$params['form_id'];
        }
        if (isset($params['mode'])) {
            $mode = (string)$params['mode'];
        }
    }

    if ($formId <= 0) {
        return '<!-- avtek_callback: missing form id -->';
    }

    return avtek_cb_render_form($formId, $mode);
}

// На всякий случай для TWIG/callPlugin
function avtek_callback_show($params = [])
{
    return plugin_avtek_callback($params);
}

// Обёртка для TWIG callPlugin('avtek_callback.show', {...})
function plugin_avtek_callback_show($params = [])
{
    return plugin_avtek_callback($params);
}

// ----------------------------
// ROUTER: Регистрация страницы /plugin/avtek_callback/
// ----------------------------
function plugin_avtek_callback_router()
{
    // Обработчик для пути /plugin/avtek_callback/
    $sub = (string)avtek_cb_req('sub', '');

    if ($sub === 'submit') {
        avtek_cb_handle_submit();
        return;
    }

    if ($sub === 'render') {
        $formId = (int)avtek_cb_req('form_id', 0);
        $mode   = (string)avtek_cb_req('mode', 'embed');

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }
        echo avtek_cb_render_form($formId, $mode);
        return;
    }

    if ($sub === 'export') {
        $formId = (int)avtek_cb_req('form_id', 0);
        if (function_exists('avtek_cb_export_xls')) {
            avtek_cb_export_xls($formId);
        } else {
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
            }
            echo 'Export module not installed';
        }
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
    }
    echo 'AVTEK Callback';
}

// Зарегистрировать страницу, если доступна функция API движка
if (function_exists('register_plugin_page')) {
    register_plugin_page('avtek_callback', '', 'plugin_avtek_callback_router', 0);
}

// ----------------------------
// NEWS FILTER: Вставка формы в полную новость без Twig-вызова
// ----------------------------
if (class_exists('NewsFilter')) {
    class AvtekCallbackNewsFilter extends NewsFilter
    {
        public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
        {
            // Встраиваем только в полную новость
            $style = $mode['style'] ?? '';
            if ($style !== 'full') {
                return 1;
            }

            // ID формы можно задать в настройках плагина (key: auto_form_id), иначе 1
            $fid = (int)avtek_cb_get_setting('auto_form_id', '1');
            if ($fid <= 0) {
                $fid = 1;
            }

            $modeVal = (string)avtek_cb_get_setting('auto_mode', 'embed');
            if ($modeVal !== 'modal') {
                $modeVal = 'embed';
            }
            $html = avtek_cb_render_form($fid, $modeVal);
            if ($html && (mb_strpos($html, 'avtek_callback: form not found') === false)) {
                // Доступно в шаблоне как {{ plugin_avtek_cb|raw }}
                $tvars['vars']['plugin_avtek_cb'] = $html;
            } else {
                $tvars['vars']['plugin_avtek_cb'] = '';
            }
            return 1;
        }
    }
    register_filter('news', 'avtek_callback', new AvtekCallbackNewsFilter);
}

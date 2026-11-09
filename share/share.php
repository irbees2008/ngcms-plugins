<?php

// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

class ShareNewsFilter extends NewsFilter
{

    function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        global $twig, $config, $template;

        LoadPluginLang('share', 'site', '', '', ':');

        // localsource: 1 = use plugin templates, 0 = use theme templates
        $localsource = intval(pluginGetVariable('share', 'localsource'));
        $skin = pluginGetVariable('share', 'skin');
        if (empty($skin)) {
            $skin = 'basic';
        }

        $tpath = locatePluginTemplates(array('share'), 'share', $localsource, $skin);

        // Получаем правильный URL для CSS
        if ($localsource) {
            $cssUrl = $config['home_url'] . '/engine/plugins/share/tpl/site/share.css';
        } else {
            $currentTheme = $template['theme'] ?? $config['theme'] ?? 'default';
            $cssUrl = $config['home_url'] . '/templates/' . $currentTheme . '/plugins/share/share.css';
        }

        // Получаем настройки социальных сетей
        $networks = array();
        $networkList = array('facebook', 'x', 'vk', 'ok', 'telegram', 'whatsapp', 'viber', 'mailru', 'linkedin', 'pinterest', 'reddit', 'instagram', 'threads', 'print');
        foreach ($networkList as $network) {
            $enabled = pluginGetVariable('share', 'network_' . $network);
            $networks[$network] = ($enabled === null || intval($enabled) === 1);
        }

        $tVars = array(
            'home' => $config['home_url'],
            'css_url' => $cssUrl,
            'networks' => $networks,
            'news' => array(
                'url' => $tvars['vars']['news']['url']['full'],
                'title' => $tvars['vars']['news']['title'],
            ),
        );

        $templateName = 'share';

        $tvars['vars']['plugin_share'] = $twig->render($tpath[$templateName] . $templateName . '.tpl', $tVars);

        return 1;
    }
}

register_filter('news', 'share', new ShareNewsFilter);

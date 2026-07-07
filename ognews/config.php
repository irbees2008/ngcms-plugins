<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
// Load localization
LoadPluginLang('ognews', 'main', '', '', ':');
// Debug output for language keys if ?debug=1
if (isset($_REQUEST['debug'])) {
    echo '<pre style="background:#fff;border:1px solid #ccc;padding:8px;">';
    echo "LANG[ognews:*] keys:\n";
    foreach ($lang as $k => $v) {
        if (strpos($k, 'ognews:') === 0) {
            echo $k . ' = ' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . "\n";
        }
    }
    echo "\nCount: ";
    echo count(array_filter(array_keys($lang), function ($k) {
        return strpos($k, 'ognews:') === 0;
    }));
    echo '</pre>';
}
// Defaults (оптимальные значения для соцсетей и SEO)
$defTitleLen       = pluginGetVariable('ognews', 'title_length')       !== null ? pluginGetVariable('ognews', 'title_length') : 60;  // Google обрезает после ~60 символов
$defDescrLen       = pluginGetVariable('ognews', 'description_length') !== null ? pluginGetVariable('ognews', 'description_length') : 125; // Соцсети показывают ~125 символов
$defTwitterTitleLen = pluginGetVariable('ognews', 'twitter_title_length') !== null ? pluginGetVariable('ognews', 'twitter_title_length') : 60;
$defTwitterDescrLen = pluginGetVariable('ognews', 'twitter_description_length') !== null ? pluginGetVariable('ognews', 'twitter_description_length') : 125;
$defKeywordsLen    = pluginGetVariable('ognews', 'keywords_length')    !== null ? pluginGetVariable('ognews', 'keywords_length') : 0; // 0 = не ограничивать
$defSource         = pluginGetVariable('ognews', 'description_source') !== null ? pluginGetVariable('ognews', 'description_source') : 'description';
$defTwitterSite    = pluginGetVariable('ognews', 'twitter_site') !== null ? pluginGetVariable('ognews', 'twitter_site') : '';
$cfg = array();
$grp = array();
$grp[] = array(
    'name'   => 'title_length',
    'title'  => $lang['ognews:title_length_title'],
    'descr'  => $lang['ognews:title_length_descr'],
    'type'   => 'input',
    'value'  => intval($defTitleLen),
);
$grp[] = array(
    'name'   => 'description_length',
    'title'  => $lang['ognews:description_length_title'],
    'descr'  => $lang['ognews:description_length_descr'],
    'type'   => 'input',
    'value'  => intval($defDescrLen),
);
$grp[] = array(
    'name'   => 'description_source',
    'title'  => $lang['ognews:description_source_title'],
    'descr'  => $lang['ognews:description_source_descr'],
    'type'   => 'select',
    'values' => array(
        'description' => $lang['ognews:source_description'],
        'content'     => $lang['ognews:source_content']
    ),
    'value'  => $defSource,
);
$grp[] = array(
    'name'   => 'twitter_title_length',
    'title'  => $lang['ognews:twitter_title_length_title'],
    'descr'  => $lang['ognews:twitter_title_length_descr'],
    'type'   => 'input',
    'value'  => intval($defTwitterTitleLen),
);
$grp[] = array(
    'name'   => 'twitter_description_length',
    'title'  => $lang['ognews:twitter_description_length_title'],
    'descr'  => $lang['ognews:twitter_description_length_descr'],
    'type'   => 'input',
    'value'  => intval($defTwitterDescrLen),
);
$grp[] = array(
    'name'   => 'keywords_length',
    'title'  => $lang['ognews:keywords_length_title'],
    'descr'  => $lang['ognews:keywords_length_descr'],
    'type'   => 'input',
    'value'  => intval($defKeywordsLen),
);
$grp[] = array(
    'name'   => 'twitter_site',
    'title'  => 'Twitter username',
    'descr'  => 'Ваш Twitter username (например @sitename). Оставьте пустым, если нет',
    'type'   => 'input',
    'value'  => $defTwitterSite,
);
$cfg[] = array(
    'mode'    => 'group',
    'title'   => '<b>' . $lang['ognews:group_title'] . '</b>',
    'entries' => $grp,
);
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes('ognews', $cfg);
    print_commit_complete('ognews');
} else {
    generate_config_page('ognews', $cfg);
}

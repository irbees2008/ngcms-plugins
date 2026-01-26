<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Основная страница плагина
function plugin_helloworld_screen()
{
    global $mysql, $tpl, $template;
    LoadPluginLang('helloworld', 'site');

    // Инкремент счётчика (id = 1)
    $mysql->query("insert into " . prefix . "_helloworld_hits (id, cnt) values (1,1) on duplicate key update cnt = cnt + 1");
    $rec = $mysql->record("select cnt from " . prefix . "_helloworld_hits where id = 1");

    // Локализация и шаблон
    $tpath = locatePluginTemplates(array('helloworld'), 'helloworld', 1);
    $tvars = array();
    $tvars['vars']['title'] = $lang = $GLOBALS['lang']['helloworld_page_title'] ?? 'HelloWorld';
    $tvars['vars']['body'] = $GLOBALS['lang']['helloworld_page_body'] ?? 'Demo plugin body';
    $tvars['vars']['hits'] = intval($rec['cnt']);

    $tpl->template('helloworld', $tpath['helloworld']);
    $tpl->vars('helloworld', $tvars);
    $template['vars']['mainblock'] = $tpl->show('helloworld');
}

// Фильтр новостей: добавляем суффикс к заголовку
class HelloWorldNewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        LoadPluginLang('helloworld', 'site');
        if (extra_get_param('helloworld', 'add_suffix')) {
            if (isset($tvars['vars']['title'])) {
                $suffix = $GLOBALS['lang']['helloworld_suffix'] ?? 'Hello';
                $tvars['vars']['title'] .= ' ' . $suffix;
            }
        }
    }
}

register_filter('news', 'helloworld', new HelloWorldNewsFilter);
register_plugin_page('helloworld', '', 'plugin_helloworld_screen', 0);

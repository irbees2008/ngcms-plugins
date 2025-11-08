<?php
// Protect
if (!defined('NGCMS')) {
    exit('HAL');
}

// RPC: вернуть список активных шаблонов
rpcRegisterFunction('plugin.news_templates.list', 'plugin_news_templates_rpc_list');

function plugin_news_templates_rpc_list($params)
{
    global $mysql, $userROW;

    // Разрешим только авторизованным пользователям (админка)
    if (!is_array($userROW)) {
        return ['status' => 0, 'errorCode' => 3, 'errorText' => 'Access denied'];
    }

    $rows = $mysql->select('SELECT id, ord, title, content FROM ' . prefix . '_news_templates WHERE active = 1 ORDER BY ord ASC, id ASC');
    $list = [];
    foreach ($rows as $r) {
        $list[] = [
            'id'      => intval($r['id']),
            'ord'     => intval($r['ord']),
            'title'   => $r['title'],
            'content' => $r['content'],
        ];
    }

    return ['status' => 1, 'errorCode' => 0, 'data' => $list];
}

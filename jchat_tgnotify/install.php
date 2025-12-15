<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

function plugin_jchat_tgnotify_install($action)
{
    switch ($action) {
        case 'confirm':
            generate_install_page('jchat_tgnotify', 'Плагин добавит Telegram-уведомления к jChat. Будут установлены параметры: токен бота, ID чата, фильтры уведомлений.');
            break;

        case 'autoapply':
        case 'apply':
            // Устанавливаем параметры плагина
            extra_set_param('jchat_tgnotify', 'enabled', '0');
            extra_set_param('jchat_tgnotify', 'bot_token', '');
            extra_set_param('jchat_tgnotify', 'chat_id', '');
            extra_set_param('jchat_tgnotify', 'guests_only', '0');
            extra_set_param('jchat_tgnotify', 'first_only', '1');
            extra_set_param('jchat_tgnotify', 'flood_seconds', '20');

            extra_commit_changes();
            plugin_mark_installed('jchat_tgnotify');
            break;
    }
    return true;
}

<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

function plugin_jchat_tgnotify_install($action)
{
    switch ($action) {
        case 'confirm':
            generate_install_page('jchat_tgnotify', 'Плагин добавит универсальные Telegram-уведомления для всех событий NGCMS: jChat, обратная связь, личные сообщения, жалобы, комментарии.');
            break;

        case 'autoapply':
        case 'apply':
            // Устанавливаем параметры плагина
            extra_set_param('jchat_tgnotify', 'enabled', '0');
            extra_set_param('jchat_tgnotify', 'bot_token', '');
            extra_set_param('jchat_tgnotify', 'chat_id', '');

            // Фильтры для jChat
            extra_set_param('jchat_tgnotify', 'guests_only', '0');
            extra_set_param('jchat_tgnotify', 'first_only', '1');
            extra_set_param('jchat_tgnotify', 'flood_seconds', '20');

            // Типы уведомлений (по умолчанию только jChat включен)
            extra_set_param('jchat_tgnotify', 'notify_jchat', '1');
            extra_set_param('jchat_tgnotify', 'notify_feedback', '0');
            extra_set_param('jchat_tgnotify', 'notify_pm', '0');
            extra_set_param('jchat_tgnotify', 'notify_complain', '0');
            extra_set_param('jchat_tgnotify', 'notify_comment', '0');

            extra_commit_changes();
            plugin_mark_installed('jchat_tgnotify');

            $url = home . "/engine/admin.php?mod=extras";
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: {$url}");
            exit();
    }
    return true;
}

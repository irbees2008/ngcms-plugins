<?php

/**
 * Installation script for Mailing plugin
 *
 * @version 2.0.0
 * @requires PHP 8.1+
 */

if (!defined('NGCMS')) die('HAL');

pluginsLoadConfig();

/**
 * Функция установки плагина
 */
function plugin_mailing_install($action)
{
    global $mysql;
    // Описание таблиц для создания
    $db_update = array(
        // Таблица кампаний
        array(
            'table'  => 'mailing_campaigns',
            'action' => 'cmodify',
            'key'    => 'primary key(id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'title', 'type' => 'varchar(255)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'subject', 'type' => 'varchar(255)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'body_html', 'type' => 'text', 'params' => 'not null'),
                array('action' => 'cmodify', 'name' => 'body_text', 'type' => 'text', 'params' => 'not null'),
                array('action' => 'cmodify', 'name' => 'status', 'type' => "varchar(20)", 'params' => "not null default 'scheduled'"),
                array('action' => 'cmodify', 'name' => 'send_at', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'created_at', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'created_by', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'segment_json', 'type' => 'text', 'params' => 'not null'),
                array('action' => 'cmodify', 'name' => 'auto_news_id', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'sent_count', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'delivered_count', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'failed_count', 'type' => 'int', 'params' => "not null default '0'"),
            ),
        ),
        // Таблица очереди
        array(
            'table'  => 'mailing_queue',
            'action' => 'cmodify',
            'key'    => 'primary key(id), key(campaign_id), key(status)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'campaign_id', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'email', 'type' => 'varchar(255)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'name', 'type' => 'varchar(255)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'status', 'type' => "varchar(20)", 'params' => "not null default 'pending'"),
                array('action' => 'cmodify', 'name' => 'try_count', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'last_try_at', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'err_msg', 'type' => 'varchar(255)', 'params' => "not null default ''"),
            ),
        ),
        // Таблица вложений
        array(
            'table'  => 'mailing_attachments',
            'action' => 'cmodify',
            'key'    => 'primary key(id), key(campaign_id)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'campaign_id', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'file_path', 'type' => 'varchar(500)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'file_name', 'type' => 'varchar(255)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'mime', 'type' => 'varchar(100)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'size', 'type' => 'int', 'params' => "not null default '0'"),
            ),
        ),
        // Таблица отписок
        array(
            'table'  => 'mailing_unsub',
            'action' => 'cmodify',
            'key'    => 'primary key(id), unique key(token), key(user_id), key(email)',
            'fields' => array(
                array('action' => 'cmodify', 'name' => 'id', 'type' => 'int', 'params' => 'not null auto_increment'),
                array('action' => 'cmodify', 'name' => 'user_id', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'email', 'type' => 'varchar(255)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'token', 'type' => 'varchar(64)', 'params' => "not null default ''"),
                array('action' => 'cmodify', 'name' => 'created_at', 'type' => 'int', 'params' => "not null default '0'"),
                array('action' => 'cmodify', 'name' => 'unsub_at', 'type' => 'int', 'params' => "not null default '0'"),
            ),
        ),
    );

    switch ($action) {
        case 'confirm':
            // Страница подтверждения установки
            $info = '<b>Плагин Email-рассылки v2.0</b><br/><br/>';
            $info .= 'Будут созданы следующие таблицы:<br/>';
            $info .= '• <b>mailing_campaigns</b> - кампании рассылок<br/>';
            $info .= '• <b>mailing_queue</b> - очередь отправки писем<br/>';
            $info .= '• <b>mailing_attachments</b> - вложения к письмам<br/>';
            $info .= '• <b>mailing_unsub</b> - отписки пользователей<br/><br/>';
            $info .= 'Возможности:<br/>';
            $info .= '✓ Массовые рассылки с сегментацией<br/>';
            $info .= '✓ Вложения файлов<br/>';
            $info .= '✓ Отложенная отправка<br/>';
            $info .= '✓ Авто-рассылка новостей<br/>';
            $info .= '✓ Интеграция с Twig<br/>';
            generate_install_page('mailing', $info);
            break;

        case 'autoapply':
        case 'apply':
            // Установка таблиц
            if (fixdb_plugin_install('mailing', $db_update, 'install', ($action == 'autoapply'))) {
                // Мягкая авто-миграция: добавляем недостающие колонки статистики
                try {
                    $tbl = prefix . '_mailing_campaigns';
                    $rows = $mysql->select('DESCRIBE ' . $tbl);
                    $have = array();
                    foreach ((array)$rows as $r) {
                        if (!empty($r['Field'])) {
                            $have[$r['Field']] = true;
                        }
                    }

                    if (empty($have['sent_count'])) {
                        $mysql->query('ALTER TABLE ' . $tbl . ' ADD COLUMN sent_count INT NOT NULL DEFAULT 0');
                    }
                    if (empty($have['delivered_count'])) {
                        $mysql->query('ALTER TABLE ' . $tbl . ' ADD COLUMN delivered_count INT NOT NULL DEFAULT 0');
                    }
                    if (empty($have['failed_count'])) {
                        $mysql->query('ALTER TABLE ' . $tbl . ' ADD COLUMN failed_count INT NOT NULL DEFAULT 0');
                    }
                } catch (Throwable $e) {
                    // Игнорируем ошибки прав/DDL, т.к. плагин работает и без этих колонок
                }
                // Параметры по умолчанию
                $params = array(
                    'cron_secret'          => bin2hex(random_bytes(16)),
                    'send_batch'           => '50',
                    'max_tries'            => '3',
                    'period'               => '5m',
                    'from_email'           => 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
                    'from_name'            => 'Site',
                    'reply_to'             => '',
                    'smtp_enable'          => '0',
                    'smtp_host'            => '',
                    'smtp_port'            => '587',
                    'smtp_auth'            => '1',
                    'smtp_user'            => '',
                    'smtp_pass'            => '',
                    'smtp_secure'          => 'tls',
                    'allow_iframe'         => '0',
                    'enable_tick'          => '0',
                    'tick_chance'          => '10',
                    'auto_news_enable'     => '0',
                    'auto_news_category'   => '0',
                    'auto_news_groups'     => '[]',
                    'auto_news_scan_limit' => '5',
                    'auto_news_last_id'    => '0',
                );

                foreach ($params as $key => $value) {
                    extra_set_param('mailing', $key, $value);
                }

                extra_commit_changes();
                plugin_mark_installed('mailing');
            } else {
                return false;
            }
            break;
    }

    return true;
}

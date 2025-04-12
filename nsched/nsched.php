<?php
if (!defined('NGCMS')) die('HAL');

class NSchedNewsFilter extends NewsFilter
{
    public const EMPTY_DATETIME = '0';
    public const FORMAT_DATETIME = 'd.m.Y H:i';
    public const PERMISSION_IDENTIFIER = [
        'plugin' => '#admin',
        'item' => 'news',
    ];

    private $timeZone;

    public function __construct()
    {
        global $config;
        $this->timeZone = new DateTimeZone($config['timezone'] ?? 'Asia/Almaty');
        date_default_timezone_set($config['timezone'] ?? 'Asia/Almaty');
    }

    public function addNewsForm(&$tvars)
    {
        global $twig;

        $permissions = $this->permissions('personal', ['publish', 'unpublish']);
        $tvars['plugin']['nsched'] = '';

        if ($permissions['personal.publish'] || $permissions['personal.unpublish']) {
            $tvars['plugin']['nsched'] = $twig->render('plugins/nsched/tpl/add_news.tpl', [
                'format_datetime' => self::FORMAT_DATETIME,
                'flags' => [
                    'can_publish' => (bool)$permissions['personal.publish'],
                    'can_unpublish' => (bool)$permissions['personal.unpublish'],
                ],
            ]);
        }
        return 1;
    }

    public function addNews(&$tvars, &$SQL)
    {
        $permissions = $this->permissions('personal', ['publish', 'unpublish']);

        // Обработка даты активации
        if ($permissions['personal.publish'] && !empty($_REQUEST['nsched_activate'])) {
            $publishDate = DateTime::createFromFormat(
                self::FORMAT_DATETIME,
                $_REQUEST['nsched_activate'],
                $this->timeZone
            );

            if ($publishDate) {
                $SQL['nsched_activate'] = $publishDate->getTimestamp();
                error_log("NSched: Saving activate timestamp " . $SQL['nsched_activate'] . " for " . $_REQUEST['nsched_activate']);

                if (pluginGetVariable('nsched', 'sync_dates')) {
                    $SQL['postdate'] = $SQL['nsched_activate'];
                    $SQL['editdate'] = $SQL['nsched_activate'];
                }
            } else {
                $SQL['nsched_activate'] = self::EMPTY_DATETIME;
                error_log("NSched: Invalid activate date format: " . $_REQUEST['nsched_activate']);
            }
        }

        // Добавляем обработку даты деактивации
        if ($permissions['personal.unpublish'] && !empty($_REQUEST['nsched_deactivate'])) {
            $unpublishDate = DateTime::createFromFormat(
                self::FORMAT_DATETIME,
                $_REQUEST['nsched_deactivate'],
                $this->timeZone
            );

            if ($unpublishDate) {
                $SQL['nsched_deactivate'] = $unpublishDate->getTimestamp();
                error_log("NSched: Saving deactivate timestamp " . $SQL['nsched_deactivate'] . " for " . $_REQUEST['nsched_deactivate']);
            } else {
                $SQL['nsched_deactivate'] = self::EMPTY_DATETIME;
                error_log("NSched: Invalid deactivate date format: " . $_REQUEST['nsched_deactivate']);
            }
        }

        return 1;
    }

    public function editNews($newsID, $SQLold, &$SQLnew, &$tvars)
    {
        global $userROW;

        $permissionGroup = ($SQLold['author_id'] == ($userROW['id'] ?? 0)) ? 'personal' : 'other';
        $permissions = $this->permissions($permissionGroup, ['publish', 'unpublish']);

        // Обработка даты активации
        if ($permissions[$permissionGroup . '.publish'] && !empty($_REQUEST['nsched_activate'])) {
            $publishDate = DateTime::createFromFormat(
                self::FORMAT_DATETIME,
                $_REQUEST['nsched_activate'],
                $this->timeZone
            );

            if ($publishDate) {
                $SQLnew['nsched_activate'] = $publishDate->getTimestamp();
                error_log("NSched: Updating activate timestamp " . $SQLnew['nsched_activate'] . " for " . $_REQUEST['nsched_activate']);

                if (pluginGetVariable('nsched', 'sync_dates')) {
                    $SQLnew['postdate'] = $SQLnew['nsched_activate'];
                    $SQLnew['editdate'] = $SQLnew['nsched_activate'];
                }
            } else {
                $SQLnew['nsched_activate'] = self::EMPTY_DATETIME;
                error_log("NSched: Invalid activate date format during edit: " . $_REQUEST['nsched_activate']);
            }
        }

        // Добавляем обработку даты деактивации
        if ($permissions[$permissionGroup . '.unpublish'] && !empty($_REQUEST['nsched_deactivate'])) {
            $unpublishDate = DateTime::createFromFormat(
                self::FORMAT_DATETIME,
                $_REQUEST['nsched_deactivate'],
                $this->timeZone
            );

            if ($unpublishDate) {
                $SQLnew['nsched_deactivate'] = $unpublishDate->getTimestamp();
                error_log("NSched: Updating deactivate timestamp " . $SQLnew['nsched_deactivate'] . " for " . $_REQUEST['nsched_deactivate']);
            } else {
                $SQLnew['nsched_deactivate'] = self::EMPTY_DATETIME;
                error_log("NSched: Invalid deactivate date format during edit: " . $_REQUEST['nsched_deactivate']);
            }
        }

        return 1;
    }

    public function editNewsForm($newsID, $SQLold, &$tvars)
    {
        global $twig, $userROW;

        $permissionGroup = ($SQLold['author_id'] == ($userROW['id'] ?? 0)) ? 'personal' : 'other';
        $permissions = $this->permissions($permissionGroup, ['publish', 'unpublish']);

        $nactivate = '';
        if (!empty($SQLold['nsched_activate'])) {
            $nactivate = (new DateTime())
                ->setTimestamp((int)$SQLold['nsched_activate'])
                ->setTimezone($this->timeZone)
                ->format(self::FORMAT_DATETIME);
        }

        // Добавляем обработку даты деактивации для формы
        $ndeactivate = '';
        if (!empty($SQLold['nsched_deactivate'])) {
            $ndeactivate = (new DateTime())
                ->setTimestamp((int)$SQLold['nsched_deactivate'])
                ->setTimezone($this->timeZone)
                ->format(self::FORMAT_DATETIME);
        }

        $tvars['plugin']['nsched'] = $twig->render('plugins/nsched/tpl/edit_news.tpl', [
            'nsched_activate' => $nactivate,
            'nsched_deactivate' => $ndeactivate,
            'format_datetime' => self::FORMAT_DATETIME,
            'flags' => [
                'can_publish' => (bool)$permissions[$permissionGroup . '.publish'],
                'can_unpublish' => (bool)$permissions[$permissionGroup . '.unpublish'],
            ],
        ]);
        return 1;
    }

    private function permissions(string $group, array $actions): array
    {
        return checkPermission(
            self::PERMISSION_IDENTIFIER,
            null,
            array_map(fn($action) => "$group.$action", $actions)
        );
    }
}

register_filter('news', 'nsched', new NSchedNewsFilter());

function plugin_nsched_cron()
{
    global $mysql, $config;
    error_log("NSched CRON executed at " . date('Y-m-d H:i:s'));
    // 1. Установка часового пояса для MySQL
    $timezone = $config['timezone'] ?? 'Asia/Almaty';
    $dt = new DateTime('now', new DateTimeZone($timezone));
    $mysql->query("SET time_zone = '" . $dt->format('P') . "'");

    // 2. Логирование старта
    error_log("=== NSched CRON START [" . date('Y-m-d H:i:s') . "] ===");
    error_log("Server Time: " . date('Y-m-d H:i:s'));
    error_log("MySQL Time: " . $mysql->result("SELECT NOW()"));
    error_log("MySQL Timestamp: " . $mysql->result("SELECT UNIX_TIMESTAMP()"));

    // 3. Публикация новостей (nsched_activate)
    $activateQuery = "SELECT id, nsched_activate, FROM_UNIXTIME(nsched_activate) as activate_time 
                     FROM " . prefix . "_news 
                     WHERE nsched_activate > 0 
                     AND nsched_activate <= UNIX_TIMESTAMP()
                     AND approve = 0";

    error_log("Activation Query: " . $activateQuery);
    $newsToActivate = $mysql->select($activateQuery);

    if ($newsToActivate && count($newsToActivate)) {
        error_log("Found " . count($newsToActivate) . " news to activate");
        $mysql->query("START TRANSACTION");
        try {
            foreach ($newsToActivate as $news) {
                error_log("Activating news ID: " . $news['id'] . " (scheduled: " . $news['activate_time'] . ")");
                $mysql->query("UPDATE " . prefix . "_news SET 
                    approve = 1, 
                    nsched_activate = 0 
                    WHERE id = " . $news['id']);
                error_log("News ID " . $news['id'] . " activated successfully");
            }
            $mysql->query("COMMIT");
        } catch (Exception $e) {
            $mysql->query("ROLLBACK");
            error_log("Activation ERROR: " . $e->getMessage());
        }
    } else {
        error_log("No news to activate");
    }

    // 4. Снятие с публикации (nsched_deactivate)
    $deactivateQuery = "SELECT id, nsched_deactivate, FROM_UNIXTIME(nsched_deactivate) as deactivate_time 
                       FROM " . prefix . "_news 
                       WHERE nsched_deactivate > 0 
                       AND nsched_deactivate <= UNIX_TIMESTAMP()
                       AND approve = 1";

    error_log("Deactivation Query: " . $deactivateQuery);
    $newsToDeactivate = $mysql->select($deactivateQuery);

    if ($newsToDeactivate && count($newsToDeactivate)) {
        error_log("Found " . count($newsToDeactivate) . " news to deactivate");
        $mysql->query("START TRANSACTION");
        try {
            foreach ($newsToDeactivate as $news) {
                error_log("Deactivating news ID: " . $news['id'] . " (scheduled: " . $news['deactivate_time'] . ")");
                $mysql->query("UPDATE " . prefix . "_news SET 
                    approve = 0, 
                    nsched_deactivate = 0 
                    WHERE id = " . $news['id']);
                error_log("News ID " . $news['id'] . " deactivated successfully");
            }
            $mysql->query("COMMIT");
        } catch (Exception $e) {
            $mysql->query("ROLLBACK");
            error_log("Deactivation ERROR: " . $e->getMessage());
        }
    } else {
        error_log("No news to deactivate");
    }

    // 5. Финализация
    error_log("=== NSched CRON FINISHED ===");
    error_log("Memory usage: " . memory_get_usage() . " bytes");
    error_log("Debug: Current timestamp " . time() . " = " . date('Y-m-d H:i:s'));
    error_log("Debug: News to activate timestamp: " . ($newsToActivate[0]['nsched_activate'] ?? 'null'));
}
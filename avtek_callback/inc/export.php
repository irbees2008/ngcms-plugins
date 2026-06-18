<?php
if (!defined('NGCMS')) { die('HAL'); }

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

function avtek_cb_export_xls(int $formId = 0): void {
    $tLeads = avtek_cb_db_table('avtek_callback_leads');
    $tForms = avtek_cb_db_table('avtek_callback_forms');

    $where = '';
    if ($formId > 0) {
        $where = ' WHERE l.form_id = ' . (int)$formId;
    }

    $rows = avtek_cb_db_getall(
        'SELECT l.*, f.title AS form_title FROM `' . $tLeads . '` l LEFT JOIN `' . $tForms . '` f ON f.id=l.form_id' . $where . ' ORDER BY l.id DESC'
    );

    if (!headers_sent()) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="avtek_callback_leads_' . date('Ymd_His') . '.xls"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }

    echo "<html><head><meta charset='utf-8'></head><body>";
    echo "<table border='1' cellpadding='4' cellspacing='0'>";
    echo "<tr>";
    echo "<th>ID</th><th>Дата</th><th>Форма</th><th>IP</th><th>Страница</th><th>Данные</th>";
    echo "</tr>";

    foreach ($rows as $r) {
        $data = avtek_cb_json_decode($r['data'] ?? '');
        $pairs = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) { $v = implode(', ', $v); }
            $pairs[] = avtek_cb_h((string)$k) . ': ' . avtek_cb_h((string)$v);
        }
        echo "<tr>";
        echo "<td>" . (int)$r['id'] . "</td>";
        echo "<td>" . avtek_cb_h((string)$r['created_at']) . "</td>";
        echo "<td>" . avtek_cb_h((string)($r['form_title'] ?? '')) . "</td>";
        echo "<td>" . avtek_cb_h((string)($r['ip'] ?? '')) . "</td>";
        echo "<td>" . avtek_cb_h((string)($r['page_url'] ?? '')) . "</td>";
        echo "<td>" . implode('<br>', $pairs) . "</td>";
        echo "</tr>";
    }

    echo "</table></body></html>";
    exit;
}

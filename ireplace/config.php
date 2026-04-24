<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Ensure ng-helpers is loaded
if (!function_exists('Plugins\\logger')) {
	$ngHelpersPath = __DIR__ . '/../ng-helpers/ng-helpers.php';
	if (file_exists($ngHelpersPath)) {
		require_once $ngHelpersPath;
	}
}

// Modernized with ng-helpers v0.2.2 (2026)
// - Added array_get for safe REQUEST access
// - Added sanitize for input cleaning
// - Added logger for operations tracking
use function Plugins\{array_get, sanitize, logger};

pluginsLoadConfig();
LoadPluginLang('ireplace', 'main', '', '', ':');
$cfg = array();
array_push($cfg, array('descr' => $lang['ireplace:descr']));
array_push($cfg, array('name' => 'area', 'title' => $lang['ireplace:area'], 'descr' => $lang['ireplace:area.descr'], 'type' => 'select', 'values' => array('' => $lang['ireplace:area.choose'], 'news' => $lang['ireplace:area.news'], 'static' => $lang['ireplace:area.static'], 'comments' => $lang['ireplace:area.comments'])));
array_push($cfg, array('name' => 'src', 'title' => $lang['ireplace:source'], 'type' => 'input', 'html_flags' => 'size=40', 'value' => ''));
array_push($cfg, array('name' => 'dest', 'title' => $lang['ireplace:destination'], 'type' => 'input', 'html_flags' => 'size=40', 'value' => ''));
if (array_get($_REQUEST, 'action', '') == 'commit') {
	// Perform a replace
	$query = '';
	do {
		// Check src/dest values
		$src = sanitize(array_get($_REQUEST, 'src', ''), 'string');
		$dest = sanitize(array_get($_REQUEST, 'dest', ''), 'string');
		if (!strlen($src) || !strlen($dest)) {
			// No src/dest text
			msg(array("type" => "error", "text" => $lang['ireplace:error.notext']));
			break;
		}
		// Check area
		$area = sanitize(array_get($_REQUEST, 'area', ''), 'string');
		switch ($area) {
			case 'news':
				$query = "update " . prefix . "_news set content = replace(content, " . db_squote($src) . ", " . db_squote($dest) . ")";
				logger('ireplace', 'Replace in NEWS: "' . $src . '" -> "' . $dest . '"');
				break;
			case 'static':
				$query = "update " . prefix . "_static set content = replace(content, " . db_squote($src) . ", " . db_squote($dest) . ")";
				logger('ireplace', 'Replace in STATIC: "' . $src . '" -> "' . $dest . '"');
				break;
			case 'comments':
				$query = "update " . prefix . "_comments set text = replace(text, " . db_squote($src) . ", " . db_squote($dest) . ")";
				logger('ireplace', 'Replace in COMMENTS: "' . $src . '" -> "' . $dest . '"');
				break;
		}
		if (!$query) {
			// No area selected
			msg(array("type" => "error", "text" => $lang['ireplace:error.noarea']));
			break;
		}
	} while (0);
	// Check if we should make replacement
	if ($query) {
		// Yeah !!
		$result = $mysql->select($query);
		$count = $mysql->affected_rows($mysql->connect);
		if ($count) {
			logger('ireplace', 'Success: ' . $count . ' rows affected in area: ' . $area);
			msg(array("type" => "info", "info" => str_replace('{count}', $count, $lang['ireplace:info.done'])));
		} else {
			logger('ireplace', 'No changes: 0 rows affected in area: ' . $area);
			msg(array("type" => "info", "info" => $lang['ireplace:info.nochange']));
		}
	}
	print_commit_complete($plugin);
} else {
	generate_config_page($plugin, $cfg);
}

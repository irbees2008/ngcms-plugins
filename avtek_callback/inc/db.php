<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

/**
 * AVTEK Callback - DB helper (PHP 8+ / PDOStatement safe)
 * - Works with NGCMS NGLegacyDB + NGPDO (PDOStatement)
 * - Works with mysqli_result (older builds)
 * - Robust table prefix detection (ng_, etc.)
 */

function avtek_cb_db_q(string $sql) {
	global $mysql;

	if (!is_object($mysql) || !method_exists($mysql, 'query')) {
		return false;
	}

	try {
		return $mysql->query($sql);
	} catch (Throwable $e) {
		return false;
	}
}

function avtek_cb_db_fetch($result): ?array {
	if (!$result) {
		return null;
	}

	// PDO
	if ($result instanceof \PDOStatement) {
		$row = $result->fetch(\PDO::FETCH_ASSOC);
		return is_array($row) ? $row : null;
	}

	// mysqli
	if ($result instanceof \mysqli_result) {
		$row = $result->fetch_assoc();
		return is_array($row) ? $row : null;
	}

	// Fallback for custom objects
	if (is_object($result)) {
		if (method_exists($result, 'fetch')) {
			$row = $result->fetch();
			return is_array($row) ? $row : null;
		}
		if (method_exists($result, 'fetchAssoc')) {
			$row = $result->fetchAssoc();
			return is_array($row) ? $row : null;
		}
	}

	return null;
}

function avtek_cb_db_getrow(string $sql): ?array {
	global $mysql;

	// In your build record() expects SQL STRING (not PDOStatement)
	if (is_object($mysql) && method_exists($mysql, 'record')) {
		$row = $mysql->record($sql);
		return is_array($row) ? $row : null;
	}

	$res = avtek_cb_db_q($sql);
	return avtek_cb_db_fetch($res);
}

function avtek_cb_db_getall(string $sql): array {
	global $mysql;

	// Many NGCMS builds have select() returning array
	if (is_object($mysql) && method_exists($mysql, 'select')) {
		$rows = $mysql->select($sql);
		return is_array($rows) ? $rows : [];
	}

	$res = avtek_cb_db_q($sql);
	$rows = [];

	while (true) {
		$row = avtek_cb_db_fetch($res);
		if (!$row) break;
		$rows[] = $row;
	}

	return $rows;
}

function avtek_cb_db_lastid(): int {
	global $mysql;

	if (is_object($mysql)) {
		// Common method names in forks
		foreach (['insert_id','last_id','lastid','lastId','insertId'] as $m) {
			if (method_exists($mysql, $m)) {
				$v = $mysql->{$m}();
				return (int)$v;
			}
		}
		// Common property name
		if (property_exists($mysql, 'insert_id')) {
			return (int)$mysql->insert_id;
		}
	}

	return 0;
}

/**
 * Best-effort escape (returns escaped string WITHOUT quotes)
 */
function avtek_cb_db_escape(string $value): string {
	global $mysql;

	// NGCMS helper exists in most builds (returns quoted string usually)
	if (function_exists('db_squote')) {
		// db_squote returns QUOTED string, so we can't use it as escape-only.
		// We'll fallback to addslashes to avoid double quotes misuse in our helpers.
		// But we'll prefer mysql escape if exists.
	}

	if (is_object($mysql) && method_exists($mysql, 'escape')) {
		return (string)$mysql->escape($value);
	}

	return addslashes($value);
}

/**
 * Quote string for SQL (WITH surrounding quotes)
 * Uses db_squote if available (best for NGCMS), otherwise our escape.
 */
function avtek_cb_db_qs(string $value): string {
	if (function_exists('db_squote')) {
		return db_squote($value);
	}
	return "'" . avtek_cb_db_escape($value) . "'";
}

function avtek_cb_db_int($v): int {
	return (int)$v;
}

/**
 * Detect table prefix and build full table name.
 * Supports:
 * - $config['prefix'] = 'ng'  -> ng_<name>
 * - $config['prefix'] = 'ng_' -> ng_<name>
 * - $config['dbprefix']       -> same logic
 * - $mysql->prefix / $mysql->dbprefix / $mysql->dbPrefix
 * If nothing found: tries SHOW TABLES LIKE '%<name>' and picks match.
 */
function avtek_cb_db_table(string $name): string {
	global $mysql, $config;

	// 1) Direct config prefix (most important for your case)
	$prefix = '';

	if (is_array($config)) {
		if (!empty($config['prefix'])) {
			$prefix = (string)$config['prefix'];
		} elseif (!empty($config['dbprefix'])) {
			$prefix = (string)$config['dbprefix'];
		}
	}

	// 2) Try mysql object properties / methods
	if ($prefix === '' && is_object($mysql)) {
		foreach (['prefix','dbprefix','dbPrefix','tablePrefix'] as $p) {
			if (property_exists($mysql, $p) && !empty($mysql->{$p})) {
				$prefix = (string)$mysql->{$p};
				break;
			}
		}
		if ($prefix === '' && method_exists($mysql, 'getPrefix')) {
			$tmp = (string)$mysql->getPrefix();
			if ($tmp !== '') $prefix = $tmp;
		}
	}

	// 3) Build candidate with prefix
	if ($prefix !== '') {
		// normalize: ensure exactly one underscore between prefix and name
		$prefix = rtrim($prefix);
		if (substr($prefix, -1) === '_') {
			return $prefix . $name;
		}
		return $prefix . '_' . $name;
	}

	// 4) No prefix known -> detect real table name
	// First: if table exists without prefix - use it
	$chk = avtek_cb_db_q("SHOW TABLES LIKE " . avtek_cb_db_qs($name));
	if ($chk) {
		$r = avtek_cb_db_fetch($chk);
		if (is_array($r) && count($r) > 0) {
			return $name;
		}
	}

	// Second: try to find any table ending with _<name> or just <name>
	$like = '%' . $name;
	$res = avtek_cb_db_q("SHOW TABLES LIKE " . avtek_cb_db_qs($like));
	if ($res) {
		$found = [];
		while ($row = avtek_cb_db_fetch($res)) {
			// SHOW TABLES returns first column with table name
			$tbl = (string)array_values($row)[0];
			if ($tbl !== '') $found[] = $tbl;
		}
		// If exactly one match - use it
		if (count($found) === 1) {
			return $found[0];
		}
		// Prefer ng_<name> if exists
		foreach ($found as $tbl) {
			if (preg_match('~(^|_)ng_' . preg_quote($name, '~') . '$~', $tbl) || $tbl === ('ng_' . $name)) {
				return $tbl;
			}
		}
		// Otherwise pick first match
		if (count($found) > 0) {
			return $found[0];
		}
	}

	// Last resort
	return $name;
}

<?php
if (!defined('NGCMS')) { die('HAL'); }

function avtek_cb_now(): string {
	return date('Y-m-d H:i:s');
}

function avtek_cb_getip(): string {
	$ip = '';
	$keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'];
	foreach ($keys as $k) {
		if (!empty($_SERVER[$k])) {
			$ip = (string)$_SERVER[$k];
			break;
		}
	}
	if (strpos($ip, ',') !== false) {
		$ip = trim(explode(',', $ip)[0]);
	}
	return $ip ?: '0.0.0.0';
}

function avtek_cb_req(string $key, $default = '') {
	if (isset($_POST[$key])) { return $_POST[$key]; }
	if (isset($_GET[$key])) { return $_GET[$key]; }
	return $default;
}

function avtek_cb_bool($v): int {
	return (!empty($v) && $v !== '0' && $v !== 0) ? 1 : 0;
}

function avtek_cb_json_encode($data): string {
	$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	return ($json === false) ? '{}' : $json;
}

function avtek_cb_json_decode(?string $json): array {
	if (!$json) { return []; }
	$data = json_decode($json, true);
	return is_array($data) ? $data : [];
}

function avtek_cb_h(string $s): string {
	return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function avtek_cb_slug(string $s): string {
	$s = trim(mb_strtolower($s, 'UTF-8'));
	$s = preg_replace('~[^a-z0-9_\-]+~u', '-', $s);
	$s = trim($s ?? '', '-');
	return $s ?: 'form';
}

function avtek_cb_rand_token(int $len = 24): string {
	try {
		return bin2hex(random_bytes((int)ceil($len/2)));
	} catch (Throwable $e) {
		return substr(md5(uniqid('', true)), 0, $len);
	}
}

function avtek_cb_is_ajax(): bool {
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
		return true;
	}
	if (!empty($_SERVER['HTTP_ACCEPT']) && strpos((string)$_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
		return true;
	}
	if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') {
		return true;
	}
	if (isset($_REQUEST['format']) && $_REQUEST['format'] === 'json') {
		return true;
	}
	return false;
}

/**
 * Ответ:
 * - AJAX -> JSON (как и должно быть)
 * - НЕ AJAX -> нормальная HTML-страница с текстом (чтобы пользователь не видел JSON)
 */
function avtek_cb_response_json(array $payload, int $httpCode = 200): void {
	$isAjax = avtek_cb_is_ajax();

	if (!headers_sent()) {
		http_response_code($httpCode);
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	}

	if ($isAjax) {
		if (!headers_sent()) {
			header('Content-Type: application/json; charset=utf-8');
		}
		echo avtek_cb_json_encode($payload);
		exit;
	}

	// НЕ AJAX: показываем понятное сообщение
	$msg = '';
	if (!empty($payload['message'])) {
		$msg = (string)$payload['message'];
	} elseif (!empty($payload['error'])) {
		$msg = (string)$payload['error'];
	} else {
		$msg = 'Спасибо! Заявка отправлена.';
	}

	if (!headers_sent()) {
		header('Content-Type: text/html; charset=utf-8');
	}

	echo '<!doctype html><html><head><meta charset="utf-8"><title>AVTEK Callback</title></head><body>';
	echo '<div style="font-family:Arial,sans-serif;font-size:16px;padding:20px;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</div>';
	echo '</body></html>';
	exit;
}

function avtek_cb_trim(?string $s, int $max = 2000): string {
	$s = trim((string)$s);
	if (mb_strlen($s, 'UTF-8') > $max) {
		$s = mb_substr($s, 0, $max, 'UTF-8');
	}
	return $s;
}

function avtek_cb_parse_list(string $emails): array {
	$parts = preg_split('~[\s,;]+~', $emails, -1, PREG_SPLIT_NO_EMPTY);
	$out = [];
	foreach ($parts as $p) {
		$p = trim($p);
		if ($p === '') { continue; }
		$out[] = $p;
	}
	return array_values(array_unique($out));
}

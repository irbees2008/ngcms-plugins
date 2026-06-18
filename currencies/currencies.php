<?php

/**
 * Currencies plugin for NGCMS
 * Multi-currency support with CBR/NBU auto-update rates.
 * Works with basket and payments plugins.
 */
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize};

// Register frontend currency switch page
register_plugin_page('currencies', 'switch', 'currencies_switch');

// ─── Session / cookie currency ─────────────────────────────────────────────
define('CURRENCIES_COOKIE', 'ng_currency');
define('CURRENCIES_CACHE_FILE', __DIR__ . '/data/rates.json');

function currencies_get_active(): string
{
    if (!empty($_COOKIE[CURRENCIES_COOKIE])) {
        $code = strtoupper(preg_replace('/[^A-Z]/', '', $_COOKIE[CURRENCIES_COOKIE]));
        if ($code) return $code;
    }
    return strtoupper(pluginGetVariable('currencies', 'base') ?: 'RUB');
}

function currencies_switch()
{
    global $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

    $code = strtoupper(preg_replace('/[^A-Z]/', '', $_REQUEST['code'] ?? ''));
    if ($code) {
        setcookie(CURRENCIES_COOKIE, $code, time() + 86400 * 365, '/');
    }

    $SUPRESS_TEMPLATE_SHOW  = 1;
    $SUPRESS_MAINBLOCK_SHOW = 1;

    $back = $_SERVER['HTTP_REFERER'] ?? home;
    redirect($back);
}

// ─── Get all currencies from DB ────────────────────────────────────────────
function currencies_get_all(): array
{
    global $mysql;
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = $mysql->select("SELECT * FROM " . prefix . "_currencies ORDER BY is_base DESC, code ASC", 1) ?: [];
    return $cache;
}

// ─── Get rate for a currency code ─────────────────────────────────────────
function currencies_get_rate(string $code): float
{
    foreach (currencies_get_all() as $row) {
        if ($row['code'] === $code) {
            return (float)$row['rate'];
        }
    }
    return 1.0;
}

// ─── Convert price between currencies ─────────────────────────────────────
function currencies_convert(float $price, string $from, string $to): float
{
    if ($from === $to) return $price;
    $rateFrom = currencies_get_rate($from);
    $rateTo   = currencies_get_rate($to);
    if ($rateFrom <= 0 || $rateTo <= 0) return $price;
    return round($price / $rateFrom * $rateTo, 2);
}

// ─── Format price in active currency ──────────────────────────────────────
function currencies_format(float $price, string $baseCurrency = ''): string
{
    if (!$baseCurrency) {
        $baseCurrency = strtoupper(pluginGetVariable('currencies', 'base') ?: 'RUB');
    }
    $active = currencies_get_active();
    $converted = currencies_convert($price, $baseCurrency, $active);

    // Find symbol
    $symbol = $active;
    foreach (currencies_get_all() as $row) {
        if ($row['code'] === $active) {
            $symbol = $row['symbol'] ?: $active;
            break;
        }
    }

    return number_format($converted, 2, '.', ' ') . ' ' . $symbol;
}

// ─── Update rates from CBR (Central Bank of Russia) ───────────────────────
function currencies_update_rates_cbr(): bool
{
    global $mysql;

    $xml = @file_get_contents('https://www.cbr.ru/scripts/XML_daily.asp');
    if (!$xml) {
        logger('CBR rate update failed: cannot fetch URL', 'error', 'currencies.log');
        return false;
    }

    $doc = @simplexml_load_string($xml);
    if (!$doc) {
        logger('CBR rate update failed: invalid XML', 'error', 'currencies.log');
        return false;
    }

    $updated = 0;
    foreach ($doc->Valute as $valute) {
        $code     = (string)$valute->CharCode;
        $nominal  = (int)(string)$valute->Nominal;
        $valueStr = str_replace(',', '.', (string)$valute->Value);
        $rate     = (float)$valueStr / max(1, $nominal);

        $exists = $mysql->record("SELECT id FROM " . prefix . "_currencies WHERE code = " . db_squote($code) . " LIMIT 1");
        if ($exists) {
            $mysql->query("UPDATE " . prefix . "_currencies SET rate=" . db_squote($rate) . ", updated_at=" . db_squote(time()) . " WHERE code=" . db_squote($code));
            $updated++;
        }
    }

    logger("CBR rates updated: $updated currencies", 'info', 'currencies.log');
    return true;
}

// ─── Inject active currency into all page template vars ───────────────────
function currencies_template_inject()
{
    global $template;
    $active = currencies_get_active();
    $all    = currencies_get_all();
    $template['vars']['currency_active'] = $active;
    $template['vars']['currencies_list'] = $all;
    $template['vars']['currency_switch_link'] = generatePluginLink('currencies', 'switch');
}

add_act('index', 'currencies_template_inject');

// ─── Cron: auto-update rates daily ────────────────────────────────────────
function plugin_currencies_cron($isSysCron, $handler)
{
    $source = pluginGetVariable('currencies', 'rate_source') ?: 'cbr';
    if ($source === 'cbr') {
        currencies_update_rates_cbr();
    }
}

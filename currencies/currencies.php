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

function currencies_parse_rate_factors(string $source, string $response): ?array
{
    $factors = [];
    if ($source === 'cbr') {
        $doc = @simplexml_load_string($response);
        if (!$doc) {
            logger('CBR rate update failed: invalid XML', 'error', 'currencies.log');
            return null;
        }
        $factors['RUB'] = 1.0;
        foreach ($doc->Valute as $valute) {
            $code = strtoupper((string)$valute->CharCode);
            $nominal = (int)(string)$valute->Nominal;
            $value = (float)str_replace(',', '.', (string)$valute->Value);
            $rublesPerUnit = $value / max(1, $nominal);
            if ($code !== '' && $rublesPerUnit > 0) {
                $factors[$code] = 1 / $rublesPerUnit;
            }
        }
    } elseif ($source === 'nbu') {
        $rates = json_decode($response, true);
        if (!is_array($rates) || !$rates) {
            logger('NBU rate update failed: invalid JSON response', 'error', 'currencies.log');
            return null;
        }
        $factors['UAH'] = 1.0;
        foreach ($rates as $rate) {
            $code = strtoupper((string)($rate['cc'] ?? ''));
            $hryvniasPerUnit = (float)($rate['rate'] ?? 0);
            if ($code !== '' && $hryvniasPerUnit > 0) {
                $factors[$code] = 1 / $hryvniasPerUnit;
            }
        }
    } elseif ($source === 'nbk') {
        $doc = @simplexml_load_string($response);
        if (!$doc) {
            logger('NBK rate update failed: invalid XML', 'error', 'currencies.log');
            return null;
        }
        $factors['KZT'] = 1.0;
        foreach ($doc->channel->item as $item) {
            $code = strtoupper((string)$item->title);
            $nominal = max(1, (int)(string)$item->quant);
            $tengePerUnit = (float)str_replace(',', '.', (string)$item->description) / $nominal;
            if ($code !== '' && $tengePerUnit > 0) {
                $factors[$code] = 1 / $tengePerUnit;
            }
        }
    } elseif ($source === 'ecb') {
        $doc = @simplexml_load_string($response);
        if (!$doc) {
            logger('ECB rate update failed: invalid XML', 'error', 'currencies.log');
            return null;
        }
        $factors['EUR'] = 1.0;
        foreach ($doc->Cube->Cube->Cube as $rate) {
            $code = strtoupper((string)$rate['currency']);
            $unitsPerEuro = (float)(string)$rate['rate'];
            if ($code !== '' && $unitsPerEuro > 0) {
                $factors[$code] = $unitsPerEuro;
            }
        }
    } else {
        return null;
    }

    return count($factors) > 1 ? $factors : null;
}

function currencies_fetch_rate_factors(string $source): ?array
{
    $sources = [
        'cbr' => 'https://www.cbr.ru/scripts/XML_daily.asp',
        'nbu' => 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json',
        'nbk' => 'https://nationalbank.kz/rss/rates_all.xml',
        'ecb' => 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml',
    ];
    if (!isset($sources[$source])) {
        return null;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'NGCMS currencies plugin',
        ],
    ]);
    $response = @file_get_contents($sources[$source], false, $context);
    if ($response === false) {
        logger(strtoupper($source) . ' rate update failed: cannot fetch rate feed', 'error', 'currencies.log');
        return null;
    }

    return currencies_parse_rate_factors($source, $response);
}

function currencies_normalize_rate_factors(array $factors, string $baseCurrency): ?array
{
    $baseCurrency = strtoupper($baseCurrency);
    $baseFactor = (float)($factors[$baseCurrency] ?? 0);
    if ($baseFactor <= 0) {
        return null;
    }

    $rates = [];
    foreach ($factors as $code => $factor) {
        $factor = (float)$factor;
        if ($factor > 0) {
            $rates[strtoupper($code)] = $factor / $baseFactor;
        }
    }

    return $rates;
}

function currencies_update_rates_from_source(string $source): bool
{
    global $mysql;
    $baseCurrency = strtoupper(pluginGetVariable('currencies', 'base') ?: 'RUB');
    $factors = currencies_fetch_rate_factors($source);
    if (!is_array($factors)) {
        return false;
    }
    $rates = currencies_normalize_rate_factors($factors, $baseCurrency);
    if (!is_array($rates)) {
        logger(strtoupper($source) . ' rate update failed: base currency is not provided by the source', 'error', 'currencies.log');
        return false;
    }

    $currencies = $mysql->select("SELECT code FROM " . prefix . "_currencies", 1);
    if (!$currencies) {
        logger(strtoupper($source) . ' rate update failed: no currencies found', 'error', 'currencies.log');
        return false;
    }

    $updated = 0;
    $failed = false;
    foreach ($currencies as $currency) {
        $code = strtoupper((string)$currency['code']);
        if (!isset($rates[$code])) {
            continue;
        }
        $result = $mysql->query(
            "UPDATE " . prefix . "_currencies SET rate=" . db_squote($rates[$code]) .
            ", updated_at=" . db_squote(time()) . " WHERE code=" . db_squote($code)
        );
        if ($result === false) {
            $failed = true;
        } else {
            $updated++;
        }
    }

    logger(strtoupper($source) . " rates updated: $updated currencies", 'info', 'currencies.log');
    return $updated > 0 && !$failed;
}

function currencies_update_rates(): bool
{
    $source = pluginGetVariable('currencies', 'rate_source') ?: 'cbr';
    if ($source === 'manual') {
        return false;
    }

    return currencies_update_rates_from_source($source);
}

function currencies_update_rates_cbr(): bool
{
    return currencies_update_rates_from_source('cbr');
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
    if ($source !== 'manual') {
        currencies_update_rates();
    }
}

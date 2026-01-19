<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
$enableGarland = intval(extra_get_param('holiday_decor', 'enable_garland'));
$garlandMode = extra_get_param('holiday_decor', 'garland_mode') ?: 'sprite';
$garlandStyle = extra_get_param('holiday_decor', 'garland_style') ?: '1';
$garlandPosition = extra_get_param('holiday_decor', 'garland_position') ?: 'absolute';
$enableSnow = intval(extra_get_param('holiday_decor', 'enable_snow'));
$snowCount = intval(extra_get_param('holiday_decor', 'snow_count'));
$snowSpeed = floatval(extra_get_param('holiday_decor', 'snow_speed'));
$showSwitch = intval(extra_get_param('holiday_decor', 'show_switch'));
// Extra effects
$enableFireworks = intval(extra_get_param('holiday_decor', 'enable_fireworks'));
$enableCursorSnow = intval(extra_get_param('holiday_decor', 'enable_cursor_snow'));
$enableJqSnowfall = intval(extra_get_param('holiday_decor', 'enable_jq_snowfall'));
$enableBigSnow = intval(extra_get_param('holiday_decor', 'enable_big_snow'));
$enableFallingStars = intval(extra_get_param('holiday_decor', 'enable_falling_stars'));
$enableCountdownSanta = intval(extra_get_param('holiday_decor', 'enable_countdown_santa'));
$enableCountdownBanner = intval(extra_get_param('holiday_decor', 'enable_countdown_banner'));
$countdownShowSeconds = intval(extra_get_param('holiday_decor', 'countdown_show_seconds'));
if (!$snowCount) {
    $snowCount = 150;
}
if (!$snowSpeed) {
    $snowSpeed = 1.5;
}
$tpath = locatePluginTemplates([':holiday_decor.css', ':holiday_decor.js'], 'holiday_decor', 1);
register_stylesheet($tpath['url::holiday_decor.css'] . '/holiday_decor.css?v=3');
register_htmlvar('js', $tpath['url::holiday_decor.js'] . '/holiday_decor.js?v=3');

// If simple sprite garland is enabled, inject inline CSS tailored to selected style and position
if ($enableGarland && ($garlandMode == 'sprite')) {
    $img = home . '/engine/plugins/holiday_decor/tpl/' . (($garlandStyle == '2') ? 'gir2.png' : 'gir1.png');
    $css = '<style>#gir{position: ' . ($garlandPosition == 'fixed' ? 'fixed' : 'absolute') . '; top:0; left:0; background-image:url(\'' . $img . '\'); height:62px; width:100%; overflow:hidden; z-index:10000; pointer-events:none} .gir_1{background-position:0 0} .gir_2{background-position:0 -62px} .gir_3{background-position:0 -124px}</style>';
    register_htmlvar('plain', $css);
}

// Lightrope mode: register CSS and override position if needed
if ($enableGarland && ($garlandMode == 'lightrope')) {
    register_stylesheet(home . '/engine/plugins/holiday_decor/tpl/lightrope.css');
    if ($garlandPosition == 'fixed') {
        register_htmlvar('plain', '<style>.lightrope{position:fixed}</style>');
    }
}

// jQuery Snowfall external scripts and init
if ($enableJqSnowfall) {
    register_htmlvar('plain', '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" defer></script>');
    register_htmlvar('plain', '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.4.1/jquery-migrate.min.js" defer></script>');
    register_htmlvar('plain', '<script src="https://cdnjs.cloudflare.com/ajax/libs/JQuery-Snowfall/1.7.4/snowfall.jquery.min.js" defer></script>');
    $flakeCount = max(0, intval($snowCount));
    $init = "document.addEventListener('DOMContentLoaded',function(){ if (window.jQuery && jQuery.fn.snowfall){ jQuery(document).snowfall({flakeCount:" . $flakeCount . ",flakeColor:'#ffffff',flakeIndex:999999,minSize:1,maxSize:4,minSpeed:2,maxSpeed:8,round:true,shadow:false}); }});";
    register_htmlvar('plain', '<script>' . $init . '</script>');
}
$cfg = [
    'garland' => !!$enableGarland,
    'mode' => $garlandMode,
    'garlandStyle' => $garlandStyle,
    'garlandPosition' => $garlandPosition,
    'snow' => !!$enableSnow,
    'snowCount' => max(0, $snowCount),
    'snowSpeed' => $snowSpeed,
    'showSwitch' => !!$showSwitch,
    // extras
    'fireworks' => !!$enableFireworks,
    'cursorSnow' => !!$enableCursorSnow,
    'jqSnowfall' => ['enabled' => !!$enableJqSnowfall, 'flakeCount' => max(0, intval($snowCount))],
    'bigSnow' => !!$enableBigSnow,
    'fallingStars' => !!$enableFallingStars,
    'countdownSanta' => !!$enableCountdownSanta,
    'countdownBanner' => !!$enableCountdownBanner,
    'countdownShowSeconds' => !!$countdownShowSeconds
];
register_htmlvar('plain', '<script>window.holidayDecorConfig=' . json_encode($cfg, JSON_UNESCAPED_UNICODE) . ';</script>');

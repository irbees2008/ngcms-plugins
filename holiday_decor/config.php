<?php
if (!defined('NGCMS')) die('HAL');
pluginsLoadConfig();
$cfg = [];
$group = [];
array_push($group, [
    'name' => 'enable_garland',
    'title' => 'Включить гирлянду',
    'descr' => 'Показывать мигающую гирлянду сверху сайта',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_garland')
]);
array_push($group, [
    'name' => 'garland_mode',
    'title' => 'Режим гирлянды',
    'descr' => 'Простая (спрайт) или современная гирлянда',
    'type' => 'select',
    'values' => ['sprite' => 'Простая (спрайт)', 'modern' => 'Современная', 'lightrope' => 'CSS Lightrope'],
    'value' => extra_get_param($plugin, 'garland_mode') ?: 'sprite'
]);
array_push($group, [
    'name' => 'garland_style',
    'title' => 'Стиль гирлянды',
    'descr' => 'Выберите картинку: 1 или 2 (gir1.png / gir2.png)',
    'type' => 'select',
    'values' => ['1' => 'Гирлянда 1', '2' => 'Гирлянда 2'],
    'value' => extra_get_param($plugin, 'garland_style') ?: '1'
]);
array_push($group, [
    'name' => 'garland_position',
    'title' => 'Позиция гирлянды',
    'descr' => 'fixed (закрепить сверху) или absolute',
    'type' => 'select',
    'values' => ['absolute' => 'Absolute', 'fixed' => 'Fixed'],
    'value' => extra_get_param($plugin, 'garland_position') ?: 'absolute'
]);
array_push($group, [
    'name' => 'enable_snow',
    'title' => 'Включить снег',
    'descr' => 'Показывать падающий снег на страницах',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_snow')
]);
// Доп. эффекты
array_push($group, [
    'name' => 'enable_fireworks',
    'title' => 'Фейерверк',
    'descr' => 'Праздничный салют поверх страницы',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_fireworks') ?: '0'
]);
array_push($group, [
    'name' => 'enable_cursor_snow',
    'title' => 'Курсор-снег',
    'descr' => 'Шлейф снежинок за курсором',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_cursor_snow') ?: '0'
]);
array_push($group, [
    'name' => 'enable_jq_snowfall',
    'title' => 'jQuery Snowfall',
    'descr' => 'Подключить snowfall.jquery и снежить через jQuery',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_jq_snowfall') ?: '0'
]);
array_push($group, [
    'name' => 'enable_big_snow',
    'title' => 'Крупные снежинки',
    'descr' => 'Падающие большие символы ❄',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_big_snow') ?: '0'
]);
array_push($group, [
    'name' => 'enable_falling_stars',
    'title' => 'Падающие звёзды (верх)',
    'descr' => 'Анимированные звёзды поверх шапки',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_falling_stars') ?: '0'
]);
array_push($group, [
    'name' => 'enable_countdown_santa',
    'title' => 'Счётчик (Санта 180x191)',
    'descr' => 'Небольшой виджет-счётчик на фоне Санты',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_countdown_santa') ?: '0'
]);
array_push($group, [
    'name' => 'enable_countdown_banner',
    'title' => 'Счётчик (баннер-растяжка)',
    'descr' => 'Полоска с цифрами дней/часов/минут',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'enable_countdown_banner') ?: '0'
]);
array_push($group, [
    'name' => 'countdown_show_seconds',
    'title' => 'Счётчик: показывать секунды',
    'descr' => 'Добавить блок секунд и обновлять счётчик каждую секунду при D=0',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'countdown_show_seconds') ?: '1'
]);
array_push($group, [
    'name' => 'snow_count',
    'title' => 'Количество снежинок',
    'descr' => 'Сколько снежинок одновременно на экране (0-500)',
    'type' => 'input',
    'value' => extra_get_param($plugin, 'snow_count') ?: '150'
]);
array_push($group, [
    'name' => 'snow_speed',
    'title' => 'Скорость снега',
    'descr' => 'Начальная скорость падения снежинок',
    'type' => 'select',
    'values' => ['0.5' => 'Медленно', '1.5' => 'Средне', '3' => 'Быстро'],
    'value' => extra_get_param($plugin, 'snow_speed') ?: '1.5'
]);
array_push($group, [
    'name' => 'show_switch',
    'title' => 'Показать выключатель',
    'descr' => 'Показывать выключатель гирлянды справа',
    'type' => 'select',
    'values' => ['0' => 'Нет', '1' => 'Да'],
    'value' => extra_get_param($plugin, 'show_switch') ?: '1'
]);
array_push($cfg, ['mode' => 'group', 'title' => '<b>Настройки украшений</b>', 'entries' => $group]);
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'commit') {
    commit_plugin_config_changes($plugin, $cfg);
    print_commit_complete($plugin);
} else {
    generate_config_page($plugin, $cfg);
}

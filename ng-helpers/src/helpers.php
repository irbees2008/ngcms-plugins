<?php

namespace Plugins;
// Базовые расширения PHP.
use Closure;
use stdClass;
// Сторонние зависимости.
use _mysqli;
use Twig_Environment;

/**
 * Коллекция вспомогательных функций для плагинов системы NGCMS.
 *
 * @version: 0.2.1 от 2026-01-16
 * @author: https://github.com/russsiq
 *
 * Базовые функции:
 * array_dotset - Установить значение элементу массива, используя «точечную» нотацию.
 * array_get - Безопасно получить значение из массива через точечную нотацию.
 * cache - Получить данные из кэша, либо сохранить указанные данные в кэш.
 * cacheRemember - Получить данные из кэша, либо выполнить замыкание и сохранить результат в кэш.
 * catz - Получить категорию по идентификатору, либо массив всех категорий.
 * config - Получить значение конфигурации системы.
 * database - Получить текущее подключение к базе данных.
 * dd - Распечатать переменную, массив переменных или объект и прекратить выполнение скрипта.
 * pageInfo - Установить системную информацию о текущей странице.
 * request - Получить значение из глобального $_REQUEST.
 * setting - Получить настройку плагина по ключу, либо задать массив настроек.
 * starts_with - Определить, начинается ли переданная строка с указанной подстроки.
 * trans - Получить перевод строки.
 * value - Возвращает значение по умолчанию для переданного значения.
 * view - Выводит шаблон с заданным контекстом и возвращает его в виде строки.
 *
 * Расширенные функции (v0.2.0):
 * - 60+ новых функций для валидации, работы с массивами, строками, датами, HTTP, безопасности и др.
 * - См. полный список в README.md
 */
if (! function_exists(__NAMESPACE__ . '\array_dotset')) {
    /**
     * Установить значение элементу массива, используя «точечную» нотацию.
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $value
     * @return array
     *
     * @source Illuminate\Support\Arr
     */
    function array_dotset(&$array, $key, $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = preg_split('/\./', $key, -1, PREG_SPLIT_NO_EMPTY);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }
}
if (! function_exists(__NAMESPACE__ . '\cache')) {
    /**
     * Получить данные из кэша, либо сохранить указанные данные в кэш.
     * @param  string  $plugin  Идентификатор плагина.
     * @param  string  $filename  Имя файла для сохранения / возврата данных из кэша.
     * @param  mixed  $data
     * @return mixed
     */
    function cache(string $plugin, string $filename, $data = null)
    {
        $cacheExpire = setting($plugin, 'cache') ? (int) setting($plugin, 'cacheExpire', 60) : 0;
        if (! $cacheExpire) {
            return is_null($data) ? false : $data;
        }
        if (is_null($data)) {
            return unserialize(cacheRetrieveFile($filename, $cacheExpire, $plugin));
        }
        return cacheStoreFile($filename, serialize($data), $plugin);
    }
}
if (! function_exists(__NAMESPACE__ . '\cacheRemember')) {
    /**
     * Получить данные из кэша, либо выполнить замыкание и сохранить результат в кэш.
     * @param  string  $plugin  Идентификатор плагина.
     * @param  string  $filename  Имя файла для сохранения / возврата данных из кэша.
     * @param  Closure  $callback
     * @return mixed
     */
    function cacheRemember(string $plugin, string $filename, Closure $callback)
    {
        if (! $value = cache($plugin, $filename)) {
            cache($plugin, $filename, $value = $callback());
        }
        return $value;
    }
}
if (! function_exists(__NAMESPACE__ . '\catz')) {
    /**
     * Получить категорию по идентификатору, либо массив всех категорий.
     * @param  int|null  $id  Идентификатор категории.
     * @return array
     */
    function catz(int $id = null): array
    {
        /**
         * @var  array  $catz
         */
        global $catz;
        if (is_null($id)) {
            return $catz;
        }
        foreach ($catz as $cat) {
            if ($id == $cat['id']) {
                return $cat;
            }
        }
        return [];
    }
}
if (! function_exists(__NAMESPACE__ . '\config')) {
    /**
     * Получить значение конфигурации системы.
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        /**
         * @var  array  $config
         */
        global $config;
        return array_key_exists($key, $config) ? $config[$key] : value($default);
    }
}
if (! function_exists(__NAMESPACE__ . '\database')) {
    /**
     * Получить текущее подключение к базе данных.
     * @return object
     */
    function database()
    {
        /**
         * @var  _mysqli  $mysql
         */
        global $mysql;
        return $mysql;
    }
}
if (! function_exists(__NAMESPACE__ . '\dd')) {
    /**
     * Распечатать переменную, массив переменных или объект
     * и прекратить выполнение скрипта.
     * @param  mixed  $vars
     * @return void
     */
    function dd(...$vars): void
    {
        $style = 'style="
            background-color: #23241f;
            border-radius: 3px;
            color: #f8f8f2;
            margin-bottom: 15px;
            overflow: visible;
            padding: 5px 10px;
            white-space: pre-wrap;
        "';
        foreach ($vars as $v) {
            if (is_array($v) or is_object($v)) {
                $printable = print_r($v, true);
            } else {
                $printable = var_export($v, true);
            }
            echo "<pre {$style}>{$printable}</pre><br>\n";
        }
        die(1);
    }
}
if (! function_exists(__NAMESPACE__ . '\pageInfo')) {
    /**
     * Установить системную информацию о текущей странице.
     * @param  string  $section
     * @param  mixed  $info
     * @return void
     */
    function pageInfo(string $section, $info): void
    {
        /**
         * @var  array  $SYSTEM_FLAGS
         */
        global $SYSTEM_FLAGS;
        array_dotset($SYSTEM_FLAGS, $section, $info);
    }
}
if (! function_exists(__NAMESPACE__ . '\request')) {
    /**
     * Получить значение из глобального $_REQUEST.
     * @param  string|null  $key
     * @param  mixed  $default
     * @return string|array
     */
    function request(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $_REQUEST;
        }
        return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : value($default);
    }
}
if (! function_exists(__NAMESPACE__ . '\setting')) {
    /**
     * Получить настройку плагина по ключу, либо задать массив настроек.
     * @param  string  $plugin  Идентификатор плагина.
     * @param  string|array  $variety  Имя ключа получаемой настройки, либо массив сохраняемых настроек.
     * @param  mixed  $default
     * @return mixed
     */
    function setting(string $plugin, $variety, $default = null)
    {
        /**
         * @var  array  $PLUGINS
         */
        global $PLUGINS;
        if (pluginsLoadConfig()) {
            // pluginGetVariable
            if (is_string($variety)) {
                return $PLUGINS['config'][$plugin][$variety] ?? $default;
            }
            // pluginSetVariable
            if (is_array($variety)) {
                foreach ($variety as $key => $value) {
                    $PLUGINS['config'][$plugin][$key] = $value;
                }
                return true;
            }
        }
        return false;
    }
}
if (! function_exists(__NAMESPACE__ . '\starts_with')) {
    /**
     * Определить, начинается ли переданная строка с указанной подстроки.
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function starts_with($haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }
}
if (! function_exists(__NAMESPACE__ . '\trans')) {
    /**
     * Получить перевод строки.
     * @param  string  $key
     * @return string
     */
    function trans(string $key): string
    {
        /**
         * @var  array  $lang
         */
        global $lang;
        return array_key_exists($key, $lang) ? $lang[$key] : $key;
    }
}
if (! function_exists(__NAMESPACE__ . '\value')) {
    /**
     * Возвращает значение по умолчанию для переданного значения.
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
if (! function_exists(__NAMESPACE__ . '\view')) {
    /**
     * Выводит шаблон с заданным контекстом и возвращает его в виде строки.
     * @param  string  $name  Имя шаблона.
     * @param  array  $context  Массив передаваемых параметров шаблону.
     * @param  array  $mergeData  Массив дополнительных параметров.
     * @return string
     */
    function view(string $name, array $context = [], array $mergeData = []): string
    {
        /**
         * @var  Twig_Environment  $twig
         */
        global $twig;
        return $twig->render($name, array_merge($context, $mergeData));
    }
}
if (! function_exists(__NAMESPACE__ . '\env')) {
    /**
     * Получить значение переменной окружения.
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        // Преобразование строковых boolean значений
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        return $value;
    }
}
if (! function_exists(__NAMESPACE__ . '\session')) {
    /**
     * Получить или установить значение сессии.
     * @param  string|array|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (is_null($key)) {
            return $_SESSION;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            return true;
        }
        if (is_string($key)) {
            return $_SESSION[$key] ?? value($default);
        }
        return $default;
    }
}
if (! function_exists(__NAMESPACE__ . '\redirect')) {
    /**
     * Перенаправить пользователя на указанный URL.
     * @param  string  $url
     * @param  int  $code
     * @return void
     */
    function redirect(string $url, int $code = 302): void
    {
        if (!headers_sent()) {
            header("Location: {$url}", true, $code);
            exit;
        }
        echo "<script>window.location.href='{$url}';</script>";
        exit;
    }
}
if (! function_exists(__NAMESPACE__ . '\url')) {
    /**
     * Сгенерировать полный URL для указанного пути.
     * @param  string  $path
     * @param  array  $params
     * @return string
     */
    function url(string $path = '', array $params = []): string
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = ltrim($path, '/');
        $url = "{$scheme}://{$host}/{$path}";
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }
}
if (! function_exists(__NAMESPACE__ . '\abort')) {
    /**
     * Прервать выполнение скрипта с HTTP кодом ошибки.
     * @param  int  $code
     * @param  string  $message
     * @return void
     */
    function abort(int $code = 404, string $message = ''): void
    {
        $codes = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];
        $statusText = $codes[$code] ?? 'Error';
        if (!headers_sent()) {
            header("HTTP/1.1 {$code} {$statusText}");
        }
        $displayMessage = $message ?: $statusText;
        echo "<h1>{$code} - {$displayMessage}</h1>";
        exit;
    }
}
if (! function_exists(__NAMESPACE__ . '\old')) {
    /**
     * Получить старое значение из сессии (для форм после редиректа).
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function old(string $key, $default = null)
    {
        $old = session('_old_input', []);
        return $old[$key] ?? value($default);
    }
}
if (! function_exists(__NAMESPACE__ . '\sanitize')) {
    /**
     * Очистить данные от потенциально опасных символов.
     * @param  string|array  $data
     * @param  bool  $stripTags
     * @return string|array
     */
    function sanitize($data, bool $stripTags = true)
    {
        if (is_array($data)) {
            return array_map(function ($item) use ($stripTags) {
                return sanitize($item, $stripTags);
            }, $data);
        }
        $data = trim($data);
        $data = $stripTags ? strip_tags($data) : $data;
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}
if (! function_exists(__NAMESPACE__ . '\str_limit')) {
    /**
     * Обрезать строку до указанной длины.
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     * @return string
     */
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit) . $end;
    }
}
if (! function_exists(__NAMESPACE__ . '\array_get')) {
    /**
     * Безопасно получить значение из массива, используя «точечную» нотацию.
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function array_get(array $array, string $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }
        return $array;
    }
}
if (! function_exists(__NAMESPACE__ . '\filled')) {
    /**
     * Определить, является ли значение "заполненным".
     * @param  mixed  $value
     * @return bool
     */
    function filled($value): bool
    {
        return !blank($value);
    }
}
if (! function_exists(__NAMESPACE__ . '\blank')) {
    /**
     * Определить, является ли значение "пустым".
     * @param  mixed  $value
     * @return bool
     */
    function blank($value): bool
    {
        if (is_null($value)) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        if (is_numeric($value) || is_bool($value)) {
            return false;
        }
        if ($value instanceof stdClass) {
            return count(get_object_vars($value)) === 0;
        }
        return empty($value);
    }
}
if (! function_exists(__NAMESPACE__ . '\tap')) {
    /**
     * Вызвать замыкание с переданным значением и вернуть это значение.
     * @param  mixed  $value
     * @param  callable  $callback
     * @return mixed
     */
    function tap($value, callable $callback)
    {
        $callback($value);
        return $value;
    }
}
if (! function_exists(__NAMESPACE__ . '\now')) {
    /**
     * Получить текущую дату и время.
     * @param  string|null  $format
     * @return string|int
     */
    function now(string $format = null)
    {
        $timestamp = time();
        if (is_null($format)) {
            return $timestamp;
        }
        return date($format, $timestamp);
    }
}
if (! function_exists(__NAMESPACE__ . '\optional')) {
    /**
     * Безопасный доступ к свойствам объекта или элементам массива.
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function optional($value = null, callable $callback = null)
    {
        if (is_null($callback)) {
            return $value === null ? new class {
                public function __get($key)
                {
                    return null;
                }
                public function __call($method, $parameters)
                {
                    return null;
                }
            }
                : $value;
        }
        return $value !== null ? $callback($value) : null;
    }
}
if (! function_exists(__NAMESPACE__ . '\collect')) {
    /**
     * Создать массив с дополнительными методами для работы.
     * @param  array  $items
     * @return object
     */
    function collect(array $items = []): object
    {
        return new class($items) {
            private $items;
            public function __construct(array $items = [])
            {
                $this->items = $items;
            }
            public function all(): array
            {
                return $this->items;
            }
            public function count(): int
            {
                return count($this->items);
            }
            public function first()
            {
                return reset($this->items) ?: null;
            }
            public function last()
            {
                return end($this->items) ?: null;
            }
            public function map(callable $callback): object
            {
                return new self(array_map($callback, $this->items));
            }
            public function filter(callable $callback = null): object
            {
                if ($callback) {
                    return new self(array_filter($this->items, $callback));
                }
                return new self(array_filter($this->items));
            }
            public function pluck(string $key): object
            {
                $result = [];
                foreach ($this->items as $item) {
                    if (is_array($item) && isset($item[$key])) {
                        $result[] = $item[$key];
                    } elseif (is_object($item) && isset($item->$key)) {
                        $result[] = $item->$key;
                    }
                }
                return new self($result);
            }
            public function toArray(): array
            {
                return $this->items;
            }
        };
    }
}
if (! function_exists(__NAMESPACE__ . '\ends_with')) {
    /**
     * Определить, заканчивается ли переданная строка указанной подстрокой.
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function ends_with(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }
}
if (! function_exists(__NAMESPACE__ . '\str_contains')) {
    /**
     * Определить, содержит ли строка указанную подстроку.
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function str_contains(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}
if (! function_exists(__NAMESPACE__ . '\retry')) {
    /**
     * Повторить выполнение функции указанное количество раз при возникновении исключения.
     * @param  int  $times
     * @param  callable  $callback
     * @param  int  $sleep
     * @return mixed
     */
    function retry(int $times, callable $callback, int $sleep = 0)
    {
        $attempts = 0;
        beginning:
        $attempts++;
        try {
            return $callback($attempts);
        } catch (\Exception $e) {
            if ($attempts >= $times) {
                throw $e;
            }
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}
if (! function_exists(__NAMESPACE__ . '\paginate')) {
    /**
     * Создать постраничную навигацию.
     * @param  int  $currentPage  Текущая страница
     * @param  int  $totalPages  Всего страниц
     * @param  array  $params  Параметры для генерации ссылок
     * @param  int  $maxLinks  Максимум ссылок на странице
     * @return string
     */
    function paginate(int $currentPage, int $totalPages, array $params = [], int $maxLinks = 8): string
    {
        if ($totalPages < 2) {
            return '';
        }
        return ngSitePagination($currentPage, $totalPages, $params, $maxLinks);
    }
}
if (! function_exists(__NAMESPACE__ . '\formatBytes')) {
    /**
     * Форматировать размер файла в удобочитаемый вид.
     * @param  int  $bytes  Размер в байтах
     * @param  int  $precision  Точность округления
     * @return string
     */
    function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, $precision) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, $precision) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, $precision) . ' KB';
        }
        return $bytes . ' B';
    }
}
if (! function_exists(__NAMESPACE__ . '\slug')) {
    /**
     * Создать URL-friendly строку (slug) из текста.
     * @param  string  $text
     * @param  string  $separator
     * @return string
     */
    function slug(string $text, string $separator = '-'): string
    {
        // Транслитерация
        $text = transliterate($text);
        // Приведение к нижнему регистру
        $text = mb_strtolower($text, 'UTF-8');
        // Замена не буквенно-цифровых символов на разделитель
        $text = preg_replace('/[^a-z0-9]+/i', $separator, $text);
        // Удаление повторяющихся разделителей
        $text = preg_replace('/' . preg_quote($separator, '/') . '{2,}/', $separator, $text);
        // Удаление разделителей в начале и конце
        return trim($text, $separator);
    }
}
if (! function_exists(__NAMESPACE__ . '\transliterate')) {
    /**
     * Транслитерация кириллицы в латиницу.
     * @param  string  $text
     * @return string
     */
    function transliterate(string $text): string
    {
        $converter = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sch',
            'Ь' => '',
            'Ы' => 'Y',
            'Ъ' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
        ];
        return strtr($text, $converter);
    }
}
if (! function_exists(__NAMESPACE__ . '\excerpt')) {
    /**
     * Создать выдержку из текста с удалением HTML тегов.
     * @param  string  $text
     * @param  int  $length
     * @param  string  $end
     * @return string
     */
    function excerpt(string $text, int $length = 150, string $end = '...'): string
    {
        $text = strip_tags($text);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return str_limit($text, $length, $end);
    }
}
if (! function_exists(__NAMESPACE__ . '\breadcrumb')) {
    /**
     * Создать хлебные крошки.
     * @param  array  $items  Массив элементов [['title' => 'Название', 'url' => '/link'], ...]
     * @param  string  $separator
     * @return string
     */
    function breadcrumb(array $items, string $separator = ' / '): string
    {
        if (empty($items)) {
            return '';
        }
        $result = [];
        $lastKey = array_key_last($items);
        foreach ($items as $key => $item) {
            if ($key === $lastKey || empty($item['url'])) {
                $result[] = $item['title'];
            } else {
                $result[] = '<a href="' . htmlspecialchars($item['url']) . '">' .
                    htmlspecialchars($item['title']) . '</a>';
            }
        }
        return implode($separator, $result);
    }
}
if (! function_exists(__NAMESPACE__ . '\truncate_html')) {
    /**
     * Обрезать HTML текст без повреждения тегов.
     * @param  string  $html
     * @param  int  $length
     * @param  string  $ending
     * @return string
     */
    function truncate_html(string $html, int $length = 100, string $ending = '...'): string
    {
        $printedLength = 0;
        $position = 0;
        $tags = [];
        $output = '';
        while ($printedLength < $length && preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $html, $match, PREG_OFFSET_CAPTURE, $position)) {
            list($tag, $tagPosition) = $match[0];
            $str = mb_substr($html, $position, $tagPosition - $position);
            if ($printedLength + mb_strlen($str) > $length) {
                $output .= mb_substr($str, 0, $length - $printedLength);
                $printedLength = $length;
                break;
            }
            $output .= $str;
            $printedLength += mb_strlen($str);
            if ($tag[0] == '&') {
                $output .= $tag;
                $printedLength++;
            } else {
                $tagName = $match[1][0];
                if ($tag[1] == '/') {
                    array_pop($tags);
                } elseif ($tag[mb_strlen($tag) - 2] != '/') {
                    $tags[] = $tagName;
                }
                $output .= $tag;
            }
            $position = $tagPosition + mb_strlen($tag);
        }
        if ($printedLength < $length && $position < mb_strlen($html)) {
            $output .= mb_substr($html, $position, $length - $printedLength);
        }
        if (mb_strlen($html) > $length) {
            $output .= $ending;
        }
        while (!empty($tags)) {
            $output .= '</' . array_pop($tags) . '>';
        }
        return $output;
    }
}
if (! function_exists(__NAMESPACE__ . '\csrf_field')) {
    /**
     * Сгенерировать скрытое поле CSRF токена.
     * @return string
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}
if (! function_exists(__NAMESPACE__ . '\csrf_token')) {
    /**
     * Получить CSRF токен.
     * @return string
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
}
if (! function_exists(__NAMESPACE__ . '\validate_csrf')) {
    /**
     * Проверить CSRF токен.
     * @param  string|null  $token
     * @return bool
     */
    function validate_csrf(string $token = null): bool
    {
        $token = $token ?? request('_token');
        if (!$token) {
            return false;
        }
        return hash_equals(csrf_token(), $token);
    }
}
if (! function_exists(__NAMESPACE__ . '\cache_get')) {
    /**
     * Получить значение из кэша.
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function cache_get(string $key, $default = null)
    {
        $filename = md5($key) . '.cache';
        $filepath = root . 'cache/' . $filename;
        if (!file_exists($filepath)) {
            return value($default);
        }
        $data = @unserialize(file_get_contents($filepath));
        if ($data === false || !isset($data['expires']) || $data['expires'] < time()) {
            @unlink($filepath);
            return value($default);
        }
        return $data['value'];
    }
}
if (! function_exists(__NAMESPACE__ . '\cache_put')) {
    /**
     * Сохранить значение в кэш.
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     * @return bool
     */
    function cache_put(string $key, $value, int $minutes = 60): bool
    {
        $filename = md5($key) . '.cache';
        $filepath = root . 'cache/' . $filename;
        $data = [
            'value' => $value,
            'expires' => time() + ($minutes * 60),
        ];
        return file_put_contents($filepath, serialize($data)) !== false;
    }
}
if (! function_exists(__NAMESPACE__ . '\cache_forget')) {
    /**
     * Удалить значение из кэша.
     * @param  string  $key
     * @return bool
     */
    function cache_forget(string $key): bool
    {
        $filename = md5($key) . '.cache';
        $filepath = root . 'cache/' . $filename;
        if (file_exists($filepath)) {
            return @unlink($filepath);
        }
        return true;
    }
}
if (! function_exists(__NAMESPACE__ . '\random_string')) {
    /**
     * Сгенерировать случайную строку.
     * @param  int  $length
     * @return string
     */
    function random_string(int $length = 16): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
if (! function_exists(__NAMESPACE__ . '\get_ip')) {
    /**
     * Получить IP адрес пользователя с учетом прокси.
     * @return string
     */
    function get_ip(): string
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($keys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// ===================================
// Валидация данных
// ===================================

if (! function_exists(__NAMESPACE__ . '\validate_email')) {
    /**
     * Проверить валидность email адреса.
     * @param  string  $email
     * @return bool
     */
    function validate_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (! function_exists(__NAMESPACE__ . '\validate_url')) {
    /**
     * Проверить валидность URL.
     * @param  string  $url
     * @return bool
     */
    function validate_url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

if (! function_exists(__NAMESPACE__ . '\validate_phone')) {
    /**
     * Проверить валидность телефонного номера.
     * @param  string  $phone
     * @return bool
     */
    function validate_phone(string $phone): bool
    {
        return preg_match('/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', $phone) === 1;
    }
}

if (! function_exists(__NAMESPACE__ . '\validate_date')) {
    /**
     * Проверить валидность даты.
     * @param  string  $date
     * @param  string  $format
     * @return bool
     */
    function validate_date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

// ===================================
// Работа с массивами
// ===================================

if (! function_exists(__NAMESPACE__ . '\array_only')) {
    /**
     * Получить из массива только указанные ключи.
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    function array_only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }
}

if (! function_exists(__NAMESPACE__ . '\array_except')) {
    /**
     * Получить массив без указанных ключей.
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    function array_except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }
}

if (! function_exists(__NAMESPACE__ . '\array_flatten')) {
    /**
     * Развернуть многомерный массив в одномерный.
     * @param  array  $array
     * @return array
     */
    function array_flatten(array $array): array
    {
        $result = [];
        array_walk_recursive($array, function ($value) use (&$result) {
            $result[] = $value;
        });
        return $result;
    }
}

if (! function_exists(__NAMESPACE__ . '\array_first')) {
    /**
     * Получить первый элемент массива.
     * @param  array  $array
     * @param  mixed  $default
     * @return mixed
     */
    function array_first(array $array, $default = null)
    {
        if (empty($array)) {
            return value($default);
        }

        foreach ($array as $item) {
            return $item;
        }

        return value($default);
    }
}

if (! function_exists(__NAMESPACE__ . '\array_last')) {
    /**
     * Получить последний элемент массива.
     * @param  array  $array
     * @param  mixed  $default
     * @return mixed
     */
    function array_last(array $array, $default = null)
    {
        return empty($array) ? value($default) : end($array);
    }
}

if (! function_exists(__NAMESPACE__ . '\array_pluck')) {
    /**
     * Извлечь значения из массива по указанному ключу.
     * @param  array  $array
     * @param  string  $key
     * @return array
     */
    function array_pluck(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item) && isset($item[$key])) {
                $result[] = $item[$key];
            } elseif (is_object($item) && isset($item->$key)) {
                $result[] = $item->$key;
            }
        }
        return $result;
    }
}

// ===================================
// Работа со строками
// ===================================

if (! function_exists(__NAMESPACE__ . '\str_snake')) {
    /**
     * Преобразовать строку в snake_case.
     * @param  string  $value
     * @return string
     */
    function str_snake(string $value): string
    {
        $value = preg_replace('/\s+/u', '', $value);
        $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);
        return mb_strtolower($value, 'UTF-8');
    }
}

if (! function_exists(__NAMESPACE__ . '\str_camel')) {
    /**
     * Преобразовать строку в camelCase.
     * @param  string  $value
     * @return string
     */
    function str_camel(string $value): string
    {
        $value = str_studly($value);
        return lcfirst($value);
    }
}

if (! function_exists(__NAMESPACE__ . '\str_studly')) {
    /**
     * Преобразовать строку в StudlyCase.
     * @param  string  $value
     * @return string
     */
    function str_studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }
}

if (! function_exists(__NAMESPACE__ . '\str_before')) {
    /**
     * Получить часть строки до первого вхождения подстроки.
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    function str_before(string $subject, string $search): string
    {
        $pos = strpos($subject, $search);
        return $pos === false ? $subject : substr($subject, 0, $pos);
    }
}

if (! function_exists(__NAMESPACE__ . '\str_after')) {
    /**
     * Получить часть строки после первого вхождения подстроки.
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    function str_after(string $subject, string $search): string
    {
        $pos = strpos($subject, $search);
        return $pos === false ? $subject : substr($subject, $pos + strlen($search));
    }
}

if (! function_exists(__NAMESPACE__ . '\str_between')) {
    /**
     * Получить часть строки между двумя подстроками.
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * @return string
     */
    function str_between(string $subject, string $from, string $to): string
    {
        $after = str_after($subject, $from);
        return str_before($after, $to);
    }
}

// ===================================
// Работа с датами
// ===================================

if (! function_exists(__NAMESPACE__ . '\time_ago')) {
    /**
     * Получить человекочитаемое представление времени ("5 минут назад").
     * @param  int|string  $timestamp
     * @param  string  $lang
     * @return string
     */
    function time_ago($timestamp, string $lang = 'ru'): string
    {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $diff = time() - $timestamp;

        if ($lang === 'ru') {
            $units = [
                31536000 => ['год', 'года', 'лет'],
                2592000 => ['месяц', 'месяца', 'месяцев'],
                604800 => ['неделю', 'недели', 'недель'],
                86400 => ['день', 'дня', 'дней'],
                3600 => ['час', 'часа', 'часов'],
                60 => ['минуту', 'минуты', 'минут'],
                1 => ['секунду', 'секунды', 'секунд']
            ];

            foreach ($units as $unit => $names) {
                if ($diff >= $unit) {
                    $numberOfUnits = floor($diff / $unit);
                    $idx = ($numberOfUnits % 10 == 1 && $numberOfUnits % 100 != 11) ? 0 : (($numberOfUnits % 10 >= 2 && $numberOfUnits % 10 <= 4 && ($numberOfUnits % 100 < 10 || $numberOfUnits % 100 >= 20)) ? 1 : 2);
                    return $numberOfUnits . ' ' . $names[$idx] . ' назад';
                }
            }
            return 'только что';
        } else {
            $units = [
                31536000 => 'year',
                2592000 => 'month',
                604800 => 'week',
                86400 => 'day',
                3600 => 'hour',
                60 => 'minute',
                1 => 'second'
            ];

            foreach ($units as $unit => $name) {
                if ($diff >= $unit) {
                    $numberOfUnits = floor($diff / $unit);
                    return $numberOfUnits . ' ' . $name . ($numberOfUnits > 1 ? 's' : '') . ' ago';
                }
            }
            return 'just now';
        }
    }
}

if (! function_exists(__NAMESPACE__ . '\format_date')) {
    /**
     * Форматировать дату по указанному формату.
     * @param  int|string  $timestamp
     * @param  string  $format
     * @return string
     */
    function format_date($timestamp, string $format = 'd.m.Y H:i'): string
    {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        return date($format, $timestamp);
    }
}

// ===================================
// HTTP & Request
// ===================================

if (! function_exists(__NAMESPACE__ . '\is_ajax')) {
    /**
     * Проверить, является ли запрос AJAX.
     * @return bool
     */
    function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

if (! function_exists(__NAMESPACE__ . '\is_post')) {
    /**
     * Проверить, является ли запрос POST.
     * @return bool
     */
    function is_post(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

if (! function_exists(__NAMESPACE__ . '\is_get')) {
    /**
     * Проверить, является ли запрос GET.
     * @return bool
     */
    function is_get(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

if (! function_exists(__NAMESPACE__ . '\get_user_agent')) {
    /**
     * Получить User Agent браузера.
     * @return string
     */
    function get_user_agent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}

if (! function_exists(__NAMESPACE__ . '\is_mobile')) {
    /**
     * Проверить, является ли устройство мобильным.
     * @return bool
     */
    function is_mobile(): bool
    {
        $userAgent = get_user_agent();
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) ||
            preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4));
    }
}

// ===================================
// Debug & Logging
// ===================================

if (! function_exists(__NAMESPACE__ . '\dump')) {
    /**
     * Вывести переменную без остановки выполнения.
     * @param  mixed  ...$vars
     * @return void
     */
    function dump(...$vars): void
    {
        $style = 'style="
            background-color: #23241f;
            border-radius: 3px;
            color: #f8f8f2;
            margin-bottom: 15px;
            overflow: visible;
            padding: 5px 10px;
            white-space: pre-wrap;
        "';

        foreach ($vars as $v) {
            if (is_array($v) || is_object($v)) {
                $printable = print_r($v, true);
            } else {
                $printable = var_export($v, true);
            }

            echo "<pre {$style}>{$printable}</pre><br>\n";
        }
    }
}

if (! function_exists(__NAMESPACE__ . '\logger')) {
    /**
     * Записать сообщение в лог файл.
     * @param  string  $message
     * @param  string  $level
     * @param  string  $file
     * @return bool
     */
    function logger(string $message, string $level = 'info', string $file = 'plugin.log'): bool
    {
        $logPath = root . 'cache/logs/' . $file;
        $dir = dirname($logPath);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        return file_put_contents($logPath, $logMessage, FILE_APPEND) !== false;
    }
}

if (! function_exists(__NAMESPACE__ . '\benchmark')) {
    /**
     * Замерить время выполнения функции.
     * @param  callable  $callback
     * @return array ['result' => mixed, 'time' => float]
     */
    function benchmark(callable $callback): array
    {
        $start = microtime(true);
        $result = $callback();
        $time = microtime(true) - $start;

        return [
            'result' => $result,
            'time' => $time,
            'memory' => memory_get_peak_usage(true)
        ];
    }
}

// ===================================
// Безопасность
// ===================================

if (! function_exists(__NAMESPACE__ . '\hash_make')) {
    /**
     * Создать хеш пароля.
     * @param  string  $password
     * @return string
     */
    function hash_make(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (! function_exists(__NAMESPACE__ . '\hash_check')) {
    /**
     * Проверить пароль против хеша.
     * @param  string  $password
     * @param  string  $hash
     * @return bool
     */
    function hash_check(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

if (! function_exists(__NAMESPACE__ . '\encrypt')) {
    /**
     * Зашифровать строку.
     * @param  string  $data
     * @param  string  $key
     * @return string
     */
    function encrypt(string $data, string $key = ''): string
    {
        if (empty($key)) {
            $key = config('salt', 'default_key');
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }
}

if (! function_exists(__NAMESPACE__ . '\decrypt')) {
    /**
     * Расшифровать строку.
     * @param  string  $data
     * @param  string  $key
     * @return string|false
     */
    function decrypt(string $data, string $key = '')
    {
        if (empty($key)) {
            $key = config('salt', 'default_key');
        }

        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}

// ===================================
// Работа с путями
// ===================================

if (! function_exists(__NAMESPACE__ . '\storage_path')) {
    /**
     * Получить путь к директории хранилища.
     * @param  string  $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        $base = root . 'uploads/';
        return $path ? $base . ltrim($path, '/') : $base;
    }
}

if (! function_exists(__NAMESPACE__ . '\public_path')) {
    /**
     * Получить путь к публичной директории.
     * @param  string  $path
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return $path ? root . ltrim($path, '/') : root;
    }
}

if (! function_exists(__NAMESPACE__ . '\plugin_path')) {
    /**
     * Получить путь к директории плагина.
     * @param  string  $plugin
     * @param  string  $path
     * @return string
     */
    function plugin_path(string $plugin, string $path = ''): string
    {
        $base = root . 'engine/plugins/' . $plugin . '/';
        return $path ? $base . ltrim($path, '/') : $base;
    }
}

// ===================================
// Условные хелперы
// ===================================

if (! function_exists(__NAMESPACE__ . '\when')) {
    /**
     * Выполнить callback если условие истинно.
     * @param  bool  $condition
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return mixed
     */
    function when(bool $condition, callable $callback, callable $default = null)
    {
        if ($condition) {
            return $callback();
        } elseif ($default) {
            return $default();
        }

        return null;
    }
}

if (! function_exists(__NAMESPACE__ . '\unless')) {
    /**
     * Выполнить callback если условие ложно.
     * @param  bool  $condition
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return mixed
     */
    function unless(bool $condition, callable $callback, callable $default = null)
    {
        return when(!$condition, $callback, $default);
    }
}

if (! function_exists(__NAMESPACE__ . '\transform')) {
    /**
     * Трансформировать значение с помощью callback.
     * @param  mixed  $value
     * @param  callable  $callback
     * @param  mixed  $default
     * @return mixed
     */
    function transform($value, callable $callback, $default = null)
    {
        if (filled($value)) {
            return $callback($value);
        }

        return value($default);
    }
}

// ===================================
// JSON
// ===================================

if (! function_exists(__NAMESPACE__ . '\json_validate')) {
    /**
     * Проверить валидность JSON строки.
     * @param  string  $json
     * @return bool
     */
    function json_validate(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (! function_exists(__NAMESPACE__ . '\json_decode_safe')) {
    /**
     * Безопасное декодирование JSON с значением по умолчанию.
     * @param  string  $json
     * @param  mixed  $default
     * @param  bool  $assoc
     * @return mixed
     */
    function json_decode_safe(string $json, $default = null, bool $assoc = true)
    {
        $result = json_decode($json, $assoc);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return value($default);
        }

        return $result;
    }
}

// ===================================
// Работа с числами
// ===================================

if (! function_exists(__NAMESPACE__ . '\number_format_locale')) {
    /**
     * Форматировать число с учетом локали.
     * @param  float  $number
     * @param  int  $decimals
     * @param  string  $locale
     * @return string
     */
    function number_format_locale(float $number, int $decimals = 2, string $locale = 'ru'): string
    {
        if ($locale === 'ru') {
            return number_format($number, $decimals, ',', ' ');
        }

        return number_format($number, $decimals, '.', ',');
    }
}

if (! function_exists(__NAMESPACE__ . '\percentage')) {
    /**
     * Вычислить процент.
     * @param  float  $value
     * @param  float  $total
     * @param  int  $decimals
     * @return float
     */
    function percentage(float $value, float $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($value / $total) * 100, $decimals);
    }
}

if (! function_exists(__NAMESPACE__ . '\clamp')) {
    /**
     * Ограничить число в заданном диапазоне.
     * @param  float  $value
     * @param  float  $min
     * @param  float  $max
     * @return float
     */
    function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}

// ===================================
// HTML хелперы
// ===================================

if (! function_exists(__NAMESPACE__ . '\link_to')) {
    /**
     * Создать HTML ссылку.
     * @param  string  $url
     * @param  string  $title
     * @param  array  $attributes
     * @return string
     */
    function link_to(string $url, string $title = '', array $attributes = []): string
    {
        $title = $title ?: $url;
        $attrs = '';

        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<a href="' . htmlspecialchars($url) . '"' . $attrs . '>' . htmlspecialchars($title) . '</a>';
    }
}

if (! function_exists(__NAMESPACE__ . '\image_tag')) {
    /**
     * Создать HTML тег изображения.
     * @param  string  $src
     * @param  string  $alt
     * @param  array  $attributes
     * @return string
     */
    function image_tag(string $src, string $alt = '', array $attributes = []): string
    {
        $attrs = 'src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '"';

        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<img ' . $attrs . '>';
    }
}

if (! function_exists(__NAMESPACE__ . '\mailto')) {
    /**
     * Создать mailto ссылку.
     * @param  string  $email
     * @param  string  $title
     * @param  array  $attributes
     * @return string
     */
    function mailto(string $email, string $title = '', array $attributes = []): string
    {
        $title = $title ?: $email;
        return link_to('mailto:' . $email, $title, $attributes);
    }
}
if (! function_exists(__NAMESPACE__ . '\notify')) {
    /**
     * Генерировать HTML-блок с toast-уведомлением для вставки через JavaScript.
     *
     * Создает HTML-блок, содержащий <script> с вызовом функции notify(),
     * который можно вставить через insertAdjacentHTML() или innerHTML.
     *
     * @param  string  $type    Тип уведомления: 'success', 'error', 'info', 'warning'
     * @param  string  $message Текст сообщения для отображения
     * @return string HTML-блок со скриптом уведомления
     *
     * Примеры использования:
     *
     * 1. В AJAX-ответе (возврат HTML-блока):
     *    return json_encode([
     *        'status' => 1,
     *        'data' => notify('success', 'Комментарий добавлен')
     *    ]);
     *
     * 2. В обработчике формы:
     *    echo notify('error', 'Заполните все поля');
     *
     * 3. С экранированием спецсимволов:
     *    echo notify('info', $userMessage); // автоматически экранирует
     *
     * JavaScript должен обрабатывать так:
     *    if (result.data.indexOf('<') !== -1) {
     *        document.body.insertAdjacentHTML('beforeend', result.data);
     *    } else {
     *        notify(type, result.data);
     *    }
     */
    function notify(string $type, string $message): string
    {
        // Валидация типа уведомления
        $allowedTypes = ['success', 'error', 'info', 'warning'];
        if (!in_array($type, $allowedTypes)) {
            $type = 'info';
        }

        // Экранирование сообщения для безопасной вставки в JavaScript
        $escapedMessage = addslashes(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

        // Генерация HTML-блока со скриптом
        return '<script>notify(\'' . $type . '\', \'' . $escapedMessage . '\');</script>';
    }
}
if (! function_exists(__NAMESPACE__ . '\formatMoney')) {
    /**
     * Форматировать число как денежную сумму.
     *
     * @param  float|int|string  $amount  Сумма для форматирования
     * @param  int  $decimals  Количество знаков после запятой (по умолчанию 2)
     * @param  string  $decPoint  Разделитель целой и дробной части (по умолчанию '.')
     * @param  string  $thousandsSep  Разделитель тысяч (по умолчанию ' ')
     * @return string  Отформатированная строка
     *
     * Примеры использования:
     *
     * formatMoney(1234.56)           // "1 234.56"
     * formatMoney(1234.56, 0)        // "1 235"
     * formatMoney(1234.56, 2, ',')   // "1 234,56"
     * formatMoney(1234.56, 2, '.', ',') // "1,234.56"
     */
    function formatMoney($amount, int $decimals = 2, string $decPoint = '.', string $thousandsSep = ' '): string
    {
        // Приводим к числу
        $amount = floatval($amount);

        // Форматируем
        return number_format($amount, $decimals, $decPoint, $thousandsSep);
    }
}

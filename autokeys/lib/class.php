<?php

use function Plugins\{logger, sanitize, cache_get, cache_put};

// Подключаем необходимые файлы Morphos вручную
require __DIR__ . '/morphos/src/Cases.php'; // Подключаем интерфейс Cases
require __DIR__ . '/morphos/src/CasesHelper.php'; // Подключаем интерфейс Cases
require __DIR__ . '/morphos/src/BaseInflection.php'; // Подключаем BaseInflection
require __DIR__ . '/morphos/src/Russian/RussianCasesHelper.php'; // Подключаем RussianCasesHelper
require __DIR__ . '/morphos/src/Russian/RussianLanguage.php'; // Подключаем RussianLanguage
require __DIR__ . '/morphos/src/Russian/Cases.php'; // Подключаем Cases
require __DIR__ . '/morphos/src/Gender.php';
require __DIR__ . '/morphos/src/Russian/NounDeclension.php'; // Подключаем NounDeclension
require __DIR__ . '/morphos/src/S.php'; // Подключаем S
// Теперь можно использовать класс Morphos\Russian\NounDeclension
use Morphos\Russian\NounDeclension;

class AutoKeyword
{
	public $contents;
	public $encoding;
	public $keywords;
	public $wordLengthMin;
	public $wordOccuredMin;
	public $wordLengthMax;
	public $wordGoodArray;
	public $wordBlockArray;
	public $wordMaxCount;
	public $wordB;
	public $wordAddTitle;
	public $wordTitle;

	public function __construct($params, $encoding)
	{
		$this->wordGoodArray = [];
		$this->wordBlockArray = [];
		$this->encoding = $encoding;
		$this->wordLengthMin = $params['min_word_length'] ?? 0;
		$this->wordLengthMax = $params['max_word_length'] ?? 0;
		$this->wordOccuredMin = $params['min_word_occur'] ?? 0;
		$this->wordMaxCount = $params['word_count'] ?? 0;

		$this->wordB = !empty($params['good_b']);
		$this->wordAddTitle = $params['add_title'] ?? 0;
		$this->wordTitle = $params['title'] ?? '';

		$content = '';
		if ($this->wordAddTitle > 0) {
			for ($i = 0; $i < $this->wordAddTitle; $i++) {
				$content .= $this->wordTitle . ' ';
			}
			$params['content'] = $content . ' ' . ($params['content'] ?? '');
		}

		if (!empty($params['good_array']) && !empty($params['good_word'])) {
			$this->wordGoodArray = explode("\r\n", $params['good_array']);
		}

		if (!empty($params['block_array']) && !empty($params['block_word'])) {
			$this->wordBlockArray = explode("\r\n", $params['block_array']);
		}

		$this->contents = $this->replace_chars($params['content'] ?? '');
		logger('autokeys', 'AutoKeyword init: length=' . mb_strlen($this->contents) . ' chars, minLen=' . $this->wordLengthMin . ', maxLen=' . $this->wordLengthMax);
	}

	public function replace_chars($content)
	{
		$content = sanitize($content, 'html'); // Очистка HTML
		$content = mb_strtolower($content, $this->encoding); // Приводим текст к нижнему регистру с учетом кодировки
		$content = strip_tags($content); // Удаляем HTML-теги
		$content = html_entity_decode($content, ENT_QUOTES, $this->encoding); // Декодируем HTML-сущности
		$content = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $content); // Заменяем спецсимволы на пробелы
		$content = preg_replace('/\s+/u', ' ', $content); // Удаляем лишние пробелы
		$content = trim($content); // Удаляем пробелы в начале и конце

		return $content;
	}

	public function parse_words()
	{
		$startTime = microtime(true);

		// Стоп-слова (общие предлоги, союзы, частицы)
		$stopWords = [
			'это',
			'как',
			'так',
			'для',
			'что',
			'или',
			'его',
			'все',
			'уже',
			'был',
			'быть',
			'она',
			'при',
			'даже',
			'если',
			'под',
			'еще',
			'ещё',
			'может',
			'мне',
			'вас',
			'нас',
			'они',
			'тот',
			'эта',
			'эти',
			'тем',
			'чем',
			'без',
			'где',
			'над',
			'про',
			'раз',
			'там',
			'зачем',
			'потом',
			'того',
			'чтобы',
			'можно',
			'было',
			'были',
			'будет',
			'была',
			'этот',
			'этой',
			'этом',
			'этих',
			'через',
			'после',
			'перед',
			'между',
			'около',
			'более',
			'менее',
			'очень',
			'также',
			'тоже',
			'только',
			'лишь',
			'ведь',
			'вот',
			'вообще',
			'всегда'
		];

		$s = explode(" ", $this->contents);
		$k = [];

		foreach ($s as $val) {
			$val = trim($val);
			// Проверяем длину, стоп-слова, числа
			if (
				mb_strlen($val, $this->encoding) >= $this->wordLengthMin &&
				mb_strlen($val, $this->encoding) <= $this->wordLengthMax &&
				!in_array($val, $stopWords) &&
				!is_numeric($val) &&
				!preg_match('/^\d/', $val) // Исключаем слова, начинающиеся с цифры
			) {
				// Приводим к начальной форме с помощью Morphos
				try {
					// Получаем именительный падеж единственного числа
					$normalizedWord = NounDeclension::isMutable($val)
						? NounDeclension::getCase($val, \Morphos\Russian\Cases::IMENIT)
						: $val;
					$k[] = $normalizedWord;
				} catch (Exception $e) {
					// Если морфология не сработала, используем исходное слово
					$k[] = $val;
				}
			}
		}

		$k = array_count_values($k);
		$occur_filtered = $this->occure_filter($k, $this->wordOccuredMin);
		arsort($occur_filtered);

		// Добавляем приоритетные слова в начало
		$occur_filtered = array_flip($this->wordGoodArray) + $occur_filtered;

		// Ограничиваем количество ключевых слов
		array_splice($occur_filtered, $this->wordMaxCount);

		$imploded = $this->implode(", ", $occur_filtered);
		unset($k);
		unset($s);

		$duration = round((microtime(true) - $startTime) * 1000, 2);
		logger('autokeys', 'parse_words: extracted ' . count($occur_filtered) . ' keywords, time=' . $duration . 'ms');

		return $imploded;
	}

	public function occure_filter($array_count_values, $min_occur)
	{
		$occur_filtered = [];
		foreach ($array_count_values as $word => $occured) {
			if ($occured >= $min_occur) {
				$occur_filtered[$word] = $occured;
			}
		}

		return $occur_filtered;
	}

	public function implode($glue, $array)
	{
		$c = "";
		foreach ($array as $key => $val) {
			$c .= $key . $glue;
		}

		return $c;
	}
}

function akeysGetKeys($params)
{
	$cfg = array(
		'content'         => $params['content'],
		'title'           => $params['title'],
		'min_word_length' => (intval(pluginGetVariable('autokeys', 'length'))) ? intval(pluginGetVariable('autokeys', 'length')) : 5,
		'max_word_length' => (intval(pluginGetVariable('autokeys', 'sub'))) ? intval(pluginGetVariable('autokeys', 'sub')) : 100,
		'min_word_occur'  => (intval(pluginGetVariable('autokeys', 'occur'))) ? intval(pluginGetVariable('autokeys', 'occur')) : 2,
		'word_sum'        => (intval(pluginGetVariable('autokeys', 'sum'))) ? intval(pluginGetVariable('autokeys', 'sum')) : 245,
		'block_word'      => pluginGetVariable('autokeys', 'block_y') ? pluginGetVariable('autokeys', 'block_y') : false,
		'block_array'     => pluginGetVariable('autokeys', 'block'),
		'good_word'       => pluginGetVariable('autokeys', 'good_y') ? pluginGetVariable('autokeys', 'good_y') : false,
		'good_array'      => pluginGetVariable('autokeys', 'good'),
		'add_title'       => (intval(pluginGetVariable('autokeys', 'add_title'))) ? intval(pluginGetVariable('autokeys', 'add_title')) : 0,
		'word_count'      => (intval(pluginGetVariable('autokeys', 'count'))) ? intval(pluginGetVariable('autokeys', 'count')) : 245,
		'good_b'          => pluginGetVariable('autokeys', 'good_b') ? pluginGetVariable('autokeys', 'good_b') : false,
	);

	$keyword = new AutoKeyword($cfg, "utf-8");

	$words = $keyword->parse_words();
	$words = implode(', ', array_slice(explode(', ', $words), 0, $cfg['word_count']));

	if (!empty($words)) {
		$words = rtrim($words, ', ');
	}

	logger('autokeys', 'akeysGetKeys: result=' . count(explode(', ', $words)) . ' keywords, length=' . mb_strlen($words) . ' chars');

	return $words;
}

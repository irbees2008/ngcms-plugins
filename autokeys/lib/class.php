<?php
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
	}

	public function replace_chars($content)
	{
		$content = strtolower($content); // Приводим текст к нижнему регистру
		$content = strip_tags($content); // Удаляем HTML-теги
		$content = preg_replace('/[^\p{L}\p{N}\s]/u', '', $content); // Удаляем все символы, кроме букв, цифр и пробелов
		$content = preg_replace('/\s+/', ' ', $content); // Удаляем лишние пробелы

		return $content;
	}

	public function parse_words()
	{
		$common = ["aaaaaaa", "aaaaaaa"];
		$s = explode(" ", $this->contents);
		$k = [];

		foreach ($s as $val) {
			$val = trim($val);
			if (
				strlen($val) >= $this->wordLengthMin &&
				strlen($val) <= $this->wordLengthMax &&
				!in_array($val, $common) &&
				!is_numeric($val)
			) {
				// Склоняем слово в родительный падеж с помощью Morphos
				try {
					$val = NounDeclension::getCase($val, 'именительный');
				} catch (Exception $e) {
					// Если слово не найдено в словаре, пропускаем его
					continue;
				}
				$k[] = $val;
			}
		}

		$k = array_count_values($k);
		$occur_filtered = $this->occure_filter($k, $this->wordOccuredMin);
		arsort($occur_filtered);
		$occur_filtered = array_flip($this->wordGoodArray) + $occur_filtered;
		array_splice($occur_filtered, $this->wordMaxCount);

		$imploded = $this->implode(", ", $occur_filtered);
		unset($k);
		unset($s);

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

	return $words;
}

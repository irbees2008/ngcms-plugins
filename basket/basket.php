<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

use function Plugins\{logger, sanitize, get_ip, formatMoney, clamp, cache_get, cache_put, cache_forget, benchmark, array_get};

LoadPluginLibrary('xfields', 'common');
LoadPluginLibrary('feedback', 'common');
register_htmlvar('js', admin_url . '/plugins/basket/js/basket.js');
//
// Отображение общей информации/остатков в корзине
function plugin_basket_total()
{

	global $mysql, $twig, $userROW, $template;

	$startTime = microtime(true);

	// Определяем условия выборки
	$filter = array();
	if (is_array($userROW)) {
		$filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
	}
	$cookieID = array_get($_COOKIE, 'ngTrackID', '');
	if ($cookieID !== '') {
		$filter[] = '(cookie = ' . db_squote($cookieID) . ')';
	}

	// Генерируем ключ кеша на основе фильтров
	$cacheKey = 'basket_total_' . md5(implode('_', $filter));

	// Пытаемся получить из кеша
	$cached = cache_get($cacheKey, false);
	if ($cached !== false) {
		$tCount = $cached['count'];
		$tPrice = $cached['price'];
		logger('Total: count=' . $tCount . ', price=' . $tPrice . ', IP=' . get_ip() . ' (from cache)', 'info', 'basket.log');
	} else {
		// Считаем итоги из базы
		$tCount = 0;
		$tPrice = 0;
		if (count($filter) && is_array($res = $mysql->record("select count(*) as count, sum(price*count) as price from " . prefix . "_basket where " . join(" or ", $filter), 1))) {
			$tCount = $res['count'];
			$tPrice = $res['price'];
			// Кешируем на 30 секунд
			cache_put($cacheKey, ['count' => $tCount, 'price' => $tPrice], 0.5);
			logger('Total: count=' . $tCount . ', price=' . $tPrice . ', IP=' . get_ip() . ' (cached)', 'info', 'basket.log');
		}
	}

	$duration = round((microtime(true) - $startTime) * 1000, 2);
	logger('Total completed in ' . $duration . 'ms', 'debug', 'basket.log');

	// Готовим переменные
	$tVars = array(
		'count' => $tCount,
		'price' => $tPrice,
		'price_formatted' => formatMoney($tPrice),
	);
	// Выводим шаблон
	$tpath = locatePluginTemplates(array('total'), 'basket', pluginGetVariable('basket', 'localsource'));
	$xt = $twig->loadTemplate($tpath['total'] . '/total.tpl');
	$template['vars']['plugin_basket'] = $xt->render($tVars);
}

//
// Показать содержимое корзины
function plugin_basket_list()
{

	global $mysql, $twig, $userROW, $template;

	$result = benchmark(function () use ($mysql, $twig, $userROW, &$template) {
		// Определяем условия выборки
		$filter = array();
		if (is_array($userROW)) {
			$filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
		}
		$cookieID = array_get($_COOKIE, 'ngTrackID', '');
		if ($cookieID !== '') {
			$filter[] = '(cookie = ' . db_squote($cookieID) . ')';
		}
		// Выполняем выборку
		$recs = array();
		$total = 0;
		if (count($filter)) {
			foreach ($mysql->select("select * from " . prefix . "_basket where " . join(" or ", $filter), 1) as $rec) {
				$total += round($rec['price'] * $rec['count'], 2);
				$rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
				$rec['sum_formatted'] = formatMoney(round($rec['price'] * $rec['count'], 2));
				$rec['price_formatted'] = formatMoney($rec['price']);
				$rec['xfields'] = unserialize($rec['linked_fld']);
				unset($rec['linked_fld']);
				$recs[] = $rec;
			}
			logger('List: count=' . count($recs) . ', total=' . $total . ', IP=' . get_ip(), 'info', 'basket.log');
		}
		$tVars = array(
			'recs'     => count($recs),
			'entries'  => $recs,
			'total'    => sprintf('%9.2f', $total),
			'total_formatted' => formatMoney($total),
			'form_url' => generatePluginLink('feedback', null, array(), array('id' => intval(pluginGetVariable('basket', 'feedback_form')))),
		);
		// Выводим шаблон
		$xt = $twig->loadTemplate('plugins/basket/list.tpl');
		$template['vars']['mainblock'] = $xt->render($tVars);
	});

	logger('List completed in ' . round($result['time'] * 1000, 2) . 'ms', 'debug', 'basket.log');
}

// Update basket content/counters
function plugin_basket_update()
{

	global $mysql, $twig, $userROW, $template, $SUPRESS_TEMPLATE_SHOW;
	// Определяем условия выборки
	$filter = array();
	if (is_array($userROW)) {
		$filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
	}
	$cookieID = array_get($_COOKIE, 'ngTrackID', '');
	if ($cookieID !== '') {
		$filter[] = '(cookie = ' . db_squote($cookieID) . ')';
	}
	// Scan POST params
	if (count($filter)) {
		$updatedCount = 0;
		$deletedCount = 0;
		foreach ($_POST as $k => $v) {
			if (preg_match('#^count_(\d+)$#', $k, $m)) {
				$itemId = intval($m[1]);
				// Используем clamp для ограничения количества (1-999)
				$newCount = clamp(intval(sanitize($v, 'int')), 0, 999);
				if ($newCount < 1) {
					$mysql->query("delete from " . prefix . "_basket where (id = " . db_squote($itemId) . ") and (" . join(" or ", $filter) . ")");
					$deletedCount++;
				} else {
					$mysql->query("update " . prefix . "_basket set count = " . db_squote($newCount) . "where (id = " . db_squote($itemId) . ") and (" . join(" or ", $filter) . ")");
					$updatedCount++;
				}
			}
		}
		logger('Update: updated=' . $updatedCount . ', deleted=' . $deletedCount . ', IP=' . get_ip(), 'info', 'basket.log');

		// Сбрасываем кеш итогов после изменений
		$cacheKey = 'basket_total_' . md5(implode('_', $filter));
		cache_forget($cacheKey);
	}
}

// Plugin definition
if (pluginGetVariable('basket', 'feedback_form')) {
	// News tables management filter
	class BasketXFieldsFilter extends XFieldsFilter
	{

		function showTableEntry($newsID, $SQLnews, $rowData, &$rowVars)
		{

			global $DSlist;
			// Определяем - работаем ли мы внутри строк таблиц
			if (!pluginGetVariable('basket', 'ntable_flag'))
				return;
			// Работаем. Определяем режим работы - по всем строкам или по условию "поле из xfields не равно нулю"
			if (pluginGetVariable('basket', 'ntable_activated')) {
				if (!$rowData['xfields_' . pluginGetVariable('basket', 'ntable_xfield')])
					return;
			}
			$rowVars['flags']['basket_allow'] = true;
			$rowVars['basket_link'] = generatePluginLink('basket', 'add', array('ds' => $DSlist['#xfields:tdata'], 'id' => $rowData['id']), array(), false, true);
			// Строку можно добавлять в корзину
			//print "rowData <pre>(".var_export($rowVars, true).")</pre><br/>\n";
		}
	}

	// Feedback filter
	class BasketFeedbackFilter extends FeedbackFilter
	{

		// Action executed when form is showed
		function onShow($formID, $formStruct, $formData, &$tvars)
		{

			global $userROW, $mysql, $twig;
			// Проверяем ID формы - данные корзины отображаются только в конкретной форме
			if (pluginGetVariable('basket', 'feedback_form') != $formID)
				return;
			// Определяем условия выборки
			$filter = array();
			if (is_array($userROW)) {
				$filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
			}
			$cookieID = array_get($_COOKIE, 'ngTrackID', '');
			if ($cookieID !== '') {
				$filter[] = '(cookie = ' . db_squote($cookieID) . ')';
			}
			// Выполняем выборку
			$recs = array();
			$total = 0;
			if (count($filter)) {
				foreach ($mysql->select("select * from " . prefix . "_basket where " . join(" or ", $filter)) as $rec) {
					$total += round($rec['price'] * $rec['count'], 2);
					$rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
					$rec['sum_formatted'] = formatMoney(round($rec['price'] * $rec['count'], 2));
					$rec['price_formatted'] = formatMoney($rec['price']);
					$rec['xfields'] = unserialize($rec['linked_fld']);
					unset($rec['linked_fld']);
					$recs[] = $rec;
				}
				logger('Feedback show: formID=' . $formID . ', count=' . count($recs) . ', total=' . $total . ', IP=' . get_ip(), 'info', 'basket.log');
			}
			$tVars = array(
				'recs'    => count($recs),
				'entries' => $recs,
				'total'   => sprintf('%9.2f', $total),
				'total_formatted' => formatMoney($total),
			);
			// Выводим шаблон
			$xt = $twig->loadTemplate('plugins/basket/lfeedback.tpl');
			$tvars['plugin_basket'] = $xt->render($tVars);
		}

		function onProcess($formID, $formStruct, $formData, $flagHTML, &$tvars)
		{

			global $userROW, $mysql, $twig;
			// Проверяем ID формы - данные корзины отображаются только в конкретной форме
			if (pluginGetVariable('basket', 'feedback_form') != $formID)
				return 1;
			// Определяем условия выборки
			$filter = array();
			if (is_array($userROW)) {
				$filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
			}
			$cookieID = array_get($_COOKIE, 'ngTrackID', '');
			if ($cookieID !== '') {
				$filter[] = '(cookie = ' . db_squote($cookieID) . ')';
			}
			// Выполняем выборку
			$recs = array();
			$total = 0;
			if (count($filter)) {
				foreach ($mysql->select("select * from " . prefix . "_basket where " . join(" or ", $filter)) as $rec) {
					$total += round($rec['price'] * $rec['count'], 2);
					$rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
					$rec['sum_formatted'] = formatMoney(round($rec['price'] * $rec['count'], 2));
					$rec['price_formatted'] = formatMoney($rec['price']);
					$rec['xfields'] = unserialize($rec['linked_fld']);
					unset($rec['linked_fld']);
					$recs[] = $rec;
				}
				logger('Feedback process: formID=' . $formID . ', count=' . count($recs) . ', total=' . $total . ', IP=' . get_ip(), 'info', 'basket.log');
			}
			$bVars = array(
				'recs'    => count($recs),
				'entries' => $recs,
				'total'   => sprintf('%9.2f', $total),
				'total_formatted' => formatMoney($total),
			);
			// Выводим шаблон
			$xt = $twig->loadTemplate('plugins/basket/lfeedback.tpl');
			$tvars['plugin_basket'] = $xt->render($bVars);
		}

		// Action executed when post request is completed
		function onProcessNotify($formID)
		{

			global $mysql, $userROW;
			// Определяем условия выборки
			$filter = array();
			if (is_array($userROW)) {
				$filter[] = '(user_id = ' . db_squote($userROW['id']) . ')';
			}
			$cookieID = array_get($_COOKIE, 'ngTrackID', '');
			if ($cookieID !== '') {
				$filter[] = '(cookie = ' . db_squote($cookieID) . ')';
			}
			// Выполняем выборку
			if (count($filter)) {
				$stmt = $mysql->query("delete from " . prefix . "_basket where " . join(" or ", $filter));
				$deletedCount = $mysql->affected_rows($stmt);
				logger('Feedback notify: formID=' . $formID . ', cleared=' . $deletedCount . ' items, IP=' . get_ip(), 'info', 'basket.log');

				// Сбрасываем кеш после очистки корзины
				$cacheKey = 'basket_total_' . md5(implode('_', $filter));
				cache_forget($cacheKey);
			}
		}
	}

	register_plugin_page('basket', '', 'plugin_basket_list', 0);
	register_plugin_page('basket', 'update', 'plugin_basket_update', 0);
	register_filter('xfields', 'basket', new BasketXFieldsFilter);
	register_filter('feedback', 'basket', new BasketFeedbackFilter);
} else {
	//print "Basket error: XFields and Feedback plugins must be activated";
}

// Perform replacements while showing news
class BasketNewsFilter extends NewsFilter
{

	// Show news call :: processor (call after all processing is finished and before show)
	public function showNews($newsID, $SQLnews, &$tvars, $mode = [])
	{

		global $DSlist;
		// Определяем - работаем ли мы внутри строк таблиц
		if (!pluginGetVariable('basket', 'news_flag')) {
			$tvars['regx']['#\[basket\](.*?)\[\/basket\]#is'] = '';

			return;
		}
		// Работаем. Определяем режим работы - по всем строкам или по условию "поле из xfields не равно нулю"
		if (pluginGetVariable('basket', 'news_activated')) {
			if (!$SQLnews['xfields_' . pluginGetVariable('basket', 'news_xfield')]) {
				$tvars['regx']['#\[basket\](.*?)\[\/basket\]#is'] = '';

				return;
			}
		}
		$tvars['regx']['#\[basket\](.*?)\[\/basket\]#is'] = '$1';
		$tvars['vars']['basket_link'] = generatePluginLink('basket', 'add', array('ds' => $DSlist['news'], 'id' => $SQLnews['id']), array(), false, true);
	}
}

register_filter('news', 'basket', new BasketNewsFilter);
//
// Вызов обработчика
add_act('index', 'plugin_basket_total');

<?php

function addStatics($mode = [])
{
    global $mysql, $lang, $userROW, $parse, $PFILTERS, $config, $catz, $catmap;

    $perm = checkPermission(['plugin' => '#admin', 'item' => 'static'], null, ['modify', 'view', 'template', 'template.main', 'html', 'publish', 'unpublish']);

    if (!$perm['modify']) {
        msg(['type' => 'error', 'text' => $lang['perm.denied']], 1, 1);

        return 0;
    }

    if (isset($_REQUEST['flag_published']) && isset($_REQUEST['flag_published'])) {
        if (!$perm['publish']) {
            msgSticker([[$lang['perm.denied'], 'title', 1], [$lang['perm.publish'], '', 1]], 'error', 1);

            return 0;
        }
    }

    if ((!isset($mode['no.token']) || (!$mode['no.token'])) && ((!isset($_REQUEST['token'])) || ($_REQUEST['token'] != genUToken('admin.static')))) {
        msg(['type' => 'error', 'text' => $lang['error.security.token'], 'info' => $lang['error.security.token#desc']]);

        return 0;
    }
	
    $title = $_REQUEST['title'];
    $content = $_REQUEST['content'];
    $content = str_replace("\r\n", "\n", $content);

    $alt_name = strtolower($parse->translit(trim($_REQUEST['alt_name']), 1, 1));
	
    if ($alt_name) {
        if (is_array($mysql->record('select id from '.prefix.'_static where alt_name = '.db_squote($alt_name).' limit 1'))) {
            msg(['type' => 'error', 'text' => $lang['msge_alt_name'], 'info' => $lang['msgi_alt_name']]);

            return 0;
        }
        $SQL['alt_name'] = $alt_name;
    } else {
        $alt_name = strtolower($parse->translit(trim($title), 1));
        $i = '';
        while (is_array($mysql->record('select id from '.prefix.'_static where alt_name = '.db_squote($alt_name.$i).' limit 1'))) {
            $i++;
        }
        $SQL['alt_name'] = $alt_name.$i;
    }
	
	$SQL['title'] = $title;
    $SQL['content'] = $content;
    $SQL['approve'] = intval($_REQUEST['flag_published']);
	
    $vnames = [];
    $vparams = [];
    foreach ($SQL as $k => $v) {
        $vnames[] = $k;
        $vparams[] = db_squote($v);
    }

    $mysql->query('insert into '.prefix.'_static (postdate, '.implode(',', $vnames).') values (unix_timestamp(now()), '.implode(',', $vparams).')');
	
}
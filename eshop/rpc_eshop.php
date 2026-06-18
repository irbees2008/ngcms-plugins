<?php

if (!defined('NGCMS')) {
    exit('HAL');
}

include_once(__DIR__.'/functions.php');

rpcRegisterFunction('eshop_linked_products', 'linked_prd');
rpcRegisterFunction('eshop_change_img_pos', 'change_img_pos');
rpcRegisterFunction('eshop_change_variant', 'change_variant');

rpcRegisterFunction('eshop_compare', 'compare_prd');
rpcRegisterFunction('eshop_viewed', 'viewed_prd');

rpcRegisterFunction('eshop_likes_result', 'likes_result');

rpcRegisterFunction('eshop_comments_add', 'comments_add');
rpcRegisterFunction('eshop_comments_show', 'comments_show');

rpcRegisterFunction('eshop_ebasket_manage', 'ebasket_rpc_manage');

rpcRegisterFunction('eshop_amain', 'main_prd');

function linked_prd($params)
{
    global $mysql;

    $req = urldecode(filter_var($_REQUEST["q"], FILTER_SANITIZE_STRING));
    $mode = filter_var($_REQUEST["mode"], FILTER_SANITIZE_STRING);
    $id = filter_var($_REQUEST["id"], FILTER_SANITIZE_STRING);

    $conditions = array();
    if ($req) {
        array_push($conditions, "p.name LIKE '%".$req."%' ");
    }

    if ($mode == "edit") {
        array_push($conditions, "p.id != '".$id."' ");
    }

    //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
	$fSort = " ORDER BY p.id DESC";
    $sqlQPart = "FROM ".prefix."_eshop_products p LEFT JOIN ".prefix."_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN ".prefix."_eshop_categories c ON pc.category_id = c.id LEFT JOIN ".prefix."_eshop_images i ON i.product_id = p.id ".(count($conditions) ? "WHERE ".implode(" AND ", $conditions) : '').$fSort;
    $sqlQ = "SELECT p.id AS id, p.code AS code, p.name AS name, p.active AS active, p.featured AS featured, p.position AS position, c.name AS category, i.filepath AS image_filepath ".$sqlQPart;

    foreach ($mysql->select($sqlQ) as $row) {

        $tEntry[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'image_filepath' => $row['image_filepath'],
            'code' => $row['code'],
            'category' => $row['category'],
        );
    }

    return $tEntry;
}

function change_img_pos($params)
{
    global $userROW, $mysql;

    $img_id = (int)$params['img_id'];
    $position = (int)$params['position'];

    if ($userROW['status'] < 3) {
        $img_row = $mysql->record("select * from ".prefix."_eshop_images where id = ".db_squote($img_id)."");
        if (isset($img_row)) {
            $mysql->query("update ".prefix."_eshop_images set position = ".db_squote($img_row['position'])." where (product_id = ".db_squote($img_row['product_id']).") and (position = ".db_squote($position).") ");
            $mysql->query("update ".prefix."_eshop_images set position = ".db_squote($position)." where (id = ".db_squote($img_row['id']).") ");

            return array(
                'status' => 1,
                'errorCode' => 0,
                'data' => 'Images swaped success',
                'update' => '',
            );
        }
    }

    return array(
        'status' => 0,
        'errorCode' => 0,
        'data' => 'OK, '.var_export($params, true),
    );
}

function change_variant($params)
{
    global $userROW, $mysql;

    $qid = (int)$params['id'];
    $mode = filter_var($params['mode'], FILTER_SANITIZE_STRING);
    $value = (float)filter_var($params['value'], FILTER_SANITIZE_STRING);

    if ($userROW['status'] < 3) {

        $SQL = array();
        switch ($mode) {
            case 'price':
                $SQL['price'] = $value;
                break;
            case 'compare_price':
                $SQL['compare_price'] = $value;
                break;
            case 'amount':
                if ($value == '') {
                    $value = 'NULL';
                }
                $SQL['amount'] = $value;
                break;
        }

        if (isset($qid)) {
            $vnames = array();
            foreach ($SQL as $k => $v) {
                if ($k == 'amount') {
                    $vnames[] = $k.' = '.$v;
                } else {
                    $vnames[] = $k.' = '.db_squote($v);
                }
            }
            $mysql->query('UPDATE '.prefix.'_eshop_variants SET '.implode(', ', $vnames).' WHERE id = \''.intval($qid).'\'  ');

            return array(
                'status' => 1,
                'errorCode' => 0,
                'data' => 'Variant success changed',
                'update' => '',
            );
        }

    }

    return array(
        'status' => 0,
        'errorCode' => 0,
        'data' => 'OK, '.var_export($params, true),
    );
}

function compare_prd($params)
{
    global $twig, $userROW, $mysql;

    $id = (int)$params['id'];

    if ($id < 1) {
        return array(
            'status' => 0,
            'errorCode' => 2,
            'errorText' => 'ID should be positive',
        );
    }

    switch ($params['action']) {
        // **** ADD NEW ITEM INTO compare ****
        case 'add':

            // Check if now we're logged in and earlier we started filling ebasket before logging in
            if (is_array($userROW)) {
                $mysql->query("update ".prefix."_eshop_compare set user_id = ".db_squote($userROW['id'])." where (user_id = 0) and (cookie = ".db_squote($_COOKIE['ngTrackID']).")");
            }

            $mysql->query("insert into ".prefix."_eshop_compare (user_id, cookie, linked_fld) values (".(is_array($userROW) ? db_squote($userROW['id']) : 0).", ".db_squote($_COOKIE['ngTrackID']).", ".db_squote($id).")");

            // ======== Prepare update of totals informer ========
            $filter = array();
            if (is_array($userROW)) {
                $filter [] = '(user_id = '.db_squote($userROW['id']).')';
            }
            if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
                $filter [] = '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
            }

            $tCount = 0;

            if (count($filter) && is_array($res = $mysql->record("select count(*) as count from ".prefix."_eshop_compare where ".join(" or ", $filter), 1))) {
                $tCount = $res['count'];
            }

            $tVars = array(
                'count' => $tCount,
            );

			$tpath = locatePluginTemplates(array('compare_block_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
			
            $xt = $twig->loadTemplate($tpath['compare_block_eshop'].'compare_block_eshop.tpl');

            return array(
                'status' => 1,
                'errorCode' => 0,
                'data' => 'Товар добавлен к сравнению',
                'update' => $xt->render($tVars),
            );

            break;
        case 'remove':

            // Check if now we're logged in and earlier we started filling ebasket before logging in
            if (is_array($userROW)) {
                $mysql->query("update ".prefix."_eshop_compare set user_id = ".db_squote($userROW['id'])." where (user_id = 0) and (cookie = ".db_squote($_COOKIE['ngTrackID']).")");
            }

            // ======== Prepare update of totals informer ========
            $filter = array();
            if (is_array($userROW)) {
                $filter [] = '(user_id = '.db_squote($userROW['id']).')';
            }
            if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
                $filter [] = '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
            }

            $mysql->query("delete from ".prefix."_eshop_compare where (".join(" or ", $filter).") and linked_fld = ".db_squote($id)." ");

            $tCount = 0;

            if (count($filter) && is_array($res = $mysql->record("select count(*) as count from ".prefix."_eshop_compare where ".join(" or ", $filter), 1))) {
                $tCount = $res['count'];
            }

            $tVars = array(
                'count' => $tCount,
            );

			$tpath = locatePluginTemplates(array('compare_block_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
			
            $xt = $twig->loadTemplate($tpath['compare_block_eshop'].'compare_block_eshop.tpl');

            return array(
                'status' => 1,
                'errorCode' => 0,
                'data' => 'Товар удален из сравнения',
                'update' => $xt->render($tVars),
            );

            break;
    }

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'OK, '.var_export($params, true),
    );
}

function viewed_prd($params)
{
    global $twig, $SYSTEM_FLAGS, $config, $mysql;

    $page_stack = filter_var($params['page_stack'], FILTER_SANITIZE_STRING);

    switch ($params['action']) {
        // **** ADD NEW ITEM INTO compare ****
        case 'show':

            if (!empty($page_stack)) {

                $conditions = array();
                array_push($conditions, "p.active = 1 ");
                array_push($conditions, "p.id IN (".$page_stack.") ");

                //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
				$fSort = " ORDER BY p.id DESC";
                $sqlQPart = "FROM ".prefix."_eshop_products p LEFT JOIN ".prefix."_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN ".prefix."_eshop_categories c ON pc.category_id = c.id ".(count($conditions) ? "WHERE ".implode(" AND ", $conditions) : '').$fSort;
                $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url as curl, c.name AS category ".$sqlQPart;

                $cmp_array = array();
                foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
                    $cmp_array[] = $cmp_row['linked_fld'];
                }

                foreach ($mysql->select($sqlQ) as $row) {
                    $fulllink = checkLinkAvailable('eshop', 'show') ?
                        generateLink('eshop', 'show', array('alt' => $row['url'])) :
                        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
                    $catlink = checkLinkAvailable('eshop', '') ?
                        generateLink('eshop', '', array('alt' => $row['curl'])) :
                        generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));

                    $cmp_flag = in_array($row['id'], $cmp_array);

                    $entries[$row['id']] = array(
                        'id' => $row['id'],
                        'code' => $row['code'],
                        'name' => $row['name'],

                        'annotation' => $row['annotation'],
                        'body' => $row['body'],

                        'active' => $row['active'],
                        'featured' => $row['featured'],

                        'meta_title' => $row['meta_title'],
                        'meta_keywords' => $row['meta_keywords'],
                        'meta_description' => $row['meta_description'],

                        'fulllink' => $fulllink,

                        'date' => (empty($row['date'])) ? '' : $row['date'],
                        'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],

                        'views' => $row['views'],

                        'cat_name' => $row['category'],
                        'cid' => $row['cid'],
                        'catlink' => $catlink,

                        'compare' => $cmp_flag,

                        'home' => home,
                        'tpl_url' => home.'/templates/'.$config['theme'],
                    );

                }

            }

            $entries_array_ids = array_keys($entries);

            if (isset($entries_array_ids) && !empty($entries_array_ids)) {

                $entries_string_ids = implode(',', $entries_array_ids);

                foreach ($mysql->select('SELECT * FROM '.prefix.'_eshop_images i WHERE i.product_id IN ('.$entries_string_ids.') ORDER BY i.position, i.id') as $irow) {
                    $entries[$irow['product_id']]['images'][] = $irow;
                }

                foreach ($mysql->select('SELECT * FROM '.prefix.'_eshop_variants v WHERE v.product_id IN ('.$entries_string_ids.') ORDER BY v.position, v.id') as $vrow) {
                    $entries[$vrow['product_id']]['variants'][] = $vrow;
                }

            }

            $tVars = array(
                'info' => isset($info) ? $info : '',
                'entries' => isset($entries) ? $entries : '',
                'tpl_url' => home.'/templates/'.$config['theme'],
                'tpl_home' => admin_url,
            );

			$tpath = locatePluginTemplates(array('viewed_block_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
			
            $xt = $twig->loadTemplate($tpath['viewed_block_eshop'].'viewed_block_eshop.tpl');

            return array(
                'status' => 1,
                'errorCode' => 0,
                'data' => 'Товар добавлен к сравнению',
                'update' => $xt->render($tVars),
            );

            break;
    }

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'OK, '.var_export($params, true),
    );
}

function likes_result($params)
{
    global $twig, $userROW, $mysql;

    $id = filter_var($params['id'], FILTER_SANITIZE_STRING);

    switch ($params['action']) {
        // **** ADD NEW ITEM INTO compare ****
        case 'do_like':

            if (!empty($id)) {

                // Check if now we're logged in and earlier we started filling ebasket before logging in
                if (is_array($userROW)) {
                    $mysql->query("update ".prefix."_eshop_products_likes set user_id = ".db_squote($userROW['id'])." where (user_id = 0) and (cookie = ".db_squote($_COOKIE['ngTrackID']).")");
                }

                $filter = array();
                if (is_array($userROW)) {
                    $filter [] = '(user_id = '.db_squote($userROW['id']).')';
                }
                if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
                    $filter [] = '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
                }

                $tCount = 0;

                if (count($filter) && is_array($check_like = $mysql->record("select count(*) as count from ".prefix."_eshop_products_likes where (".join(" or ", $filter).") AND product_id = ".db_squote($id)." ", 1))) {
                    $tCount = $check_like['count'];
                }

                if ($tCount == 0) {

                    $mysql->query("insert into ".prefix."_eshop_products_likes (user_id, cookie, product_id) values (".(is_array($userROW) ? db_squote($userROW['id']) : 0).", ".db_squote($_COOKIE['ngTrackID']).", ".db_squote($id).")");

                    $mysql->query("update ".prefix."_eshop_products set likes = likes + 1 where id = ".db_squote($id)." ");

                } else {

                    $mysql->query("delete from ".prefix."_eshop_products_likes where (".join(" or ", $filter).") AND product_id = ".db_squote($id)." ");

                    $mysql->query("update ".prefix."_eshop_products set likes = likes - 1 where id = ".db_squote($id)." ");
                }

                $likes = $mysql->record("SELECT COUNT(*) as count FROM ".prefix."_eshop_products_likes l WHERE l.product_id='".$id."'");

                $likes_tVars = array(
                    'count' => $likes['count'],
                    'id' => $id,
                );

				$tpath = locatePluginTemplates(array('likes_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
				
                $xt = $twig->loadTemplate($tpath['likes_eshop'].'likes_eshop.tpl');

                return array(
                    'status' => 1,
                    'errorCode' => 0,
                    'data' => 'Likes updated',
                    'update' => $xt->render($likes_tVars),
                );


            }

            break;
    }

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'OK, '.var_export($params, true),
    );
}

function comments_add($params)
{
    global $twig, $ip, $config, $userROW, $mysql;

    if (isset($userROW)) {
        $SQL['name'] = $userROW['name'];
        $SQL['author'] = $userROW['name'];
        $SQL['author_id'] = $userROW['id'];
        $SQL['mail'] = $userROW['mail'];
        $is_member = 1;
    } else {
        $SQL['name'] = secure_html(trim($params['comment_author']));
        $SQL['author'] = secure_html(trim($params['comment_author']));
        $SQL['author_id'] = 0;
        $SQL['mail'] = secure_html(trim($params['comment_email']));
        $is_member = 0;
    }

    $SQL['text'] = secure_html(trim($params['comment_text']));
    $SQL['product_id'] = $params['product_id'];
    $SQL['postdate'] = time() + ($config['date_adjust'] * 60);

    $SQL['text'] = str_replace("\r\n", "<br />", $SQL['text']);
    $SQL['ip'] = $ip;
    $SQL['reg'] = ($is_member) ? '1' : '0';

    $approve_comments = pluginGetVariable('eshop', 'approve_comments');
    if ($approve_comments == "1") {
        $SQL['status'] = '0';
    } else {
        $SQL['status'] = '1';
    }

    if (empty($SQL['name'])) {
        $error_text[] = 'Вы не ввели имя!';
    }

    if (empty($SQL['mail'])) {
        $error_text[] = 'Вы не ввели email!';
    }

    if (empty($SQL['text'])) {
        $error_text[] = 'Вы не написали комментарий!';
    }

    if (empty($error_text)) {

        // Create comment
        $vnames = array();
        $vparams = array();
        foreach ($SQL as $k => $v) {
            $vnames[] = $k;
            $vparams[] = db_squote($v);
        }

        $mysql->query("insert into ".prefix."_eshop_products_comments (".implode(",", $vnames).") values (".implode(",",$vparams).")");

        // Update comment counter
        $mysql->query("update ".prefix."_eshop_products set comments = comments + 1 where id = ".db_squote($SQL['product_id'])." ");

		$notify_tpath = locatePluginTemplates(array('mail/lfeedback_comment'),'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
		
        $notify_xt = $twig->loadTemplate($notify_tpath['mail/lfeedback_comment'].'mail/'.'lfeedback_comment.tpl');

        $prd = $mysql->record("SELECT * FROM ".prefix."_eshop_products WHERE id=".db_squote($SQL['product_id'])." ");

        $fulllink = checkLinkAvailable('eshop', 'show') ?
            generateLink('eshop', 'show', array('alt' => $prd['url'])) :
            generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $prd['url']));
        $prd['fulllink'] = $fulllink;

        $pVars = array(
            'vproduct' => $prd,
            'vnames' => $SQL,
        );

        $mailBody = $notify_xt->render($pVars);
        $mailSubject = "Новый комментарий с сайта";
        $mailTo = pluginGetVariable('eshop', 'email_notify_comments');
        $mail_from = pluginGetVariable('eshop', 'email_notify_back');

        if ($mail_from == "") {
            $mail_from = false;
        }

        if ($mailTo != "") {
            sendEmailMessage($mailTo, $mailSubject, $mailBody, $filename = false, $mail_from, $ctype = 'text/html');
        }

        $results = array(
            'eshop_comments' => 100,
            'eshop_comments_text' => 'Комментарий успешно добавлен!',
            'eshop_comments_show' => comments_show_handler($params),
        );

    } else {

        $results = array(
            'eshop_comments' => 2,
            'eshop_comments_text' => implode('<br />', $error_text),
            'eshop_comments_show' => comments_show_handler($params),
        );

    }

    return array('status' => 1, 'errorCode' => 0, 'data' => $results);

}

function comments_show($params)
{

    $results = array(
        'eshop_comments' => 100,
        'eshop_comments_text' => 'Комментарии',
        'eshop_comments_show' => comments_show_handler($params),
    );

    return array('status' => 1, 'errorCode' => 0, 'data' => $results);

}

function comments_show_handler($params)
{
    global $config, $parse, $TemplateCache, $twig, $mysql;

    // Preload template configuration variables
    templateLoadVariables();

    // Use default <noavatar> file
    // - Check if noavatar is defined on template level
    $tplVars = $TemplateCache['site']['#variables'];
    $noAvatarURL = (isset($tplVars['configuration']) && is_array($tplVars['configuration']) && isset($tplVars['configuration']['noAvatarImage']) && $tplVars['configuration']['noAvatarImage']) ? (tpl_url."/".$tplVars['configuration']['noAvatarImage']) : (avatars_url."/noavatar.gif");

	$tpath = locatePluginTemplates(array('comments.show_eshop'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
	
    $xt = $twig->loadTemplate($tpath['comments.show_eshop'].'comments.show_eshop.tpl');

    $conditions = array();
    if ($params['product_id']) {
        array_push($conditions, "c.product_id = ".db_squote($params['product_id']));
    }

    $approve_comments = pluginGetVariable('eshop', 'approve_comments');
    if ($approve_comments == "1") {
        array_push($conditions, "c.status = 1");
    }

    $sort_comments = pluginGetVariable('eshop', 'sort_comments');
    if ($sort_comments == "0") {
        $dSort = " ASC";
    } else {
        $dSort = " DESC";
    }

    $fSort = "ORDER BY c.postdate".$dSort;

    $sqlQPart = "from ".prefix."_eshop_products_comments c LEFT JOIN ".prefix."_users u ON c.author_id = u.id ".(count($conditions) ? "where ".implode(" AND ", $conditions) : '').' '.$fSort;
    $sqlQ = "select *, c.id as cid, u.id as uid, u.name as uname, c.name as name ".$sqlQPart;

    $output = '';
    foreach ($mysql->select($sqlQ) as $row) {
        // Add [hide] tag processing
        $text = $row['text'];

        if ($config['use_bbcodes']) {
            $text = $parse->bbcodes($text);
        }
        if ($config['use_htmlformatter']) {
            $text = $parse->htmlformatter($text);
        }
        if ($config['use_smilies']) {
            $text = $parse->smilies($text);
        }

        if ($config['use_avatars']) {
            if ($row['avatar']) {
                $avatar = avatars_url."/".$row['avatar'];
            } else {
                $avatar = $noAvatarURL;
            }
        } else {
            $avatar = '';
        }

        $Entries[] = array(
            'id' => $row['cid'],
            'mail' => $row['mail'],
            'author' => $row['name'],
            'date' => $row['postdate'],
            'profile_link' => checkLinkAvailable('uprofile', 'show') ?
                generateLink('uprofile', 'show', array('name' => $row['author'], 'id' => $row['author_id'])) :
                generateLink('core', 'plugin', array('plugin' => 'uprofile', 'handler' => 'show'), array('id' => $row['author_id'])),
            'avatar' => $avatar,
            'name' => $row['uname'],
            'commenttext' => $text,
        );
    }

    $tVars = array(
        'entries' => isset($Entries) ? $Entries : '',
        'tpl_url' => home.'/templates/'.$config['theme'],
        'tpl_home' => admin_url,
    );

    $output .= $xt->render($tVars);

    return $output;
}

function ebasket_add_item($linked_ds, $linked_id, $title, $price, $count, $xfld = array())
{
    global $mysql, $userROW, $twig, $config;

    // Check if now we're logged in and earlier we started filling ebasket before logging in
    if (is_array($userROW)) {
        $mysql->query("update ".prefix."_eshop_ebasket set user_id = ".db_squote($userROW['id'])." where (user_id = 0) and (cookie = ".db_squote($_COOKIE['ngTrackID']).")");
    }

    $current_time = time() + ($config['date_adjust'] * 60);

    $mysql->query("insert into ".prefix."_eshop_ebasket (user_id, cookie, dt, linked_ds, linked_id, title, linked_fld, price, count) values (".(is_array($userROW) ? db_squote($userROW['id']) : 0).", ".db_squote($_COOKIE['ngTrackID']).", ".db_squote($current_time).", ".db_squote($linked_ds).", ".db_squote($linked_id).", ".db_squote($title).", ".db_squote(serialize($xfld)).", ".db_squote($price).", ".db_squote($count).") on duplicate key update price=".db_squote($price).", count = count+".db_squote($count));

    // ======== Prepare update of totals informer ========
    $filter = array();
    if (is_array($userROW)) {
        $filter [] = '(user_id = '.db_squote($userROW['id']).')';
    }
    if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
        $filter [] = '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
    }

    $tCount = 0;
    $tPrice = 0;

    if (count($filter) && is_array($res = $mysql->record("select count(*) as count, sum(price*count) as price from ".prefix."_eshop_ebasket where ".join(" or ", $filter), 1))) {
        $tCount = $res['count'];
        $tPrice = $res['price'];
    }

    $basket_link = checkLinkAvailable('eshop', 'ebasket_list') ?
        generateLink('eshop', 'ebasket_list', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'ebasket_list'), array());

    $tVars = array(
        'count' => $tCount,
        'price' => $tPrice,
        'basket_link' => $basket_link,
    );

	$tpath = locatePluginTemplates(array('ebasket/total'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));

    $xt = $twig->loadTemplate($tpath['ebasket/total'].'ebasket/'.'total.tpl');

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'Товар добавлен в корзину',
        'update' => $xt->render($tVars),
    );
}

function ebasket_add_fast_order($linked_ds, $linked_id, $title, $price, $count, $type, $order, $xfld = array())
{
    global $mysql, $userROW, $twig, $config, $ip;

    $SQL['name'] = $order['name'];
    $SQL['email'] = $order['email'];
    $SQL['phone'] = $order['phone'];
    $SQL['address'] = $order['address'];
    $SQL['comment'] = "";

    $SQL['dt'] = time() + ($config['date_adjust'] * 60);
    $SQL['ip'] = $ip;

    $SQL['type'] = $type;

    $SQL['paid'] = 0;
    $SQL['total_price'] = round($price * $count, 2);

    if (isset($userROW)) {
        $SQL['author_id'] = $userROW['id'];
    }

    $SQL['uniqid'] = substr(str_shuffle(MD5(microtime())), 0, 10);

    $vnames = array();
    foreach ($SQL as $k => $v) {
        $vnames[] = $k.' = '.db_squote($v);
    }
    $mysql->query('INSERT INTO '.prefix.'_eshop_orders SET '.implode(', ', $vnames).' ');
    $qid = lastid('eshop_orders');

    $mysql->query("INSERT INTO ".prefix."_eshop_order_basket (`order_id`, `linked_id`, `title`, `count`, `price`, `linked_fld`) VALUES (".db_squote($qid).", ".db_squote($linked_id).", ".db_squote($title).", ".db_squote($count).", ".db_squote($price).", ".db_squote(serialize($xfld))." )");

    $v_id = $xfld['item']['v_id'];
    $variant = $mysql->record("SELECT amount FROM ".prefix."_eshop_variants where id = '".intval($v_id)."'");
    $current_amount = $variant['amount'];
    $r_count = $count;

    if ($current_amount != null) {
        if ($current_amount - $r_count > 0) {
            $mysql->query("update ".prefix."_eshop_variants set amount = amount - ".intval($r_count)." where id = ".intval($v_id));
        } else {
            $mysql->query("update ".prefix."_eshop_variants set amount = 0 where id = ".intval($v_id));
        }
    }

    // mail notify

    $filter = array();
    if ($qid) {
        $filter [] = '(order_id = '.db_squote($qid).')';
    }

    $total = 0;
    foreach ($mysql->select("select * from ".prefix."_eshop_order_basket where ".join(" or ", $filter), 1) as $rec) {
        $total += round($rec['price'] * $rec['count'], 2);

        $rec['sum'] = sprintf('%9.2f', round($rec['price'] * $rec['count'], 2));
        $rec['xfields'] = unserialize($rec['linked_fld']);
        unset($rec['linked_fld']);
        $basket [] = $rec;
    }

	$notify_tpath = locatePluginTemplates(array('mail/lfeedback'),'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop', 'localskin'));
	
    $notify_xt = $twig->loadTemplate($notify_tpath['mail/lfeedback'].'mail/'.'lfeedback.tpl');

    $pVars = array(
        'recs' => count($basket),
        'entries' => $basket,
        'total' => sprintf('%9.2f', $total),
        'vnames' => $SQL,
    );

    $mailBody = $notify_xt->render($pVars);
    $mailSubject = "Новый заказ с сайта";
    $mailTo = pluginGetVariable('eshop', 'email_notify_orders');
    $mail_from = pluginGetVariable('eshop', 'email_notify_back');

    if ($mail_from == "") {
        $mail_from = false;
    }

    if ($mailTo != "") {
        sendEmailMessage($mailTo, $mailSubject, $mailBody, $filename = false, $mail_from, $ctype = 'text/html');
    }

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'Товар добавлен в корзину',
        'update' => '',
    );
}

function basket_update_item_count($id, $linked_ds, $linked_id, $count)
{
    global $mysql;

    $mysql->query("update ".prefix."_eshop_ebasket set count = ".db_squote($count)." where (linked_id = ".db_squote($linked_id).") and (linked_ds = ".db_squote($linked_ds).") and (id = ".db_squote($id).")");

    return array('status' => 1, 'errorCode' => 0, 'data' => 'Обновлено количество товаров');
}

function basket_delete_item($id, $linked_ds, $linked_id)
{
    global $mysql;

    $mysql->query("delete from ".prefix."_eshop_ebasket where id = ".db_squote($id)." and linked_id = ".db_squote($linked_id)." and linked_ds = ".db_squote($linked_ds)." limit 1");

    return array('status' => 1, 'errorCode' => 0, 'data' => 'Элемент удален');
}

function basket_update()
{
    global $mysql, $userROW, $twig;

    // ======== Prepare update of totals informer ========
    $filter = array();
    if (is_array($userROW)) {
        $filter [] = '(user_id = '.db_squote($userROW['id']).')';
    }
    if (isset($_COOKIE['ngTrackID']) && ($_COOKIE['ngTrackID'] != '')) {
        $filter [] = '(cookie = '.db_squote($_COOKIE['ngTrackID']).')';
    }

    $tCount = 0;
    $tPrice = 0;

    if (count($filter) && is_array($res = $mysql->record("select count(*) as count, sum(price*count) as price from ".prefix."_eshop_ebasket where ".join(" or ", $filter), 1))) {
        $tCount = $res['count'];
        $tPrice = $res['price'];
    }

    $basket_link = checkLinkAvailable('eshop', 'ebasket_list') ?
        generateLink('eshop', 'ebasket_list', array()) :
        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'ebasket_list'), array());

    $tVars = array(
        'count' => $tCount,
        'price' => $tPrice,
        'basket_link' => $basket_link,
    );

	$tpath = locatePluginTemplates(array('ebasket/total'), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));

    $xt = $twig->loadTemplate($tpath['ebasket/total'].'ebasket/'.'total.tpl');

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'Корзина обновлена',
        'update' => $xt->render($tVars),
    );
}

function ebasket_rpc_manage($params)
{
    global $DSlist, $mysql;

    LoadPluginLibrary('xfields', 'common');

    if (!is_array($params) || !isset($params['action'])) {
        return array(
            'status' => 0,
            'errorCode' => 1,
            'errorText' => 'Параметр действия не задан',
        );
    }

    switch ($params['action']) {
        // **** ADD NEW ITEM INTO ebasket ****
        case 'add':
            $linked_ds = (int)$params['ds'];
            $linked_id = (int)$params['id'];
            $count = (int)$params['count'];
            $variant_id = (int)$params['variant_id'];

            // Check available DataSources
            if (!($DSlist['news'] == $linked_ds)) {
                return array(
                    'status' => 0,
                    'errorCode' => 2,
                    'errorText' => 'Плагин поддерживает работу только с элементами продукции',
                );
            }

            // Check available DataSources
            if ($count < 1) {
                return array(
                    'status' => 0,
                    'errorCode' => 3,
                    'errorText' => 'Количество продукции должно быть положительным',
                );
            }

            // Check if linked item is available
            switch ($linked_ds) {
                case $DSlist['news']:

                    $conditions = array();
                    if ($linked_id) {
                        array_push($conditions, "p.id = ".db_squote($linked_id));
                    }

                    if ($variant_id != 0) {
                        array_push($conditions, "v.id = ".db_squote($variant_id));
                    }

                    //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
					$fSort = " ORDER BY p.id DESC";
                    $sqlQPart = "FROM ".prefix."_eshop_products p LEFT JOIN ".prefix."_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN ".prefix."_eshop_categories c ON pc.category_id = c.id LEFT JOIN (SELECT * FROM ".prefix."_eshop_images ORDER BY position, id) i ON i.product_id = p.id LEFT JOIN ".prefix."_eshop_variants v ON p.id = v.product_id ".(count($conditions) ? "WHERE ".implode(" AND ", $conditions) : '').$fSort;
                    $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.active AS active, p.featured AS featured, p.position AS position, p.position AS position, p.external_id as external_id, c.url as curl, c.name AS category, i.filepath AS image_filepath, v.id AS v_id, v.sku AS v_sku, v.name AS v_name, v.amount AS v_amount, v.price AS price, v.compare_price AS compare_price, v.stock AS stock ".$sqlQPart;

                    // Retrieve news record
                    $rec = $mysql->record($sqlQ);

                    if (!is_array($rec)) {
                        return array(
                            'status' => 0,
                            'errorCode' => 4,
                            'errorText' => 'Продукт с ID ('.$linked_id.') не найден',
                        );
                    }

                    if ($rec['v_amount'] != null) {

                        if ($count > $rec['v_amount']) {
                            return array(
                                'status' => 0,
                                'errorCode' => 5,
                                'errorText' => 'Нет требуемого количества продукта с ID ('.$linked_id.'). Можно заказать только '.$rec['v_amount'].'',
                            );
                        }

                    }

                    $btitle = $rec['name'];
                    $price = $rec['price'];

                    $view_link = checkLinkAvailable('eshop', 'show') ?
                        generateLink('eshop', 'show', array('alt' => $rec['url'])) :
                        generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $rec['url']));

                    $rec['view_link'] = $view_link;

                    // Add data into basked
                    return ebasket_add_item($linked_ds, $linked_id, $btitle, $price, $count, array('item' => $rec));

                    break;

            }
            break;
        case 'update':
            return basket_update();
            break;
        case 'update_count':
            $id = (int)$params['id'];
            $linked_ds = (int)$params['linked_ds'];
            $linked_id = (int)$params['linked_id'];
            $count = (int)$params['count'];

            return basket_update_item_count($id, $linked_ds, $linked_id, $count);
            break;
        case 'delete':
            $id = (int)$params['id'];
            $linked_ds = (int)$params['linked_ds'];
            $linked_id = (int)$params['linked_id'];

            return basket_delete_item($id, $linked_ds, $linked_id);
            break;
        case 'add_fast':
            $linked_ds = (int)$params['ds'];
            $linked_id = (int)$params['id'];
            $count = (int)$params['count'];
            $variant_id = (int)$params['variant_id'];
            $type = (int)$params['type'];

            $order['name'] = filter_var($params['name'], FILTER_SANITIZE_STRING);
            if (empty($order['name'])) {
                return array(
                    'status' => 0,
                    'errorCode' => 6,
                    'errorText' => 'Не заполнено поле имя',
                );
            }

            $order['email'] = "";

            $order['phone'] = filter_var($params['phone'], FILTER_SANITIZE_STRING);
            if (empty($order['phone'])) {
                return array(
                    'status' => 0,
                    'errorCode' => 7,
                    'errorText' => 'Не заполнено поле телефон',
                );
            }

            $order['address'] = filter_var($params['address'], FILTER_SANITIZE_STRING);
            if (empty($order['address'])) {
                return array(
                    'status' => 0,
                    'errorCode' => 8,
                    'errorText' => 'Не заполнено поле адрес',
                );
            }

            // Check available DataSources
            if (!($DSlist['news'] == $linked_ds)) {
                return array(
                    'status' => 0,
                    'errorCode' => 2,
                    'errorText' => 'Плагин поддерживает работу только с элементами продукции',
                );
            }

            // Check available DataSources
            if ($count < 1) {
                $count = 1;
            }

            $conditions = array();
            if ($linked_id) {
                array_push($conditions, "p.id = ".db_squote($linked_id));
            }

            if ($variant_id != 0) {
                array_push($conditions, "v.id = ".db_squote($variant_id));
            }

            //$fSort = " GROUP BY p.id ORDER BY p.id DESC";
			$fSort = " ORDER BY p.id DESC";
            $sqlQPart = "FROM ".prefix."_eshop_products p LEFT JOIN ".prefix."_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN ".prefix."_eshop_categories c ON pc.category_id = c.id LEFT JOIN (SELECT * FROM ".prefix."_eshop_images ORDER BY position, id) i ON i.product_id = p.id LEFT JOIN ".prefix."_eshop_variants v ON p.id = v.product_id ".(count($conditions) ? "WHERE ".implode(" AND ", $conditions) : '').$fSort;
            $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.active AS active, p.featured AS featured, p.position AS position, c.url as curl, c.name AS category, i.filepath AS image_filepath, v.id AS v_id, v.sku AS v_sku, v.name AS v_name, v.amount AS v_amount, v.price AS price, v.compare_price AS compare_price, v.stock AS stock ".$sqlQPart;

            // Retrieve news record
            $rec = $mysql->record($sqlQ);
            if (!is_array($rec)) {
                return array(
                    'status' => 0,
                    'errorCode' => 4,
                    'errorText' => 'Продукт с ID ('.$linked_id.') не найден',
                );
            }

            if ($rec['v_amount'] != null) {

                if ($count > $rec['v_amount']) {
                    return array(
                        'status' => 0,
                        'errorCode' => 5,
                        'errorText' => 'Нет требуемого количества продукта с ID ('.$linked_id.'). Можно заказать только '.$rec['v_amount'].'',
                    );
                }

            }

            $btitle = $rec['name'];
            $price = $rec['price'];

            $view_link = checkLinkAvailable('eshop', 'show') ?
                generateLink('eshop', 'show', array('alt' => $rec['url'])) :
                generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $rec['url']));

            $rec['view_link'] = $view_link;

            // Add data into basked
            return ebasket_add_fast_order(
                $linked_ds,
                $linked_id,
                $btitle,
                $price,
                $count,
                $type,
                $order,
                array('item' => $rec)
            );

            break;
    }

    return array(
        'status' => 1,
        'errorCode' => 0,
        'data' => 'OK, '.var_export($params, true),
    );

}

function main_prd($params)
{
    global $twig, $SYSTEM_FLAGS, $config, $mysql;

    $results = array();
	
    $number = $params['number'];
    $mode = $params['mode'];
    $cat = $params['cat'];
    $overrideTemplateName = $params['template'];
    $prd_per_page = $number;
    $page = $params['page'];

    switch ($params['action']) {
        // **** ADD NEW ITEM INTO compare ****
        case 'show':

            $conditions = array();
            if (isset($cat) && !empty($cat)) {
                array_push($conditions, "c.id IN (".$cat.") ");
            }

            array_push($conditions, "p.active = 1");

            if (($number < 1) || ($number > 100)) {
                $number = 5;
            }

            switch ($mode) {
                case 'view':
                    $orderby = " ORDER BY p.view DESC ";
                    break;
                case 'last':
                    $orderby = " ORDER BY p.editdate DESC ";
                    break;
                case 'stocked':
                    array_push($conditions, "p.stocked = 1");
                    $orderby = " ORDER BY p.editdate DESC ";
                    break;
                case 'featured':
                    array_push($conditions, "p.featured = 1");
                    $orderby = " ORDER BY p.editdate DESC ";
                    break;
                case 'rnd':
                    $cacheDisabled = true;
                    $orderby = " ORDER BY RAND() DESC ";
                    break;
                default:
                    $mode = 'last';
                    $orderby = " ORDER BY p.editdate DESC ";
                    break;
            }

            $fSort = " ".$orderby;
            $sqlQPart = "FROM ".prefix."_eshop_products p LEFT JOIN ".prefix."_eshop_products_categories pc ON p.id = pc.product_id LEFT JOIN ".prefix."_eshop_categories c ON pc.category_id = c.id ".(count($conditions) ? "WHERE ".implode(" AND ", $conditions) : '').$fSort;
            $sqlQCount = "SELECT COUNT(p.id) ".$sqlQPart;
            $sqlQ = "SELECT p.id AS id, p.url as url, p.code AS code, p.name AS name, p.annotation AS annotation, p.body AS body, p.active AS active, p.featured AS featured, p.stocked AS stocked, p.position AS position, p.meta_title AS meta_title, p.meta_keywords AS meta_keywords, p.meta_description AS meta_description, p.date AS date, p.editdate AS editdate, p.views AS views, c.id AS cid, c.url as curl, c.name AS category ".$sqlQPart;

            $entries = array();

            $pageNo = intval($page) ? $page : 0;
            if ($pageNo < 1) {
                $pageNo = 1;
            }
            if (!$start_from) {
                $start_from = ($pageNo - 1) * $prd_per_page;
            }

            $count = $mysql->result($sqlQCount);
            $countPages = ceil($count / $prd_per_page);

            $cmp_array = array();
            foreach ($SYSTEM_FLAGS["eshop"]["compare"]["entries"] as $cmp_row) {
                $cmp_array[] = $cmp_row['linked_fld'];
            }

            foreach ($mysql->select($sqlQ.' LIMIT '.$start_from.', '.$prd_per_page) as $row) {
                $fulllink = checkLinkAvailable('eshop', 'show') ?
                    generateLink('eshop', 'show', array('alt' => $row['url'])) :
                    generateLink('core', 'plugin', array('plugin' => 'eshop', 'handler' => 'show'), array('alt' => $row['url']));
                $catlink = checkLinkAvailable('eshop', '') ?
                    generateLink('eshop', '', array('alt' => $row['curl'])) :
                    generateLink('core', 'plugin', array('plugin' => 'eshop'), array('alt' => $row['curl']));

                $cmp_flag = in_array($row['id'], $cmp_array);

                $entries[$row['id']] = array(
                    'id' => $row['id'],
                    'code' => $row['code'],
                    'name' => $row['name'],

                    'annotation' => $row['annotation'],
                    'body' => $row['body'],

                    'active' => $row['active'],
                    'featured' => $row['featured'],

                    'meta_title' => $row['meta_title'],
                    'meta_keywords' => $row['meta_keywords'],
                    'meta_description' => $row['meta_description'],

                    'fulllink' => $fulllink,

                    'date' => (empty($row['date'])) ? '' : $row['date'],
                    'editdate' => (empty($row['editdate'])) ? '' : $row['editdate'],

                    'views' => $row['views'],

                    'cat_name' => $row['category'],
                    'cid' => $row['cid'],
                    'catlink' => $catlink,

                    'compare' => $cmp_flag,

                    'home' => home,
                    'tpl_url' => home.'/templates/'.$config['theme'],
                );

            }

            $entries_array_ids = array_keys($entries);

            if (isset($entries_array_ids) && !empty($entries_array_ids)) {

                $entries_string_ids = implode(',', $entries_array_ids);

                foreach ($mysql->select('SELECT * FROM '.prefix.'_eshop_images i WHERE i.product_id IN ('.$entries_string_ids.') ORDER BY i.position, i.id') as $irow) {
                    $entries[$irow['product_id']]['images'][] = $irow;
                }

                foreach ($mysql->select('SELECT * FROM '.prefix.'_eshop_variants v WHERE v.product_id IN ('.$entries_string_ids.') ORDER BY v.position, v.id') as $vrow) {
                    $entries[$vrow['product_id']]['variants'][] = $vrow;
                }

            }

            $tVars = array(
                'info' => isset($info) ? $info : '',
                'entries' => isset($entries) ? $entries : '',
                'tpl_url' => home.'/templates/'.$config['theme'],
                'tpl_home' => admin_url,
            );

            if ($overrideTemplateName) {
                $templateName = 'block/'.$overrideTemplateName;
            } else {
                $templateName = 'block/main_block_eshop';
            }

            // Determine paths for all template files
			$tpath = locatePluginTemplates(array($templateName), 'eshop', pluginGetVariable('eshop', 'localsource'), pluginGetVariable('eshop','localskin'));
            // Preload template configuration variables
            @templateLoadVariables();

            $tVars['mode'] = $mode;
            $tVars['number'] = $number;
            $tVars['tpl_url'] = tpl_url;
            $tVars['home'] = home;
            $tVars['pagesss'] = generateLP(
                array('current' => $pageNo, 'count' => $countPages, 'url' => '#', 'tpl' => $templateName.'_pages')
            );

            $xt = $twig->loadTemplate($tpath[$templateName].$templateName.'.tpl');

            if (empty($row)) {
                $results = array(
                    'prd_main' => 2,
                    'prd_main_text' => 'Нет продукции!',
                    'prd_main_pages_text' => '',
                );
            } else {
                $results = array(
                    'prd_main' => 100,
                    'prd_main_text' => $xt->render($tVars),
                    'prd_main_pages_text' => $tVars['pagesss'],
                );

            }

            break;
    }

    return array('status' => 1, 'errorCode' => 0, 'data' => $results);

}
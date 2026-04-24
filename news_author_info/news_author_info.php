<?php
if (!defined('NGCMS')) {
    exit('HAL');
}
LoadPluginLang('uprofile', 'main', '', 'uprofile', '#');
class news_author_info__NewsFilter extends NewsFilter
{
    public function showNews($newsID, $SQLnews, &$tvars, $mode = array())
    {
        global $mysql, $lang;
        $urow = $mysql->record("SELECT * FROM " . uprefix . "_users WHERE id = " . $SQLnews['author_id'] . " LIMIT 1");
        unset($urow['pass'], $urow['newpw'], $urow['authcookie'], $urow['activation']);
        // Преобразование статуса в текстовое представление
        $urow['status_text'] = (($urow['status'] >= 1) && ($urow['status'] <= 4)) ?
            $lang['uprofile']['st_' . $urow['status']] :
            $lang['uprofile']['st_unknown'];
        $tvars['vars']['p']['news_author_info']['info'] = $urow;
        if (getPluginStatusActive('uprofile')) {
            loadPluginLibrary('uprofile', 'lib');
            $avatar = userGetAvatar($urow);
            // Исправление протокола на HTTPS если страница загружена через HTTPS
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' && isset($avatar[1])) {
                $avatar[1] = preg_replace('/^http:\/\//i', 'https://', $avatar[1]);
            }
            $tvars['vars']['p']['news_author_info']['avatar'] = $avatar;
        }
        if (getPluginStatusActive('xfields')) {
            $this->getUserXfields($urow, $tvars);
        }
    }
    public function getUserXfields($SQLrow, &$tvars)
    {
        global $mysql, $config, $twig, $parse;
        // Load xfields library
        include_once root . 'plugins/xfields/lib/common.php';
        // Try to load config. Stop processing if config was not loaded
        if (($xf = xf_configLoad()) === false) return;
        $fields = xf_decode($SQLrow['xfields']);
        // Check if we have at least one `image` field and load TWIG template if any
        if (is_array($xf['users']))
            foreach ($xf['users'] as $k => $v) {
                if ($v['type'] == 'images') {
                    // Yes, we have it!
                    $conversionParams = array();
                    $imagesTemplateFileName = 'plugins/xfields/tpl/profile.show.images.tpl';
                    $xtImages = $twig->loadTemplate($imagesTemplateFileName);
                    break;
                }
            }
        // Show extra fields if we have it
        if (is_array($xf['users']))
            foreach ($xf['users'] as $k => $v) {
                $kp = preg_quote($k, "#");
                $xfk = isset($fields[$k]) ? $fields[$k] : '';
                // Our behaviour depends on field type
                if ($v['type'] == 'images') {
                    // Check if there're attached images
                    if ($xfk && count($ilist = explode(",", $xfk)) && count($imglist = $mysql->select("select * from " . prefix . "_images where id in (" . $xfk . ")"))) {
                        //print "-xGotIMG[$k]";
                        // Yes, get list of images
                        $imgInfo = $imglist[0];
                        $iname = ($imgInfo['storage'] ? $config['attach_url'] : $config['files_url']) . '/' . $imgInfo['folder'] . '/' . $imgInfo['name'];
                        // Scan for images and prepare data for template show
                        $tiVars = array(
                            'fieldName'    => $k,
                            'fieldTitle'   => secure_html($v['title']),
                            'fieldType'    => $v['type'],
                            'entriesCount' => count($imglist),
                            'entries'      => array(),
                            'execStyle'    => $mode['style'],
                            'execPlugin'   => $mode['plugin'],
                        );
                        foreach ($imglist as $imgInfo) {
                            $tiEntry = array(
                                'url'         => ($imgInfo['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $imgInfo['folder'] . '/' . $imgInfo['name'],
                                'width'       => $imgInfo['width'],
                                'height'      => $imgInfo['height'],
                                'pwidth'      => $imgInfo['p_width'],
                                'pheight'     => $imgInfo['p_height'],
                                'name'        => $imgInfo['name'],
                                'origName'    => secure_html($imgInfo['orig_name']),
                                'description' => secure_html($imgInfo['description']),
                                'flags' => array(
                                    'hasPreview' => $imgInfo['preview'],
                                ),
                            );
                            if ($imgInfo['preview']) {
                                $tiEntry['purl'] = ($imgInfo['storage'] ? $config['attach_url'] : $config['images_url']) . '/' . $imgInfo['folder'] . '/thumb/' . $imgInfo['name'];
                            }
                            $tiVars['entries'][] = $tiEntry;
                        }
                        // TWIG based variables
                        $tvars['vars']['p']['news_author_info']['xfields'][$k]['entries'] = $tiVars['entries'];
                        $tvars['vars']['p']['news_author_info']['xfields'][$k]['count'] = count($tiVars['entries']);
                        $xv = $xtImages->render($tiVars);
                        $tvars['vars']['p']['news_author_info']['xfields'][$k]['value'] = $xv;
                    }
                } else {
                    // Process `HTML` support feature
                    if ((!$v['html_support']) && (($v['type'] == 'textarea') || ($v['type'] == 'text'))) {
                        $xfk = str_replace("<", "&lt;", $xfk);
                    }
                    // Parse BB code [if required]
                    if ($config['use_bbcodes'] && $v['bb_support']) {
                        $xfk = $parse->bbcodes($xfk);
                    }
                    // Process formatting
                    if (($v['type'] == 'textarea') && (!$v['noformat'])) {
                        $xfk = (str_replace("\n", "<br/>\n", $xfk) . (strlen($xfk) ? '<br/>' : ''));
                    }
                    // TWIG based variables
                    $tvars['vars']['p']['news_author_info']['xfields'][$k]['value'] = $xfk;
                }
            }
    }
}
register_filter('news', 'news_author_info', new news_author_info__NewsFilter);

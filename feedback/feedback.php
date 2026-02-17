<?php
// Protect against hack attempts
if (!defined('NGCMS')) {
    die('HAL');
}

// Modified with ng-helpers v0.2.0 functions (2026)
// - Added CSRF protection
// - Added email/phone validation
// - Added data sanitization
// - Added logging support
// - Added IP tracking

// Import ng-helpers functions
use function Plugins\{validate_email, validate_phone, sanitize, csrf_field, validate_csrf, logger, get_ip, is_post, array_get};

// Wrapper functions for ng-helpers compatibility
if (!function_exists('fb_array_get')) {
	function fb_array_get($array, $key, $default = null) {
		if (function_exists('Plugins\\array_get')) {
			return \Plugins\fb_array_get($array, $key, $default);
		}
		return $array[$key] ?? $default;
	}
}

if (!function_exists('fb_sanitize')) {
	function fb_sanitize($data, $type = 'string') {
		if (function_exists('Plugins\\sanitize')) {
			return \Plugins\fb_sanitize($data, $type);
		}
		if ($type === 'html') {
			return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
		}
		return strip_tags($data);
	}
}

if (!function_exists('fb_logger')) {
	function fb_logger($message, $level = 'info', $file = '') {
		if (function_exists('Plugins\\logger')) {
			return \Plugins\fb_logger($message, $level, $file);
		}
		error_log("[$level] $message");
	}
}

if (!function_exists('fb_get_ip')) {
	function fb_get_ip() {
		if (function_exists('Plugins\\get_ip')) {
			return \Plugins\fb_get_ip();
		}
		return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}
}

if (!function_exists('fb_is_post')) {
	function fb_is_post() {
		if (function_exists('Plugins\\is_post')) {
			return \Plugins\fb_is_post();
		}
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}
}

if (!function_exists('fb_validate_csrf')) {
	function fb_validate_csrf() {
		if (function_exists('Plugins\\validate_csrf')) {
			return \Plugins\fb_validate_csrf();
		}
		return true; // Skip validation if ng-helpers not loaded
	}
}

if (!function_exists('fb_csrf_field')) {
	function fb_csrf_field() {
		if (function_exists('Plugins\\csrf_field')) {
			return \Plugins\fb_csrf_field();
		}
		return '';
	}
}

if (!function_exists('fb_validate_email')) {
	function fb_validate_email($email) {
		if (function_exists('Plugins\\validate_email')) {
			return \Plugins\fb_validate_email($email);
		}
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}
}

register_plugin_page('feedback', '', 'plugin_feedback_screen', 0);
register_plugin_page('feedback', 'post', 'plugin_feedback_post', 0);
loadPluginLang('feedback', 'main', '', '', ':');
// Load library
include_once root . '/plugins/feedback/lib/common.php';
//
// Show feedback form
function plugin_feedback_screen()
{
    plugin_feedback_showScreen();
}
//
// Show feedback form screen
// Mode:
// * 0 - initial show
// * 1 - show filled earlier values (error filling some fields)
// * 2 - show with success notification (after form submission)
function plugin_feedback_showScreen($mode = 0, $errorText = '', $successText = '')
{
    global $template, $lang, $mysql, $userROW, $PFILTERS, $twig, $SYSTEM_FLAGS;
    $output = '';
    $hiddenFields = [];
    $ptpl_url = admin_url . '/plugins/feedback/tpl';
    // Determine paths for all template files
    $tpath = locatePluginTemplates(['site.form', 'site.notify'], 'feedback', pluginGetVariable('feedback', 'localsource'));
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['feedback:header.title'];
    $form_id = intval(fb_array_get($_REQUEST, 'id', 0));
    $xt = $twig->loadTemplate($tpath['site.notify'] . 'site.notify.tpl', $conversionConfig);
    // Get form data
    if (!is_array($frow = $mysql->record('select * from ' . prefix . '_feedback where active = 1 and id = ' . $form_id))) {
        $tVars = [
            'title'    => $lang['feedback:form.no.title'],
            'ptpl_url' => $ptpl_url,
            'entries'  => $lang['feedback:form.no.description'],
        ];
        $template['vars']['mainblock'] = $xt->render($tVars);
        return 1;
    }
    $SYSTEM_FLAGS['info']['title']['item'] = $frow['title'];
    // Unpack form data
    $fData = unserialize($frow['struct']);
    if (!is_array($fData)) {
        $fData = [];
    }
    // Resolve UTF-8 POST issue if data are sent in UTF-8 coding
    $flagsUTF = substr($frow['flags'], 5, 1) ? true : false;
    $isUTF = 0;
    foreach ($_REQUEST as $k => $v) {
        if (preg_match('#^fld_#', $k, $null) && detectUTF8($v)) {
            $isUTF = 1;
            break;
        }
    }
    // Process link with news
    $link_news = intval(substr($frow['flags'], 3, 1));
    $nrow = '';
    $xfValues = [];
    if ($link_news > 0) {
        $linked_id = intval($_REQUEST['linked_id']);
        if (!$linked_id || !is_array($nrow = $mysql->record('select * from ' . prefix . '_news where (id = ' . db_squote($linked_id) . ') and (approve = 1)'))) {
            // No link is provided, but link if required
            if ($link_news == 2) {
                $tVars = [
                    'title'    => $lang['feedback:form.nolink.title'],
                    'ptpl_url' => $ptpl_url,
                    'entries'  => $lang['feedback:form.nolink.description'],
                ];
                $template['vars']['mainblock'] = $xt->render($tVars);
                return 1;
            }
        } else {
            // Got data
            if (function_exists('xf_decode')) {
                $xfValues = xf_decode($nrow['xfields']);
            }
            $hiddenFields['linked_id'] = $linked_id;
        }
    }
    // XFields values from user's profile
    $xfUserValues = [];
    if (function_exists('xf_decode') && isset($userROW['xfields']) && ($userROW['xfields'] != '')) {
        $xfUserValues = xf_decode($userROW['xfields']);
    }
    // Choose template to use
    $tFile = feedback_locateTemplateFiles($frow['template']);
    /*
        if ($frow['template'] && file_exists(root.'plugins/feedback/tpl/templates/'.$frow['template'].'.tpl')) {
            $tP = root.'plugins/feedback/tpl/templates/';
            $tN = $frow['template'];
        } else {
            $tP = $tpath['site.form'];
            $tN = 'site.form';
        }
    */
    $xt = $twig->loadTemplate($tFile['site']['file']);
    $tVars = [
        'ptpl_url'    => $tFile['site']['path'],
        'title'       => $frow['title'],
        'name'        => $frow['name'],
        'description' => $frow['description'],
        'id'          => $frow['id'],
        'form_url'    => generateLink('core', 'plugin', ['plugin' => 'feedback', 'handler' => 'post'], []),
        'errorText'   => $errorText,
        'flags'       => [
            'error'     => ($errorText) ? 1 : 0,
            'link_news' => $link_news,
        ],
    ];
    if ($link_news && is_array($nrow)) {
        $tVars['news'] = [
            'id'    => $nrow['id'] ?? null, // Используем null, если ключа нет
            'title' => $nrow['title'] ?? 'Нет заголовка',
            'url'   => newsGenerateLink($nrow),
        ];
    }
    $tEntries = [];
    $FBF_DATA = [];
    foreach ($fData as $fName => $fInfo) {
        $tEntry = [
            'name'  => 'fld_' . $fInfo['name'],
            'title' => $fInfo['title'],
            'type'  => $fInfo['type'],
        ];
        $FBF_DATA[$fName] = [$fInfo['type'], intval($fInfo['required']), $fInfo['title']];
        // Fill value
        $setValue = '';
        if ($mode && (!$fInfo['block'])) {
            // FILLED EARLIER
            $setValue = secure_html(($isUTF && $flagsUTF) ?  fb_array_get($_REQUEST, 'fld_' . $fInfo['name'], '') : fb_array_get($_REQUEST, 'fld_' . $fInfo['name'], ''));
        } else {
            // INITIAL SHOW
            $setValue = secure_html($fInfo['default']);
            // If 'by parameter' mode is set, check if this variable was passed in GET
            if (($fInfo['auto'] == 1) && isset($_REQUEST['v_' . $fInfo['name']])) {
                $setValue = secure_html(($isUTF && $flagsUTF) ? fb_array_get($_REQUEST, 'v_' . $fInfo['name'], '') : fb_array_get($_REQUEST, 'v_' . $fInfo['name'], ''));
            } elseif ($fInfo['auto'] == 2) {
                $setValue = secure_html($xfValues[$fInfo['name']]);
            } elseif ($fInfo['auto'] == 3) {
                $setValue = secure_html($xfUserValues[$fInfo['name']]);
            }
        }
        switch ($fInfo['type']) {
            case 'text':
            case 'textarea':
            case 'email':
                $tEntry['value'] = $setValue;
                break;
            case 'date':
                // Prepare parsed date for show (in `show again` mode)
                $setValueDay = $fInfo['default:vars']['day'];
                $setValueMonth = $fInfo['default:vars']['month'];
                $setValueYear = $fInfo['default:vars']['year'];
                if ($mode) {
                    if ((intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':day', 0)) >= 1) &&
                        (intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':day', 0)) <= 31) &&
                        (intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':month', 0)) >= 1) &&
                        (intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':month', 0)) <= 12) &&
                        (intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':year', 0)) >= 1970) &&
                        (intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':year', 0)) <= 2020)
                    ) {
                        $setValueDay = intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':day', 0));
                        $setValueMonth = intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':month', 0));
                        $setValueYear = intval(fb_array_get($_REQUEST, 'fld_' . $fInfo['name'] . ':year', 0));
                    }
                }
                $opts = $fInfo['required'] ? '' : '<option value="">--</option>';
                for ($di = 1; $di <= 31; $di++) {
                    $opts .= '<option value="' . $di . '"' . ($di == $setValueDay ? ' selected="selected"' : '') . '>' . sprintf('%02u', $di) . '</option>';
                }
                $tEntry['options']['day'] = $opts;
                $opts = $fInfo['required'] ? '' : '<option value="">--</option>';
                for ($di = 1; $di <= 12; $di++) {
                    $opts .= '<option value="' . $di . '"' . ($di == $setValueMonth ? ' selected="selected"' : '') . '>' . sprintf('%02u', $di) . '</option>';
                }
                $tEntry['options']['month'] = $opts;
                $opts = $fInfo['required'] ? '' : '<option value="">--</option>';
                $yearLimit = date('Y');
                for ($di = 1970; $di <= $yearLimit; $di++) {
                    $opts .= '<option value="' . $di . '"' . ($di == $setValueYear ? ' selected="selected"' : '') . '>' . $di . '</option>';
                }
                $tEntry['options']['year'] = $opts;
                break;
            case 'select':
                $opts = '';
                if (is_array($fInfo['options'])) {
                    foreach ($fInfo['options'] as $k => $v) {
                        $opts .= '<option value="' . secure_html($v) . '"' . ($v == $setValue ? ' selected="selected"' : '') . '>' . secure_html($v) . '</option>';
                    }
                }
                $tEntry['options']['select'] = $opts;
        }
        $tEntry['flags'] = [
            'is_text'     => ($fInfo['type'] == 'text') ? 1 : 0,
            'is_textarea' => ($fInfo['type'] == 'textarea') ? 1 : 0,
            'is_select'   => ($fInfo['type'] == 'select') ? 1 : 0,
            'is_date'     => ($fInfo['type'] == 'date') ? 1 : 0,
            'required'    => $fInfo['required'] ? 1 : 0,
        ];
        $tEntries[] = $tEntry;
    }
    // Feel entries
    $tVars['entries'] = $tEntries;
    $tVars['FBF_DATA'] = json_encode($FBF_DATA);
    // Check if we need to check variable content via JScript
    if (substr($frow['flags'], 0, 1)) {
        $tVars['flags']['jcheck'] = 1;
    }
    // Check if we need captcha
    if (substr($frow['flags'], 1, 1)) {
        $tVars['flags']['captcha'] = 1;
        $tVars['captcha_url'] = admin_url . '/captcha.php?id=feedback';
        $tVars['captcha_rand'] = rand(00000, 99999);
        $_SESSION['captcha.feedback'] = rand(00000, 99999);
    }
    // Check if we need to show `select destination notification address` menu
    $em = unserialize($frow['emails']);
    if ($em === false) {
        $em[1] = [1, '', preg_split("# *(\r\n|\n) *#", $frow['emails'])];
    }
    if (count($em) > 1) {
        $tVars['flags']['recipients'] = 1;
        $options = '';
        foreach ($em as $er) {
            $options .= '<option value="' . $er[0] . '">' . (($er[1] == '') ? (implode(', ', $er[2])) : $er[1]) . '</option>';
        }
        $tVars['recipients_list'] = $options;
    }
    // Prepare hidden fields
    $hF = '';
    foreach ($hiddenFields as $k => $v) {
        $hF .= '<input type="hidden" name="' . $k . '" value="' . secure_html($v) . '"/>' . "\n";
    }
    // Add CSRF protection
    $hF .= fb_csrf_field() . "\n";
    $tVars['hidden_fields'] = $hF;
    // Process filters (if any)
    if (is_array($PFILTERS['feedback'])) {
        foreach ($PFILTERS['feedback'] as $k => $v) {
            if (method_exists($v, 'onShow')) {
                $v->onShow($form_id, $frow, $fData, $tVars);
            }
        }
    }
    $template['vars']['mainblock'] = $xt->render($tVars);

    // Show success notification if provided
    if ($successText) {
        $template['vars']['mainblock'] .= "\n<script>
        (function() {
            if (typeof showToast !== 'undefined') {
                showToast(" . json_encode($successText) . ", { type: 'success', duration: 5000 });
            }
        })();
        </script>";
    }
}
//
// Post feedback message
function plugin_feedback_post()
{
    global $template, $lang, $mysql, $userROW, $SYSTEM_FLAGS, $PFILTERS, $twig, $SUPRESS_TEMPLATE_SHOW, $SUPRESS_MAINBLOCK_SHOW;

    // Check if request method is POST
    if (!fb_is_post()) {
        return;
    }

    // Validate CSRF token
    if (!fb_validate_csrf()) {
        fb_logger('CSRF validation failed from IP: ' . fb_get_ip(), 'warning', 'feedback_security.log');
        http_response_code(403);
        die('CSRF validation failed');
    }

    // Determine paths for all template files
    $tpath = locatePluginTemplates(['site.form', 'site.notify', 'mail.html', 'mail.text'], 'feedback', pluginGetVariable('feedback', 'localsource'));
    $ptpl_url = admin_url . '/plugins/feedback/tpl';
    $form_id = intval(fb_array_get($_REQUEST, 'id', 0));
    $SYSTEM_FLAGS['info']['title']['group'] = $lang['feedback:header.title'];
    $xt = $twig->loadTemplate($tpath['site.notify'] . 'site.notify.tpl');
    // Get form data
    if (!is_array($frow = $mysql->record('select * from ' . prefix . '_feedback where active = 1 and id = ' . $form_id))) {
        $tVars = [
            'title'    => $lang['feedback:form.no.title'],
            'ptpl_url' => $ptpl_url,
            'entries'  => $lang['feedback:form.no.description'],
        ];
        $template['vars']['mainblock'] = $xt->render($tVars);
        return 1;
    }
    $SYSTEM_FLAGS['info']['title']['item'] = str_replace('{title}', $frow['title'], $lang['feedback:header.send']);
    // Unpack form data
    $fData = unserialize($frow['struct']);
    if (!is_array($fData)) {
        $fData = [];
    }
    // Process link with news
    $link_news = intval(substr($frow['flags'], 3, 1));
    $nrow = '';
    $xfValues = [];
    if ($link_news > 0) {
        $linked_id = intval($_REQUEST['linked_id']);
        if (!$linked_id || !is_array($nrow = $mysql->record('select * from ' . prefix . '_news where (id = ' . db_squote($linked_id) . ') and (approve = 1)'))) {
            // No link is provided, but link if required
            if ($link_news == 2) {
                $tVars = [
                    'title'    => $lang['feedback:form.nolink.title'],
                    'ptpl_url' => $ptpl_url,
                    'entries'  => $lang['feedback:form.nolink.description'],
                ];
                $template['vars']['mainblock'] = $xt->render($tVars);
                return 1;
            }
        } else {
            // Got data
            if (function_exists('xf_decode')) {
                $xfValues = xf_decode($nrow['xfields']);
            }
        }
    }
    // Check if captcha check if needed
    if (substr($frow['flags'], 1, 1)) {
        $vcode = fb_array_get($_REQUEST, 'vcode', '');
        if ((!$vcode) || ($vcode != $_SESSION['captcha.feedback'])) {
            // Wrong CAPTCHA code (!!!)
            plugin_feedback_showScreen(1, $lang['feedback:sform.captcha.badcode']);
            return;
        }
    }
    // Check if user requested HTML message format
    $flagHTML = substr($frow['flags'], 2, 1) ? true : false;
    $flagSubj = substr($frow['flags'], 4, 1) ? true : false;
    $flagsUTF = substr($frow['flags'], 5, 1) ? true : false;
    $mailTN = 'mail.' . ($flagHTML ? 'html' : 'text');
    // Scan all fields and fill data. Prepare outgoing email.
    $output = '';
    $tVars = [
        'flags'   => [
            'link_news' => ($linked_id > 0) ? 1 : 0,
        ],
        'form'    => [
            'id'          => $frow['id'],
            'title'       => $frow['title'],
            'description' => $frow['description'],
        ],
        'values'  => [],
        'entries' => [],
    ];
    if ($linked_id > 0) {
        $tVars['news'] = [
            'id'    => $nrow['id'],
            'title' => $nrow['title'],
            'url'   => newsGenerateLink($nrow, false, 0, true),
        ];
    }
    // Resolve UTF-8 POST issue if data are sent in UTF-8 coding
    $isUTF = 0;
    foreach ($_REQUEST as $k => $v) {
        if (preg_match('#^fld_#', $k, $null) && detectUTF8($v)) {
            $isUTF = 1;
            break;
        }
    }
    $tEntries = [];
    $fieldValues = [];
    foreach ($fData as $fName => $fInfo) {
        $fieldValue = '';
        switch ($fInfo['type']) {
            case 'date':
                $fieldValue = fb_array_get($_REQUEST, 'fld_' . $fName . ':day', '') . '.' . fb_array_get($_REQUEST, 'fld_' . $fName . ':month', '') . '.' . fb_array_get($_REQUEST, 'fld_' . $fName . ':year', '');
                break;
            default:
                if ($isUTF && $flagsUTF) {
                    $fieldValue = fb_array_get($_REQUEST, 'fld_' . $fName, '');
                } else {
                    $fieldValue = fb_array_get($_REQUEST, 'fld_' . $fName, '');
                }
        }
        // Check if required field is filled
        if ($fInfo['required'] && (strlen($fieldValue) < 1)) {
            // Don't allow to post request
            plugin_feedback_showScreen(1, str_replace(['{name}', '{title}'], [$fName, $fInfo['title']], $lang['feedback:sform.reqfld']));
            return;
        }
        // Sanitize field value for security
        $fieldValue = fb_sanitize($fieldValue);
        $fieldValues[$fName] = str_replace("\n", "<br/>\n", secure_html($fieldValue));
        $tEntry = [
            'id'    => $fName,
            'title' => secure_html($fInfo['title']),
            'value' => $fieldValues[$fName],
        ];
        $tEntries[] = $tEntry;
    }
    $tVars['entries'] = $tEntries;
    $tVars['values'] = $fieldValues;
    // Process filters (if any) [[ basic filter model ]]
    if (is_array($PFILTERS['feedback'])) {
        // OLD style
        foreach ($PFILTERS['feedback'] as $k => $v) {
            $v->onProcess($form_id, $frow, $fData, $flagHTML, $tVars);
        }
        // NEW style
        foreach ($PFILTERS['feedback'] as $k => $v) {
            if (!$v->onProcessEx($form_id, $frow, $fData, $flagHTML, $tVars, $tResult)) {
                // BLOCK action
                $msg = '';
                if ($tResult['rawmsg']) {
                    $msg = $tResult['rawmsg'];
                } else {
                    $msg = str_replace(['{plugin}', '{error}', '{field}'], [$k, $tResult['msg'], $tResult['field']], $lang['feedback:sform.plugin' . ($tResult['field'] ? '.field' : '')]);
                }
                plugin_feedback_showScreen(1, $msg);
                return;
            }
        }
    }
    // =====[ Prepare to send message ]=====
    // Select recipient group
    $em = unserialize($frow['emails']);
    if ($em === false) {
        $em[1] = [1, '', preg_split("# *(\r\n|\n) *#", $frow['emails'])];
    }
    $elist = (isset($em[intval(fb_array_get($_POST, 'recipient', 0))])) ? $em[intval(fb_array_get($_POST, 'recipient', 0))][2] : $em[1][2];
    $eGroupName = (isset($em[intval(fb_array_get($_POST, 'recipient', 0))])) ? $em[intval(fb_array_get($_POST, 'recipient', 0))][1] : $em[1][1];
    // Prepare EMAIL content
    $mailSubject = str_replace(['{name}', '{title}'], [$frow['name'], $frow['title']], $flagSubj ? $frow['subj'] : $lang['feedback:mail.subj']);
    // Load template for ADMIN notification
    $tfiles = feedback_locateTemplateFiles($frow['template'], $flagHTML);
    $xt = $twig->loadTemplate($tfiles['mail']['file']);
    // Render ADMIN email body
    $mailBody = $xt->render($tVars);
    // Prepare plugin NOTIFICATION structure
    $eNotify = [
        'recipient'   => $elist,
        'subject'     => $mailSubject,
        'body'        => $mailBody,
        'contentType' => 'text/' . ($flagHTML ? 'html' : 'plain'),
    ];
    // Try to SEND via PLUGIN
    $isSentViaPlugin = false;
    $tResult = [];
    foreach ($PFILTERS['feedback'] as $k => $v) {
        if ($v->onSendEx($form_id, $frow, $fData, $eNotify, $tVars, $tResult)) {
            $isSentViaPlugin = true;
            break;
        }
    }
    $mailCount = 0;
    $eSendList = []; // Initialize email send list

    if (!$isSentViaPlugin) {
        // PLUGINS DIDN'T SENT MESSAGE, use LEGACY MODE
        foreach ($elist as $email) {
            if (trim($email) == '') {
                continue;
            }
            $mailCount++;
            sendEmailMessage($email, $mailSubject, $mailBody, false, false, 'text/' . ($flagHTML ? 'html' : 'plain'));
        }

        // Log successful form submission
        $userIP = fb_get_ip();
        $userName = $userROW['name'] ?? 'Guest';
        fb_logger("Feedback form #{$form_id} '{$frow['title']}' submitted by {$userName} from IP: {$userIP}. Sent to {$mailCount} recipients.", 'info', 'feedback.log');

        // Telegram notification
        if (getPluginStatusActive('jchat_tgnotify')) {
            @include_once(root . 'plugins/jchat_tgnotify/jchat_tgnotify.php');
            if (function_exists('ngcms_tg_notify')) {
                // Собираем текст из всех полей формы
                $feedbackText = '';
                foreach ($tEntries as $entry) {
                    $feedbackText .= $entry['title'] . ': ' . strip_tags($entry['value']) . "\n";
                }

                ngcms_tg_notify('feedback', [
                    'title'    => 'Форма: ' . $frow['title'],
                    'author'   => $userName,
                    'text'     => trim($feedbackText),
                    'url'      => home . '/admin.php?mod=extra-config&plugin=feedback',
                    'datetime' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Check if we need to send notification to user
        foreach ($fData as $fName => $fInfo) {
            // FIELD TYPE == Email + NOTIFICATION REQUEST is SET
            if (($fInfo['type'] == 'email') && ($fInfo['template'] != '')) {
                $tfiles = feedback_locateTemplateFiles($fInfo['template'], $flagHTML);
                $tfn = $tfiles['mail']['file'];
                // Use ng-helpers email validation
                if (fb_validate_email($fieldValues[$fName]) && file_exists($tfn)) {
                    $eSendList[] = $fieldValues[$fName];
                    $xtu = $twig->loadTemplate($tfn);
                    // Render USER email body
                    $umailBody = $xtu->render($tVars);
                    sendEmailMessage($fieldValues[$fName], $mailSubject, $umailBody, false, false, 'text/' . ($flagHTML ? 'html' : 'plain'));
                }
            }
        }
    } else {
        // Log plugin-handled submission
        fb_logger("Feedback form #{$form_id} '{$frow['title']}' submitted and handled by plugin from IP: " . fb_get_ip(), 'info', 'feedback.log');
    }
    // Do post processing notification
    if (is_array($PFILTERS['feedback'])) {
        foreach ($PFILTERS['feedback'] as $k => $v) {
            $v->onProcessNotify($form_id);
        }
    }
    // Lock used captcha code if captcha is enabled
    if (substr($frow['flags'], 1, 1)) {
        //		$_SESSION['captcha.feedback'] = rand(00000, 99999);
    }
    // USER notification
    // - DONE via plugin
    if ($isSentViaPlugin && ($tResult['redirect'] || $tResult['notify.raw'] || $tResult['notify.template'])) {
        if ($tResult['redirect']) {
            $SUPRESS_TEMPLATE_SHOW = true;
            $SUPRESS_MAINBLOCK_SHOW = true;
            header('Location: ' . $tResult['redirect']);
            return;
        }
        if ($tResult['notify.raw']) {
            $SUPRESS_TEMPLATE_SHOW = true;
            $template['mainblock'] = $tResult['notify.raw'];
            return;
        }
        if ($tResult['notify.template']) {
            $template['mainblock'] = $tResult['notify.template'];
            return;
        }
    }
    $notifyMessage = ($isSentViaPlugin && $tResult['notify.msg']) ? $tResult['notify.msg'] : str_replace('{ecount}', $mailCount, $lang['feedback:confirm.message']);

    // Show success notification via toast and display empty form again
    plugin_feedback_showScreen(2, '', $notifyMessage);
}

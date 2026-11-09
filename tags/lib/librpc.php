<?php

/**
 * Suggest helper for Add/Edit news.
 */
rpcRegisterFunction('plugin.tags.suggest', function (string $params): array {
    global $userROW, $mysql;

    // Only registered users can use suggest
    if (!is_array($userROW)) {
        return [
            'status' => 0,
            'errorCode' => 1,
            'errorText' => 'Permission denied',
        ];
    }

    // Check if suggest module is enabled
    $suggestEnabled = intval(pluginGetVariable('tags', 'suggestHelper'));
    if (!$suggestEnabled) {
        return [
            'status' => 0,
            'errorCode' => 2,
            'errorText' => 'Suggest helper is not enabled',
        ];
    }

    $output = [];

    // Check if tag is specified
    if ($params) {
        $searchTag = db_squote($params . '%');

        $sql = "select * from " . prefix . "_tags where tag like " . $searchTag . " order by posts desc limit 20";

        foreach ($mysql->select($sql) as $row) {
            $output[] = [
                $row['tag'],
                $row['posts'] . ' постов'
            ];
        }
    }

    return [
        'status' => 1,
        'errorCode' => 0,
        'data' => [
            $params,
            $output,
        ],
    ];
});

rpcRegisterFunction('plugin.tags.generate', function ($params): array {
    global $userROW, $mysql;

    if (!is_array($userROW) || !is_array($params)) {
        return ['status' => 0, 'errorCode' => 1, 'errorText' => 'Доступ запрещён'];
    }

    $mode = ($params['mode'] ?? '') === 'edit' ? 'edit' : 'add';
    $tokenName = 'admin.news.' . $mode;
    if (!isset($params['token']) || !hash_equals(genUToken($tokenName), (string) $params['token'])) {
        return ['status' => 0, 'errorCode' => 2, 'errorText' => 'Недействительный токен безопасности'];
    }

    $permissionModes = [
        'add',
        'personal.modify',
        'personal.modify.published',
        'personal.unpublish',
        'other.modify',
        'other.modify.published',
        'other.unpublish',
    ];
    $permissions = checkPermission(['plugin' => '#admin', 'item' => 'news'], null, $permissionModes);

    if ($mode === 'add') {
        if (!is_array($permissions) || empty($permissions['add'])) {
            return ['status' => 0, 'errorCode' => 3, 'errorText' => 'Недостаточно прав для добавления новости'];
        }
    } else {
        $newsId = intval($params['news_id'] ?? 0);
        $news = $newsId ? $mysql->record('select id, author_id, approve from ' . prefix . '_news where id = ' . db_squote($newsId)) : null;
        if (!is_array($news) || !is_array($permissions)) {
            return ['status' => 0, 'errorCode' => 3, 'errorText' => 'Новость не найдена или недостаточно прав'];
        }
        $permissionGroup = ($news['author_id'] == $userROW['id']) ? 'personal' : 'other';
        $modifyPermission = $permissionGroup . '.modify' . ($news['approve'] == 1 ? '.published' : '');
        if (empty($permissions[$modifyPermission]) && empty($permissions[$permissionGroup . '.unpublish'])) {
            return ['status' => 0, 'errorCode' => 3, 'errorText' => 'Недостаточно прав для редактирования этой новости'];
        }
    }

    if (!getPluginStatusActive('ai_rewriter')) {
        return ['status' => 0, 'errorCode' => 4, 'errorText' => 'Для генерации тегов активируйте плагин AI Rewriter'];
    }

    $aiRewriterPath = extras_dir . '/ai_rewriter/ai_rewriter.php';
    if (!is_file($aiRewriterPath)) {
        return ['status' => 0, 'errorCode' => 4, 'errorText' => 'Файл плагина AI Rewriter не найден'];
    }
    require_once $aiRewriterPath;
    if (!function_exists('ai_rewriter_generate_tags')) {
        return ['status' => 0, 'errorCode' => 4, 'errorText' => 'Генератор тегов AI Rewriter недоступен'];
    }

    $title = trim(strip_tags((string) ($params['title'] ?? '')));
    $content = trim(strip_tags((string) ($params['content'] ?? '')));
    $categories = trim(strip_tags((string) ($params['categories'] ?? '')));
    if ($title === '' || $content === '') {
        return ['status' => 0, 'errorCode' => 5, 'errorText' => 'Заполните заголовок и содержание новости'];
    }

    $content = mb_substr($content, 0, 20000);
    $categories = mb_substr($categories, 0, 1000);
    list($ok, $result) = ai_rewriter_generate_tags(mb_substr($title, 0, 500), $content, $categories);
    if (!$ok) {
        return ['status' => 0, 'errorCode' => 6, 'errorText' => $result];
    }

    $tags = preg_split('/[\r\n,;]+/u', $result, -1, PREG_SPLIT_NO_EMPTY);
    $uniqueTags = [];
    $seen = [];
    foreach ($tags as $tag) {
        $tag = trim(preg_replace('/^\s*(?:[-*•]|\d+[.)])\s*/u', '', $tag), " \t\n\r\0\x0B\"'`#");
        $tag = mb_substr($tag, 0, 100);
        if ($tag === '') {
            continue;
        }
        $key = mb_strtolower($tag);
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $uniqueTags[] = $tag;
            if (count($uniqueTags) >= 10) {
                break;
            }
        }
    }

    if (!$uniqueTags) {
        return ['status' => 0, 'errorCode' => 7, 'errorText' => 'ИИ не вернул теги. Попробуйте ещё раз'];
    }

    return ['status' => 1, 'errorCode' => 0, 'tags' => $uniqueTags];
});

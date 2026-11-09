<?php

if (!defined('NGCMS')) {
    die('HAL');
}

function rpc_updater_error($message)
{
    return [
        'status' => 0,
        'errorCode' => 999,
        'errorText' => $message,
    ];
}

function rpc_updater_load($params, &$sourceConfig, &$error)
{
    global $userROW;

    $sourceConfig = null;
    $error = null;
    if (!is_array($params)) {
        $error = rpc_updater_error('Invalid updater request.');
        return null;
    }

    $token = $params['token'] ?? null;
    if (!is_string($token) || !hash_equals((string)genUToken('core.system.update'), $token)) {
        $error = rpc_updater_error('Invalid security token.');
        return null;
    }

    if (!is_array($userROW)
        || !checkPermission(['plugin' => '#admin', 'item' => 'configuration'], null, 'modify')) {
        $error = rpc_updater_error('Permission denied.');
        return null;
    }

    $settings = include root . '/plugins/update_to_git/params.php';
    $sources = is_array($settings) ? ($settings['sources'] ?? null) : null;
    $source = $params['source'] ?? 'core';
    if (!is_array($sources)
        || !is_string($source)
        || !isset($sources[$source])
        || !is_array($sources[$source])) {
        $error = rpc_updater_error('Invalid updater source.');
        return null;
    }

    $sourceConfig = $sources[$source];
    if (!isset(
        $sourceConfig['user'],
        $sourceConfig['repository'],
        $sourceConfig['branch'],
        $sourceConfig['base_ref'],
        $sourceConfig['destination_prefix'],
        $sourceConfig['except']
    )
        || !is_string($sourceConfig['user'])
        || !is_string($sourceConfig['repository'])
        || !is_string($sourceConfig['branch'])
        || !is_string($sourceConfig['base_ref'])
        || !is_string($sourceConfig['destination_prefix'])
        || !is_array($sourceConfig['except'])) {
        $error = rpc_updater_error('Invalid updater configuration.');
        return null;
    }

    $params['source'] = $source;
    return $params;
}

function rpc_updater_save_plugins_state($lastCommit, $pendingCommit = '')
{
    if (!is_string($lastCommit)
        || !preg_match('/^[a-f0-9]{40,64}$/i', $lastCommit)
        || !is_string($pendingCommit)
        || ($pendingCommit !== '' && !preg_match('/^[a-f0-9]{40,64}$/i', $pendingCommit))) {
        return false;
    }

    if (!extra_set_param('update_to_git', 'plugins_last_commit', $lastCommit)
        || !extra_set_param('update_to_git', 'plugins_pending_commit', $pendingCommit)) {
        return false;
    }

    return pluginsSaveConfig(true);
}

function rpc_updater_check($params = [])
{
    $sourceConfig = null;
    $error = null;
    $params = rpc_updater_load($params, $sourceConfig, $error);
    if ($error !== null) {
        return $error;
    }

    include_once root . '/plugins/update_to_git/lib/CSystemUpdate.php';
    try {
        $updater = new CSystemUpdate($sourceConfig);
        $source = $params['source'];

        if ($source === 'plugins') {
            $baseRef = extra_get_param('update_to_git', 'plugins_last_commit');
            if (!is_string($baseRef) || !preg_match('/^[a-f0-9]{40,64}$/i', $baseRef)) {
                $headSha = $updater->getLatestCommit();
                if (!rpc_updater_save_plugins_state($headSha)) {
                    return rpc_updater_error('Unable to save the initial plugins update checkpoint.');
                }

                return [
                    'status' => 1,
                    'errorCode' => 0,
                    'data' => [
                        'files' => [],
                        'baseline_initialized' => true,
                        'head_sha' => $headSha,
                    ],
                ];
            }
        } else {
            $baseRef = $sourceConfig['base_ref'];
        }

        $updater->check($baseRef);
        $result = $updater->execute();
        if (($result['status'] ?? 0) !== 1) {
            return $result;
        }

        $headSha = $result['data']['head_sha'] ?? '';
        $files = $result['data']['files'] ?? null;
        if (!is_string($headSha)
            || !preg_match('/^[a-f0-9]{40,64}$/i', $headSha)
            || !is_array($files)) {
            return rpc_updater_error('GitHub returned an invalid update result.');
        }

        if ($source === 'plugins') {
            if (!$files) {
                if (!rpc_updater_save_plugins_state($headSha)) {
                    return rpc_updater_error('Unable to save the plugins update checkpoint.');
                }
            } elseif (!rpc_updater_save_plugins_state($baseRef, $headSha)) {
                return rpc_updater_error('Unable to save the pending plugins update.');
            }
        }

        return $result;
    } catch (Throwable $e) {
        return rpc_updater_error($e->getMessage());
    }
}

function rpc_updater($params = [])
{
    $sourceConfig = null;
    $error = null;
    $params = rpc_updater_load($params, $sourceConfig, $error);
    if ($error !== null) {
        return $error;
    }

    if (!isset($params['url'], $params['name'], $params['action'])
        || !is_string($params['url'])
        || !is_string($params['name'])
        || !is_string($params['action'])) {
        return rpc_updater_error('Invalid file update request.');
    }

    include_once root . '/plugins/update_to_git/lib/CSystemUpdate.php';
    try {
        $updater = new CSystemUpdate($sourceConfig);
        $updater->download(
            $params['url'],
            $params['name'],
            $params['action'],
            isset($params['previous_name']) && is_string($params['previous_name'])
                ? $params['previous_name']
                : ''
        );
        return $updater->execute();
    } catch (Throwable $e) {
        return rpc_updater_error($e->getMessage());
    }
}

function rpc_updater_finish($params = [])
{
    $sourceConfig = null;
    $error = null;
    $params = rpc_updater_load($params, $sourceConfig, $error);
    if ($error !== null) {
        return $error;
    }
    if ($params['source'] !== 'plugins') {
        return rpc_updater_error('Only plugins updates have a checkpoint.');
    }

    $targetSha = $params['target_sha'] ?? '';
    $pendingSha = extra_get_param('update_to_git', 'plugins_pending_commit');
    if (!is_string($targetSha)
        || !is_string($pendingSha)
        || !preg_match('/^[a-f0-9]{40,64}$/i', $targetSha)
        || !hash_equals($pendingSha, $targetSha)) {
        return rpc_updater_error('The plugins update checkpoint does not match this request.');
    }

    if (!rpc_updater_save_plugins_state($pendingSha)) {
        return rpc_updater_error('Unable to save the completed plugins update checkpoint.');
    }

    return [
        'status' => 1,
        'errorCode' => 0,
        'data' => 'Позиция обновления плагинов сохранена.',
    ];
}

rpcRegisterFunction('plugin.updater.check', 'rpc_updater_check');
rpcRegisterFunction('plugin.updater.execute', 'rpc_updater');
rpcRegisterFunction('plugin.updater.finish', 'rpc_updater_finish');

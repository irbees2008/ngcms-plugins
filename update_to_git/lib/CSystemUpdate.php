<?php

if (!defined('NGCMS')) {
    die('HAL');
}

class CSystemUpdate
{
    protected $except;
    protected $user;
    protected $repository;
    protected $branch;
    protected $destinationPrefix;
    protected $answer = [];

    public function __construct($params)
    {
        if (!is_array($params)
            || !isset($params['user'], $params['repository'], $params['branch'])
            || !is_string($params['user'])
            || !is_string($params['repository'])
            || !is_string($params['branch'])
            || !preg_match('/^[A-Za-z0-9_.-]+$/', $params['user'])
            || !preg_match('/^[A-Za-z0-9_.-]+$/', $params['repository'])
            || !preg_match('/^[A-Za-z0-9_.-]+$/', $params['branch'])) {
            throw new InvalidArgumentException('Invalid GitHub updater configuration.');
        }

        $this->user = $params['user'];
        $this->repository = $params['repository'];
        $this->branch = $params['branch'];
        $prefix = $params['destination_prefix'] ?? '';
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('Invalid updater destination path.');
        }
        $this->destinationPrefix = $prefix === '' ? '' : $this->normalizeName($prefix);
        $this->except = isset($params['except']) && is_array($params['except'])
            ? $params['except']
            : [];
    }

    public function execute()
    {
        return $this->answer;
    }

    public function getLatestCommit()
    {
        $url = 'https://api.github.com/repos/' . rawurlencode($this->user)
            . '/' . rawurlencode($this->repository) . '/commits/'
            . rawurlencode($this->branch);
        $response = $this->request($url);
        if ($response['status'] !== 200) {
            throw new CSystemUpdateException('GitHub API returned HTTP ' . $response['status'] . '.');
        }

        $data = json_decode($response['body'], true);
        $sha = is_array($data) ? ($data['sha'] ?? '') : '';
        if (!is_string($sha) || !preg_match('/^[a-f0-9]{40,64}$/i', $sha)) {
            throw new CSystemUpdateException('GitHub returned an invalid commit response.');
        }

        return $sha;
    }

    public function check($baseRef)
    {
        try {
            if (!is_string($baseRef) || !preg_match('/^[A-Za-z0-9_.-]{1,128}$/', $baseRef)) {
                throw new CSystemUpdateException('Invalid base revision for GitHub comparison.');
            }

            $headSha = $this->getLatestCommit();
            $url = 'https://api.github.com/repos/' . rawurlencode($this->user)
                . '/' . rawurlencode($this->repository) . '/compare/'
                . rawurlencode($baseRef) . '...' . rawurlencode($headSha);
            $response = $this->request($url);
            if ($response['status'] !== 200) {
                throw new CSystemUpdateException('GitHub API returned HTTP ' . $response['status'] . '.');
            }

            $data = json_decode($response['body'], true);
            if (!is_array($data)
                || !isset($data['total_commits'])
                || !is_array($data['files'] ?? null)) {
                throw new CSystemUpdateException('GitHub returned an invalid comparison response.');
            }
            if (isset($data['changed_files'])
                && (!is_int($data['changed_files']) || $data['changed_files'] !== count($data['files']))) {
                throw new CSystemUpdateException('GitHub returned an incomplete file comparison.');
            }

            $data['head_sha'] = $headSha;
            $this->answer = [
                'status' => 1,
                'errorCode' => 0,
                'data' => arrayCharsetConvert(0, $data),
            ];
        } catch (Throwable $e) {
            $this->setError($e->getMessage());
        }
    }

    public function download($url, $name, $action, $previousName = '')
    {
        try {
            $relativeName = $this->normalizeName($name);
            if (in_array($relativeName, $this->except, true)) {
                $this->answer = [
                    'status' => 2,
                    'errorCode' => 0,
                    'data' => arrayCharsetConvert(0, 'Пропущен защищённый файл: ' . $relativeName),
                ];
                return;
            }

            if ($action === 'removed') {
                $this->removeFile($this->destinationName($relativeName));
            } elseif (in_array($action, ['added', 'modified', 'renamed'], true)) {
                $rawUrl = $this->validateRawUrl($url, $relativeName);
                $response = $this->request($rawUrl);
                if ($response['status'] !== 200) {
                    throw new CSystemUpdateException('GitHub file download returned HTTP ' . $response['status'] . '.');
                }

                $this->writeFile($this->destinationName($relativeName), $response['body']);
                if ($action === 'renamed' && is_string($previousName) && $previousName !== '') {
                    $previousName = $this->normalizeName($previousName);
                    $samePath = DIRECTORY_SEPARATOR === '\\'
                        ? strcasecmp($previousName, $relativeName) === 0
                        : $previousName === $relativeName;
                    if (!$samePath && !in_array($previousName, $this->except, true)) {
                        $this->removeFile($this->destinationName($previousName));
                    }
                }
            } else {
                throw new CSystemUpdateException('Unsupported GitHub file action.');
            }

            $this->answer = [
                'status' => 1,
                'errorCode' => 0,
                'data' => arrayCharsetConvert(0, $action . ' :: ' . $relativeName),
            ];
        } catch (Throwable $e) {
            $this->setError($e->getMessage());
        }
    }

    protected function request($url)
    {
        if (!function_exists('curl_init')) {
            throw new CSystemUpdateException('The PHP cURL extension is not available.');
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new CSystemUpdateException('Unable to initialize the GitHub request.');
        }

        curl_setopt_array($handle, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'ngcms-core-updater',
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        $body = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($body === false) {
            throw new CSystemUpdateException('GitHub request failed: ' . $error);
        }

        return [
            'body' => $body,
            'status' => $status,
        ];
    }

    protected function validateRawUrl($url, $relativeName)
    {
        if (!is_string($url)) {
            throw new CSystemUpdateException('Invalid GitHub file URL.');
        }

        $parts = parse_url($url);
        if (!is_array($parts)
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
            || isset($parts['query'])
            || isset($parts['fragment'])) {
            throw new CSystemUpdateException('Only HTTPS URLs from the configured GitHub repository are allowed.');
        }

        $host = strtolower($parts['host'] ?? '');
        $segments = explode('/', trim($parts['path'] ?? '', '/'));
        if ($host === 'github.com') {
            if (count($segments) < 5
                || $segments[0] !== $this->user
                || $segments[1] !== $this->repository
                || $segments[2] !== 'raw') {
                throw new CSystemUpdateException('The file URL does not belong to the configured GitHub repository.');
            }
            $ref = rawurldecode($segments[3]);
            $fileSegments = array_slice($segments, 4);
        } elseif ($host === 'raw.githubusercontent.com') {
            if (count($segments) < 4
                || $segments[0] !== $this->user
                || $segments[1] !== $this->repository) {
                throw new CSystemUpdateException('The file URL does not belong to the configured GitHub repository.');
            }
            $ref = rawurldecode($segments[2]);
            $fileSegments = array_slice($segments, 3);
        } else {
            throw new CSystemUpdateException('Only GitHub repository file URLs are allowed.');
        }

        if (!preg_match('/^[A-Za-z0-9_.-]{1,128}$/', $ref)) {
            throw new CSystemUpdateException('Invalid GitHub revision in file URL.');
        }

        $urlName = $this->normalizeName(rawurldecode(implode('/', $fileSegments)));
        if ($urlName !== $relativeName) {
            throw new CSystemUpdateException('The URL path does not match the requested file.');
        }

        $encodedName = implode('/', array_map('rawurlencode', explode('/', $relativeName)));
        return 'https://raw.githubusercontent.com/' . rawurlencode($this->user)
            . '/' . rawurlencode($this->repository) . '/' . rawurlencode($ref) . '/' . $encodedName;
    }

    protected function normalizeName($name)
    {
        if (!is_string($name)
            || $name === ''
            || strpos($name, '\\') !== false
            || strpos($name, ':') !== false
            || preg_match('/[\x00-\x1F\x7F<>:"|?*]/', $name)
            || $name[0] === '/') {
            throw new CSystemUpdateException('Invalid repository file path.');
        }

        $segments = explode('/', $name);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new CSystemUpdateException('Invalid repository file path.');
            }
        }

        return $name;
    }

    protected function destinationName($relativeName)
    {
        return $this->destinationPrefix === ''
            ? $relativeName
            : $this->destinationPrefix . '/' . $relativeName;
    }

    protected function resolvePath($relativeName)
    {
        $root = realpath(site_root);
        if ($root === false || !is_dir($root)) {
            throw new CSystemUpdateException('Unable to resolve the CMS root directory.');
        }

        $path = $root;
        $segments = explode('/', $relativeName);
        foreach ($segments as $index => $segment) {
            $path .= DIRECTORY_SEPARATOR . $segment;
            if (is_link($path)) {
                throw new CSystemUpdateException('Symbolic links cannot be replaced by the updater.');
            }
            if (file_exists($path)) {
                $resolved = realpath($path);
                if ($resolved === false || !$this->isWithinRoot($root, $resolved)) {
                    throw new CSystemUpdateException('The requested path is outside the CMS root.');
                }
                if ($index < count($segments) - 1 && !is_dir($resolved)) {
                    throw new CSystemUpdateException('A parent path component is not a directory.');
                }
                $path = $resolved;
            }
        }

        return [$root, $path];
    }

    protected function isWithinRoot($root, $path)
    {
        $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $prefix = $root . DIRECTORY_SEPARATOR;
        if (DIRECTORY_SEPARATOR === '\\') {
            $root = strtolower($root);
            $path = strtolower($path);
            $prefix = strtolower($prefix);
        }

        return $path === $root || strpos($path, $prefix) === 0;
    }

    protected function ensureParentDirectory($root, $relativeName)
    {
        $segments = explode('/', dirname($relativeName));
        if (count($segments) === 1 && $segments[0] === '.') {
            return;
        }

        $path = $root;
        foreach ($segments as $segment) {
            $path .= DIRECTORY_SEPARATOR . $segment;
            if (is_link($path)) {
                throw new CSystemUpdateException('Symbolic links cannot be used as updater directories.');
            }
            if (!is_dir($path) && !@mkdir($path, 0755) && !is_dir($path)) {
                throw new CSystemUpdateException('Unable to create destination directory.');
            }
            $resolved = realpath($path);
            if ($resolved === false || !$this->isWithinRoot($root, $resolved)) {
                throw new CSystemUpdateException('The destination directory is outside the CMS root.');
            }
            $path = $resolved;
        }
    }

    protected function writeFile($relativeName, $contents)
    {
        list($root, $target) = $this->resolvePath($relativeName);
        $this->ensureParentDirectory($root, $relativeName);
        list($root, $target) = $this->resolvePath($relativeName);
        if (is_dir($target)) {
            throw new CSystemUpdateException('The destination path is a directory: ' . $relativeName);
        }
        $directory = dirname($target);
        $temporary = tempnam($directory, '.ngcms-update-');
        if ($temporary === false) {
            throw new CSystemUpdateException('Unable to create a temporary update file.');
        }

        $written = file_put_contents($temporary, $contents, LOCK_EX);
        if ($written === false || $written !== strlen($contents)) {
            unlink($temporary);
            throw new CSystemUpdateException('Unable to write the downloaded update file.');
        }

        $backup = '';
        if (file_exists($target)) {
            $backup = $target . '.ngcms-backup-' . uniqid('', true);
            if (!rename($target, $backup)) {
                unlink($temporary);
                throw new CSystemUpdateException('Unable to prepare the existing file for replacement.');
            }
        }

        if (!rename($temporary, $target)) {
            if ($backup !== '') {
                rename($backup, $target);
            }
            unlink($temporary);
            throw new CSystemUpdateException('Unable to replace the destination file.');
        }

        if ($backup !== '' && file_exists($backup) && !unlink($backup)) {
            throw new CSystemUpdateException('The file was updated, but its temporary backup could not be removed.');
        }
    }

    protected function removeFile($relativeName)
    {
        list($root, $path) = $this->resolvePath($relativeName);
        if (!file_exists($path)) {
            return;
        }
        if (!is_file($path) || !unlink($path)) {
            throw new CSystemUpdateException('Unable to remove destination file: ' . $relativeName);
        }
    }

    protected function setError($message)
    {
        $this->answer = [
            'status' => 0,
            'errorCode' => 999,
            'errorText' => $message,
        ];
    }
}

class CSystemUpdateException extends RuntimeException
{
}

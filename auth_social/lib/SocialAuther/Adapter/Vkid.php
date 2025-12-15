<?php
namespace SocialAuther\Adapter;
class Vkid extends AbstractAdapter
{
    protected $scope = null;
    protected $authUrl = 'https://id.vk.com/authorize';
    protected $tokenUrl = 'https://id.vk.com/oauth2/auth';
    public function __construct($config)
    {
        parent::__construct($config);
        if (isset($config['scope']) && is_string($config['scope'])) {
            $this->scope = $config['scope'];
        }
        if (!empty($config['auth_url'])) {
            $this->authUrl = $config['auth_url'];
        }
        if (!empty($config['token_url'])) {
            $this->tokenUrl = $config['token_url'];
        }
        $this->socialFieldsMap = array(
            'socialId'   => 'uid',
            'email'      => 'email',
            'avatar'     => 'photo_big',
            'birthday'   => 'bdate',
            'phone'      => 'mobile_phone',
            'country'    => 'country_name',
            'city'       => 'city_name',
            'screenName' => 'screen_name'
        );
        $this->provider = 'vkid';
    }
    public function getName()
    {
        $result = null;
        if (isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
        } elseif (isset($this->userInfo['first_name'])) {
            $result = $this->userInfo['first_name'];
        } elseif (isset($this->userInfo['last_name'])) {
            $result = $this->userInfo['last_name'];
        }
        return $result;
    }
    public function getSocialPage()
    {
        $result = null;
        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://vk.com/' . $this->userInfo['screen_name'];
        }
        return $result;
    }
    public function getSex()
    {
        $result = null;
        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }
        return $result;
    }
    public function authenticate()
    {
        // $this->log('[VKID] authenticate called', ['has_code' => isset($_GET['code']), 'GET' => $_GET]);
        $result = false;
        if (isset($_GET['code'])) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            // $this->log('[VKID] session started', ['session_id' => session_id(), 'vkid_state' => $_SESSION['vkid_state'] ?? 'none', 'vkid_code_verifier' => isset($_SESSION['vkid_code_verifier']) ? 'exists' : 'none']);
            $code = $_GET['code'];
            $deviceId = isset($_GET['device_id']) ? $_GET['device_id'] : null;
            // $this->log('[VKID] authenticate start', ['code' => $code, 'device_id' => $deviceId, 'redirect' => $this->redirectUri]);
            if (isset($_SESSION['vkid_state']) && isset($_GET['state']) && $_SESSION['vkid_state'] !== $_GET['state']) {
                // $this->log('[VKID] state mismatch', ['session_state' => $_SESSION['vkid_state'] ?? null, 'state' => $_GET['state'] ?? null]);
                return false;
            }
            $codeVerifier = isset($_SESSION['vkid_code_verifier']) ? $_SESSION['vkid_code_verifier'] : null;
            $params = array(
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->clientId,
                'code'          => $code,
                'redirect_uri'  => $this->redirectUri,
            );
            if (!empty($this->clientSecret)) {
                $params['client_secret'] = $this->clientSecret;
            }
            if (!empty($codeVerifier)) {
                $params['code_verifier'] = $codeVerifier;
            }
            if (!empty($deviceId)) {
                $params['device_id'] = $deviceId;
            }
            // $this->log('[VKID] requesting token', ['url' => $this->tokenUrl, 'params' => $params]);
            // Get raw response first
            $rawResponse = $this->post($this->tokenUrl, $params, false);
            // $this->log('[VKID] raw token response', ['raw' => $rawResponse]);
            $tokenInfo = json_decode($rawResponse, true);
            // $this->log('[VKID] parsed token response', ['response' => $tokenInfo]);
            if (isset($tokenInfo['access_token'])) {
                $accessToken = $tokenInfo['access_token'];
                $userId = isset($tokenInfo['user_id']) ? $tokenInfo['user_id'] : null;
                $email = isset($tokenInfo['email']) ? $tokenInfo['email'] : null;
                $params = array(
                    'fields'       => 'id,first_name,last_name,screen_name,sex,bdate,photo_big,city,country,contacts',
                    'access_token' => $accessToken,
                    'v'            => '5.131',
                );
                if ($userId) {
                    // For API v5.x use user_ids
                    $params['user_ids'] = $userId;
                }
                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);
                // $this->log('[VKID] users.get response', $userInfo);
                if (isset($userInfo['response'][0])) {
                    $this->userInfo = $userInfo['response'][0];
                    if ($email) {
                        $this->userInfo['email'] = $email;
                    }
                    // Normalize socialId to legacy key 'uid'
                    if (!isset($this->userInfo['uid'])) {
                        if (isset($this->userInfo['id'])) {
                            $this->userInfo['uid'] = $this->userInfo['id'];
                        } elseif (isset($tokenInfo['user_id'])) {
                            $this->userInfo['uid'] = $tokenInfo['user_id'];
                        }
                    }
                    // $this->log('[VKID] authenticate success', ['uid' => $this->userInfo['uid'] ?? null, 'email' => $this->userInfo['email'] ?? null]);
                    $result = true;
                }
            } else {
                // $this->log('[VKID] access_token missing', ['params' => $params]);
            }
        }
        return $result;
    }
    public function prepareAuthParams()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $verifier = $this->generateCodeVerifier();
        $_SESSION['vkid_code_verifier'] = $verifier;
        $challenge = $this->codeChallengeS256($verifier);
        $state = $this->generateState();
        $_SESSION['vkid_state'] = $state;
        $params = array(
            'client_id'             => $this->clientId,
            'redirect_uri'          => $this->redirectUri,
            'response_type'         => 'code',
            'code_challenge'        => $challenge,
            'code_challenge_method' => 'S256',
        );
        $params['state'] = $state;
        if (!empty($this->scope)) {
            $params['scope'] = $this->scope;
        }
        return array(
            'auth_url'    => $this->authUrl,
            'auth_params' => $params,
        );
    }
    protected function generateCodeVerifier($length = 64)
    {
        $raw = random_bytes($length);
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
    protected function codeChallengeS256($verifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
    protected function generateState($length = 24)
    {
        $raw = random_bytes($length);
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
    protected function log($message, $data = null)
    {
        try {
            $payload = '[' . date('Y-m-d H:i:s') . '] ' . $message;
            if ($data !== null) {
                $payload .= ' ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $file = $_SERVER['DOCUMENT_ROOT'] . '/engine/plugins/auth_social/log.txt';
            @file_put_contents($file, $payload . "\n", FILE_APPEND);
        } catch (\Throwable $e) {
            // ignore logging errors
        }
    }
}

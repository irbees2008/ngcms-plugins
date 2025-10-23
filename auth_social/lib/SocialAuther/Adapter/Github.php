<?php

namespace SocialAuther\Adapter;

class Github extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'id',
            'email'      => 'email',
            'avatar'     => 'avatar_url',
            'socialPage' => 'html_url',
            'name'       => 'name',
        );

        $this->provider = 'github';
    }

    public function getName()
    {
        // Prefer full name; fallback to login
        if (isset($this->userInfo['name']) && $this->userInfo['name']) {
            return $this->userInfo['name'];
        }
        return isset($this->userInfo['login']) ? $this->userInfo['login'] : null;
    }

    public function authenticate()
    {
        $result = false;
        if (isset($_GET['code'])) {
            $tokenUrl = 'https://github.com/login/oauth/access_token';
            $params = array(
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code'          => $_GET['code'],
                'redirect_uri'  => $this->redirectUri,
            );
            // GitHub returns urlencoded by default; parse manually
            $tokenRaw = $this->post($tokenUrl, $params, false);
            $tokenInfo = array();
            parse_str($tokenRaw, $tokenInfo);
            if (!empty($tokenInfo['access_token'])) {
                $accessToken = $tokenInfo['access_token'];
                // Fetch user info; GitHub requires User-Agent
                $userInfo = $this->githubGet('https://api.github.com/user', array('access_token' => $accessToken));
                if (is_array($userInfo) && isset($userInfo['id'])) {
                    // Try fetch primary email if not present
                    if (empty($userInfo['email'])) {
                        $emails = $this->githubGet('https://api.github.com/user/emails', array('access_token' => $accessToken));
                        if (is_array($emails)) {
                            foreach ($emails as $e) {
                                if (!empty($e['primary']) && !empty($e['verified']) && !empty($e['email'])) {
                                    $userInfo['email'] = $e['email'];
                                    break;
                                }
                            }
                        }
                    }
                    $this->userInfo = $userInfo;
                    $result = true;
                }
            }
        }
        return $result;
    }

    public function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'https://github.com/login/oauth/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'redirect_uri'  => $this->redirectUri,
                'scope'         => 'read:user user:email',
                'response_type' => 'code',
            ),
        );
    }

    protected function githubGet($url, $params)
    {
        $curl = curl_init();
        $query = $url . '?' . urldecode(http_build_query($params));
        curl_setopt($curl, CURLOPT_URL, $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // Set required User-Agent for GitHub API
        curl_setopt($curl, CURLOPT_USERAGENT, 'NGCMS-SocialAuth');
        $result = curl_exec($curl);
        curl_close($curl);
        $decoded = json_decode($result, true);
        return $decoded;
    }
}

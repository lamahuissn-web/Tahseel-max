<?php

namespace App\Services\Sas4;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Sas4ApiService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $aesKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('sas4.url'), '/');
        $this->username = config('sas4.username');
        $this->password = config('sas4.password');
        $this->aesKey = config('sas4.aes_key');
    }

    /**
     * CryptoJS-compatible AES-256-CBC encryption
     */
    protected function aesEncrypt($data)
    {
        $salt = openssl_random_pseudo_bytes(8);
        $keyIv = '';
        $prev = '';
        while (strlen($keyIv) < 48) {
            $prev = md5($prev . $this->aesKey . $salt, true);
            $keyIv .= $prev;
        }
        $key = substr($keyIv, 0, 32);
        $iv = substr($keyIv, 32, 16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode('Salted__' . $salt . $encrypted);
    }

    /**
     * Get JWT token (cached)
     */
    public function getToken()
    {
        return Cache::remember('sas4_token', config('sas4.token_cache_minutes') * 60, function () {
            $payload = $this->aesEncrypt(json_encode([
                'username' => $this->username,
                'password' => $this->password,
                'language' => 'en',
            ]));

            $response = $this->request('POST', '/admin/api/index.php/api/login', [
                'payload' => $payload,
            ], false);

            if ($response && isset($response['token'])) {
                return $response['token'];
            }

            Log::error('SAS4: Failed to get token', ['response' => $response]);
            return null;
        });
    }

    /**
     * Make HTTP request to SAS 4 API
     */
    protected function request($method, $path, $data = null, $useToken = true)
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);

        $headers = [
            'Accept: application/json',
        ];

        if ($useToken) {
            $token = $this->getToken();
            if (!$token) {
                return null;
            }
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                if (isset($data['payload'])) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    $headers[] = 'Content-Type: application/json';
                }
            }
        } elseif ($data !== null) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            Log::error('SAS4 API error', ['url' => $url, 'code' => $httpCode, 'response' => $response]);
            return null;
        }

        return $decoded;
    }

    /**
     * Search users by query (for autocomplete)
     */
    public function searchUsers($query, $page = 1, $count = 20)
    {
        $payload = $this->aesEncrypt(json_encode([
            'search' => $query,
            'page' => $page,
            'count' => $count,
        ]));

        return $this->request('POST', '/admin/api/index.php/api/index/user', [
            'payload' => $payload,
        ]);
    }

    /**
     * Get user details by username
     */
    public function getUserByUsername($username)
    {
        $result = $this->searchUsers($username, 1, 100);
        if (!$result || !isset($result['data'])) {
            return null;
        }

        foreach ($result['data'] as $user) {
            if (strtolower($user['username']) === strtolower($username)) {
                return $this->getUserById($user['id']);
            }
        }

        return null;
    }

    /**
     * Get user details by ID
     */
    public function getUserById($userId)
    {
        return $this->request('GET', "/admin/api/index.php/api/user/{$userId}");
    }

    /**
     * Get user overview (status, balance, expiration, last IP, etc.)
     */
    public function getUserOverview($userId)
    {
        return $this->request('GET', "/admin/api/index.php/api/user/overview/{$userId}");
    }

    /**
     * Get user traffic data
     */
    public function getUserTraffic($userId)
    {
        $payload = $this->aesEncrypt(json_encode([
            'user_id' => $userId,
        ]));

        return $this->request('POST', '/admin/api/index.php/api/user/traffic', [
            'payload' => $payload,
        ]);
    }

    /**
     * Get list of profiles (bandwidth plans)
     */
    public function getProfiles()
    {
        return $this->request('GET', '/admin/api/index.php/api/list/profile/0');
    }

    /**
     * Get online users list
     */
    public function getOnlineUsers($search = null)
    {
        $data = [];
        if ($search) {
            $data['search'] = $search;
        }
        $payload = $this->aesEncrypt(json_encode($data));

        return $this->request('POST', '/admin/api/index.php/api/index/online', [
            'payload' => $payload,
        ]);
    }

    /**
     * Ping a user
     */
    public function pingUser($userId)
    {
        $payload = $this->aesEncrypt(json_encode([
            'user_id' => $userId,
        ]));

        return $this->request('POST', '/admin/api/index.php/api/user/ping', [
            'payload' => $payload,
        ]);
    }

    /**
     * Get all data for a SAS 4 user by username
     */
    public function getUserFullInfo($username)
    {
        $user = $this->getUserByUsername($username);
        if (!$user || !isset($user['data'])) {
            return null;
        }

        $userData = $user['data'];
        $userId = $userData['id'];

        $overview = $this->getUserOverview($userId);
        $traffic = $this->getUserTraffic($userId);

        return [
            'user' => $userData,
            'overview' => $overview ? ($overview['data'] ?? $overview) : null,
            'traffic' => $traffic ? ($traffic['data'] ?? $traffic) : null,
        ];
    }

    /**
     * Create a new SAS 4 user
     */
    public function createUser($username, $password, $profileId, $firstname = '')
    {
        $payload = $this->aesEncrypt(json_encode([
            'username' => $username,
            'password' => $password,
            'profile_id' => $profileId,
            'firstname' => $firstname,
            'enabled' => 1,
        ]));

        return $this->request('POST', '/admin/api/index.php/api/user', [
            'payload' => $payload,
        ]);
    }

    /**
     * Check if a username exists
     */
    public function usernameExists($username)
    {
        $result = $this->searchUsers($username, 1, 100);
        if (!$result || !isset($result['data'])) {
            return false;
        }

        foreach ($result['data'] as $user) {
            if (strtolower($user['username']) === strtolower($username)) {
                return true;
            }
        }

        return false;
    }
}

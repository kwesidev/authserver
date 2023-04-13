<?php 
/**
 * JwtAuth
 * Jwt Library 
 */
class WebAuthorizationClient {
    const AuthServerUrl = 'http://localhost:8080';
    /**
     * Function to login
     * @param string username
     * @param string password
     */
    public static function login(string $username, string $password): bool {
        $curl = curl_init();
        $jsonData = json_encode([
            'username' => $username,
            'password' => $password,
        ]);
        curl_setopt($curl, CURLOPT_URL, WebAuthorizationClient::AuthServerUrl."/api/auth/login");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($curl), true);
        if (array_key_exists("token", $result)) {
                $_SESSION['token'] = $result['token'];
                $_SESSION['refreshToken'] = $result['refreshToken'];
                return true;
        }
        return false;

    }
    
    /**
     * We assume we get refreshToken from session
     */
    public static function refreshToken(): bool {
        $curl = curl_init();
        $jsonData = json_encode([
            'refreshToken' => $_SESSION['refreshToken']
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, WebAuthorizationClient::AuthServerUrl . "/api/auth/tokenRefresh");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        $result = json_decode(curl_exec($curl) , true);
        if (array_key_exists("token",$result)) {
            $_SESSION['token'] = $result['token'];
            $_SESSION['refreshToken'] = $result['refreshToken'];
            return true;
        }
        return false;
    }
    /**
     * Function to check the login status
     * @param {string} $token 
     */
    public static function checkAuth(): bool {
        if (!isset($_SESSION['token'])) {
            return false;
        }
        $token = $_SESSION['token'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, WebAuthorizationClient::AuthServerUrl . "/api/user");
        curl_setopt($curl, CURLOPT_HTTPHEADER,array('token:'.$token));
        $result = json_decode(curl_exec($curl),true);
        if (array_key_exists("id",$result)) {
            return true;
        }
        else {
            return false;
        }
    }
    /**
     * Function to logout
     * @param {string}  
     */
    public static function logout(): bool {
        //session_regenerate_id();
        session_destroy();
        return true;

    }
    /**
     * Function to get user details
     * @param {string} token
     */
    public static function getUserDetails(): array {
        if (!isset($_SESSION['token'])) {
            return [];
        }
        $token = $_SESSION['token'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, WebAuthorizationClient::AuthServerUrl . "/api/user");
        curl_setopt($curl, CURLOPT_HTTPHEADER,array('token:'.$token));
        $result = json_decode(curl_exec($curl),true);
        if (array_key_exists("id",$result)) {
            return $result;
        }
        else {
            return [];
        }
    }
}
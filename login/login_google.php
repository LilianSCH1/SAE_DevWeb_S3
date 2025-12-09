<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();

$GOOGLE_CLIENT_ID = $_SERVER['GOOGLE_CLIENT_ID'] ?? '';
$GOOGLE_CLIENT_SECRET = $_SERVER['GOOGLE_CLIENT_SECRET'] ?? '';

error_reporting(E_ALL & ~E_DEPRECATED);

// FIX SSL WAMP
set_time_limit(0);
ini_set('default_socket_timeout', 300);

// Bypass SSL pour localhost
putenv('CURL_CA_BUNDLE=');

require_once '../vendor/autoload.php';

$client = new Google\Client();
$client->setClientId($GOOGLE_CLIENT_ID);
$client->setClientSecret($GOOGLE_CLIENT_SECRET);
$client->setRedirectUri('http://localhost/SAE_DevWeb_S3/login/login_google.php');
$client->addScope([
    Google_Service_Oauth2::USERINFO_EMAIL,
    Google_Service_Oauth2::USERINFO_PROFILE
]);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token);
        $service = new Google_Service_Oauth2($client);
        $userInfo = $service->userinfo->get();
        
        $_SESSION['google_user'] = [
            'id' => $userInfo->id,
            'email' => $userInfo->email,
            'name' => $userInfo->name,
            'picture' => $userInfo->picture
        ];
        header('Location: ../index/index.php');
        exit;
    }
}

$authUrl = $client->createAuthUrl();
header('Location: ' . $authUrl);
exit;
?>

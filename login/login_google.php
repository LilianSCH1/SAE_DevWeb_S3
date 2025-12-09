<?php
session_start();
require_once 'vendor/autoload.php';

$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_$_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost:8080/login_google.php'); // WAMP
$client->addScope(['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile']);

$payload = $client->setAccessType('offline')->createAuthUrl();
if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($accessToken);
    
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    }
    
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
?>
<a href="<?= $client->createAuthUrl() ?>">Login Google</a>

<?php
session_start();
require_once 'vendor/autoload.php';

$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);  // ← ICI CORRIGÉ
$client->setRedirectUri('http://localhost/login_google.php');
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
        header('Location: /dashboard.php');
        exit;
    }
}

$authUrl = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html>
<head><title>Google Login</title></head>
<body>
    <a href="<?= $authUrl ?>" class="btn btn-primary">
        <i class="bi bi-google"></i> Se connecter avec Google
    </a>
</body>
</html>

<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/commons/env.php';
require_once __DIR__ . '/commons/SessionSecurity.php';

SessionSecurity::initialize(__DIR__ . '/storage/sessions');
SessionSecurity::start();

if (trim((string)GOOGLE_CLIENT_ID) === '' || trim((string)GOOGLE_CLIENT_SECRET) === '') {
	$_SESSION['error'] = 'Dang nhap Google chua duoc cau hinh. Vui long cap nhat GOOGLE_CLIENT_ID va GOOGLE_CLIENT_SECRET trong file .env';
	header('Location: index.php?act=auth/login');
	exit;
}

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile');
$client->setPrompt('select_account');
$client->setState(SessionSecurity::generateOAuthState('google'));
$auth_url = $client->createAuthUrl();
header('Location: ' . $auth_url);
exit;

<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/commons/env.php';
require_once __DIR__ . '/commons/function.php';

initializeSecureSession(__DIR__ . '/storage/sessions');

if (!verifyOAuthState('google', $_GET['state'] ?? '')) {
    $_SESSION['error'] = 'Phien dang nhap Google khong hop le. Vui long thu lai.';
    header('Location: index.php?act=auth/login');
    exit;
}

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

if (!empty($_GET['error'])) {
    $_SESSION['error'] = 'Dang nhap Google that bai: ' . sanitizeText((string)$_GET['error']);
    header('Location: index.php?act=auth/login');
    exit;
}

if (empty($_GET['code'])) {
    $_SESSION['error'] = 'Khong nhan duoc ma xac thuc tu Google.';
    header('Location: index.php?act=auth/login');
    exit;
}

$token = $client->fetchAccessTokenWithAuthCode((string)$_GET['code']);
if (isset($token['error']) || empty($token['access_token'])) {
    $_SESSION['error'] = 'Khong the xac thuc voi Google. Vui long thu lai.';
    header('Location: index.php?act=auth/login');
    exit;
}

$client->setAccessToken($token['access_token']);
$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();

$email = trim((string)($userInfo->email ?? ''));
if ($email === '') {
    $_SESSION['error'] = 'Tai khoan Google khong cung cap email hop le.';
    header('Location: index.php?act=auth/login');
    exit;
}

require_once __DIR__ . '/models/NguoiDung.php';
require_once __DIR__ . '/models/KhachHang.php';

$nguoiDungModel = new NguoiDung();
$khachHangModel = new KhachHang();

$nguoiDung = $nguoiDungModel->findByEmail($email);
if (!$nguoiDung) {
    $localPassword = generateTemporaryPassword(20);
    $nguoiDungId = $nguoiDungModel->insert([
        'ten_dang_nhap' => $email,
        'ho_ten' => trim((string)($userInfo->name ?? 'Nguoi dung Google')),
        'email' => $email,
        'so_dien_thoai' => '',
        'mat_khau' => password_hash($localPassword, PASSWORD_DEFAULT),
        'vai_tro' => 'KhachHang',
        'ngay_tao' => date('Y-m-d H:i:s')
    ]);

    if (!$nguoiDungId) {
        $_SESSION['error'] = 'Khong the tao tai khoan tu Google. Vui long thu lai sau.';
        header('Location: index.php?act=auth/login');
        exit;
    }

    $nguoiDung = $nguoiDungModel->findById((int)$nguoiDungId);
}

if (!$nguoiDung) {
    $_SESSION['error'] = 'Khong the doc thong tin tai khoan Google sau khi xac thuc.';
    header('Location: index.php?act=auth/login');
    exit;
}

$nguoiDungId = (int)($nguoiDung['id'] ?? 0);
$vaiTro = (string)($nguoiDung['vai_tro'] ?? 'KhachHang');

$sessionData = [];

if ($vaiTro === 'KhachHang' && $nguoiDungId > 0) {
    $khachHang = $khachHangModel->findByNguoiDungId($nguoiDungId);
    if (!$khachHang) {
        $khachHangId = $khachHangModel->insert(['nguoi_dung_id' => $nguoiDungId]);
        if ($khachHangId) {
            $sessionData['khach_hang_id'] = (int)$khachHangId;
        }
    } else {
        $sessionData['khach_hang_id'] = (int)($khachHang['khach_hang_id'] ?? 0);
    }
}

$sessionData['user'] = [
    'id' => $nguoiDungId,
    'email' => (string)($nguoiDung['email'] ?? $email),
    'name' => (string)($nguoiDung['ho_ten'] ?? ($userInfo->name ?? 'Nguoi dung')),
    'picture' => (string)($userInfo->picture ?? ''),
    'role' => $vaiTro,
];

completeUserLoginSession(
    $nguoiDungId,
    $vaiTro,
    (string)($nguoiDung['ho_ten'] ?? ($userInfo->name ?? 'Nguoi dung')),
    $sessionData
);

if ($vaiTro === 'Admin') {
    header('Location: index.php?act=admin/dashboard');
    exit;
}
if ($vaiTro === 'HDV') {
    header('Location: index.php?act=hdv/dashboard');
    exit;
}
if ($vaiTro === 'NhaCungCap') {
    header('Location: index.php?act=nhaCungCap/dichVu');
    exit;
}

header('Location: index.php?act=khachHang/dashboard');
exit;

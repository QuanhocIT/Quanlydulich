<?php

class Authorization {
    public const AUTHENTICATED = '__authenticated__';

    public static function isLoggedIn() {
        if (!isset($_SESSION)) {
            session_start();
        }

        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
    }

    public static function currentRole() {
        if (!isset($_SESSION)) {
            session_start();
        }

        $role = trim((string)($_SESSION['role'] ?? ''));
        return $role !== '' ? $role : null;
    }

    public static function hasAnyRole(array|string $roles): bool {
        $role = self::currentRole();
        if ($role === null) {
            return false;
        }

        $allowedRoles = is_array($roles) ? $roles : [$roles];
        return in_array($role, $allowedRoles, true);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: index.php?act=auth/login');
            exit();
        }
    }

    public static function requireRole(array|string $roles, ?string $redirectAct = null, string $message = 'Ban khong co quyen truy cap chuc nang nay.'): void {
        $allowedRoles = is_array($roles) ? array_values($roles) : [$roles];

        if (in_array(self::AUTHENTICATED, $allowedRoles, true)) {
            self::requireLogin();
            return;
        }

        self::requireLogin();
        if (!self::hasAnyRole($allowedRoles)) {
            self::deny($message, $redirectAct ?: self::getRoleHomeRoute());
        }
    }

    public static function getRoleHomeRoute($role = null) {
        $role = $role ?: self::currentRole();

        return match ($role) {
            'Admin' => 'admin/dashboard',
            'HDV' => 'hdv/dashboard',
            'KhachHang' => 'khachHang/dashboard',
            'NhaCungCap' => 'nhaCungCap/dichVu',
            default => 'auth/login',
        };
    }

    public static function redirectToRoleHome($defaultAct = 'auth/login') {
        $targetAct = self::getRoleHomeRoute();
        if ($targetAct === 'auth/login' && $defaultAct !== '') {
            $targetAct = $defaultAct;
        }

        header('Location: index.php?act=' . $targetAct);
        exit();
    }

    public static function getRouteRoleMatrix() {
        return [
            'module_defaults' => [
                'admin' => ['Admin'],
                'hdv' => ['HDV'],
                'nhaCungCap' => ['NhaCungCap'],
                'khachHang' => ['KhachHang'],
            ],
            'public_routes' => [
                'auth/login',
                'auth/register',
                'auth/verify2fa',
                'auth/verifyEmail',
                'auth/resendVerification',
                'auth/forgotPassword',
                'auth/resetPassword',
                'auth/logout',
                'tour/index',
                'tour/show',
                'payment/callback',
                'payment/vnpayIpn',
                'payment/bankWebhook',
            ],
            'route_overrides' => [
                'auth/profile' => [self::AUTHENTICATED],
                'auth/forcePasswordChange' => [self::AUTHENTICATED],
                'auth/setup2fa' => ['Admin'],
                'booking/index' => ['Admin', 'KhachHang'],
                'booking/create' => ['KhachHang'],
                'booking/show' => [self::AUTHENTICATED],
                'booking/chiTiet' => ['Admin', 'HDV', 'KhachHang'],
                'booking/update' => ['Admin', 'HDV'],
                'booking/updateTrangThai' => ['Admin', 'HDV'],
                'booking/updateTienCoc' => ['Admin', 'HDV'],
                'booking/delete' => ['Admin'],
                'booking/hideCompleted' => ['Admin'],
                'booking/datTourChoKhach' => ['Admin'],
                'booking/kiemTraChoTrong' => [self::AUTHENTICATED],
                'booking/xuatTaiLieu' => ['Admin'],
                'booking/exportPDF' => ['Admin'],
                'booking/sendEmail' => ['Admin'],
                'tour/create' => ['Admin'],
                'tour/update' => ['Admin'],
                'tour/delete' => ['Admin'],
                'tour/clone' => ['Admin'],
                'tour/generateQr' => ['Admin'],
                'tour/taoLichKhoiHanh' => ['Admin'],
                'tour/chiTietLichKhoiHanh' => ['Admin'],
                'tour/phanBoNhanSuLichKhoiHanh' => ['Admin'],
                'tour/updateTrangThaiNhanSuLichKhoiHanh' => ['Admin'],
                'tour/phanBoDichVuLichKhoiHanh' => ['Admin'],
                'tour/updateTrangThaiDichVuLichKhoiHanh' => ['Admin'],
                'tour/deleteNhanSuLichKhoiHanh' => ['Admin'],
                'tour/deleteDichVuLichKhoiHanh' => ['Admin'],
                'payment/redirect' => [self::AUTHENTICATED],
            ],
        ];
    }

    public static function resolveAllowedRolesForRoute(string $act): array|null {
        $matrix = self::getRouteRoleMatrix();

        if (in_array($act, $matrix['public_routes'], true)) {
            return [];
        }

        if (isset($matrix['route_overrides'][$act])) {
            return $matrix['route_overrides'][$act];
        }

        [$module] = explode('/', (string)$act, 2);
        return $matrix['module_defaults'][$module] ?? null;
    }

    public static function enforceRouteAccess(string $act): bool {
        $allowedRoles = self::resolveAllowedRolesForRoute($act);
        if ($allowedRoles === null || $allowedRoles === []) {
            return true;
        }

        self::requireRole($allowedRoles);
        return true;
    }

    public static function deny($message = 'Ban khong co quyen truy cap chuc nang nay.', $redirectAct = 'tour/index') {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (function_exists('setValidationErrors')) {
            setValidationErrors(['permission' => 'denied'], $message);
        }

        $_SESSION['error'] = $message;
        header('Location: index.php?act=' . ($redirectAct ?: 'tour/index'));
        exit();
    }
}
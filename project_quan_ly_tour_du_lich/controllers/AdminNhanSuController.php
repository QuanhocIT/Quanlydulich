<?php
class AdminNhanSuController {
    public function __construct() {
        requireRole('Admin');
    }

    private function requirePostCsrf(string $redirectAct = 'admin/dashboard') {
        $scopedToken = $_POST['_csrf_token'] ?? '';
        $globalToken = $_POST['_csrf_global'] ?? '';

        $validScoped = verifyCsrfToken($scopedToken, 'admin_form');
        $validGlobal = verifyCsrfToken($globalToken, 'global_form');

        if (!$validScoped && !$validGlobal) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=' . urlencode($redirectAct));
            exit;
        }
    }

    private function optionalPostString(string $key) {
        $value = requestString($key, '', 'POST');
        return $value === '' ? null : $value;
    }

    private function optionalPostEmail(string $key) {
        if (!isset($_POST[$key])) {
            return null;
        }
        $rawValue = sanitizeText($_POST[$key]);
        if ($rawValue === '') {
            return null;
        }
        return validateEmail($rawValue);
    }

    private function optionalPostPhone(string $key) {
        if (!isset($_POST[$key])) {
            return null;
        }
        $rawValue = sanitizeText($_POST[$key]);
        if ($rawValue === '') {
            return null;
        }
        return validatePhone($rawValue);
    }

    private function optionalPostDate(string $key) {
        if (!isset($_POST[$key])) {
            return null;
        }
        $rawValue = sanitizeText($_POST[$key]);
        if ($rawValue === '') {
            return null;
        }
        return validateDateYmd($rawValue);
    }

    private function optionalPostId(string $key) {
        return validateId($_POST[$key] ?? null);
    }

    private function chonAnhChinh(array $hinhAnhList) {
        foreach ($hinhAnhList as $anh) {
            if (!empty($anh['url_anh'])) {
                return $anh;
            }
        }
        return null;
    }

    public function nhanSu() {
        $nhanSuModel = new NhanSu();
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';
        $allNhanSu = $nhanSuModel->getAll();
        $data_by_role = [];
        foreach ($allNhanSu as $item) {
            $itemRole = trim((string)($item['vai_tro'] ?? ''));
            if ($itemRole === '') {
                $itemRole = 'Khac';
            }
            if (!isset($data_by_role[$itemRole])) {
                $data_by_role[$itemRole] = [];
            }
            $data_by_role[$itemRole][] = $item;
        }
        $roles = array_keys($data_by_role);
        sort($roles);

        // apply filters: if search query, search across all; if role filter, use that role's data
        if ($q !== '') {
            $nhan_su_list = $nhanSuModel->search($q);
            $active_role = null;
        } elseif ($role !== '' && isset($data_by_role[$role])) {
            $nhan_su_list = $data_by_role[$role];
            $active_role = $role;
        } else {
            $nhan_su_list = $allNhanSu;
            $active_role = null;
        }

        require 'views/admin/quan_ly_nhan_su.php';
    }

    // Admin: quản lý HDV (danh sách + CRUD cơ bản)
    public function quanLyHDV() {
        $hdvModel = new HDV();
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if ($q !== '') {
            // sử dụng search trên nhan_su (tạm gọi chung)
            $ns = new NhanSu();
            $hdv_list = $ns->search($q);
        } else {
            $hdv_list = $hdvModel->getAll($groupId);
        }
        // load groups
        $groups = [];
        try {
            $stmt = $hdvModel->conn->prepare('SELECT * FROM hdv_groups ORDER BY name ASC');
            $stmt->execute();
            $groups = $stmt->fetchAll();
        } catch (Exception $e) {
            // ignore if table not exists
        }
        require 'views/admin/quan_ly_hdv.php';
    }

    public function quanLyHDVCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/quanLyHDV');
            $model = new HDV();
            $hoTen = requestString('ho_ten', '', 'POST');
            $ngaySinh = $this->optionalPostDate('ngay_sinh');
            $soDienThoai = $this->optionalPostPhone('so_dien_thoai');
            $email = $this->optionalPostEmail('email');
            $groupId = $this->optionalPostId('group_id');

            if ($hoTen === '') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Họ tên HDV không được để trống.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            if (isset($_POST['ngay_sinh']) && sanitizeText($_POST['ngay_sinh']) !== '' && $ngaySinh === null) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ngày sinh không hợp lệ.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            if (isset($_POST['so_dien_thoai']) && sanitizeText($_POST['so_dien_thoai']) !== '' && $soDienThoai === null) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Số điện thoại không hợp lệ.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            if (isset($_POST['email']) && sanitizeText($_POST['email']) !== '' && $email === null) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không hợp lệ.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            $data = [
                'ho_ten' => $hoTen,
                'ngay_sinh' => $ngaySinh,
                'anh' => $this->optionalPostString('anh'),
                'so_dien_thoai' => $soDienThoai,
                'email' => $email,
                'dia_chi' => $this->optionalPostString('dia_chi'),
                'chung_chi' => $this->optionalPostString('chung_chi'),
                'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                'suc_khoe' => $this->optionalPostString('suc_khoe'),
                'group_id' => $groupId,
                'note' => $this->optionalPostString('note'),
            ];
            $ok = $model->insert($data);
            $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Thêm HDV thành công'] : ['type'=>'danger','message'=>'Thêm HDV thất bại'];
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    public function quanLyHDVUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/quanLyHDV');
            $model = new HDV();
            $id = requestInt('nhan_su_id', 0, 'POST');
            if ($id > 0) {
                $hoTen = requestString('ho_ten', '', 'POST');
                $ngaySinh = $this->optionalPostDate('ngay_sinh');
                $soDienThoai = $this->optionalPostPhone('so_dien_thoai');
                $email = $this->optionalPostEmail('email');
                $groupId = $this->optionalPostId('group_id');

                if ($hoTen === '') {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Họ tên HDV không được để trống.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                if (isset($_POST['ngay_sinh']) && sanitizeText($_POST['ngay_sinh']) !== '' && $ngaySinh === null) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ngày sinh không hợp lệ.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                if (isset($_POST['so_dien_thoai']) && sanitizeText($_POST['so_dien_thoai']) !== '' && $soDienThoai === null) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Số điện thoại không hợp lệ.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                if (isset($_POST['email']) && sanitizeText($_POST['email']) !== '' && $email === null) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không hợp lệ.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                $data = [
                    'ho_ten' => $hoTen,
                    'ngay_sinh' => $ngaySinh,
                    'anh' => $this->optionalPostString('anh'),
                    'so_dien_thoai' => $soDienThoai,
                    'email' => $email,
                    'dia_chi' => $this->optionalPostString('dia_chi'),
                    'chung_chi' => $this->optionalPostString('chung_chi'),
                    'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                    'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                    'suc_khoe' => $this->optionalPostString('suc_khoe'),
                    'group_id' => $groupId,
                    'is_available' => isset($_POST['is_available']) ? 1 : 0,
                    'note' => $this->optionalPostString('note'),
                ];
                $ok = $model->update($id, $data);
                $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Cập nhật HDV thành công'] : ['type'=>'danger','message'=>'Cập nhật HDV thất bại'];
            }
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    public function quanLyHDVDelete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $model = new HDV();
            $ok = $model->delete($id);
            $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Xóa HDV thành công'] : ['type'=>'danger','message'=>'Xóa HDV thất bại'];
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    // Hiển thị lịch phân công HDV (calendar)
    public function hdvSchedule() {
        $hdvModel = new HDV();
        // load hdv list
        $hdv_list = $hdvModel->getAll();
        require 'views/admin/hdv_schedule.php';
    }

    // Trang hồ sơ HDV
    public function hdvProfile() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $hdvModel = new HDV();
        $hdv = $hdvModel->findById($id);
        $history = [];
        if ($hdv) {
            $history = $hdvModel->getTourHistory($id, 100);
        }
        require 'views/admin/hdv_profile.php';
    }

    // API: trả về lịch của HDV (JSON)
    public function hdvApiGetSchedule() {
        header('Content-Type: application/json');
        $hdvId = isset($_GET['hdv_id']) ? (int)$_GET['hdv_id'] : 0;
        $hdvModel = new HDV();
        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $events = [];
        if ($hdvId > 0) {
            $rows = $hdvModel->getSchedule($hdvId, $from, $to);
            foreach ($rows as $r) {
                $events[] = [
                    'id' => $r['id'],
                    'title' => 'Tour ' . ($r['tour_id'] ?? ''),
                    'start' => $r['start_time'],
                    'end' => $r['end_time'],
                    'extendedProps' => ['note' => $r['note'] ?? '']
                ];
            }
        }
        echo json_encode($events);
        exit;
    }

    // API: kiểm tra khả dụng
    public function hdvApiCheck() {
        header('Content-Type: application/json');
        $hdvId = isset($_GET['hdv_id']) ? (int)$_GET['hdv_id'] : 0;
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $hdvModel = new HDV();
        $ok = false;
        if ($hdvId && $start && $end) {
            $ok = $hdvModel->isAvailable($hdvId, $start, $end);
        }
        echo json_encode(['available' => $ok]);
        exit;
    }

    // API: phân công (thêm schedule) — POST JSON {hdv_id, tour_id, start, end, note}
    public function hdvApiAssign() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'msg'=>'Method not allowed']); exit; }
        $payload = $_POST;
        $hdvId = isset($payload['hdv_id']) ? (int)$payload['hdv_id'] : 0;
        $tourId = isset($payload['tour_id']) ? (int)$payload['tour_id'] : null;
        $start = $payload['start'] ?? null;
        $end = $payload['end'] ?? null;
        $note = $payload['note'] ?? null;
        $hdvModel = new HDV();
        if (!$hdvId || !$start || !$end) { echo json_encode(['ok'=>false,'msg'=>'Thiếu dữ liệu']); exit; }
        if (!$hdvModel->isAvailable($hdvId, $start, $end)) {
            echo json_encode(['ok'=>false,'msg'=>'HDV không rảnh trong khung thời gian này']); exit;
        }
        $ok = $hdvModel->addSchedule($hdvId, $tourId, $start, $end, $note);
        echo json_encode(['ok'=>$ok]); exit;
    }

    // API: đề xuất HDV rảnh cho khoảng thời gian (trả danh sách hdv_id)
    public function hdvApiSuggest() {
        header('Content-Type: application/json');
        $start   = $_GET['start'] ?? null;
        $end     = $_GET['end']   ?? null;
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $hdvModel   = new HDV();
        $candidates = $hdvModel->getAll($groupId, true);

        if (empty($candidates) || $start === null || $end === null) {
            echo json_encode(['available' => []]); exit;
        }

        // P6: Một batch query thay vì N query trong vòng lặp
        $allIds       = array_map(fn($c) => (int)$c['nhan_su_id'], $candidates);
        $availableIds = array_flip($hdvModel->getAvailableIdsBatch($allIds, $start, $end));

        $available = [];
        foreach ($candidates as $c) {
            if (isset($availableIds[(int)$c['nhan_su_id']])) {
                $available[] = ['id' => (int)$c['nhan_su_id'], 'ho_ten' => $c['ho_ten']];
            }
        }

        echo json_encode(['available' => $available]); exit;
    }

    public function nhanSuCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhanSu');
            $model = new NhanSu();

            $allowedRoles = ['HDV', 'DieuHanh', 'NhaCungCap', 'Khac'];
            $nguoiDungId = requestInt('nguoi_dung_id', 0, 'POST');
            $vaiTro = requestString('vai_tro', 'Khac', 'POST');

            if (!in_array($vaiTro, $allowedRoles, true)) {
                $vaiTro = 'Khac';
            }

            $data = [
                    'nguoi_dung_id' => $nguoiDungId > 0 ? $nguoiDungId : null,
                    'vai_tro' => $vaiTro,
                'chung_chi' => $this->optionalPostString('chung_chi'),
                'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                'suc_khoe' => $this->optionalPostString('suc_khoe'),
            ];

                // Validate: người dùng phải được chọn
                if (empty($data['nguoi_dung_id'])) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Vui lòng chọn người dùng.'];
                } else {
                    $ok = $model->insert($data);
                    if ($ok) {
                        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Thêm nhân sự thành công. Vai trò người dùng đã được cập nhật.'];
                    } else {
                        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Thêm nhân sự thất bại.'];
                    }
                }
        }
        header('Location: index.php?act=admin/nhanSu');
        exit;
    }

    // API: trả về danh sách người dùng chưa có nhân sự (JSON)
    public function nhanSu_get_users() {
        $model = new NhanSu();
        $users = $model->getAvailableUsers();
        header('Content-Type: application/json');
        echo json_encode(['users' => $users]);
        exit;
    }

    public function nhanSuUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhanSu');
            $model = new NhanSu();
            $allowedRoles = ['HDV', 'DieuHanh', 'NhaCungCap', 'Khac'];

            $id = requestInt('nhan_su_id', 0, 'POST');
            if ($id <= 0) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'ID nhân sự không hợp lệ.'];
                header('Location: index.php?act=admin/nhanSu');
                exit;
            }

            $vaiTro = requestString('vai_tro', 'Khac', 'POST');
            if (!in_array($vaiTro, $allowedRoles, true)) {
                $vaiTro = 'Khac';
            }

            $data = [
                'vai_tro' => $vaiTro,
                'chung_chi' => $this->optionalPostString('chung_chi'),
                'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                'suc_khoe' => $this->optionalPostString('suc_khoe'),
            ];
            $ok = $model->update($id, $data);
            if ($ok) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cập nhật nhân sự thành công.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Cập nhật nhân sự thất bại.'];
            }
        }
        header('Location: index.php?act=admin/nhanSu');
        exit;
    }

    public function nhanSuDelete() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/nhanSu');
            exit;
        }

        $this->requirePostCsrf('admin/nhanSu');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $delete_user = isset($_POST['delete_user']) && $_POST['delete_user'] === '1' ? true : false;

        if ($id > 0) {
            $model = new NhanSu();
            if ($delete_user) {
                // kiểm tra blocker quan trọng trước khi xóa (chỉ tour.tao_boi)
                $nhanSu = $model->findById($id);
                if ($nhanSu && !empty($nhanSu['nguoi_dung_id'])) {
                    $blockers = $model->getCriticalDeleteBlockers($nhanSu['nguoi_dung_id']);
                    if (!empty($blockers)) {
                        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không thể xóa tài khoản do: ' . implode(' ', $blockers)];
                        header('Location: index.php?act=admin/nhanSu');
                        exit;
                    }
                }
                $ok = $model->deleteWithUser($id);
                $msg = $ok ? 'Xóa nhân sự, tài khoản và dữ liệu liên quan thành công.' : 'Xóa nhân sú và tài khoản thất bại.';
            } else {
                $ok = $model->delete($id);
                $msg = $ok ? 'Xóa nhân sự thành công. Tài khoản vẫn được giữ.' : 'Xóa nhân sự thất bại.';
            }

            if ($ok) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Xóa thất bại.'];
            }
        }
        header('Location: index.php?act=admin/nhanSu');
        exit;
    }

    // Xem chi tiết sơ yếu lý lịch nhân sự
    public function nhanSuChiTiet() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $nhanSu = null;
        $error = null;

        if ($id <= 0) {
            $error = 'Thiếu mã nhân sự cần xem.';
        } else {
            $model = new NhanSu();
            $nhanSu = $model->findById($id);

            if (!$nhanSu) {
                $error = 'Nhân sự không tồn tại hoặc đã bị xóa.';
            } else {
                // Lấy thêm thông tin vai trò người dùng
                if (!empty($nhanSu['nguoi_dung_id'])) {
                    $nguoiDungModel = new NguoiDung();
                    $nguoiDung = $nguoiDungModel->findById($nhanSu['nguoi_dung_id']);
                    if ($nguoiDung) {
                        $nhanSu['vai_tro_nguoi_dung'] = $nguoiDung['vai_tro'];
                        $nhanSu['quyen_cap_cao'] = $nguoiDung['quyen_cap_cao'];
                        $nhanSu['trang_thai'] = $nguoiDung['trang_thai'];
                        $nhanSu['ngay_tao'] = $nguoiDung['ngay_tao'];
                        $nhanSu['avatar'] = $nguoiDung['avatar'];
                    }
                }
            }
        }

        require 'views/admin/nhan_su_chi_tiet.php';
    }

    // ==================== QUẢN LÝ HDV NÂNG CAO (SỬ DỤNG DATABASE HIỆN CÓ) ====================

    public function hdvAdvanced() {
        $hdvMgmt = new HDVManagement();

        $hdv_list = $hdvMgmt->getAllHDV();
        $stats = $hdvMgmt->getThongKeTongQuan();
        $hieu_suat_list = $hdvMgmt->getBaoCaoHieuSuat();
        $thong_bao_list = $hdvMgmt->getThongBao(null, 20);
        $lich_lam_viec = $hdvMgmt->getAllLichLamViec(); // Lấy tất cả lịch làm việc
        $tourOptions = (new Tour())->getOptions(300);

        require 'views/admin/hdv_quan_ly_nang_cao.php';
    }

    public function hdvAddSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hdvMgmt = new HDVManagement();

            $data = [
                'tour_id' => $_POST['tour_id'],
                'hdv_id' => $_POST['hdv_id'],
                'ngay_khoi_hanh' => $_POST['ngay_khoi_hanh'],
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'],
                'diem_tap_trung' => $_POST['diem_tap_trung'] ?? '',
                'trang_thai' => $_POST['trang_thai'] ?? 'DaXacNhan'
            ];

            $result = $hdvMgmt->phanCongHDV($data);
            $_SESSION['flash'] = [
                'type' => $result['success'] ? 'success' : 'danger',
                'message' => $result['message']
            ];
        }

        header('Location: index.php?act=admin/hdv_advanced');
        exit;
    }

    public function hdvGetSchedule() {
        $hdvMgmt = new HDVManagement();

        $hdv_id = $_GET['hdv_id'] ?? null;
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        $events = $hdvMgmt->getLichLamViec($hdv_id, $start, $end);

        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }

    public function hdvSendNotification() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hdvMgmt = new HDVManagement();

            $data = [
                'nhan_su_id' => !empty($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : null,
                'loai_thong_bao' => $_POST['loai_thong_bao'] ?? 'ThongBao',
                'tieu_de' => $_POST['tieu_de'] ?? '',
                'noi_dung' => $_POST['noi_dung'] ?? '',
                'uu_tien' => $_POST['uu_tien'] ?? 'TrungBinh'
            ];

            $result = $hdvMgmt->guiThongBao($data);

            $_SESSION['flash'] = [
                'type' => $result ? 'success' : 'danger',
                'message' => $result ? 'Gửi thông báo thành công!' : 'Lỗi khi gửi thông báo!'
            ];
        }

        header('Location: index.php?act=admin/hdv_advanced');
        exit;
    }

    public function hdvLichTable() {
        $hdvMgmt = new HDVManagement();

        $hdv_list = $hdvMgmt->getAllHDV();
        $lich_lam_viec = $hdvMgmt->getLichLamViec(); // Lấy tất cả lịch

        require 'views/admin/hdv_lich_lam_viec_table.php';
    }

    public function hdvDetail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $hdvMgmt = new HDVManagement();
        $nhanSuModel = new NhanSu();

        $hdv = $nhanSuModel->findById($id);
        $hieu_suat = $hdvMgmt->getHieuSuatTheoThang($id);
        $danh_gia_list = $hdvMgmt->getDanhGiaByHDV($id);
        $lich_lam_viec = $hdvMgmt->getLichLamViec($id);
        $nhat_ky_list = $hdvMgmt->getNhatKyByHDV($id);

        require 'views/admin/hdv_chi_tiet.php';
    }
}

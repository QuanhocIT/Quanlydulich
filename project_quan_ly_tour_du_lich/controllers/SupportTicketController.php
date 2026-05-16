<?php

require_once 'models/SupportTicket.php';
require_once 'models/KhachHang.php';

class SupportTicketController
{
    private SupportTicket $ticketModel;
    private KhachHang $khachHangModel;

    public function __construct()
    {
        $this->ticketModel = new SupportTicket();
        $this->khachHangModel = new KhachHang();
    }

    private function currentKhachHangId(): int
    {
        $kh = $this->khachHangModel->findByUserId((int)($_SESSION['user_id'] ?? 0));
        return (int)($kh['khach_hang_id'] ?? 0);
    }

    public function customerTickets(): void
    {
        requireRole('KhachHang');

        $khachHangId = $this->currentKhachHangId();
        if ($khachHangId <= 0) {
            $_SESSION['error'] = 'Khong tim thay thong tin khach hang.';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        try {
            $tickets = $this->ticketModel->getByKhachHangId($khachHangId);
        } catch (Throwable $e) {
            $tickets = [];
            $_SESSION['error'] = $e->getMessage();
        }

        require 'views/khach_hang/tickets.php';
    }

    public function customerCreateTicket(): void
    {
        requireRole('KhachHang');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        if (!verifyCsrfToken((string)($_POST['_csrf_global'] ?? ''), 'global_form')) {
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        $khachHangId = $this->currentKhachHangId();
        if ($khachHangId <= 0) {
            $_SESSION['error'] = 'Khong tim thay thong tin khach hang.';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        $priority = trim((string)($_POST['priority'] ?? 'TrungBinh'));
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        if ($subject === '' || mb_strlen($subject) < 5) {
            $_SESSION['error'] = 'Tieu de can toi thieu 5 ky tu.';
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }
        if ($message === '' || mb_strlen($message) < 10) {
            $_SESSION['error'] = 'Noi dung can toi thieu 10 ky tu.';
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        try {
            $ticketId = $this->ticketModel->createTicket([
                'khach_hang_id' => $khachHangId,
                'booking_id' => $bookingId > 0 ? $bookingId : null,
                'subject' => mb_substr($subject, 0, 255),
                'message' => mb_substr($message, 0, 2000),
                'priority' => $priority,
                'sender_id' => (int)($_SESSION['user_id'] ?? 0),
            ]);

            $_SESSION['success'] = 'Da tao ticket ho tro thanh cong.';
            header('Location: index.php?act=khachHang/ticketDetail&id=' . $ticketId);
            exit();
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }
    }

    public function customerTicketDetail(): void
    {
        requireRole('KhachHang');

        $ticketId = (int)($_GET['id'] ?? 0);
        $khachHangId = $this->currentKhachHangId();

        if ($ticketId <= 0 || $khachHangId <= 0) {
            $_SESSION['error'] = 'Thong tin ticket khong hop le.';
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        try {
            $ticket = $this->ticketModel->getByIdForKhachHang($ticketId, $khachHangId);
            if (!$ticket) {
                $_SESSION['error'] = 'Khong tim thay ticket.';
                header('Location: index.php?act=khachHang/tickets');
                exit();
            }
            $messages = $this->ticketModel->getMessagesByTicketId($ticketId);
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        require 'views/khach_hang/ticket_detail.php';
    }

    public function customerTicketReply(): void
    {
        requireRole('KhachHang');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        if (!verifyCsrfToken((string)($_POST['_csrf_global'] ?? ''), 'global_form')) {
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
            header('Location: index.php?act=khachHang/tickets');
            exit();
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $khachHangId = $this->currentKhachHangId();
        $message = trim((string)($_POST['message'] ?? ''));

        if ($ticketId <= 0 || $khachHangId <= 0 || $message === '') {
            $_SESSION['error'] = 'Thong tin phan hoi khong hop le.';
            header('Location: index.php?act=khachHang/ticketDetail&id=' . max(0, $ticketId));
            exit();
        }

        try {
            $ticket = $this->ticketModel->getByIdForKhachHang($ticketId, $khachHangId);
            if (!$ticket) {
                $_SESSION['error'] = 'Khong tim thay ticket.';
                header('Location: index.php?act=khachHang/tickets');
                exit();
            }

            $this->ticketModel->addMessage($ticketId, (int)($_SESSION['user_id'] ?? 0), 'KhachHang', mb_substr($message, 0, 2000));
            $_SESSION['success'] = 'Da gui phan hoi cho ticket.';
            header('Location: index.php?act=khachHang/ticketDetail&id=' . $ticketId);
            exit();
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?act=khachHang/ticketDetail&id=' . $ticketId);
            exit();
        }
    }

    public function adminTickets(): void
    {
        requireRole('Admin');

        $status = trim((string)($_GET['status'] ?? ''));
        $allowed = ['Open', 'InProgress', 'WaitingCustomer', 'Resolved', 'Closed'];
        if ($status !== '' && !in_array($status, $allowed, true)) {
            $status = '';
        }

        try {
            $tickets = $this->ticketModel->getAllForAdmin($status, 500);
        } catch (Throwable $e) {
            $tickets = [];
            $_SESSION['error'] = $e->getMessage();
        }

        require 'views/admin/support_tickets.php';
    }

    public function adminTicketDetail(): void
    {
        requireRole('Admin');

        $ticketId = (int)($_GET['id'] ?? 0);
        if ($ticketId <= 0) {
            $_SESSION['error'] = 'ID ticket khong hop le.';
            header('Location: index.php?act=admin/tickets');
            exit();
        }

        try {
            $ticket = $this->ticketModel->getByIdForAdmin($ticketId);
            if (!$ticket) {
                $_SESSION['error'] = 'Khong tim thay ticket.';
                header('Location: index.php?act=admin/tickets');
                exit();
            }
            $messages = $this->ticketModel->getMessagesByTicketId($ticketId);
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?act=admin/tickets');
            exit();
        }

        require 'views/admin/support_ticket_detail.php';
    }

    public function adminTicketReply(): void
    {
        requireRole('Admin');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?act=admin/tickets');
            exit();
        }

        if (!verifyCsrfToken((string)($_POST['_csrf_global'] ?? ''), 'global_form')) {
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
            header('Location: index.php?act=admin/tickets');
            exit();
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));

        if ($ticketId <= 0 || $message === '') {
            $_SESSION['error'] = 'Thong tin phan hoi khong hop le.';
            header('Location: index.php?act=admin/ticketDetail&id=' . max(0, $ticketId));
            exit();
        }

        try {
            $ticket = $this->ticketModel->getByIdForAdmin($ticketId);
            if (!$ticket) {
                $_SESSION['error'] = 'Khong tim thay ticket.';
                header('Location: index.php?act=admin/tickets');
                exit();
            }

            $this->ticketModel->addMessage($ticketId, (int)($_SESSION['user_id'] ?? 0), 'Admin', mb_substr($message, 0, 2000));
            $_SESSION['success'] = 'Da gui phan hoi cho ticket.';
            header('Location: index.php?act=admin/ticketDetail&id=' . $ticketId);
            exit();
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?act=admin/ticketDetail&id=' . $ticketId);
            exit();
        }
    }

    public function adminTicketStatus(): void
    {
        requireRole('Admin');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?act=admin/tickets');
            exit();
        }

        if (!verifyCsrfToken((string)($_POST['_csrf_global'] ?? ''), 'global_form')) {
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
            header('Location: index.php?act=admin/tickets');
            exit();
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));
        $allowed = ['Open', 'InProgress', 'WaitingCustomer', 'Resolved', 'Closed'];

        if ($ticketId <= 0 || !in_array($status, $allowed, true)) {
            $_SESSION['error'] = 'Trang thai cap nhat khong hop le.';
            header('Location: index.php?act=admin/ticketDetail&id=' . max(0, $ticketId));
            exit();
        }

        try {
            $ok = $this->ticketModel->updateStatus($ticketId, $status);
            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Da cap nhat trang thai ticket.'
                : 'Khong the cap nhat trang thai ticket.';
            header('Location: index.php?act=admin/ticketDetail&id=' . $ticketId);
            exit();
        } catch (Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?act=admin/ticketDetail&id=' . $ticketId);
            exit();
        }
    }
}

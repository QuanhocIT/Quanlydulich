<?php
require_once __DIR__ . '/../commons/mail.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/KhachHang.php';
class SendInvoiceMailController {
    public static function send($conn, $invoice_id) {
        $invoice = Invoice::find($conn, $invoice_id);
        if (!$invoice) die('Không tìm thấy hóa đơn');

        $customer = null;
        if (!empty($invoice['customer_id'])) {
            $khachHangModel = new KhachHang();
            $customer = $khachHangModel->getKhachHangWithNguoiDung((int)$invoice['customer_id']);
        }

        $to = trim((string)($customer['email'] ?? ''));
        if ($to === '') {
            die('Không tìm thấy email khách hàng cho hóa đơn này');
        }

        $subject = 'Hóa đơn điện tử #' . $invoice['invoice_id'];
        $body = 'Cảm ơn bạn đã thanh toán. Tải hóa đơn tại: ' . BASE_URL . 'index.php?act=invoice/exportPDF&id=' . $invoice_id;
        $sent = sendInvoiceEmail($to, $subject, $body);
        echo $sent ? 'Đã gửi email hóa đơn thành công.' : 'Không thể gửi email hóa đơn. Kiểm tra storage/mail_log.txt và cấu hình mail server.';
        exit;
    }
}

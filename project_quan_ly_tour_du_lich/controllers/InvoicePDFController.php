<?php
// Sinh hóa đơn PDF (demo, thực tế cần cài mPDF qua Composer)
require_once __DIR__ . '/../models/Invoice.php';

class InvoicePDFController {
    public static function export($conn, $invoice_id) {
        $invoice = Invoice::find($conn, $invoice_id);
        if (!$invoice) die('Không tìm thấy hóa đơn');
        // Demo: xuất HTML, thực tế dùng mPDF để xuất PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice_' . $invoice_id . '.pdf"');
        echo "PDF hóa đơn (demo):\n";
        echo "Mã hóa đơn: " . $invoice['invoice_id'] . "\n";
        echo "Khách hàng: " . $invoice['customer_id'] . "\n";
        echo "Tổng tiền: " . $invoice['total_amount'] . "\n";
        exit;
    }
}

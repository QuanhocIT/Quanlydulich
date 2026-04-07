<?php
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/InvoiceItem.php';
class InvoiceController {
    public static function index($conn) {
        $allInvoices = Invoice::all($conn);

        $keyword = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $today = date('Y-m-d');
        $stats = [
            'total' => count($allInvoices),
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'total_amount' => 0,
        ];

        foreach ($allInvoices as $inv) {
            $stats['total_amount'] += (float)($inv['total_amount'] ?? 0);
            if (($inv['status'] ?? '') === 'DaThanhToan') {
                $stats['paid']++;
            } else {
                $stats['unpaid']++;
                if (!empty($inv['due_date']) && $inv['due_date'] < $today) {
                    $stats['overdue']++;
                }
            }
        }

        $invoices = array_values(array_filter($allInvoices, function ($inv) use ($keyword, $status, $dateFrom, $dateTo, $today) {
            if ($status !== '') {
                if ($status === 'QuaHan') {
                    $isOverdue = ($inv['status'] ?? '') !== 'DaThanhToan' && !empty($inv['due_date']) && $inv['due_date'] < $today;
                    if (!$isOverdue) {
                        return false;
                    }
                } elseif (($inv['status'] ?? '') !== $status) {
                    return false;
                }
            }

            if ($keyword !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $inv['invoice_id'] ?? '',
                    $inv['booking_id'] ?? '',
                    $inv['customer_id'] ?? '',
                    $inv['note'] ?? '',
                ]));
                if (mb_strpos($haystack, mb_strtolower($keyword)) === false) {
                    return false;
                }
            }

            if ($dateFrom !== '' && !empty($inv['issue_date']) && $inv['issue_date'] < $dateFrom) {
                return false;
            }
            if ($dateTo !== '' && !empty($inv['issue_date']) && $inv['issue_date'] > $dateTo) {
                return false;
            }

            return true;
        }));

        usort($invoices, function ($a, $b) {
            return strcmp($b['issue_date'] ?? '', $a['issue_date'] ?? '');
        });

        include __DIR__ . '/../views/admin/invoices/index.php';
    }
    public static function show($conn, $id) {
        $invoice = Invoice::find($conn, $id);
        $items = InvoiceItem::all($conn, $id);
        include __DIR__ . '/../views/admin/invoices/show.php';
    }
    public static function create($conn, $data, $items) {
        Invoice::create($conn, $data);
        // Thêm các item nếu cần
        // Redirect or show message
    }
    public static function update($conn, $id, $data, $items) {
        Invoice::update($conn, $id, $data);
        // Cập nhật các item nếu cần
        // Redirect or show message
    }
    public static function delete($conn, $id) {
        Invoice::delete($conn, $id);
        // Redirect or show message
    }
}

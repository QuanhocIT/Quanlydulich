<?php
class Invoice {
    public $invoice_id;
    public $booking_id;
    public $customer_id;
    public $issue_date;
    public $due_date;
    public $total_amount;
    public $status;
    public $note;

    public static function all($conn) {
        $stmt = $conn->prepare("SELECT * FROM invoices WHERE deleted_at IS NULL");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM invoices WHERE invoice_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO invoices (booking_id, customer_id, issue_date, due_date, total_amount, status, note) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['booking_id'], $data['customer_id'], $data['issue_date'], $data['due_date'], $data['total_amount'], $data['status'], $data['note']
        ]);
    }
    public static function update($conn, $id, $data) {
        $stmt = $conn->prepare("UPDATE invoices SET booking_id=?, customer_id=?, issue_date=?, due_date=?, total_amount=?, status=?, note=? WHERE invoice_id=?");
        return $stmt->execute([
            $data['booking_id'], $data['customer_id'], $data['issue_date'], $data['due_date'], $data['total_amount'], $data['status'], $data['note'], $id
        ]);
    }
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("UPDATE invoices SET deleted_at = NOW() WHERE invoice_id = ? AND deleted_at IS NULL");
        return $stmt->execute([$id]);
    }
}

<?php
class Payment {
    public $payment_id;
    public $booking_id;
    public $amount;
    public $payment_method;
    public $payment_date;
    public $status;
    public $note;

    public static function all($conn) {
        $stmt = $conn->prepare("SELECT * FROM payments ORDER BY payment_id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_date, status, note) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['booking_id'], $data['amount'], $data['payment_method'], $data['payment_date'], $data['status'], $data['note']
        ]);
    }
    public static function update($conn, $id, $data) {
        $stmt = $conn->prepare("UPDATE payments SET booking_id=?, amount=?, payment_method=?, payment_date=?, status=?, note=? WHERE payment_id=?");
        return $stmt->execute([
            $data['booking_id'], $data['amount'], $data['payment_method'], $data['payment_date'], $data['status'], $data['note'], $id
        ]);
    }
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
        return $stmt->execute([$id]);
    }
}

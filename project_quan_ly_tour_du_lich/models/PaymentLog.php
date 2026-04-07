<?php
class PaymentLog {
    public $log_id;
    public $payment_id;
    public $action;
    public $log_time;
    public $note;

    public static function all($conn, $payment_id = null) {
        if ($payment_id) {
            $stmt = $conn->prepare("SELECT * FROM payment_logs WHERE payment_id = ?");
            $stmt->execute([$payment_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT * FROM payment_logs");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    public static function find($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM payment_logs WHERE log_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['payment_id'], $data['action'], $data['log_time'], $data['note']
        ]);
    }
    public static function update($conn, $id, $data) {
        $stmt = $conn->prepare("UPDATE payment_logs SET payment_id=?, action=?, log_time=?, note=? WHERE log_id=?");
        return $stmt->execute([
            $data['payment_id'], $data['action'], $data['log_time'], $data['note'], $id
        ]);
    }
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM payment_logs WHERE log_id = ?");
        return $stmt->execute([$id]);
    }
}

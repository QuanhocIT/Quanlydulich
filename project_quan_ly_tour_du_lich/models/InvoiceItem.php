<?php
class InvoiceItem {
    public $item_id;
    public $invoice_id;
    public $description;
    public $quantity;
    public $unit_price;
    public $amount;

    public static function all($conn, $invoice_id = null) {
        if ($invoice_id) {
            $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? AND deleted_at IS NULL");
            $stmt->execute([$invoice_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE deleted_at IS NULL");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    public static function find($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE item_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['invoice_id'], $data['description'], $data['quantity'], $data['unit_price'], $data['amount']
        ]);
    }
    public static function update($conn, $id, $data) {
        $stmt = $conn->prepare("UPDATE invoice_items SET invoice_id=?, description=?, quantity=?, unit_price=?, amount=? WHERE item_id=?");
        return $stmt->execute([
            $data['invoice_id'], $data['description'], $data['quantity'], $data['unit_price'], $data['amount'], $id
        ]);
    }
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("UPDATE invoice_items SET deleted_at = NOW() WHERE item_id = ? AND deleted_at IS NULL");
        return $stmt->execute([$id]);
    }
}

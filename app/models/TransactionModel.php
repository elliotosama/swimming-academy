<?php
// app/models/TransactionModel.php

class TransactionModel {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── All transactions ──────────────────────────────────────────────────────

    public function findAll(): array {
        $stmt = $this->db->query("
            SELECT t.*,
                   CONCAT(u.first_name, ' ', u.last_name) AS creator_name
            FROM transactions t
            LEFT JOIN users u ON u.id = t.created_by
            ORDER BY t.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Transactions for a receipt ────────────────────────────────────────────

    public function findByReceipt(int $receiptId): array {
        $stmt = $this->db->prepare("
            SELECT t.*,
                   CONCAT(u.first_name, ' ', u.last_name) AS creator_name
            FROM transactions t
            LEFT JOIN users u ON u.id = t.created_by
            WHERE t.receipt_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$receiptId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single transaction ────────────────────────────────────────────────────

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT t.*,
                   CONCAT(u.first_name, ' ', u.last_name) AS creator_name
            FROM transactions t
            LEFT JOIN users u ON u.id = t.created_by
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Total paid for a receipt ──────────────────────────────────────────────

    public function totalByReceipt(int $receiptId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM transactions
            WHERE receipt_id = ? AND type = 'payment'
        ");
        $stmt->execute([$receiptId]);
        return (float) $stmt->fetchColumn();
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO transactions
                (payment_method, amount, receipt_id, created_by, created_at, attachment, notes, type)
            VALUES
                (:payment_method, :amount, :receipt_id, :created_by, CURDATE(), :attachment, :notes, :type)
        ");
        $stmt->execute($this->bind($data));
        return (int) $this->db->lastInsertId();
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("
            UPDATE transactions SET
                payment_method = :payment_method,
                amount         = :amount,
                receipt_id     = :receipt_id,
                attachment     = :attachment,
                notes          = :notes,
                type           = :type
            WHERE id = :id
        ");
        $stmt->execute(array_merge($this->bind($data), [':id' => $id]));
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function bind(array $data): array {
        return [
            ':payment_method' => $data['payment_method'] ?? null,
            ':amount'         => $data['amount']         ?? null,
            ':receipt_id'     => $data['receipt_id']     ?: null,
            ':created_by'     => $data['created_by']     ?: null,
            ':attachment'     => $data['attachment']     ?? null,
            ':notes'          => $data['notes']          ?? null,
            ':type'           => $data['type']           ?? 'payment',
        ];
    }
}
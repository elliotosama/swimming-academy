<?php
// app/models/PriceModel.php

class PriceModel {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── All prices ────────────────────────────────────────────────────────────

    public function findAll(): array {
        $stmt = $this->db->query("
            SELECT * FROM prices
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single price ──────────────────────────────────────────────────────────

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM prices WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO prices
                (description, price, country, visible, number_of_sessions, created_at, updated_at)
            VALUES
                (:description, :price, :country, :visible, :number_of_sessions, CURDATE(), CURDATE())
        ");
        $stmt->execute($this->bind($data));
        return (int) $this->db->lastInsertId();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("
            UPDATE prices SET
                description        = :description,
                price              = :price,
                country            = :country,
                visible            = :visible,
                number_of_sessions = :number_of_sessions,
                updated_at         = CURDATE()
            WHERE id = :id
        ");
        $stmt->execute(array_merge($this->bind($data), [':id' => $id]));
    }

    // ── Soft-delete (hide) ────────────────────────────────────────────────────

    public function hide(int $id): void {
        $stmt = $this->db->prepare("UPDATE prices SET visible = 0, updated_at = CURDATE() WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Reactivate ────────────────────────────────────────────────────────────

    public function show(int $id): void {
        $stmt = $this->db->prepare("UPDATE prices SET visible = 1, updated_at = CURDATE() WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function bind(array $data): array {
        return [
            ':description'        => $data['description'],
            ':price'              => $data['price'],
            ':country'            => $data['country'],
            ':visible'            => $data['visible'],
            ':number_of_sessions' => $data['number_of_sessions'],
        ];
    }
}
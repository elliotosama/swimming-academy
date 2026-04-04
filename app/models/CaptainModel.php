<?php
// app/models/CaptainModel.php

class CaptainModel {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── All captains ──────────────────────────────────────────────────────────

    public function findAll(array $filters = []): array {
        $where  = [];
        $params = [];

        if (!empty($filters['visible']) && $filters['visible'] === 'visible') {
            $where[] = 'visible = 1';
        } elseif (!empty($filters['visible']) && $filters['visible'] === 'hidden') {
            $where[] = 'visible = 0';
        }

        if (!empty($filters['search'])) {
            $where[]           = '(captain_name LIKE :search OR phone_number LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql = 'SELECT * FROM captains';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY captain_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single captain ────────────────────────────────────────────────────────

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM captains WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Name uniqueness check (exclude current id on edit) ────────────────────

    public function nameExists(string $name, int $excludeId = 0): bool {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM captains
            WHERE captain_name = ? AND id != ?
        ');
        $stmt->execute([$name, $excludeId]);
        return (bool) $stmt->fetchColumn();
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare('
            INSERT INTO captains
                (captain_name, phone_number, visible, created_at, created_by)
            VALUES
                (:captain_name, :phone_number, :visible, CURDATE(), :created_by)
        ');
        $stmt->execute([
            ':captain_name' => $data['captain_name'],
            ':phone_number' => $data['phone_number'] ?: null,
            ':visible'      => $data['visible'],
            ':created_by'   => $data['created_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare('
            UPDATE captains SET
                captain_name  = :captain_name,
                phone_number  = :phone_number,
                visible       = :visible
            WHERE id = :id
        ');
        $stmt->execute([
            ':captain_name' => $data['captain_name'],
            ':phone_number' => $data['phone_number'] ?: null,
            ':visible'      => $data['visible'],
            ':id'           => $id,
        ]);
    }

    // ── Soft-delete (hide) ────────────────────────────────────────────────────

    public function hide(int $id): void {
        $stmt = $this->db->prepare('UPDATE captains SET visible = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    // ── Reactivate ────────────────────────────────────────────────────────────

    public function show(int $id): void {
        $stmt = $this->db->prepare('UPDATE captains SET visible = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
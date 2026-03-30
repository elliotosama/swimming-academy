<?php
// app/models/BranchModel.php

class BranchModel {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── All branches ─────────────────────────────────────────────────────────

    public function findAll(): array {
        $stmt = $this->db->query("
            SELECT * FROM branches
            ORDER BY branch_name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single branch ────────────────────────────────────────────────────────

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Name uniqueness check (exclude current id on edit) ───────────────────

    public function nameExists(string $name, int $excludeId = 0): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM branches
            WHERE branch_name = ? AND id != ?
        ");
        $stmt->execute([$name, $excludeId]);
        return (bool) $stmt->fetchColumn();
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO branches
                (branch_name, country, visible, working_days1, working_days2, working_days3, created_at)
            VALUES
                (:branch_name, :country, :visible, :working_days1, :working_days2, :working_days3, CURDATE())
        ");
        $stmt->execute($this->bind($data));
        return (int) $this->db->lastInsertId();
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("
            UPDATE branches SET
                branch_name   = :branch_name,
                country       = :country,
                visible       = :visible,
                working_days1 = :working_days1,
                working_days2 = :working_days2,
                working_days3 = :working_days3
            WHERE id = :id
        ");
        $stmt->execute(array_merge($this->bind($data), [':id' => $id]));
    }

    // ── Soft-delete (hide) ───────────────────────────────────────────────────

    public function hide(int $id): void {
        $stmt = $this->db->prepare("UPDATE branches SET visible = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Reactivate ───────────────────────────────────────────────────────────

    public function show(int $id): void {
        $stmt = $this->db->prepare("UPDATE branches SET visible = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Convert working_days arrays to comma-separated SET strings for MySQL.
     */
    private function bind(array $data): array {
        return [
            ':branch_name'   => $data['branch_name'],
            ':country'       => $data['country'],
            ':visible'       => $data['visible'],
            ':working_days1' => $this->daysToSet($data['working_days1'] ?? []),
            ':working_days2' => $this->daysToSet($data['working_days2'] ?? []),
            ':working_days3' => $this->daysToSet($data['working_days3'] ?? []),
        ];
    }

    /**
     * MySQL SET columns are stored as comma-separated strings.
     * Accept either an array ['Sunday','Monday'] or a string and normalise.
     */
    private function daysToSet(array|string $days): string {
        if (is_string($days)) return $days;
        return implode(',', array_filter($days));
    }
}
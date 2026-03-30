<?php
// app/models/ReceiptModel.php

class ReceiptModel {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── All receipts ──────────────────────────────────────────────────────────

    public function findAll(): array {
        $stmt = $this->db->query("
            SELECT r.*,
                   c.client_name AS client_name,
                   cr.username AS creator_name,
                   ca.captain_name AS captain_name,
                   b.branch_name,
                   p.description as plan_name
            FROM receipts r
            LEFT JOIN clients  c  ON c.id  = r.client_id
            LEFT JOIN users    cr ON cr.id = r.creator_id
            LEFT JOIN captains    ca ON ca.id = r.captain_id
            LEFT JOIN branches b  ON b.id  = r.branch_id
            LEFT JOIN prices    p  ON p.id  = r.plan_id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single receipt ────────────────────────────────────────────────────────

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   c.client_name AS client_name,
                   cr.username AS creator_name,
                   ca.captain_name AS captain_name,
                   b.branch_name,
                   p.description as plan_name
            FROM receipts r
            LEFT JOIN clients  c  ON c.id  = r.client_id
            LEFT JOIN users    cr ON cr.id = r.creator_id
            LEFT JOIN users    ca ON ca.id = r.captain_id
            LEFT JOIN branches b  ON b.id  = r.branch_id
            LEFT JOIN prices    p  ON p.id  = r.plan_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Receipts by client ────────────────────────────────────────────────────

    public function findByClient(int $clientId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, p.description as plan_name, b.branch_name
            FROM receipts r
            LEFT JOIN prices    p ON p.id = r.plan_id
            LEFT JOIN branches b ON b.id = r.branch_id
            WHERE r.client_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO receipts
                (client_id, creator_id, captain_id, branch_id,
                 first_session, last_session, renewal_session,
                 created_at, renewal_type, receipt_status,
                 exercise_time, plan_id, level, pdf_path)
            VALUES
                (:client_id, :creator_id, :captain_id, :branch_id,
                 :first_session, :last_session, :renewal_session,
                 CURDATE(), :renewal_type, :receipt_status,
                 :exercise_time, :plan_id, :level, :pdf_path)
        ");
        $stmt->execute($this->bind($data));
        return (int) $this->db->lastInsertId();
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("
            UPDATE receipts SET
                client_id       = :client_id,
                creator_id      = :creator_id,
                captain_id      = :captain_id,
                branch_id       = :branch_id,
                first_session   = :first_session,
                last_session    = :last_session,
                renewal_session = :renewal_session,
                renewal_type    = :renewal_type,
                receipt_status  = :receipt_status,
                exercise_time   = :exercise_time,
                plan_id         = :plan_id,
                level           = :level,
                pdf_path        = :pdf_path
            WHERE id = :id
        ");
        $stmt->execute(array_merge($this->bind($data), [':id' => $id]));
    }

    // ── Update status only ────────────────────────────────────────────────────

    public function updateStatus(int $id, string $status): void {
        $stmt = $this->db->prepare("UPDATE receipts SET receipt_status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM receipts WHERE id = ?");
        $stmt->execute([$id]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function bind(array $data): array {
        return [
            ':client_id'       => $data['client_id']       ?: null,
            ':creator_id'      => $data['creator_id']      ?: null,
            ':captain_id'      => $data['captain_id']      ?: null,
            ':branch_id'       => $data['branch_id']       ?: null,
            ':first_session'   => $data['first_session']   ?: null,
            ':last_session'    => $data['last_session']    ?: null,
            ':renewal_session' => $data['renewal_session'] ?: null,
            ':renewal_type'    => $data['renewal_type']    ?: null,
            ':receipt_status'  => $data['receipt_status']  ?? 'not_completed',
            ':exercise_time'   => $data['exercise_time']   ?: null,
            ':plan_id'         => $data['plan_id']         ?: null,
            ':level'           => $data['level']           ?: null,
            ':pdf_path'        => $data['pdf_path']        ?: null,
        ];
    }
}
<?php
// app/models/ReceiptModel.php

class ReceiptModel {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── All receipts (no filter) ──────────────────────────────────────────────

    public function findAll(): array {
        $stmt = $this->db->query("
            SELECT r.*,
                   c.client_name AS client_name,
                   cr.username   AS creator_name,
                   ca.captain_name AS captain_name,
                   b.branch_name,
                   p.description  AS plan_name
            FROM receipts r
            LEFT JOIN clients  c  ON c.id  = r.client_id
            LEFT JOIN users    cr ON cr.id = r.creator_id
            LEFT JOIN captains ca ON ca.id = r.captain_id
            LEFT JOIN branches b  ON b.id  = r.branch_id
            LEFT JOIN prices   p  ON p.id  = r.plan_id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Filtered + paginated search ───────────────────────────────────────────
    //
    //  $filters keys (all optional):
    //    search          – string  matched against client name/phone/id
    //    first_session_from / first_session_to   – date range for first_session
    //    last_session_from  / last_session_to    – date range for last_session
    //    created_from    / created_to            – date range for created_at
    //    statuses        – array of receipt_status values
    //    creator_id      – int
    //    branch_ids      – array of branch IDs
    //    has_updates     – bool: only rows that have an audit-log entry OR a transaction
    //
    //  Returns ['data' => [...], 'total' => int]

   public function search(array $filters = [], int $page = 1, int $perPage = 25): array {
    [$where, $params] = $this->buildWhere($filters);

    // COUNT uses the same $params (no :limit/:offset here)
    $countSql = "
        SELECT COUNT(DISTINCT r.id)
        FROM receipts r
        LEFT JOIN clients  c  ON c.id  = r.client_id
        LEFT JOIN users    cr ON cr.id = r.creator_id
        LEFT JOIN captains ca ON ca.id = r.captain_id
        LEFT JOIN branches b  ON b.id  = r.branch_id
        LEFT JOIN prices   p  ON p.id  = r.plan_id
        {$where}
    ";
    $countStmt = $this->db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // DATA uses $params + :limit/:offset bound separately
    $offset  = ($page - 1) * $perPage;
    $dataSql = "
        SELECT r.*,
               c.client_name   AS client_name,
               c.phone         AS client_phone,
               cr.username     AS creator_name,
               ca.captain_name AS captain_name,
               b.branch_name,
               p.description   AS plan_name,
               (SELECT COUNT(*) FROM receipt_audit_log al WHERE al.receipt_id = r.id) AS audit_count,
               (SELECT COUNT(*) FROM transactions t WHERE t.receipt_id = r.id) AS transaction_count
        FROM receipts r
        LEFT JOIN clients  c  ON c.id  = r.client_id
        LEFT JOIN users    cr ON cr.id = r.creator_id
        LEFT JOIN captains ca ON ca.id = r.captain_id
        LEFT JOIN branches b  ON b.id  = r.branch_id
        LEFT JOIN prices   p  ON p.id  = r.plan_id
        {$where}
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    $dataStmt = $this->db->prepare($dataSql);
    foreach ($params as $key => $val) {
        $dataStmt->bindValue($key, $val);
    }
    $dataStmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $dataStmt->execute();

    return [
        'data'  => $dataStmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
    ];
} 

    // ── Export (no pagination) ────────────────────────────────────────────────

    public function searchAll(array $filters = []): array {
        [$where, $params] = $this->buildWhere($filters);

        $sql = "
            SELECT r.id,
                   c.client_name,
                   c.phone         AS client_phone,
                   b.branch_name,
                   ca.captain_name,
                   p.description   AS plan_name,
                   r.first_session,
                   r.last_session,
                   r.renewal_session,
                   r.renewal_type,
                   r.receipt_status,
                   r.exercise_time,
                   r.level,
                   cr.username     AS creator_name,
                   r.created_at,
                   (SELECT COUNT(*) FROM receipt_audit_log al WHERE al.receipt_id = r.id) AS audit_count,
                   (SELECT COUNT(*) FROM transactions        t  WHERE t.receipt_id  = r.id) AS transaction_count,
                   (SELECT COALESCE(SUM(t2.amount),0) FROM transactions t2 WHERE t2.receipt_id = r.id) AS total_paid
            FROM receipts r
            LEFT JOIN clients  c  ON c.id  = r.client_id
            LEFT JOIN users    cr ON cr.id = r.creator_id
            LEFT JOIN captains ca ON ca.id = r.captain_id
            LEFT JOIN branches b  ON b.id  = r.branch_id
            LEFT JOIN prices   p  ON p.id  = r.plan_id
            {$where}
            ORDER BY r.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Single receipt ────────────────────────────────────────────────────────

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   c.client_name   AS client_name,
                   cr.username     AS creator_name,
                   ca.captain_name AS captain_name,
                   c.phone         AS phone_number,
                   b.branch_name,
                   p.description   AS plan_name
            FROM receipts r
            LEFT JOIN clients  c  ON c.id  = r.client_id
            LEFT JOIN users    cr ON cr.id = r.creator_id
            LEFT JOIN captains    ca ON ca.id = r.captain_id
            LEFT JOIN branches b  ON b.id  = r.branch_id
            LEFT JOIN prices   p  ON p.id  = r.plan_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Receipts by client ────────────────────────────────────────────────────

    public function findByClient(int $clientId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, p.description AS plan_name, b.branch_name
            FROM receipts r
            LEFT JOIN prices   p ON p.id = r.plan_id
            LEFT JOIN branches b ON b.id = r.branch_id
            WHERE r.client_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Create ────────────────────────────────────────────────────────────────

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

    // ── Update ────────────────────────────────────────────────────────────────

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

    private function buildWhere(array $filters): array {
        $conditions = [];
        $params     = [];

        // free-text search: client name, phone, or id
if (!empty($filters['search'])) {
    $conditions[] = "(c.client_name LIKE :search_name OR c.phone LIKE :search_phone OR r.client_id = :search_id)";
    $params[':search_name']  = '%' . $filters['search'] . '%';
    $params[':search_phone'] = '%' . $filters['search'] . '%';
    $params[':search_id']    = (int) $filters['search'];
}

        // first_session range
        if (!empty($filters['first_session_from'])) {
            $conditions[]                     = "r.first_session >= :fs_from";
            $params[':fs_from']               = $filters['first_session_from'];
        }
        if (!empty($filters['first_session_to'])) {
            $conditions[]                   = "r.first_session <= :fs_to";
            $params[':fs_to']               = $filters['first_session_to'];
        }

        // last_session range
        if (!empty($filters['last_session_from'])) {
            $conditions[]                    = "r.last_session >= :ls_from";
            $params[':ls_from']              = $filters['last_session_from'];
        }
        if (!empty($filters['last_session_to'])) {
            $conditions[]                  = "r.last_session <= :ls_to";
            $params[':ls_to']              = $filters['last_session_to'];
        }

        // created_at range
        if (!empty($filters['created_from'])) {
            $conditions[]              = "DATE(r.created_at) >= :cr_from";
            $params[':cr_from']        = $filters['created_from'];
        }
        if (!empty($filters['created_to'])) {
            $conditions[]            = "DATE(r.created_at) <= :cr_to";
            $params[':cr_to']        = $filters['created_to'];
        }

        // statuses  (multi-select array)
        if (!empty($filters['statuses']) && is_array($filters['statuses'])) {
            $placeholders = [];
            foreach ($filters['statuses'] as $i => $s) {
                $key                = ":status_{$i}";
                $placeholders[]     = $key;
                $params[$key]       = $s;
            }
            $conditions[] = "r.receipt_status IN (" . implode(',', $placeholders) . ")";
        }

        // creator
        if (!empty($filters['creator_id'])) {
            $conditions[]          = "r.creator_id = :creator_id";
            $params[':creator_id'] = (int) $filters['creator_id'];
        }

        // branches (multi-select array)
        if (!empty($filters['branch_ids']) && is_array($filters['branch_ids'])) {
            $placeholders = [];
            foreach ($filters['branch_ids'] as $i => $bid) {
                $key                = ":branch_{$i}";
                $placeholders[]     = $key;
                $params[$key]       = (int) $bid;
            }
            $conditions[] = "r.branch_id IN (" . implode(',', $placeholders) . ")";
        }

        // has_updates: receipt must have at least one audit log OR one transaction
        if (!empty($filters['has_updates'])) {
            $conditions[] = "
                (EXISTS (SELECT 1 FROM receipt_audit_log al WHERE al.receipt_id = r.id)
                 OR
                 EXISTS (SELECT 1 FROM transactions t WHERE t.receipt_id = r.id))
            ";
        }

        $sql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        return [$sql, $params];
    }

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
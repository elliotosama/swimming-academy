<?php

class BranchModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(string $country = '', string $visibility = ''): array
    {
        $sql    = "SELECT * FROM branches WHERE 1";
        $params = [];

        if ($country !== '') {
            $sql     .= " AND country = ?";
            $params[] = $country;
        }

        if ($visibility === 'visible') {
            $sql .= " AND visible = 1";
        } elseif ($visibility === 'hidden') {
            $sql .= " AND visible = 0";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existsByName(string $name): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM branches WHERE branch_name = ?");
        $stmt->execute([$name]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $name, string $country, ?string $wd1, ?string $wd2, ?string $wd3, int $visible): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO branches (branch_name, created_at, country, working_days1, working_days2, working_days3, visible)
            VALUES (?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $country, $wd1, $wd2, $wd3, $visible]);
    }

    public function update(int $id, string $name, string $country, ?string $wd1, ?string $wd2, ?string $wd3, int $visible): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE branches
            SET branch_name   = ?,
                country       = ?,
                working_days1 = ?,
                working_days2 = ?,
                working_days3 = ?,
                visible       = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $country, $wd1, $wd2, $wd3, $visible, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getCountries(): array
    {
        return $this->pdo->query("SELECT DISTINCT country FROM branches ORDER BY country")
                         ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStats(int $branchId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(DISTINCT r.id) AS total_receipts,
                COALESCE(SUM(CASE WHEN t.type='payment' THEN t.amount ELSE 0 END), 0) AS total_payments,
                COALESCE(SUM(CASE WHEN t.type='refund'  THEN t.amount ELSE 0 END), 0) AS total_refunds,
                COALESCE(SUM(CASE WHEN t.type='payment' THEN t.amount WHEN t.type='refund' THEN -t.amount ELSE 0 END), 0) AS total_amount,
                COALESCE(MAX(t.amount), 0) AS max_transaction,
                COALESCE(MIN(t.amount), 0) AS min_transaction
            FROM receipts r
            LEFT JOIN transactions t ON t.receipt_id = r.id
            WHERE r.branch_id = ?
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReceipts(int $branchId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id AS receipt_id, r.receipt_status, r.created_at,
                   COALESCE(SUM(t.amount), 0) AS receipt_total
            FROM receipts r
            LEFT JOIN transactions t ON t.receipt_id = r.id
            WHERE r.branch_id = ?
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransactions(int $branchId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.id AS receipt_id, t.id AS transaction_id,
                   t.amount, t.payment_method, t.type, t.created_at, t.notes
            FROM receipts r
            JOIN transactions t ON t.receipt_id = r.id
            WHERE r.branch_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCaptains(int $branchId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.* FROM captains c
            JOIN captain_branch bc ON bc.captain_id = c.id
            WHERE bc.branch_id = ?
            ORDER BY c.id DESC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClients(int $branchId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT c.* FROM clients c
            JOIN receipts r ON r.client_id = c.id
            WHERE r.branch_id = ?
            ORDER BY c.id DESC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsers(int $branchId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.* FROM users u
            JOIN user_branch ub ON ub.user_id = u.id
            WHERE ub.branch_id = ?
            ORDER BY u.id DESC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper: convert days array to 3 working_days columns
    public static function chunkDays(array $days): array
    {
        $chunks = array_chunk($days, 2);
        return [
            isset($chunks[0]) ? implode(',', $chunks[0]) : null,
            isset($chunks[1]) ? implode(',', $chunks[1]) : null,
            isset($chunks[2]) ? implode(',', $chunks[2]) : null,
        ];
    }
}
<?php
// app/controllers/DashboardController.php

class DashboardController {

    private PDO $db;

    public function __construct() {
        $this->db = get_db();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function renderView(string $view, array $data = []): void {
        extract($data);
        require ROOT . "/views/dashboard/{$view}.php";
    }

    // ════════════════════════════════════════════════════════════════════════
    // INDEX  —  GET /dashboard
    // ════════════════════════════════════════════════════════════════════════

    public function index(): void {

        // ── Receipts stats ──────────────────────────────────────────
        $receiptsRow = $this->db->query("
            SELECT
                COUNT(*)                                              AS total,
                SUM(receipt_status = 'completed')                     AS completed,
                SUM(receipt_status = 'not_completed')                 AS not_completed,
                SUM(receipt_status = 'pending')                       AS pending,
                SUM(DATE(created_at) = CURDATE())                     AS today,
                SUM(YEAR(created_at)  = YEAR(NOW())
                    AND MONTH(created_at) = MONTH(NOW()))             AS this_month
            FROM receipts
        ")->fetch(PDO::FETCH_ASSOC);

        // ── Transactions stats ──────────────────────────────────────
        $txRow = $this->db->query("
            SELECT
                COUNT(*)                                              AS count,
                COALESCE(SUM(amount), 0)                             AS total_amount,
                COALESCE(AVG(amount), 0)                             AS avg_amount,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN amount END), 0)  AS today_amount,
                COALESCE(SUM(CASE WHEN YEAR(created_at)  = YEAR(NOW())
                                   AND MONTH(created_at) = MONTH(NOW())
                              THEN amount END), 0)                    AS month_amount
            FROM transactions
        ")->fetch(PDO::FETCH_ASSOC);

        // ── Clients stats ───────────────────────────────────────────
        $clientsRow = $this->db->query("
            SELECT
                COUNT(*)                                              AS total,
                SUM(YEAR(created_at)  = YEAR(NOW())
                    AND MONTH(created_at) = MONTH(NOW()))             AS this_month
            FROM clients
        ")->fetch(PDO::FETCH_ASSOC);

        // ── Simple counts ───────────────────────────────────────────
        $captainsTotal = (int) $this->db->query("SELECT COUNT(*) FROM captains")->fetchColumn();
        $usersTotal    = (int) $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $branchesTotal = (int) $this->db->query("SELECT COUNT(*) FROM branches")->fetchColumn();
        $plansTotal    = (int) $this->db->query("SELECT COUNT(*) FROM prices")->fetchColumn();

        // ── Branch performance ──────────────────────────────────────
        $branchStats = $this->db->query("
            SELECT
                b.branch_name,
                COUNT(DISTINCT r.id)                                  AS receipt_count,
                COALESCE(SUM(t.amount), 0)                           AS total_paid
            FROM branches b
            LEFT JOIN receipts     r ON r.branch_id  = b.id
            LEFT JOIN transactions t ON t.receipt_id = r.id
            GROUP BY b.id, b.branch_name
            ORDER BY receipt_count DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Captain performance ─────────────────────────────────────
        $captainStats = $this->db->query("
            SELECT
                ca.captain_name,
                COUNT(r.id)                                           AS receipt_count,
                SUM(r.receipt_status = 'completed')                   AS completed_count
            FROM captains ca
            LEFT JOIN receipts r ON r.captain_id = ca.id
            GROUP BY ca.id, ca.captain_name
            ORDER BY receipt_count DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Top clients by payments ─────────────────────────────────
        $topClients = $this->db->query("
            SELECT
                c.client_name,
                c.phone,
                COUNT(DISTINCT r.id)                                  AS receipt_count,
                COALESCE(SUM(t.amount), 0)                           AS total_paid
            FROM clients c
            LEFT JOIN receipts     r ON r.client_id  = c.id
            LEFT JOIN transactions t ON t.receipt_id = r.id
            GROUP BY c.id, c.client_name, c.phone
            ORDER BY total_paid DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Employee (creator) activity ─────────────────────────────
        $userStats = $this->db->query("
            SELECT
                u.username,
                COUNT(r.id)                                           AS total_receipts,
                SUM(YEAR(r.created_at)  = YEAR(NOW())
                    AND MONTH(r.created_at) = MONTH(NOW()))           AS month_receipts
            FROM users u
            LEFT JOIN receipts r ON r.creator_id = u.id
            GROUP BY u.id, u.username
            ORDER BY total_receipts DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Recent receipts ─────────────────────────────────────────
        $recentReceipts = $this->db->query("
            SELECT r.id, c.client_name, b.branch_name, r.receipt_status
            FROM receipts r
            LEFT JOIN clients  c ON c.id = r.client_id
            LEFT JOIN branches b ON b.id = r.branch_id
            ORDER BY r.created_at DESC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Recent transactions ─────────────────────────────────────
        $recentTransactions = $this->db->query("
            SELECT t.amount, t.created_at, c.client_name
            FROM transactions t
            LEFT JOIN receipts r ON r.id = t.receipt_id
            LEFT JOIN clients  c ON c.id = r.client_id
            ORDER BY t.created_at DESC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Recent audit log ────────────────────────────────────────
        $recentAuditLog = $this->db->query("
            SELECT al.receipt_id, al.field_name, al.old_value, al.new_value,
                   al.changed_at, u.username AS changed_by_name
            FROM receipt_audit_log al
            LEFT JOIN users u ON u.id = al.changed_by
            ORDER BY al.changed_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // ── Render ──────────────────────────────────────────────────
        $this->renderView('admin', [
            'pageTitle'          => 'لوحة التحكم',
            'breadcrumb'         => 'لوحة التحكم',
            'stats'              => [
                'receipts'     => $receiptsRow,
                'transactions' => $txRow,
                'clients'      => $clientsRow,
                'captains'     => ['total' => $captainsTotal],
                'users'        => ['total' => $usersTotal],
                'branches'     => ['total' => $branchesTotal],
                'plans'        => ['total' => $plansTotal],
            ],
            'branchStats'        => $branchStats,
            'captainStats'       => $captainStats,
            'topClients'         => $topClients,
            'userStats'          => $userStats,
            'recentReceipts'     => $recentReceipts,
            'recentTransactions' => $recentTransactions,
            'recentAuditLog'     => $recentAuditLog,
        ]);
    }
}
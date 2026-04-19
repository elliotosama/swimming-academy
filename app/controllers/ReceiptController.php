<?php
// app/controllers/ReceiptController.php
require ROOT . '/app/models/ReceiptAuditLogModel.php';

class ReceiptController {

    private ReceiptModel         $receipts;
    private ReceiptAuditLogModel $auditLog;
    private TransactionModel     $transactions;

    private const PER_PAGE = 25;

    public function __construct() {
        $this->receipts     = new ReceiptModel();
        $this->auditLog     = new ReceiptAuditLogModel();
        $this->transactions = new TransactionModel();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function redirect(string $path): void {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    private function renderView(string $view, array $data = []): void {
        extract($data);
        require ROOT . "/views/receipts/{$view}.php";
    }

    private function flash(string $key, string $msg): void {
        $_SESSION[$key] = $msg;
    }

    private function parseForm(): array {
        return [
            'client_name'     => trim($_POST['client_name']     ?? ''),
            'phone'           => trim($_POST['full_phone']       ?? trim($_POST['phone'] ?? '')),
            'client_id'       => (int) ($_POST['client_id']     ?? 0),
            'creator_id'      => (int) ($_POST['creator_id']    ?? 0),
            'captain_id'      => (int) ($_POST['captain_id']    ?? 0),
            'branch_id'       => (int) ($_POST['branch_id']     ?? 0),
            'first_session'   => trim($_POST['first_session']   ?? ''),
            'last_session'    => trim($_POST['last_session']    ?? ''),
            'renewal_session' => trim($_POST['renewal_session'] ?? ''),
            'receipt_status'  => trim($_POST['receipt_status']  ?? 'not_completed'),
            'exercise_time'   => trim($_POST['exercise_time']   ?? ''),
            'plan_id'         => (int) ($_POST['plan_id']       ?? 0) ?: null,
            'level'           => (int) ($_POST['level']         ?? 0) ?: null,
            'pdf_path'        => trim($_POST['pdf_path']        ?? ''),
            'amount'          => (float) ($_POST['amount']      ?? 0),
            'remaining'       => (float) ($_POST['remaining']   ?? 0),
            'payment_method'  => trim($_POST['payment_method']  ?? ''),
            'notes'           => trim($_POST['notes']           ?? ''),
            'renewal_type'    => trim($_POST['renewal_type']    ?? 'new'),
        ];
    }


    // ════════════════════════════════════════════════════════════════════════
// SEARCH JSON — GET /receipts/search-json
// ════════════════════════════════════════════════════════════════════════

public function searchJson(): void {
    auth_require(['admin', 'branch_manager', 'customer_service', 'area_manager']);

    $scope   = $this->roleScope();
    $filters = array_merge($this->parseFilters(), $scope['forced']);
    $page    = max(1, (int) ($_GET['page'] ?? 1));

    $result   = $this->receipts->search($filters, $page, self::PER_PAGE);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'data'     => $result['data'],
        'total'    => $result['total'],
        'page'     => $page,
        'lastPage' => (int) ceil($result['total'] / self::PER_PAGE),
        'perPage'  => self::PER_PAGE,
    ]);
    exit;
}
    private function validate(array $data): array {
        $errors = [];

        if (empty($data['branch_id']))
            $errors[] = 'يجب اختيار الفرع.';

        if (!empty($data['first_session']) && !empty($data['last_session'])
            && $data['last_session'] < $data['first_session']) {
            $errors[] = 'تاريخ آخر جلسة لا يمكن أن يكون قبل تاريخ أول جلسة.';
        }

        if (empty($data['payment_method']))
            $errors[] = 'يجب اختيار طريقة الدفع.';

        return $errors;
    }

    private function parseFilters(): array {
        return [
            'search'              => trim($_GET['search']              ?? ''),
            'first_session_from'  => trim($_GET['first_session_from']  ?? ''),
            'first_session_to'    => trim($_GET['first_session_to']    ?? ''),
            'last_session_from'   => trim($_GET['last_session_from']   ?? ''),
            'last_session_to'     => trim($_GET['last_session_to']     ?? ''),
            'created_from'        => trim($_GET['created_from']        ?? ''),
            'created_to'          => trim($_GET['created_to']          ?? ''),
            'statuses'            => (array) ($_GET['statuses']        ?? []),
            'creator_id'          => (int)   ($_GET['creator_id']      ?? 0) ?: null,
            'branch_ids'          => array_filter(array_map('intval', (array) ($_GET['branch_ids'] ?? []))),
            'has_updates'         => !empty($_GET['has_updates']),
        ];
    }

    private function roleScope(): array {
        $user = auth_user();
        $role = $user['role'];

        $allFilterControls = [
            'search', 'first_session', 'last_session', 'created',
            'statuses', 'branch', 'creator', 'has_updates',
        ];

        switch ($role) {
            case 'branch_manager':
                $branchId = $this->receipts->getBranchIdByManager($user['id']);
                return [
                    'forced'          => [
                        'force_branch_ids' => $branchId ? [$branchId] : [0],
                    ],
                    'allowed_filters' => array_diff($allFilterControls, ['branch', 'creator']),
                ];

            case 'customer_service':
                return [
                    'forced'          => [
                        'force_creator_id' => $user['id'],
                    ],
                    'allowed_filters' => array_diff($allFilterControls, ['branch', 'creator']),
                ];

            case 'area_manager':
                $branchIds = $this->receipts->getBranchIdsByArea($user['id']);
                return [
                    'forced'          => [
                        'force_branch_ids' => $branchIds ?: [0],
                    ],
                    'allowed_filters' => array_diff($allFilterControls, ['creator']),
                    'managed_branch_ids' => $branchIds,
                ];

            default:
                return [
                    'forced'          => [],
                    'allowed_filters' => $allFilterControls,
                ];
        }
    }

    private function formDropdowns(): array {
        $db = get_db();

        $branches = $db->query("
            SELECT b.id, b.branch_name, b.working_days1, b.working_days2, b.working_days3,
                   c.id AS country_id, c.country, c.country_code
            FROM branches b
            JOIN countries c ON c.id = b.country_id
            WHERE b.visible = 1
            ORDER BY b.branch_name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $captainRows = $db->query("
            SELECT cb.branch_id, c.id, c.captain_name
            FROM captain_branch cb
            JOIN captains c ON c.id = cb.captain_id
            WHERE c.visible = 1
            ORDER BY c.captain_name
        ")->fetchAll(PDO::FETCH_ASSOC);

        $captainsByBranch = [];
        foreach ($captainRows as $row) {
            $captainsByBranch[$row['branch_id']][] = [
                'id'   => $row['id'],
                'name' => $row['captain_name'],
            ];
        }

        $plans = $db->query("
            SELECT p.id, p.description, p.price, p.number_of_sessions,
                   p.country_id, c.country
            FROM prices p
            JOIN countries c ON c.id = p.country_id
            WHERE p.visible = 1
            ORDER BY p.description
        ")->fetchAll(PDO::FETCH_ASSOC);

        $plansByCountry = [];
        foreach ($plans as $plan) {
            $plansByCountry[$plan['country_id']][] = $plan;
        }

        return [
            'branches'         => $branches,
            'captainsByBranch' => $captainsByBranch,
            'plans'            => $plans,
            'plansByCountry'   => $plansByCountry,
        ];
    }

    // ── File upload helper ────────────────────────────────────────────────────
    // Handles the optional 'transaction_evidence' file upload.
    // Returns the public path string, or null if no file was uploaded.
    private function handleEvidenceUpload(): ?string {
        if (empty($_FILES['transaction_evidence']['tmp_name'])) {
            return null;
        }

        $file    = $_FILES['transaction_evidence'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $mime    = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowed, true)) {
            return null; // silently ignore invalid types (controller can add error if desired)
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('ev_', true) . '.' . $ext;
        $saveDir  = ROOT . '/public/uploads/evidence';

        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0775, true);
        }

        $dest = $saveDir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/uploads/evidence/' . $filename;
        }

        return null;
    }

    // ════════════════════════════════════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════════════════════════════════════

    public function index(): void {
        auth_require(['admin', 'branch_manager', 'customer_service', 'area_manager']);

        $scope   = $this->roleScope();
        $filters = array_merge($this->parseFilters(), $scope['forced']);
        $page    = max(1, (int) ($_GET['page'] ?? 1));

        $result   = $this->receipts->search($filters, $page, self::PER_PAGE);
        $receipts = $result['data'];
        $total    = $result['total'];
        $lastPage = (int) ceil($total / self::PER_PAGE);

        $db = get_db();

        if (!empty($scope['managed_branch_ids'])) {
            $placeholders = implode(',', array_fill(0, count($scope['managed_branch_ids']), '?'));
            $stmt = $db->prepare("SELECT id, branch_name FROM branches WHERE id IN ({$placeholders}) ORDER BY branch_name");
            $stmt->execute($scope['managed_branch_ids']);
            $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $branches = $db->query("SELECT id, branch_name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
        }

        $creators = $db->query("SELECT id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

        $this->renderView('index', [
            'pageTitle'      => 'الإيصالات',
            'breadcrumb'     => 'لوحة التحكم · الإيصالات',
            'receipts'       => $receipts,
            'filters'        => $filters,
            'allowedFilters' => $scope['allowed_filters'],
            'page'           => $page,
            'lastPage'       => $lastPage,
            'total'          => $total,
            'perPage'        => self::PER_PAGE,
            'branches'       => $branches,
            'creators'       => $creators,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EXPORT
    // ════════════════════════════════════════════════════════════════════════

    public function export(): void {
        auth_require(['admin', 'branch_manager', 'customer_service', 'area_manager']);

        $scope   = $this->roleScope();
        $filters = array_merge($this->parseFilters(), $scope['forced']);
        $rows    = $this->receipts->searchAll($filters);

        $statusLabels = [
            'completed'     => 'مكتمل',
            'not_completed' => 'غير مكتمل',
            'pending'       => 'معلّق',
        ];

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="receipts_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            '#', 'اسم العميل', 'هاتف العميل', 'الفرع', 'الكابتن', 'الخطة',
            'أول جلسة', 'آخر جلسة', 'جلسة التجديد', 'نوع التجديد', 'الحالة',
            'وقت التمرين', 'المستوى', 'المنشئ', 'تاريخ الإنشاء',
            'إجمالي المدفوع', 'عدد التعديلات', 'عدد المعاملات',
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['client_name']       ?? '',
                $r['phone']             ?? '',
                $r['branch_name']       ?? '',
                $r['captain_name']      ?? '',
                $r['plan_name']         ?? '',
                $r['first_session']     ?? '',
                $r['last_session']      ?? '',
                $r['renewal_session']   ?? '',
                $r['renewal_type']      ?? '',
                $statusLabels[$r['receipt_status']] ?? $r['receipt_status'],
                $r['exercise_time']     ?? '',
                $r['level']             ?? '',
                $r['creator_name']      ?? '',
                $r['created_at']        ?? '',
                $r['total_paid']        ?? 0,
                $r['audit_count']       ?? 0,
                $r['transaction_count'] ?? 0,
            ]);
        }

        fclose($out);
        log_action('exported_receipts', 'filters: ' . json_encode($filters), auth_user()['id']);
        exit;
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE
    // ════════════════════════════════════════════════════════════════════════

    public function create(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $this->renderView('create', array_merge($this->formDropdowns(), [
            'pageTitle'  => 'إيصال جديد',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · إيصال جديد',
            'receipt'    => [],
            'errors'     => [],
            'isEdit'     => false,
            'isAdmin'    => (auth_user()['role'] === 'admin'),
        ]));
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE
    // ════════════════════════════════════════════════════════════════════════

    private function findOrCreateClient(string $name, string $phone): int {
        $db = get_db();

        $stmt = $db->prepare("SELECT id FROM clients WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        $stmt = $db->prepare("
            INSERT INTO clients (client_name, phone, created_by, created_at)
            VALUES (:client_name, :phone, :created_by, CURDATE())
        ");
        $stmt->execute([
            ':client_name' => $name,
            ':phone'       => $phone,
            ':created_by'  => auth_user()['id'],
        ]);

        return (int) $db->lastInsertId();
    }

    public function store(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $data               = $this->parseForm();
        $data['creator_id'] = auth_user()['id'];

        $errors = $this->validate($data);

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('create', array_merge($this->formDropdowns(), [
                'pageTitle'  => 'إيصال جديد',
                'breadcrumb' => 'لوحة التحكم · الإيصالات · إيصال جديد',
                'receipt'    => $data,
                'errors'     => $errors,
                'isEdit'     => false,
                'isAdmin'    => (auth_user()['role'] === 'admin'),
            ]));
            return;
        }

        $data['client_id'] = $this->findOrCreateClient(
            $data['client_name'],
            $data['phone']
        );

        $newId = $this->receipts->create($data);

        $evidencePath = $this->handleEvidenceUpload();

        if ((float) $data['amount'] > 0) {
            $this->transactions->create([
                'receipt_id'     => $newId,
                'payment_method' => $data['payment_method'],
                'amount'         => $data['amount'],
                'created_by'     => auth_user()['id'],
                'type'           => 'payment',
                'notes'          => 'دفعة أولى عند إنشاء الإيصال / Initial payment',
                'attachment'     => $evidencePath,
            ]);
        }

        require_once ROOT . '/app/Services/ReceiptPdfGenerator.php';
        $fullReceipt = $this->receipts->findById($newId);
        $planPrice   = (float) ($fullReceipt['plan_price'] ?? 0);
        $paid        = (float) $data['amount'];
        $remaining   = max(0, $planPrice - $paid);

        $autoStatus = ($paid > 0 && $remaining == 0) ? 'completed' : 'not_completed';
        get_db()->prepare("UPDATE receipts SET receipt_status = ? WHERE id = ?")
                ->execute([$autoStatus, $newId]);

        $saveDir = ROOT . '/public/uploads/receipts';
        $pdfFile = ReceiptPdfGenerator::save(
            $fullReceipt,
            $paid,
            $remaining,
            $data['payment_method'],
            $saveDir
        );

        get_db()->prepare("UPDATE receipts SET pdf_path = ? WHERE id = ?")
                ->execute([$pdfFile, $newId]);

        log_action('created_receipt', "id: {$newId}, client: {$data['client_name']}", auth_user()['id']);
        $this->flash('flash_success', 'تم إنشاء الإيصال بنجاح.');
        $this->redirect('/receipt/preview?id=' . $newId);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PREVIEW
    // ════════════════════════════════════════════════════════════════════════

    public function preview(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id      = (int) ($_GET['id'] ?? 0);
        $receipt = $this->receipts->findById($id);

        if (!$receipt) {
            $this->flash('flash_error', 'الإيصال غير موجود.');
            $this->redirect('/receipts');
            return;
        }

        $this->renderView('preview', [
            'pageTitle'  => 'تفاصيل الإيصال #' . $id,
            'breadcrumb' => 'لوحة التحكم · الإيصالات · معاينة',
            'receipt'    => $receipt,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // SHOW
    // ════════════════════════════════════════════════════════════════════════

    public function show(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id      = (int) ($_GET['id'] ?? 0);
        $receipt = $this->receipts->findById($id);

        if (!$receipt) {
            $this->flash('flash_error', 'الإيصال غير موجود.');
            $this->redirect('/receipts');
            return;
        }

        $transactions = $this->transactions->findByReceipt($id);
        $auditLogs    = $this->auditLog->findByReceipt($id);
        $totalPaid    = $this->transactions->totalByReceipt($id);

        $this->renderView('show', [
            'pageTitle'    => 'عرض الإيصال #' . $id,
            'breadcrumb'   => 'لوحة التحكم · الإيصالات · عرض',
            'receipt'      => $receipt,
            'transactions' => $transactions,
            'auditLogs'    => $auditLogs,
            'totalPaid'    => $totalPaid,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EDIT
    // ════════════════════════════════════════════════════════════════════════

    public function edit(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id      = (int) ($_GET['id'] ?? 0);
        $receipt = $this->receipts->findById($id);

        if (!$receipt) {
            $this->flash('flash_error', 'الإيصال غير موجود.');
            $this->redirect('/receipts');
            return;
        }

        $this->renderView('create', array_merge($this->formDropdowns(), [
            'pageTitle'  => 'تعديل الإيصال',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · تعديل',
            'receipt'    => $receipt,
            'errors'     => [],
            'isEdit'     => true,
            'isAdmin'    => (auth_user()['role'] === 'admin'),
        ]));
    }

    // ════════════════════════════════════════════════════════════════════════
    // UPDATE
    // ════════════════════════════════════════════════════════════════════════

    public function update(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id      = (int) ($_GET['id'] ?? 0);
        $receipt = $this->receipts->findById($id);

        if (!$receipt) {
            $this->flash('flash_error', 'الإيصال غير موجود.');
            $this->redirect('/receipts');
            return;
        }

        $data   = $this->parseForm();
        $errors = $this->validate($data);

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('create', array_merge($this->formDropdowns(), [
                'pageTitle'  => 'تعديل الإيصال',
                'breadcrumb' => 'لوحة التحكم · الإيصالات · تعديل',
                'receipt'    => array_merge($receipt, $data),
                'errors'     => $errors,
                'isEdit'     => true,
                'isAdmin'    => (auth_user()['role'] === 'admin'),
            ]));
            return;
        }

        $this->auditLog->logChanges($id, auth_user()['id'], auth_user()['role'], $receipt, $data);
        $this->receipts->update($id, $data);

        log_action('updated_receipt', "id: {$id}", auth_user()['id']);
        $this->flash('flash_success', 'تم تحديث الإيصال بنجاح.');
        $this->redirect('/receipts');
    }

    // ════════════════════════════════════════════════════════════════════════
    // DESTROY
    // ════════════════════════════════════════════════════════════════════════

    public function destroy(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id      = (int) ($_GET['id'] ?? 0);
        $receipt = $this->receipts->findById($id);

        if (!$receipt) {
            $this->flash('flash_error', 'الإيصال غير موجود.');
            $this->redirect('/receipts');
            return;
        }

        $this->receipts->delete($id);
        log_action('deleted_receipt', "id: {$id}", auth_user()['id']);

        $this->flash('flash_success', 'تم حذف الإيصال بنجاح.');
        $this->redirect('/receipts');
    }

    // ════════════════════════════════════════════════════════════════════════
    // RENEW — GET /receipt/renew
    // ════════════════════════════════════════════════════════════════════════

    public function renew(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $client = null;
        $search = trim($_GET['search'] ?? '');

        if ($search) {
            $db   = get_db();
            $stmt = $db->prepare("
                SELECT * FROM clients
                WHERE phone = :q OR client_name LIKE :like
                LIMIT 1
            ");
            $stmt->execute([':q' => $search, ':like' => '%' . $search . '%']);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $this->renderView('create', array_merge($this->formDropdowns(), [
            'pageTitle'  => 'تجديد اشتراك',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · تجديد',
'receipt' => $client ? [
    'client_name'  => $client['client_name'],
    'phone'        => $client['phone'],
    'phone_number' => $client['phone'],
    // Strip leading country code digit(s) for the local field.
    // Adjust the slice offset to match your country code length.
    'phone_local'  => ltrim($client['phone'], '0'),   // or substr($client['phone'], 2) for +20 etc.
    'country_code' => $client['country_code'] ?? '',  // if stored on client; else leave empty
    'client_email' => $client['client_email'] ?? $client['email'] ?? '',
    'client_id'    => $client['id'],
] : [],
            'client'     => $client,
            'search'     => $search,
            'errors'     => [],
            'isEdit'     => false,
            'isRenewal'  => true,
            'isAdmin'    => (auth_user()['role'] === 'admin'),
        ]));
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE RENEWAL — POST /receipt/renew
    // ════════════════════════════════════════════════════════════════════════

    public function storeRenewal(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $data                 = $this->parseForm();
        $data['creator_id']   = auth_user()['id'];
        $data['renewal_type'] = trim($_POST['renewal_type'] ?? '');
        $errors               = $this->validate($data);

        if (empty($data['renewal_type'])) {
            $errors[] = 'يجب اختيار نوع التجديد.';
        }

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('create', array_merge($this->formDropdowns(), [
                'pageTitle'  => 'تجديد اشتراك',
                'breadcrumb' => 'لوحة التحكم · الإيصالات · تجديد',
                'receipt'    => $data,
                'client'     => null,
                'search'     => '',
                'errors'     => $errors,
                'isEdit'     => false,
                'isRenewal'  => true,
                'isAdmin'    => (auth_user()['role'] === 'admin'),
            ]));
            return;
        }

        if (!empty($_POST['client_id'])) {
            $data['client_id'] = (int) $_POST['client_id'];
        } else {
            $data['client_id'] = $this->findOrCreateClient(
                $data['client_name'],
                $data['phone']
            );
        }

        $newId = $this->receipts->create($data);

        $evidencePath = $this->handleEvidenceUpload();

        if ((float) $data['amount'] > 0) {
            $this->transactions->create([
                'receipt_id'     => $newId,
                'payment_method' => $data['payment_method'],
                'amount'         => $data['amount'],
                'created_by'     => auth_user()['id'],
                'type'           => 'payment',
                'notes'          => 'دفعة تجديد / Renewal payment',
                'attachment'     => $evidencePath,
            ]);
        }

        $fullReceipt = $this->receipts->findById($newId);
        $planPrice   = (float) ($fullReceipt['plan_price'] ?? 0);
        $paid        = (float) $data['amount'];
        $remaining   = max(0, $planPrice - $paid);
        $autoStatus  = ($paid > 0 && $remaining == 0) ? 'completed' : 'not_completed';
        get_db()->prepare("UPDATE receipts SET receipt_status = ? WHERE id = ?")
                ->execute([$autoStatus, $newId]);

        log_action('renewed_receipt', "id: {$newId}, client: {$data['client_name']}", auth_user()['id']);
        $this->flash('flash_success', 'تم إنشاء إيصال التجديد بنجاح.');
        $this->redirect('/receipt/preview?id=' . $newId . '&type=renewal');
    }

    // ════════════════════════════════════════════════════════════════════════
    // PAYMENT PAGE — GET /receipt/payment
    // ════════════════════════════════════════════════════════════════════════

    public function paymentPage(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $client   = null;
        $receipts = [];
        $search   = trim($_GET['search'] ?? '');

        if ($search) {
            $db   = get_db();
            $stmt = $db->prepare("
                SELECT * FROM clients
                WHERE phone = :q OR client_name LIKE :like
                LIMIT 1
            ");
            $stmt->execute([':q' => $search, ':like' => '%' . $search . '%']);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($client) {
                $receipts = $this->receipts->findByClientWithTotals($client['id']);
            }
        }

        $this->renderView('payment', [
            'pageTitle'  => 'إضافة دفعة',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · إضافة دفعة',
            'client'     => $client,
            'receipts'   => $receipts,
            'search'     => $search,
            'errors'     => [],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // PDF
    // ════════════════════════════════════════════════════════════════════════

    public function pdf(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id      = (int) ($_GET['id'] ?? 0);
        $receipt = $this->receipts->findById($id);

        if (!$receipt) {
            $this->flash('flash_error', 'الإيصال غير موجود.');
            $this->redirect('/receipts');
            return;
        }

        $db   = get_db();
        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN type = 'refund'  THEN amount ELSE 0 END), 0) AS total_refunded
            FROM transactions WHERE receipt_id = ?
        ");
        $stmt->execute([$id]);
        $tx = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalPaid = (float) $tx['total_paid'];
        $remaining = max(0, (float)($receipt['plan_price'] ?? 0) - $totalPaid + (float)$tx['total_refunded']);

        $pmStmt = $db->prepare("
            SELECT payment_method FROM transactions
            WHERE receipt_id = ? AND type = 'payment'
            ORDER BY id DESC LIMIT 1
        ");
        $pmStmt->execute([$id]);
        $paymentMethod = $pmStmt->fetchColumn() ?: '';

        require_once ROOT . '/app/Services/ReceiptPdfGenerator.php';
        ReceiptPdfGenerator::generate($receipt, $totalPaid, $remaining, $paymentMethod);
        exit;
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE PAYMENT — POST /receipt/payment
    // ════════════════════════════════════════════════════════════════════════

    public function storePayment(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $receiptId     = (int) ($_POST['receipt_id']     ?? 0);
        $amount        = (float) ($_POST['amount']        ?? 0);
        $paymentMethod = trim($_POST['payment_method']    ?? '');
        $notes         = trim($_POST['notes']             ?? '');

        $receipt = $this->receipts->findById($receiptId);
        $errors  = [];

        if (!$receipt)       $errors[] = 'الإيصال غير موجود.';
        if ($amount <= 0)    $errors[] = 'يجب إدخال مبلغ أكبر من صفر.';
        if (!$paymentMethod) $errors[] = 'يجب اختيار طريقة الدفع.';

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->redirect('/receipt/payment?search=' . urlencode($_POST['search'] ?? ''));
            return;
        }

        $evidencePath = $this->handleEvidenceUpload();

        $this->transactions->create([
            'receipt_id'     => $receiptId,
            'payment_method' => $paymentMethod,
            'amount'         => $amount,
            'created_by'     => auth_user()['id'],
            'type'           => 'payment',
            'notes'          => $notes ?: 'دفعة إضافية / Additional payment',
            'attachment'     => $evidencePath,
        ]);

        // Recalculate status after payment
        $db   = get_db();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(CASE WHEN type='payment' THEN amount ELSE 0 END), 0)
                 - COALESCE(SUM(CASE WHEN type='refund'  THEN amount ELSE 0 END), 0)
            FROM transactions WHERE receipt_id = ?
        ");
        $stmt->execute([$receiptId]);
        $netPaid   = (float) $stmt->fetchColumn();
        $planPrice = (float) ($receipt['plan_price'] ?? 0);

        $autoStatus = ($netPaid > 0 && $planPrice > 0 && $netPaid >= $planPrice)
            ? 'completed'
            : 'not_completed';

        $db->prepare("UPDATE receipts SET receipt_status = ? WHERE id = ?")
           ->execute([$autoStatus, $receiptId]);

        // ── Branch update for branch_manager ─────────────────────────────────
        // If the user making this additional payment is a branch_manager, update
        // the receipt's branch to their managed branch.
        $user = auth_user();
        if ($user['role'] === 'branch_manager') {
            $managerBranchId = $this->receipts->getBranchIdByManager($user['id']);
            if ($managerBranchId) {
                $db->prepare("UPDATE receipts SET branch_id = ? WHERE id = ?")
                   ->execute([$managerBranchId, $receiptId]);

                $this->auditLog->logChanges(
                    $receiptId,
                    $user['id'],
                    $user['role'],
                    ['branch_id' => $receipt['branch_id']],
                    ['branch_id' => $managerBranchId]
                );
            }
        }

        log_action('added_payment', "receipt_id: {$receiptId}, amount: {$amount}", auth_user()['id']);
        $this->flash('flash_success', 'تم تسجيل الدفعة بنجاح.');
        $this->redirect('/receipt/preview?id=' . $receiptId . '&type=payment');
    }

    // ════════════════════════════════════════════════════════════════════════
    // REFUND PAGE — GET /receipt/refund
    // ════════════════════════════════════════════════════════════════════════

    public function refundPage(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $client   = null;
        $receipts = [];
        $search   = trim($_GET['search'] ?? '');

        if ($search) {
            $db   = get_db();
            $stmt = $db->prepare("
                SELECT * FROM clients
                WHERE phone = :q OR client_name LIKE :like
                LIMIT 1
            ");
            $stmt->execute([':q' => $search, ':like' => '%' . $search . '%']);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($client) {
                $receipts = $this->receipts->findByClientWithTotals($client['id']);
            }
        }

        $this->renderView('refund', [
            'pageTitle'  => 'استرداد مبلغ',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · استرداد',
            'client'     => $client,
            'receipts'   => $receipts,
            'search'     => $search,
            'errors'     => [],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE REFUND — POST /receipt/refund
    // ════════════════════════════════════════════════════════════════════════

    public function storeRefund(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $receiptId     = (int) ($_POST['receipt_id']     ?? 0);
        $amount        = (float) ($_POST['amount']        ?? 0);
        $paymentMethod = trim($_POST['payment_method']    ?? '');
        $notes         = trim($_POST['notes']             ?? '');

        $receipt = $this->receipts->findById($receiptId);
        $errors  = [];

        if (!$receipt)       $errors[] = 'الإيصال غير موجود.';
        if ($amount <= 0)    $errors[] = 'يجب إدخال مبلغ أكبر من صفر.';
        if (!$paymentMethod) $errors[] = 'يجب اختيار طريقة الدفع.';

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->redirect('/receipt/refund?search=' . urlencode($_POST['search'] ?? ''));
            return;
        }

        $evidencePath = $this->handleEvidenceUpload();

        $this->transactions->create([
            'receipt_id'     => $receiptId,
            'payment_method' => $paymentMethod,
            'amount'         => $amount,
            'created_by'     => auth_user()['id'],
            'type'           => 'refund',
            'notes'          => $notes ?: 'استرداد مبلغ / Refund',
            'attachment'     => $evidencePath,
        ]);

        log_action('refunded', "receipt_id: {$receiptId}, amount: {$amount}", auth_user()['id']);
        $this->flash('flash_success', 'تم تسجيل الاسترداد بنجاح.');
        $this->redirect('/receipt/preview?id=' . $receiptId . '&type=refund');
    }
}
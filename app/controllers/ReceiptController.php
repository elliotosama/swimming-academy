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
            'phone'           => trim($_POST['phone']           ?? ''),
            'client_id'       => (int) ($_POST['client_id']     ?? 0),
            'creator_id'      => (int) ($_POST['creator_id']    ?? 0),
            'captain_id'      => (int) ($_POST['captain_id']    ?? 0),
            'branch_id'       => (int) ($_POST['branch_id']     ?? 0),
            'first_session'   => trim($_POST['first_session']   ?? ''),
            'last_session'    => trim($_POST['last_session']    ?? ''),
            'renewal_session' => trim($_POST['renewal_session'] ?? ''),
            'renewal_type'    => trim($_POST['renewal_type']    ?? ''),
            'receipt_status'  => trim($_POST['receipt_status']  ?? 'not_completed'),
            'exercise_time'   => trim($_POST['exercise_time']   ?? ''),
            'plan_id'         => (int) ($_POST['plan_id']       ?? 0) ?: null,
            'level'           => (int) ($_POST['level']         ?? 0) ?: null,
            'pdf_path'        => trim($_POST['pdf_path']        ?? ''),
            'amount'          => (float) ($_POST['amount']      ?? 0),
            'remaining'       => (float) ($_POST['remaining']   ?? 0),
            'payment_method'  => trim($_POST['payment_method']  ?? ''),
            'notes'           => trim($_POST['notes']           ?? ''),
        ];
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

    /** Parse all filter inputs from GET */
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

    /** Load dropdown data shared across create / edit forms */
/** Load dropdown data shared across create / edit forms */
/** Load dropdown data shared across create / edit forms */
private function formDropdowns(): array {
    $db = get_db();

    $branches = $db->query("
        SELECT id, branch_name, country, working_days1, working_days2, working_days3
        FROM branches
        WHERE visible = 1
        ORDER BY branch_name
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Captains grouped by branch (many-to-many via captain_branch)
    // Only active captains (visible = 1)
    $captainRows = $db->query("
        SELECT cb.branch_id, c.id, c.captain_name
        FROM captain_branch cb
        JOIN captains c ON c.id = cb.captain_id
        WHERE c.visible = 1
        ORDER BY c.captain_name
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Build CAPTAINS_BY_BRANCH[branch_id] = [{id, name}, ...]
    $captainsByBranch = [];
    foreach ($captainRows as $row) {
        $captainsByBranch[$row['branch_id']][] = [
            'id'   => $row['id'],
            'name' => $row['captain_name'],
        ];
    }

    $plans = $db->query("
        SELECT id, description, price, number_of_sessions, country
        FROM prices
        WHERE visible = 1
        ORDER BY description
    ")->fetchAll(PDO::FETCH_ASSOC);

    return [
        'branches'         => $branches,
        'captainsByBranch' => $captainsByBranch,
        'plans'            => $plans,
    ];
}
 // ════════════════════════════════════════════════════════════════════════
    // INDEX  —  GET /receipts
    // ════════════════════════════════════════════════════════════════════════

    public function index(): void {
        auth_require(['admin']);

        $filters = $this->parseFilters();
        $page    = max(1, (int) ($_GET['page'] ?? 1));

        $result   = $this->receipts->search($filters, $page, self::PER_PAGE);
        $receipts = $result['data'];
        $total    = $result['total'];
        $lastPage = (int) ceil($total / self::PER_PAGE);

        $db       = get_db();
        $branches = $db->query("SELECT id, branch_name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
        $creators = $db->query("SELECT id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

        $this->renderView('index', [
            'pageTitle'  => 'الإيصالات',
            'breadcrumb' => 'لوحة التحكم · الإيصالات',
            'receipts'   => $receipts,
            'filters'    => $filters,
            'page'       => $page,
            'lastPage'   => $lastPage,
            'total'      => $total,
            'perPage'    => self::PER_PAGE,
            'branches'   => $branches,
            'creators'   => $creators,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EXPORT  —  GET /receipts/export
    // ════════════════════════════════════════════════════════════════════════

    public function export(): void {
        auth_require(['admin']);

        $filters = $this->parseFilters();
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
                $r['client_name']    ?? '',
                $r['phone']          ?? '',
                $r['branch_name']    ?? '',
                $r['captain_name']   ?? '',
                $r['plan_name']      ?? '',
                $r['first_session']  ?? '',
                $r['last_session']   ?? '',
                $r['renewal_session'] ?? '',
                $r['renewal_type']   ?? '',
                $statusLabels[$r['receipt_status']] ?? $r['receipt_status'],
                $r['exercise_time']  ?? '',
                $r['level']          ?? '',
                $r['creator_name']   ?? '',
                $r['created_at']     ?? '',
                $r['total_paid']     ?? 0,
                $r['audit_count']    ?? 0,
                $r['transaction_count'] ?? 0,
            ]);
        }

        fclose($out);
        log_action('exported_receipts', 'filters: ' . json_encode($filters), auth_user()['id']);
        exit;
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE  —  GET /receipts/create
    // ════════════════════════════════════════════════════════════════════════

    public function create(): void {
        auth_require(['admin']);

        $this->renderView('create', array_merge($this->formDropdowns(), [
            'pageTitle'  => 'إيصال جديد',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · إيصال جديد',
            'receipt'    => [],
            'errors'     => [],
            'isEdit'     => false,
        ]));
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE  —  POST /receipts/create
    // ════════════════════════════════════════════════════════════════════════

    private function findOrCreateClient(string $name, string $phone): int {
    $db = get_db();

    // Look up by phone first
    $stmt = $db->prepare("SELECT id FROM clients WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        return (int) $existing;
    }

    // Not found — create a new client
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
    auth_require(['admin']);

    $data               = $this->parseForm();
    $data['creator_id'] = auth_user()['id'];
    $errors             = $this->validate($data);

    if ($errors) {
        $this->flash('flash_error', implode('<br>', $errors));
        $this->renderView('create', array_merge($this->formDropdowns(), [
            'pageTitle'  => 'إيصال جديد',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · إيصال جديد',
            'receipt'    => $data,
            'errors'     => $errors,
            'isEdit'     => false,
        ]));
        return;
    }

    // Resolve client
    $data['client_id'] = $this->findOrCreateClient(
        $data['client_name'],
        $data['phone']
    );

    $newId = $this->receipts->create($data);

    // ── Auto-log the initial payment into transactions ──────────────────────
    if ((float) $data['amount'] > 0) {
        $this->transactions->create([
            'receipt_id'     => $newId,
            'payment_method' => $data['payment_method'],
            'amount'         => $data['amount'],
            'created_by'     => auth_user()['id'],
            'type'           => 'payment',
            'notes'          => 'دفعة أولى عند إنشاء الإيصال / Initial payment',
            'attachment'     => null,
        ]);
    }

    log_action('created_receipt', "id: {$newId}, client: {$data['client_name']}", auth_user()['id']);
    $this->flash('flash_success', 'تم إنشاء الإيصال بنجاح.');
    $this->redirect('/receipt/preview?id=' . $newId);
}


// ════════════════════════════════════════════════════════════════════════
// PREVIEW  —  GET /receipts/preview?id=x
// Shown immediately after creation — receipt card + WhatsApp button
// ════════════════════════════════════════════════════════════════════════

public function preview(): void {
    auth_require(['admin']);

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
    // SHOW  —  GET /receipts/show?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function show(): void {
        auth_require(['admin']);

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
    // EDIT  —  GET /receipts/edit?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function edit(): void {
        auth_require(['admin']);

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
        ]));
    }

    // ════════════════════════════════════════════════════════════════════════
    // UPDATE  —  POST /receipts/edit?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function update(): void {
        auth_require(['admin']);

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
    // DESTROY  —  POST /receipts/delete?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function destroy(): void {
        auth_require(['admin']);

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
// RENEW  —  GET /receipt/renew
// ════════════════════════════════════════════════════════════════════════

public function renew(): void {
    auth_require(['admin']);

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

    $this->renderView('renew', array_merge($this->formDropdowns(), [
        'pageTitle'  => 'تجديد اشتراك',
        'breadcrumb' => 'لوحة التحكم · الإيصالات · تجديد',
        'receipt'    => $client ? [
            'client_name' => $client['client_name'],
            'phone'       => $client['phone'],
            'client_id'   => $client['id'],
        ] : [],
        'client'     => $client,
        'search'     => $search,
        'errors'     => [],
        'isEdit'     => false,
        'isRenewal'  => true,
    ]));
}

// ════════════════════════════════════════════════════════════════════════
// STORE RENEWAL  —  POST /receipt/renew
// ════════════════════════════════════════════════════════════════════════

public function storeRenewal(): void {
    auth_require(['admin']);

    $data               = $this->parseForm();
    $data['creator_id'] = auth_user()['id'];
    $data['renewal_type'] = trim($_POST['renewal_type'] ?? '');
    $errors             = $this->validate($data);

    if (empty($data['renewal_type'])) {
        $errors[] = 'يجب اختيار نوع التجديد.';
    }

    if ($errors) {
        $this->flash('flash_error', implode('<br>', $errors));
        $this->renderView('renew', array_merge($this->formDropdowns(), [
            'pageTitle'  => 'تجديد اشتراك',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · تجديد',
            'receipt'    => $data,
            'client'     => null,
            'search'     => '',
            'errors'     => $errors,
            'isEdit'     => false,
            'isRenewal'  => true,
        ]));
        return;
    }

    // Use posted client_id if provided, otherwise lookup/create
    if (!empty($_POST['client_id'])) {
        $data['client_id'] = (int) $_POST['client_id'];
    } else {
        $data['client_id'] = $this->findOrCreateClient(
            $data['client_name'],
            $data['phone']
        );
    }

    $newId = $this->receipts->create($data);

    if ((float) $data['amount'] > 0) {
        $this->transactions->create([
            'receipt_id'     => $newId,
            'payment_method' => $data['payment_method'],
            'amount'         => $data['amount'],
            'created_by'     => auth_user()['id'],
            'type'           => 'payment',
            'notes'          => 'دفعة تجديد / Renewal payment',
            'attachment'     => null,
        ]);
    }

    log_action('renewed_receipt', "id: {$newId}, client: {$data['client_name']}", auth_user()['id']);
    $this->flash('flash_success', 'تم إنشاء إيصال التجديد بنجاح.');
    $this->redirect('/receipt/preview?id=' . $newId . '&type=renewal');
}

// ════════════════════════════════════════════════════════════════════════
// PAYMENT PAGE  —  GET /receipt/payment
// ════════════════════════════════════════════════════════════════════════

public function paymentPage(): void {
    auth_require(['admin']);

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
            $receipts = $this->receipts->findByClient($client['id']);
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
// STORE PAYMENT  —  POST /receipt/payment
// ════════════════════════════════════════════════════════════════════════

public function storePayment(): void {
    auth_require(['admin']);

    $receiptId     = (int) ($_POST['receipt_id']     ?? 0);
    $amount        = (float) ($_POST['amount']        ?? 0);
    $paymentMethod = trim($_POST['payment_method']    ?? '');
    $notes         = trim($_POST['notes']             ?? '');

    $receipt = $this->receipts->findById($receiptId);
    $errors  = [];

    if (!$receipt)          $errors[] = 'الإيصال غير موجود.';
    if ($amount <= 0)       $errors[] = 'يجب إدخال مبلغ أكبر من صفر.';
    if (!$paymentMethod)    $errors[] = 'يجب اختيار طريقة الدفع.';

    if ($errors) {
        $this->flash('flash_error', implode('<br>', $errors));
        $this->redirect('/receipt/payment?search=' . urlencode($_POST['search'] ?? ''));
        return;
    }

    // Insert transaction
    $this->transactions->create([
        'receipt_id'     => $receiptId,
        'payment_method' => $paymentMethod,
        'amount'         => $amount,
        'created_by'     => auth_user()['id'],
        'type'           => 'payment',
        'notes'          => $notes ?: 'دفعة إضافية / Additional payment',
        'attachment'     => null,
    ]);

    // Recalculate remaining = old remaining - new payment (floor at 0)

    log_action('added_payment', "receipt_id: {$receiptId}, amount: {$amount}", auth_user()['id']);
    $this->flash('flash_success', 'تم تسجيل الدفعة بنجاح.');
    $this->redirect('/receipt/preview?id=' . $receiptId . '&type=payment');
}

// ════════════════════════════════════════════════════════════════════════
// REFUND PAGE  —  GET /receipt/refund
// ════════════════════════════════════════════════════════════════════════

public function refundPage(): void {
    auth_require(['admin']);

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
            $receipts = $this->receipts->findByClient($client['id']);
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
// STORE REFUND  —  POST /receipt/refund
// ════════════════════════════════════════════════════════════════════════

public function storeRefund(): void {
    auth_require(['admin']);

    $receiptId     = (int) ($_POST['receipt_id']     ?? 0);
    $amount        = (float) ($_POST['amount']        ?? 0);
    $paymentMethod = trim($_POST['payment_method']    ?? '');
    $notes         = trim($_POST['notes']             ?? '');

    $receipt = $this->receipts->findById($receiptId);
    $errors  = [];

    if (!$receipt)          $errors[] = 'الإيصال غير موجود.';
    if ($amount <= 0)       $errors[] = 'يجب إدخال مبلغ أكبر من صفر.';
    if (!$paymentMethod)    $errors[] = 'يجب اختيار طريقة الدفع.';

    if ($errors) {
        $this->flash('flash_error', implode('<br>', $errors));
        $this->redirect('/receipt/refund?search=' . urlencode($_POST['search'] ?? ''));
        return;
    }

    // Insert refund transaction (positive amount, type = refund)
    $this->transactions->create([
        'receipt_id'     => $receiptId,
        'payment_method' => $paymentMethod,
        'amount'         => $amount,
        'created_by'     => auth_user()['id'],
        'type'           => 'refund',
        'notes'          => $notes ?: 'استرداد مبلغ / Refund',
        'attachment'     => null,
    ]);

    log_action('refunded', "receipt_id: {$receiptId}, amount: {$amount}", auth_user()['id']);
    $this->flash('flash_success', 'تم تسجيل الاسترداد بنجاح.');
    $this->redirect('/receipt/preview?id=' . $receiptId . '&type=refund');
}
}
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
private function formDropdowns(): array {
    $db = get_db();
    return [
        'branches' => $db->query("
            SELECT id, branch_name, country, working_days1, working_days2, working_days3
            FROM branches
            WHERE visible = 1
            ORDER BY branch_name
        ")->fetchAll(PDO::FETCH_ASSOC),

        'plans' => $db->query("
            SELECT id, description, price, number_of_sessions, country
            FROM prices
            WHERE visible = 1
            ORDER BY description
        ")->fetchAll(PDO::FETCH_ASSOC),
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

        $newId = $this->receipts->create($data);

        log_action('created_receipt', "id: {$newId}, client: {$data['client_name']}", auth_user()['id']);
        $this->flash('flash_success', 'تم إنشاء الإيصال بنجاح.');
        $this->redirect('/receipts');
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
}
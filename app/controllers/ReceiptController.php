<?php
// app/controllers/ReceiptController.php
require ROOT . '/app/models/ReceiptAuditLogModel.php';
class ReceiptController {

    private ReceiptModel        $receipts;
    private ReceiptAuditLogModel $auditLog;
    private TransactionModel    $transactions;

    public function __construct() {
        $this->receipts     = new ReceiptModel();
        $this->auditLog     = new ReceiptAuditLogModel();
        $this->transactions = new TransactionModel();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

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
            'client_id'       => (int) ($_POST['client_id']       ?? 0),
            'creator_id'      => (int) ($_POST['creator_id']      ?? 0),
            'captain_id'      => (int) ($_POST['captain_id']      ?? 0),
            'branch_id'       => (int) ($_POST['branch_id']       ?? 0),
            'first_session'   => trim($_POST['first_session']   ?? ''),
            'last_session'    => trim($_POST['last_session']    ?? ''),
            'renewal_session' => trim($_POST['renewal_session'] ?? ''),
            'renewal_type'    => trim($_POST['renewal_type']    ?? ''),
            'receipt_status'  => trim($_POST['receipt_status']  ?? 'not_completed'),
            'exercise_time'   => trim($_POST['exercise_time']   ?? ''),
            'plan_id'         => (int) ($_POST['plan_id']         ?? 0) ?: null,
            'level'           => (int) ($_POST['level']           ?? 0) ?: null,
            'pdf_path'        => trim($_POST['pdf_path']        ?? ''),
        ];
    }

    private function validate(array $data): array {
        $errors = [];

        if (empty($data['client_id']))
            $errors[] = 'يجب اختيار العميل.';

        if (empty($data['branch_id']))
            $errors[] = 'يجب اختيار الفرع.';

        if (!empty($data['first_session']) && !empty($data['last_session'])
            && $data['last_session'] < $data['first_session']) {
            $errors[] = 'تاريخ آخر جلسة لا يمكن أن يكون قبل تاريخ أول جلسة.';
        }

        return $errors;
    }

    // ════════════════════════════════════════════════════════════════════════
    // INDEX  —  GET /admin/receipts
    // ════════════════════════════════════════════════════════════════════════

    public function index(): void {
        auth_require(['admin']);

        $receipts = $this->receipts->findAll();

        $this->renderView('index', [
            'pageTitle'  => 'الإيصالات',
            'breadcrumb' => 'لوحة التحكم · الإيصالات',
            'receipts'   => $receipts,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE  —  GET /admin/receipts/create
    // ════════════════════════════════════════════════════════════════════════

    public function create(): void {
        auth_require(['admin']);

        $this->renderView('create', [
            'pageTitle'  => 'إيصال جديد',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · إيصال جديد',
            'receipt'    => [],
            'errors'     => [],
            'isEdit'     => false,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE  —  POST /admin/receipts/create
    // ════════════════════════════════════════════════════════════════════════

    public function store(): void {
        auth_require(['admin']);

        $data            = $this->parseForm();
        $data['creator_id'] = auth_user()['id'];
        $errors          = $this->validate($data);

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('create', [
                'pageTitle'  => 'إيصال جديد',
                'breadcrumb' => 'لوحة التحكم · الإيصالات · إيصال جديد',
                'receipt'    => $data,
                'errors'     => $errors,
                'isEdit'     => false,
            ]);
            return;
        }

        $newId = $this->receipts->create($data);

        log_action('created_receipt', "id: {$newId}, client_id: {$data['client_id']}", auth_user()['id']);
        $this->flash('flash_success', 'تم إنشاء الإيصال بنجاح.');
        $this->redirect('/receipts');
    }

    // ════════════════════════════════════════════════════════════════════════
    // SHOW  —  GET /admin/receipts/show?id=x
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
    // EDIT  —  GET /admin/receipts/edit?id=x
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

        $this->renderView('edit', [
            'pageTitle'  => 'تعديل الإيصال',
            'breadcrumb' => 'لوحة التحكم · الإيصالات · تعديل',
            'receipt'    => $receipt,
            'errors'     => [],
            'isEdit'     => true,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // UPDATE  —  POST /admin/receipts/edit?id=x
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
            $this->renderView('edit', [
                'pageTitle'  => 'تعديل الإيصال',
                'breadcrumb' => 'لوحة التحكم · الإيصالات · تعديل',
                'receipt'    => array_merge($receipt, $data),
                'errors'     => $errors,
                'isEdit'     => true,
            ]);
            return;
        }

        $this->auditLog->logChanges($id, auth_user()['id'], auth_user()['role'], $receipt, $data);
        $this->receipts->update($id, $data);

        log_action('updated_receipt', "id: {$id}", auth_user()['id']);
        $this->flash('flash_success', 'تم تحديث الإيصال بنجاح.');
        $this->redirect('/receipts');
    }

    // ════════════════════════════════════════════════════════════════════════
    // DESTROY  —  POST /admin/receipts/delete?id=x
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
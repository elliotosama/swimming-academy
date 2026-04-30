<?php
// app/controllers/TransactionController.php

class TransactionController {

    private TransactionModel     $transactions;
    private ReceiptModel         $receipts;
    private ReceiptAuditLogModel $auditLog;

    public function __construct() {
        $this->transactions = new TransactionModel();
        $this->receipts     = new ReceiptModel();
        $this->auditLog     = new ReceiptAuditLogModel();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function redirect(string $path): void {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    private function renderView(string $view, array $data = []): void {
        extract($data);
        require ROOT . "/views/transactions/{$view}.php";
    }

    private function flash(string $key, string $msg): void {
        $_SESSION[$key] = $msg;
    }

    private function parseForm(): array {
        return [
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'amount'         => (float) ($_POST['amount'] ?? 0),
            'receipt_id'     => (int) ($_POST['receipt_id'] ?? 0) ?: null,
            'created_by'     => auth_user()['id'],
            'attachment'     => trim($_POST['attachment'] ?? ''),
            'notes'          => trim($_POST['notes']      ?? ''),
            'type'           => trim($_POST['type']       ?? 'payment'),
        ];
    }

    private function validate(array $data): array {
        $errors = [];

        if (empty($data['payment_method']))
            $errors[] = 'طريقة الدفع مطلوبة.';

        if ($data['amount'] <= 0)
            $errors[] = 'يجب أن يكون المبلغ أكبر من صفر.';

        $allowed = ['payment', 'refund', 'discount'];
        if (!in_array($data['type'], $allowed))
            $errors[] = 'نوع المعاملة غير صالح.';

        return $errors;
    }

    // ════════════════════════════════════════════════════════════════════════
    // INDEX  —  GET /transactions
    // ════════════════════════════════════════════════════════════════════════

    public function index(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $user    = auth_user();
        $role    = $user['role'];
        $perPage = 20;
        $page    = max(1, (int) ($_GET['page'] ?? 1));

        // ── User-supplied search filters ──────────────────────────────────
        $searchReceiptId   = (int)  ($_GET['receipt_id']   ?? 0) ?: null;
        $searchClientPhone = trim(   $_GET['client_phone'] ?? '');

        // ── Role-based filters ────────────────────────────────────────────
        $filters = $this->buildFilters($user, $role);

        // Merge search filters (role filters are not overwritten)
        if ($searchReceiptId)   $filters['receipt_id']   = $searchReceiptId;
        if ($searchClientPhone) $filters['client_phone'] = $searchClientPhone;

        $transactions = $this->transactions->findFiltered($filters, $page, $perPage);
        $total        = $this->transactions->countFiltered($filters);
        $totalPages   = (int) ceil($total / $perPage);

        $this->renderView('index', [
            'pageTitle'    => 'المعاملات المالية',
            'breadcrumb'   => 'لوحة التحكم · المعاملات',
            'transactions' => $transactions,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
        ]);
    }

    // ── Build filters based on role ───────────────────────────────────────

    private function buildFilters(array $user, string $role): array {
        switch ($role) {

            case 'customer_service':
                return ['created_by' => $user['id']];

            case 'branch_manager':
                return ['branch_id' => $user['branch_id']];

            case 'area_manager':
                $branchIds = $this->receipts->getBranchIdsByArea($user['id']);
                return ['branch_ids' => $branchIds];

            case 'admin':
            default:
                return [];
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE  —  GET /transaction/create
    // ════════════════════════════════════════════════════════════════════════

    public function create(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $receiptId = (int) ($_GET['receipt_id'] ?? 0);
        $receipt   = $receiptId ? $this->receipts->findById($receiptId) : null;

        $this->renderView('create', [
            'pageTitle'   => 'معاملة جديدة',
            'breadcrumb'  => 'لوحة التحكم · المعاملات · جديدة',
            'transaction' => ['receipt_id' => $receiptId],
            'receipt'     => $receipt,
            'errors'      => [],
            'isEdit'      => false,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE  —  POST /transaction/create
    // ════════════════════════════════════════════════════════════════════════

    public function store(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $data   = $this->parseForm();
        $errors = $this->validate($data);

        if ($errors) {
            $receiptId = $data['receipt_id'];
            $receipt   = $receiptId ? $this->receipts->findById($receiptId) : null;

            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('create', [
                'pageTitle'   => 'معاملة جديدة',
                'breadcrumb'  => 'لوحة التحكم · المعاملات · جديدة',
                'transaction' => $data,
                'receipt'     => $receipt,
                'errors'      => $errors,
                'isEdit'      => false,
            ]);
            return;
        }

        $newId = $this->transactions->create($data);

        if (!empty($data['receipt_id'])) {
            $this->auditLog->log(
                $data['receipt_id'],
                auth_user()['id'],
                auth_user()['role'],
                'transaction_added',
                null,
                "id:{$newId}, amount:{$data['amount']}, type:{$data['type']}"
            );
        }

        log_action('created_transaction', "id: {$newId}, amount: {$data['amount']}", auth_user()['id']);
        $this->flash('flash_success', 'تمت إضافة المعاملة بنجاح.');

        if (!empty($data['receipt_id'])) {
            $this->redirect('/receipt/show?id=' . $data['receipt_id']);
        } else {
            $this->redirect('/transactions');
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // SHOW  —  GET /transaction/show?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function show(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id          = (int) ($_GET['id'] ?? 0);
        $transaction = $this->transactions->findById($id);

        if (!$transaction) {
            $this->flash('flash_error', 'المعاملة غير موجودة.');
            $this->redirect('/transactions');
            return;
        }

        $receipt = $transaction['receipt_id']
            ? $this->receipts->findById($transaction['receipt_id'])
            : null;

        $this->renderView('show', [
            'pageTitle'   => 'عرض المعاملة #' . $id,
            'breadcrumb'  => 'لوحة التحكم · المعاملات · عرض',
            'transaction' => $transaction,
            'receipt'     => $receipt,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EDIT  —  GET /transaction/edit?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function edit(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id          = (int) ($_GET['id'] ?? 0);
        $transaction = $this->transactions->findById($id);

        if (!$transaction) {
            $this->flash('flash_error', 'المعاملة غير موجودة.');
            $this->redirect('/transactions');
            return;
        }

        $receipt = $transaction['receipt_id']
            ? $this->receipts->findById($transaction['receipt_id'])
            : null;

        $this->renderView('edit', [
            'pageTitle'   => 'تعديل المعاملة',
            'breadcrumb'  => 'لوحة التحكم · المعاملات · تعديل',
            'transaction' => $transaction,
            'receipt'     => $receipt,
            'errors'      => [],
            'isEdit'      => true,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // UPDATE  —  POST /transaction/edit?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function update(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id          = (int) ($_GET['id'] ?? 0);
        $transaction = $this->transactions->findById($id);

        if (!$transaction) {
            $this->flash('flash_error', 'المعاملة غير موجودة.');
            $this->redirect('/transactions');
            return;
        }

        $data   = $this->parseForm();
        $errors = $this->validate($data);

        if ($errors) {
            $receipt = $data['receipt_id'] ? $this->receipts->findById($data['receipt_id']) : null;
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('edit', [
                'pageTitle'   => 'تعديل المعاملة',
                'breadcrumb'  => 'لوحة التحكم · المعاملات · تعديل',
                'transaction' => array_merge($transaction, $data),
                'receipt'     => $receipt,
                'errors'      => $errors,
                'isEdit'      => true,
            ]);
            return;
        }

        $this->transactions->update($id, $data);
        log_action('updated_transaction', "id: {$id}", auth_user()['id']);
        $this->flash('flash_success', 'تم تحديث المعاملة بنجاح.');
        $this->redirect('/transactions');
    }

    // ════════════════════════════════════════════════════════════════════════
    // DESTROY  —  POST /transaction/delete?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function destroy(): void {
        auth_require(['admin', 'branch_manager', 'area_manager', 'customer_service']);

        $id          = (int) ($_GET['id'] ?? 0);
        $transaction = $this->transactions->findById($id);

        if (!$transaction) {
            $this->flash('flash_error', 'المعاملة غير موجودة.');
            $this->redirect('/transactions');
            return;
        }

        $receiptId = $transaction['receipt_id'];
        $this->transactions->delete($id);
        log_action('deleted_transaction', "id: {$id}", auth_user()['id']);
        $this->flash('flash_success', 'تم حذف المعاملة بنجاح.');

        if ($receiptId) {
            $this->redirect('/receipt/show?id=' . $receiptId);
        } else {
            $this->redirect('/transactions');
        }
    }
}
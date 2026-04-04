<?php
// app/controllers/CaptainController.php

class CaptainController {

    private CaptainModel $captains;

    public function __construct() {
        $this->captains = new CaptainModel();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function redirect(string $path): void {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    private function renderView(string $view, array $data = []): void {
        extract($data);
        require ROOT . "/views/admin/captains/{$view}.php";
    }

    private function flash(string $key, string $msg): void {
        $_SESSION[$key] = $msg;
    }

    private function parseForm(): array {
        return [
            'captain_name' => trim($_POST['captain_name'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'visible'      => ($_POST['visible'] ?? '1') === '1' ? 1 : 0,
        ];
    }

    private function validate(array $data): array {
        $errors = [];

        if (strlen($data['captain_name']) < 2)
            $errors[] = 'اسم الكابتن يجب أن يكون حرفين على الأقل.';

        if (!empty($data['phone_number']) && !preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $data['phone_number']))
            $errors[] = 'رقم الهاتف غير صحيح.';

        return $errors;
    }

    // ════════════════════════════════════════════════════════════════════════
    // INDEX  —  GET /admin/captains
    // ════════════════════════════════════════════════════════════════════════

    public function index(): void {
        auth_require(['admin']);

        $filters = [
            'visible' => $_GET['visibility'] ?? '',
            'search'  => trim($_GET['search'] ?? ''),
        ];

        $captains = $this->captains->findAll($filters);

        $this->renderView('index', [
            'pageTitle'  => 'الكباتن',
            'breadcrumb' => 'الإدارة · الكباتن',
            'captains'   => $captains,
            'visibility' => $filters['visible'],
            'search'     => $filters['search'],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // CREATE  —  GET /admin/captains/create
    // ════════════════════════════════════════════════════════════════════════

    public function create(): void {
        auth_require(['admin']);

        $this->renderView('create', [
            'pageTitle'  => 'كابتن جديد',
            'breadcrumb' => 'الإدارة · الكباتن · كابتن جديد',
            'captain'    => [],
            'errors'     => [],
            'isEdit'     => false,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // STORE  —  POST /admin/captains/create
    // ════════════════════════════════════════════════════════════════════════

    public function store(): void {
        auth_require(['admin']);

        $data   = $this->parseForm();
        $errors = $this->validate($data);

        if (!$errors && $this->captains->nameExists($data['captain_name'])) {
            $errors[] = 'يوجد كابتن بهذا الاسم مسبقاً.';
        }

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('create', [
                'pageTitle'  => 'كابتن جديد',
                'breadcrumb' => 'الإدارة · الكباتن · كابتن جديد',
                'captain'    => $data,
                'errors'     => $errors,
                'isEdit'     => false,
            ]);
            return;
        }

        $newId = $this->captains->create($data);

        log_action('created_captain', "id: {$newId}, name: {$data['captain_name']}", auth_user()['id']);
        $this->flash('flash_success', 'تم إضافة الكابتن "' . htmlspecialchars($data['captain_name']) . '" بنجاح.');
        $this->redirect('/admin/captains');
    }

    // ════════════════════════════════════════════════════════════════════════
    // SHOW  —  GET /admin/captains/show?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function show(): void {
        auth_require(['admin']);

        $id      = (int) ($_GET['id'] ?? 0);
        $captain = $this->captains->findById($id);

        if (!$captain) {
            $this->flash('flash_error', 'الكابتن غير موجود.');
            $this->redirect('/admin/captains');
            return;
        }

        $this->renderView('show', [
            'pageTitle'  => htmlspecialchars($captain['captain_name']),
            'breadcrumb' => 'الإدارة · الكباتن · ' . htmlspecialchars($captain['captain_name']),
            'captain'    => $captain,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EDIT  —  GET /admin/captains/edit?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function edit(): void {
        auth_require(['admin']);

        $id      = (int) ($_GET['id'] ?? 0);
        $captain = $this->captains->findById($id);

        if (!$captain) {
            $this->flash('flash_error', 'الكابتن غير موجود.');
            $this->redirect('/admin/captains');
            return;
        }

        $this->renderView('edit', [
            'pageTitle'  => 'تعديل الكابتن',
            'breadcrumb' => 'الإدارة · الكباتن · تعديل',
            'captain'    => $captain,
            'errors'     => [],
            'isEdit'     => true,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // UPDATE  —  POST /admin/captains/edit?id=x
    // ════════════════════════════════════════════════════════════════════════

    public function update(): void {
        auth_require(['admin']);

        $id      = (int) ($_GET['id'] ?? 0);
        $captain = $this->captains->findById($id);

        if (!$captain) {
            $this->flash('flash_error', 'الكابتن غير موجود.');
            $this->redirect('/admin/captains');
            return;
        }

        $data   = $this->parseForm();
        $errors = $this->validate($data);

        if (!$errors && $this->captains->nameExists($data['captain_name'], $id)) {
            $errors[] = 'يوجد كابتن بهذا الاسم مسبقاً.';
        }

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->renderView('edit', [
                'pageTitle'  => 'تعديل الكابتن',
                'breadcrumb' => 'الإدارة · الكباتن · تعديل',
                'captain'    => array_merge($captain, $data),
                'errors'     => $errors,
                'isEdit'     => true,
            ]);
            return;
        }

        $this->captains->update($id, $data);

        log_action('updated_captain', "id: {$id}, name: {$data['captain_name']}", auth_user()['id']);
        $this->flash('flash_success', 'تم تحديث بيانات الكابتن "' . htmlspecialchars($data['captain_name']) . '" بنجاح.');
        $this->redirect('/admin/captains');
    }

    // ════════════════════════════════════════════════════════════════════════
    // DESTROY  —  POST /admin/captains/delete?id=x
    // Soft-delete only — sets visible = 0
    // ════════════════════════════════════════════════════════════════════════

    public function destroy(): void {
        auth_require(['admin']);

        $id      = (int) ($_GET['id'] ?? 0);
        $captain = $this->captains->findById($id);

        if (!$captain) {
            $this->flash('flash_error', 'الكابتن غير موجود.');
            $this->redirect('/admin/captains');
            return;
        }

        $this->captains->hide($id);
        log_action('hidden_captain', "id: {$id}, name: {$captain['captain_name']}", auth_user()['id']);

        $this->flash('flash_success', 'تم إخفاء الكابتن "' . htmlspecialchars($captain['captain_name']) . '".');
        $this->redirect('/admin/captains');
    }
}
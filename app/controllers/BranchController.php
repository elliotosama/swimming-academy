<?php

require_once __DIR__ . '/../models/BranchModel.php';

class BranchController
{
    private BranchModel $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new BranchModel($pdo);
    }

    // GET /branches/index
    public function index(): void
    {
        $country    = $_GET['country']    ?? '';
        $visibility = $_GET['visibility'] ?? '';

        $branches  = $this->model->getAll($country, $visibility);
        $countries = $this->model->getCountries();

        $this->render('index', compact('branches', 'countries', 'country', 'visibility'));
    }

    // GET /branches/create  |  POST /branches/create
    public function create(): void
    {
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name      = trim($_POST['branch_name'] ?? '');
            $country   = trim($_POST['country']     ?? '');
            $days      = $_POST['days']             ?? [];
            $isVisible = isset($_POST['is_visible']) ? 1 : 0;

            if ($name === '' || $country === '') {
                $message = 'يرجى ملء جميع الحقول المطلوبة.';
            } elseif (count($days) < 1) {
                $message = 'يرجى اختيار يوم دراسي واحد على الأقل.';
            } elseif ($this->model->existsByName($name)) {
                $message = 'هذا الفرع موجود بالفعل.';
            } else {
                [$wd1, $wd2, $wd3] = BranchModel::chunkDays($days);
                $this->model->create($name, $country, $wd1, $wd2, $wd3, $isVisible);
                header('Location: index.php');
                exit;
            }
        }

        $this->render('create', compact('message'));
    }

    // GET /branches/edit?id=  |  POST /branches/edit?id=
    public function edit(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $branch = $this->model->findById($id);

        if (!$branch) {
            die('الفرع غير موجود');
        }

        $message = '';
        $wd1Days = !empty($branch['working_days1']) ? explode(',', $branch['working_days1']) : [];
        $wd2Days = !empty($branch['working_days2']) ? explode(',', $branch['working_days2']) : [];
        $wd3Days = !empty($branch['working_days3']) ? explode(',', $branch['working_days3']) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name      = trim($_POST['branch_name'] ?? '');
            $country   = trim($_POST['country']     ?? '');
            $isVisible = isset($_POST['is_visible']) ? 1 : 0;

            $g1 = array_slice($_POST['working_days1'] ?? [], 0, 2);
            $g2 = array_slice($_POST['working_days2'] ?? [], 0, 2);
            $g3 = array_slice($_POST['working_days3'] ?? [], 0, 2);

            if (count($g1) + count($g2) + count($g3) < 1) {
                $message = 'يرجى اختيار يوم دراسي واحد على الأقل.';
                $wd1Days = $g1;
                $wd2Days = $g2;
                $wd3Days = $g3;
            } else {
                $wd1 = $g1 ? implode(',', $g1) : null;
                $wd2 = $g2 ? implode(',', $g2) : null;
                $wd3 = $g3 ? implode(',', $g3) : null;
                $this->model->update($id, $name, $country, $wd1, $wd2, $wd3, $isVisible);
                header('Location: index.php');
                exit;
            }
        }

        $this->render('edit', compact('branch', 'message', 'wd1Days', 'wd2Days', 'wd3Days'));
    }

    // GET /branches/show?id=
    public function show(): void
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $branch = $this->model->findById($id);

        if (!$branch) {
            die('الفرع غير موجود');
        }

        $this->render('show', compact('branch'));
    }

    // GET /branches/delete?id=
    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->model->delete($id);
        }
        header('Location: index.php');
        exit;
    }

    // GET /branches/receipts?id=
    public function receipts(): void
    {
        $branchId = (int) ($_GET['id'] ?? 0);
        if (!$branchId) {
            die('معرّف الفرع مطلوب');
        }

        $stats        = $this->model->getStats($branchId);
        $receipts     = $this->model->getReceipts($branchId);
        $transactions = $this->model->getTransactions($branchId);

        $this->render('receipts', compact('branchId', 'stats', 'receipts', 'transactions'));
    }

    // GET /branches/captains?id=
    public function captains(): void
    {
        $branchId = (int) ($_GET['id'] ?? 0);
        if (!$branchId) {
            die('معرّف الفرع مطلوب');
        }

        $captains = $this->model->getCaptains($branchId);
        $this->render('captains', compact('branchId', 'captains'));
    }

    // GET /branches/clients?id=
    public function clients(): void
    {
        $branchId = (int) ($_GET['id'] ?? 0);
        if (!$branchId) {
            die('معرّف الفرع مطلوب');
        }

        $clients = $this->model->getClients($branchId);
        $this->render('clients', compact('branchId', 'clients'));
    }

    // GET /branches/users?id=
    public function users(): void
    {
        $branchId = (int) ($_GET['id'] ?? 0);
        if (!$branchId) {
            die('معرّف الفرع مطلوب');
        }

        $users = $this->model->getUsers($branchId);
        $this->render('users', compact('branchId', 'users'));
    }

    // ─── Private helpers ───────────────────────────────────────────

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../views/branches/{$view}.php";
    }
}
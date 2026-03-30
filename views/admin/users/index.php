<?php // views/admin/users/index.php
require ROOT . '/views/includes/layout_top.php';

$roleLabels = [
    'admin'            => ['label' => 'مدير النظام',     'color' => 'role-admin'],
    'branch_manager'   => ['label' => 'مدير فرع',        'color' => 'role-manager'],
    'area_manager'     => ['label' => 'مدير منطقة',      'color' => 'role-area'],
    'customer_service' => ['label' => 'خدمة العملاء',    'color' => 'role-cs'],
];
?>
<style>
    .role-admin   { background:#7c3aed20; color:#a78bfa; border:1px solid #7c3aed40; }
    .role-manager { background:#00b4d820; color:var(--accent); border:1px solid #00b4d840; }
    .role-area    { background:#f4a62320; color:var(--gold); border:1px solid #f4a62340; }
    .role-cs      { background:#34c78920; color:var(--success); border:1px solid #34c78940; }
    .avatar {
        width:34px; height:34px; border-radius:10px;
        background: linear-gradient(135deg, var(--accent2), var(--accent));
        display:inline-flex; align-items:center; justify-content:center;
        font-weight:900; font-size:.85rem; color:#fff; flex-shrink:0;
    }
    .user-cell { display:flex; align-items:center; gap:.75rem; }
    .user-info { display:flex; flex-direction:column; }
    .user-info strong { font-size:.9rem; }
    .user-info span   { font-size:.78rem; color:var(--muted); }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">👥 المستخدمون</h1>
        <p class="breadcrumb">لوحة التحكم · المستخدمون</p>
    </div>
    <a href="<?= APP_URL ?>/admin/user/create" class="btn btn-primary">+ إضافة مستخدم</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card">
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-icon">👤</div>
            <p>لا يوجد مستخدمون مسجّلون بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>الدور</th>
                        <th>الهاتف</th>
                        <th>الحالة</th>
                        <th>آخر دخول</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $initials = mb_strtoupper(mb_substr($u['username'], 0, 1));
                            $role = $roleLabels[$u['role']] ?? ['label' => $u['role'], 'color' => 'badge'];
                        ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $u['id'] ?></td>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                                    <div class="user-info">
                                        <strong><?= htmlspecialchars($u['username']) ?></strong>
                                        <span><?= htmlspecialchars($u['email'] ?? '—') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $role['color'] ?>"><?= $role['label'] ?></span>
                            </td>
                            <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                            <td>
                                <?php if ($u['is_active'] && $u['visible']): ?>
                                    <span class="badge badge-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">معطّل</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);font-size:.82rem">
                                <?= $u['last_login'] ? htmlspecialchars($u['last_login']) : '—' ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/admin/user/show?id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/admin/user/edit?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <?php if ($u['id'] !== (int)(auth_user()['id'] ?? 0)): ?>
                                        <form method="POST" action="<?= APP_URL ?>/admin/user/delete?id=<?= $u['id'] ?>"
                                              style="display:inline"
                                              onsubmit="return confirm('هل أنت متأكد من تعطيل هذا المستخدم؟')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">تعطيل</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
<?php // views/admin/users/index.php
require ROOT . '/views/includes/layout_top.php';

$roleLabels = [
    'admin'           => ['label' => 'مدير النظام',   'color' => 'role-admin'],
    'area_manager'    => ['label' => 'مدير منطقة',    'color' => 'role-area'],
    'customer_service'=> ['label' => 'خدمة العملاء',  'color' => 'role-cs'],
    'branch_manager'    => ['label' => 'موظف استقبال',  'color' => 'role-manager'],
];
?>
<style>
    .role-admin        { background:#7c3aed20; color:#a78bfa;        border:1px solid #7c3aed40; }
    .role-manager      { background:#00b4d820; color:var(--accent);   border:1px solid #00b4d840; }
    .role-area         { background:#f4a62320; color:var(--gold);     border:1px solid #f4a62340; }
    .role-cs           { background:#34c78920; color:var(--success);  border:1px solid #34c78940; }
    .role-instructor   { background:#e05c5c20; color:var(--error);    border:1px solid #e05c5c40; }
    .role-receptionist { background:#0077b620; color:#90e0ef;         border:1px solid #0077b640; }
    .role-student      { background:#ffffff10; color:var(--muted);    border:1px solid #ffffff20; }

    .avatar {
        width:36px; height:36px; border-radius:10px;
        background: linear-gradient(135deg, var(--accent2), var(--accent));
        display:inline-flex; align-items:center; justify-content:center;
        font-weight:900; font-size:.88rem; color:#fff; flex-shrink:0;
    }
    .user-cell  { display:flex; align-items:center; gap:.75rem; }
    .user-info  { display:flex; flex-direction:column; }
    .user-info strong { font-size:.9rem; }
    .user-info span   { font-size:.76rem; color:var(--muted); }

    /* stats strip */
    .stats-strip {
        display:flex; gap:1rem; margin-bottom:1.4rem; flex-wrap:wrap;
    }
    .stat-card {
        z-index:1;
        flex:1; min-width:130px;
        background:linear-gradient(145deg,#111d2b,#0d1821);
        border:1px solid var(--border); border-radius:16px;
        padding:1rem 1.2rem;
        display:flex; flex-direction:column; gap:.25rem;
        box-shadow:0 0 0 1px #00b4d808;
    }
    .stat-card__value { font-size:1.6rem; font-weight:900; }
    .stat-card__label { font-size:.76rem; color:var(--muted); font-weight:600; }
    .stat-card--accent  .stat-card__value { color:var(--accent); }
    .stat-card--success .stat-card__value { color:var(--success); }
    .stat-card--muted   .stat-card__value { color:var(--muted); }
</style>

<!-- Page header -->
<div class="page-header">
    <div>
        <h1 class="page-title">👥 الموظفين</h1>
        <p class="breadcrumb">لوحة التحكم · الموظفين</p>
    </div>
    <a href="<?= APP_URL ?>/admin/user/create" class="btn btn-primary">+ إضافة مستخدم</a>
</div>

<!-- Flash messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Stats strip -->
<div class="stats-strip">
    <div class="stat-card stat-card--accent">
        <span class="stat-card__value"><?= $totalUsers ?></span>
        <span class="stat-card__label">إجمالي المستخدمين</span>
    </div>
    <div class="stat-card stat-card--success">
        <span class="stat-card__value"><?= $activeUsers ?></span>
        <span class="stat-card__label">نشطون</span>
    </div>
    <div class="stat-card stat-card--muted">
        <span class="stat-card__value"><?= $totalUsers - $activeUsers ?></span>
        <span class="stat-card__label">معطّلون</span>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="<?= APP_URL ?>/admin/users">
    <div class="filter-bar">
        <div class="form-group">
            <label class="form-label">🔍 البحث</label>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="الاسم أو البريد أو الهاتف..."
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">الدور</label>
            <div class="form-select-wrap">
                <select name="role" class="form-control">
                    <option value="">جميع الأدوار</option>
                    <?php foreach ($roleLabels as $key => $r): ?>
                        <option value="<?= $key ?>"
                            <?= ($filters['role'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= $r['label'] ?>
                            <?php if (!empty($roleCounts[$key])): ?>
                                (<?= $roleCounts[$key] ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">الحالة</label>
            <div class="form-select-wrap">
                <select name="visible" class="form-control">
                    <option value="">الكل</option>
                    <option value="1" <?= ($filters['visible'] ?? '') === '1' ? 'selected' : '' ?>>نشط ✅</option>
                    <option value="0" <?= ($filters['visible'] ?? '') === '0' ? 'selected' : '' ?>>معطّل ❌</option>
                </select>
            </div>
        </div>

        <div class="filter-bar__actions">
            <button type="submit" class="btn btn-primary">تطبيق</button>
            <a href="<?= APP_URL ?>/admin/users" class="btn btn-secondary">مسح</a>
        </div>
    </div>
</form>

<!-- Table -->
<div class="card">
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-icon">👤</div>
            <p>لا يوجد مستخدمون يطابقون البحث.</p>
            <?php if (!empty($filters['search']) || !empty($filters['role']) || ($filters['is_active'] ?? '') !== ''): ?>
                <a href="<?= APP_URL ?>/admin/users" class="btn btn-secondary" style="margin-top:1rem">إعادة ضبط الفلاتر</a>
            <?php endif; ?>
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
                            $initials = mb_strtoupper(mb_substr($u['username'] ?? '?', 0, 1));
                            $role     = $roleLabels[$u['role']] ?? ['label' => $u['role'], 'color' => 'badge'];
                            $isActive = !empty($u['is_active']) && !empty($u['visible']);
                        ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $u['id'] ?></td>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                                    <div class="user-info">
                                        <strong><?= htmlspecialchars($u['username'] ?? '—') ?></strong>
                                        <span><?= htmlspecialchars($u['email'] ?? '—') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $role['color'] ?>"><?= $role['label'] ?></span>
                            </td>
                            <td style="color:var(--muted);font-size:.85rem">
                                <?= htmlspecialchars($u['phone'] ?? '—') ?>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge badge-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">معطّل</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);font-size:.82rem">
                                <?= !empty($u['last_login']) ? htmlspecialchars($u['last_login']) : '—' ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/admin/user/show?id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/admin/user/edit?id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-warning">تعديل</a>
                                    <?php if ($u['id'] !== (int)($_SESSION['user']['id'] ?? 0)): ?>
                                        <form method="POST"
                                              action="<?= APP_URL ?>/admin/user/delete?id=<?= $u['id'] ?>"
                                              style="display:inline"
                                              onsubmit="return confirm('هل أنت متأكد من تعطيل هذا المستخدم؟')">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
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
        <div style="padding:.75rem 1.2rem;font-size:.8rem;color:var(--muted);border-top:1px solid var(--border)">
            عرض <?= count($users) ?> مستخدم
        </div>
    <?php endif; ?>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
<?php // views/admin/branches/index.php
require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🏢 الفروع</h1>
        <p class="breadcrumb">لوحة التحكم · الفروع</p>
    </div>
    <a href="<?= APP_URL ?>/admin/branch/create" class="btn btn-primary">
        + إضافة فرع جديد
    </a>
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
    <?php if (empty($branches)): ?>
        <div class="empty-state">
            <div class="empty-icon">🏢</div>
            <p>لا توجد فروع مسجّلة بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الفرع</th>
                        <th>الدولة</th>
                        <th>الوردية 1</th>
                        <th>الوردية 2</th>
                        <th>الوردية 3</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $b): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $b['id'] ?></td>
                            <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                            <td><?= htmlspecialchars($b['country'] ?? '—') ?></td>
                            <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($b['working_days1'] ?? '—') ?></td>
                            <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($b['working_days2'] ?? '—') ?></td>
                            <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($b['working_days3'] ?? '—') ?></td>
                            <td>
                                <?php if ($b['visible']): ?>
                                    <span class="badge badge-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">معطّل</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($b['created_at'] ?? '—') ?></td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/admin/branch/show?id=<?= $b['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/admin/branch/edit?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <form method="POST" action="<?= APP_URL ?>/admin/branch/delete?id=<?= $b['id'] ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('هل أنت متأكد من تعطيل هذا الفرع؟')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">تعطيل</button>
                                    </form>
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
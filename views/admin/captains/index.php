<?php // views/admin/captains/index.php
require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🧑‍✈️ الكباتن</h1>
        <p class="breadcrumb">لوحة التحكم · الكباتن</p>
    </div>
<a href="<?= APP_URL ?>/admin/captains/create" class="btn btn-primary">
        + إضافة كابتن جديد
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
    <?php if (empty($captains)): ?>
        <div class="empty-state">
            <div class="empty-icon">🧑‍✈️</div>
            <p>لا يوجد كباتن مسجّلون بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الكابتن</th>
                        <th>رقم الهاتف</th>
                        <th>الحالة</th>
                        <th>الفروع</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($captains as $c): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $c['id'] ?></td>
                            <td><strong><?= htmlspecialchars($c['captain_name']) ?></strong></td>
                            <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($c['phone_number'] ?? '—') ?></td>
                            <td>
                                <?php if ($c['visible']): ?>
                                    <span class="badge badge-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">معطّل</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem;color:var(--muted)">
    <?= $c['branch_names'] ? htmlspecialchars($c['branch_names']) : '—' ?>
</td>
                            <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($c['created_at'] ?? '—') ?></td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/admin/captains/show?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/admin/captains/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <form method="POST" action="<?= APP_URL ?>/admin/captains/delete?id=<?= $c['id'] ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('هل أنت متأكد من تعطيل هذا الكابتن؟')">
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
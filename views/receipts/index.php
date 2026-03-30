<?php // views/admin/receipts/index.php
require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🧾 الإيصالات</h1>
        <p class="breadcrumb">لوحة التحكم · الإيصالات</p>
    </div>
    <a href="<?= APP_URL ?>/receipt/create" class="btn btn-primary">
        + إضافة إيصال جديد
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
    <?php if (empty($receipts)): ?>
        <div class="empty-state">
            <div class="empty-icon">🧾</div>
            <p>لا توجد إيصالات مسجّلة بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>الفرع</th>
                        <th>الكابتن</th>
                        <th>الخطة</th>
                        <th>أول جلسة</th>
                        <th>آخر جلسة</th>
                        <th>نوع التجديد</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipts as $r): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $r['id'] ?></td>
                            <td><strong><?= htmlspecialchars($r['client_name'] ?? '—') ?></strong></td>
                            <td><?= htmlspecialchars($r['branch_name'] ?? '—') ?></td>
                            <td style="font-size:.85rem"><?= htmlspecialchars($r['captain_name'] ?? '—') ?></td>
                            <td style="font-size:.85rem"><?= htmlspecialchars($r['plan_name'] ?? '—') ?></td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($r['first_session'] ?? '—') ?></td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($r['last_session'] ?? '—') ?></td>
                            <td style="font-size:.82rem"><?= htmlspecialchars($r['renewal_type'] ?? '—') ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'completed'     => ['badge-success', 'مكتمل'],
                                    'not_completed' => ['badge-danger',  'غير مكتمل'],
                                    'pending'       => ['badge-warning', 'معلّق'],
                                ];
                                [$cls, $label] = $statusMap[$r['receipt_status']] ?? ['badge-secondary', $r['receipt_status']];
                                ?>
                                <span class="badge <?= $cls ?>"><?= $label ?></span>
                            </td>
                            <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($r['created_at'] ?? '—') ?></td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/receipt/show?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/receipt/edit?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <form method="POST" action="<?= APP_URL ?>/receipt/delete?id=<?= $r['id'] ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الإيصال؟')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">حذف</button>
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
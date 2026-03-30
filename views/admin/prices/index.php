<?php // views/admin/prices/index.php
require ROOT . '/views/includes/layout_top.php';
?>
<style>
    .price-amount {
        font-weight: 700;
        font-size: .95rem;
        color: var(--gold);
        letter-spacing: .02em;
    }
    .sessions-badge {
        background: #00b4d820;
        color: var(--accent);
        border: 1px solid #00b4d840;
        border-radius: 6px;
        padding: 2px 10px;
        font-size: .8rem;
        font-weight: 600;
    }
    .desc-cell {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .desc-icon {
        width: 34px; height: 34px; border-radius: 10px;
        background: linear-gradient(135deg, var(--gold), var(--accent));
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .desc-info { display: flex; flex-direction: column; }
    .desc-info strong { font-size: .9rem; }
    .desc-info span   { font-size: .78rem; color: var(--muted); }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">💰 الأسعار</h1>
        <p class="breadcrumb">لوحة التحكم · الأسعار</p>
    </div>
    <a href="<?= APP_URL ?>/admin/price/create" class="btn btn-primary">+ إضافة سعر</a>
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
    <?php if (empty($prices)): ?>
        <div class="empty-state">
            <div class="empty-icon">💰</div>
            <p>لا توجد أسعار مسجّلة بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الوصف</th>
                        <th>السعر</th>
                        <th>الدولة</th>
                        <th>عدد الجلسات</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prices as $p): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $p['id'] ?></td>
                            <td>
                                <div class="desc-cell">
                                    <div class="desc-icon">🏷️</div>
                                    <div class="desc-info">
                                        <strong><?= htmlspecialchars($p['description'] ?? '—') ?></strong>
                                        <span>آخر تحديث: <?= $p['updated_at'] ? htmlspecialchars($p['updated_at']) : '—' ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="price-amount">
                                    <?= $p['price'] !== null ? number_format((float)$p['price'], 2) : '—' ?>
                                </span>
                            </td>
                            <td style="color:var(--muted);font-size:.85rem">
                                <?= htmlspecialchars($p['country'] ?? '—') ?>
                            </td>
                            <td>
                                <?php if ($p['number_of_sessions']): ?>
                                    <span class="sessions-badge"><?= (int)$p['number_of_sessions'] ?> جلسات</span>
                                <?php else: ?>
                                    <span style="color:var(--muted)">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['visible']): ?>
                                    <span class="badge badge-success">نشط</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">معطّل</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);font-size:.82rem">
                                <?= $p['created_at'] ? htmlspecialchars($p['created_at']) : '—' ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/admin/price/show?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/admin/price/edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <form method="POST" action="<?= APP_URL ?>/admin/price/delete?id=<?= $p['id'] ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('هل أنت متأكد من تعطيل هذا السعر؟')">
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
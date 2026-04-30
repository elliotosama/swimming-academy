<?php // views/clients/index.php
require ROOT . '/views/includes/layout_top.php';

function clientPaginationUrl(int $p): string {
    $params = array_filter([
        'page'   => $p,
        'search' => $_GET['search'] ?? '',
        'gender' => $_GET['gender'] ?? '',
    ], fn($v) => $v !== '' && $v !== null);
    return '?' . http_build_query($params);
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">👤 العملاء</h1>
        <p class="breadcrumb">لوحة التحكم · العملاء</p>
    </div>
    <a href="<?= APP_URL ?>/client/create" class="btn btn-primary">+ إضافة عميل</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- ══ Filter Bar ══════════════════════════════════════════════════════════ -->
<form method="GET" action="">
    <div class="filter-bar">

        <div class="form-group">
            <label class="form-label">🔍 البحث</label>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="الاسم أو الهاتف أو البريد..."
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">الجنس</label>
            <div class="form-select-wrap">
                <select name="gender" class="form-control">
                    <option value="">الكل</option>
                    <option value="male"   <?= ($filters['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>ذكر</option>
                    <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>أنثى</option>
                </select>
            </div>
        </div>

        <div class="filter-bar__actions">
            <button type="submit" class="btn btn-primary">تطبيق</button>
            <a href="<?= APP_URL ?>/clients" class="btn btn-secondary">مسح</a>
        </div>

    </div>
</form>
<!-- ══════════════════════════════════════════════════════════════════════════ -->

<div class="card">
    <?php if (empty($clients)): ?>
        <div class="empty-state">
            <div class="empty-icon">👤</div>
            <p>لا يوجد عملاء يطابقون البحث.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>الهاتف</th>
                        <th>البريد</th>
                        <th>العمر</th>
                        <th>الجنس</th>
                        <th>المنشئ</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $c['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($c['client_name']) ?></strong>
                            </td>
                            <td style="font-size:.85rem">
                                <a href="<?= APP_URL ?>/clients?search=<?= urlencode($c['phone']) ?>"
                                   style="color:var(--accent);text-decoration:none">
                                    <?= htmlspecialchars($c['phone']) ?>
                                </a>
                            </td>
                            <td style="font-size:.82rem;color:var(--muted)">
                                <?= htmlspecialchars($c['email'] ?? '—') ?>
                            </td>
                            <td style="font-size:.85rem;color:var(--muted)">
                                <?= $c['age'] ? $c['age'] . ' سنة' : '—' ?>
                            </td>
                            <td>
                                <?php if ($c['gender'] === 'male'): ?>
                                    <span class="badge" style="background:#00b4d820;color:var(--accent);border:1px solid #00b4d840">ذكر</span>
                                <?php elseif ($c['gender'] === 'female'): ?>
                                    <span class="badge" style="background:#ec489920;color:#ec4899;border:1px solid #ec489940">أنثى</span>
                                <?php else: ?>
                                    <span style="color:var(--muted)">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem;color:var(--muted)">
                                <?= htmlspecialchars($c['creator_name'] ?? '—') ?>
                            </td>
                            <td style="font-size:.82rem;color:var(--muted)">
                                <?= htmlspecialchars($c['created_at'] ?? '—') ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/client/show?id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/client/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <form method="POST" action="<?= APP_URL ?>/client/delete?id=<?= $c['id'] ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟')">
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

<?php if ($totalPages > 1): ?>
    <div class="pagination-wrap">
        <span class="pagination-info">
            عرض صفحة <?= $page ?> من <?= $totalPages ?>
            &nbsp;·&nbsp; إجمالي <?= number_format($total) ?> عميل
        </span>
        <div class="pagination">

            <?php if ($page > 1): ?>
                <a href="<?= clientPaginationUrl($page - 1) ?>" class="btn btn-sm btn-secondary">« السابق</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            ?>

            <?php if ($start > 1): ?>
                <a href="<?= clientPaginationUrl(1) ?>" class="btn btn-sm btn-secondary">1</a>
                <?php if ($start > 2): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($p = $start; $p <= $end; $p++): ?>
                <a href="<?= clientPaginationUrl($p) ?>"
                   class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
                <a href="<?= clientPaginationUrl($totalPages) ?>" class="btn btn-sm btn-secondary"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= clientPaginationUrl($page + 1) ?>" class="btn btn-sm btn-secondary">التالي »</a>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
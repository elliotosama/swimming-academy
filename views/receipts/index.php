<?php // views/receipts/index.php
require ROOT . '/views/includes/layout_top.php';

// Helper: keep current filters in pagination links
function paginationUrl(int $p): string {
    $q         = $_GET;
    $q['page'] = $p;
    return APP_URL . '/receipts?' . http_build_query($q);
}
function exportUrl(): string {
    $q = $_GET;
    unset($q['page']);
    return APP_URL . '/receipt/export?' . http_build_query($q);
}

// Convenience: is a given filter control visible for this role?
// $allowedFilters is passed from the controller via renderView()
$canFilter = fn(string $key): bool => in_array($key, $allowedFilters ?? [], true);
?>

<style>
/* ── Filter panel ──────────────────────────────────────────── */
.filter-panel{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;z-index:1}
.filter-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem 1rem}
.filter-group{z-index:1;display:flex;flex-direction:column;gap:.3rem}
.filter-group label{z-index:1;font-size:.78rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.filter-group input,.filter-group select{padding:.42rem .65rem;border:1px solid var(--border);border-radius:6px;font-size:.88rem;background:var(--bg);color:var(--text);width:100%}
.filter-group select[multiple]{height:90px}
.filter-actions{display:flex;gap:.6rem;align-items:center;margin-top:.9rem;flex-wrap:wrap}
.badge-updated{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;font-size:.72rem;padding:.15rem .45rem;border-radius:999px;margin-right:.3rem}
/* ── Pagination ────────────────────────────────────────────── */
.pagination{display:flex;gap:.35rem;align-items:center;justify-content:center;padding:1rem 0}
.pagination a,.pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:2rem;height:2rem;padding:0 .55rem;border-radius:6px;font-size:.85rem;border:1px solid var(--border);text-decoration:none;color:var(--text)}
.pagination a:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
.pagination .active{background:var(--primary);color:#fff;border-color:var(--primary);font-weight:700}
.pagination .disabled{opacity:.4;pointer-events:none}
.pag-info{font-size:.82rem;color:var(--muted);text-align:center}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">🧾 الإيصالات</h1>
        <p class="breadcrumb">لوحة التحكم · الإيصالات</p>
    </div>
    <div style="display:flex;gap:.6rem">
        <a href="<?= exportUrl() ?>" class="btn btn-secondary">⬇️ تصدير Excel</a>
        <a href="<?= APP_URL ?>/receipt/create" class="btn btn-primary">+ إضافة إيصال جديد</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- ── Filter Panel ──────────────────────────────────────────────────── -->
<div class="filter-panel">
    <form method="GET" action="<?= APP_URL ?>/receipts" id="filterForm">
        <input type="hidden" name="page" value="1">

        <div class="filter-grid">

            <!-- Search (always visible) -->
            <?php if ($canFilter('search')): ?>
            <div class="filter-group" style="grid-column:span 2">
                <label>🔍 بحث (اسم / هاتف / رقم العميل)</label>
                <input type="text" name="search"
                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                       placeholder="ابحث...">
            </div>
            <?php endif; ?>

            <!-- First session range -->
            <?php if ($canFilter('first_session')): ?>
            <div class="filter-group">
                <label>أول جلسة — من</label>
                <input type="date" name="first_session_from"
                       value="<?= htmlspecialchars($filters['first_session_from'] ?? '') ?>">
            </div>
            <div class="filter-group">
                <label>أول جلسة — إلى</label>
                <input type="date" name="first_session_to"
                       value="<?= htmlspecialchars($filters['first_session_to'] ?? '') ?>">
            </div>
            <?php endif; ?>

            <!-- Last session range -->
            <?php if ($canFilter('last_session')): ?>
            <div class="filter-group">
                <label>آخر جلسة — من</label>
                <input type="date" name="last_session_from"
                       value="<?= htmlspecialchars($filters['last_session_from'] ?? '') ?>">
            </div>
            <div class="filter-group">
                <label>آخر جلسة — إلى</label>
                <input type="date" name="last_session_to"
                       value="<?= htmlspecialchars($filters['last_session_to'] ?? '') ?>">
            </div>
            <?php endif; ?>

            <!-- Created at range -->
            <?php if ($canFilter('created')): ?>
            <div class="filter-group">
                <label>تاريخ الإنشاء — من</label>
                <input type="date" name="created_from"
                       value="<?= htmlspecialchars($filters['created_from'] ?? '') ?>">
            </div>
            <div class="filter-group">
                <label>تاريخ الإنشاء — إلى</label>
                <input type="date" name="created_to"
                       value="<?= htmlspecialchars($filters['created_to'] ?? '') ?>">
            </div>
            <?php endif; ?>

            <!-- Status multi-select (always visible) -->
            <?php if ($canFilter('statuses')): ?>
            <div class="filter-group">
                <label>الحالة (يمكن اختيار أكثر من واحدة)</label>
                <select name="statuses[]" multiple>
                    <?php
                    $allStatuses = ['completed' => 'مكتمل', 'not_completed' => 'غير مكتمل', 'pending' => 'معلّق'];
                    $selStatuses = (array) ($filters['statuses'] ?? []);
                    foreach ($allStatuses as $val => $label):
                    ?>
                        <option value="<?= $val ?>"
                            <?= in_array($val, $selStatuses) ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Branch multi-select: only when role allows it -->
            <?php if ($canFilter('branch')): ?>
            <div class="filter-group">
                <label>الفرع (يمكن اختيار أكثر من فرع)</label>
                <select name="branch_ids[]" multiple>
                    <?php
                    $selBranches = array_map('intval', (array) ($filters['branch_ids'] ?? []));
                    foreach ($branches as $b):
                    ?>
                        <option value="<?= $b['id'] ?>"
                            <?= in_array((int)$b['id'], $selBranches) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Creator filter: only admin can use this -->
            <?php if ($canFilter('creator')): ?>
            <div class="filter-group">
                <label>المنشئ</label>
                <select name="creator_id">
                    <option value="">— الكل —</option>
                    <?php foreach ($creators as $u): ?>
                        <option value="<?= $u['id'] ?>"
                            <?= ((int)($filters['creator_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Has updates toggle -->
            <?php if ($canFilter('has_updates')): ?>
            <div class="filter-group" style="justify-content:flex-end">
                <label>فقط الإيصالات المُحدَّثة أو بها معاملات</label>
                <label style="display:flex;align-items:center;gap:.4rem;margin-top:.2rem;cursor:pointer">
                    <input type="checkbox" name="has_updates" value="1"
                           <?= !empty($filters['has_updates']) ? 'checked' : '' ?>
                           style="width:auto">
                    <span style="font-size:.88rem">تفعيل</span>
                </label>
            </div>
            <?php endif; ?>

        </div><!-- .filter-grid -->

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">بحث</button>
            <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">إعادة تعيين</a>
            <span style="margin-right:auto;font-size:.83rem;color:var(--muted)">
                <?= number_format($total) ?> نتيجة
            </span>
        </div>
    </form>
</div>

<!-- ── Table ─────────────────────────────────────────────────────────── -->
<div class="card">
    <?php if (empty($receipts)): ?>
        <div class="empty-state">
            <div class="empty-icon">🧾</div>
            <p>لا توجد إيصالات تطابق معايير البحث.</p>
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
                        <th>نشاط</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $statusMap = [
                        'completed'     => ['badge-success', 'مكتمل'],
                        'not_completed' => ['badge-danger',  'غير مكتمل'],
                        'pending'       => ['badge-warning', 'معلّق'],
                    ];
                    foreach ($receipts as $r):
                        [$cls, $statusLabel] = $statusMap[$r['receipt_status']] ?? ['badge-secondary', $r['receipt_status']];
                        $hasActivity = ($r['audit_count'] > 0 || $r['transaction_count'] > 0);
                    ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $r['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['client_name'] ?? '—') ?></strong>
                                <?php if (!empty($r['client_phone'])): ?>
                                    <br><small style="color:var(--muted)"><?= htmlspecialchars($r['client_phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($r['branch_name'] ?? '—') ?></td>
                            <td style="font-size:.85rem"><?= htmlspecialchars($r['captain_name'] ?? '—') ?></td>
                            <td style="font-size:.85rem"><?= htmlspecialchars($r['plan_name'] ?? '—') ?></td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($r['first_session'] ?? '—') ?></td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($r['last_session'] ?? '—') ?></td>
                            <td style="font-size:.82rem"><?= htmlspecialchars($r['renewal_type'] ?? '—') ?></td>
                            <td><span class="badge <?= $cls ?>"><?= $statusLabel ?></span></td>
                            <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($r['created_at'] ?? '—') ?></td>
                            <td>
                                <?php if ($r['audit_count'] > 0): ?>
                                    <span class="badge-updated" title="تعديلات">✏️ <?= $r['audit_count'] ?></span>
                                <?php endif; ?>
                                <?php if ($r['transaction_count'] > 0): ?>
                                    <span class="badge-updated" title="معاملات">💳 <?= $r['transaction_count'] ?></span>
                                <?php endif; ?>
                                <?php if (!$hasActivity): ?>
                                    <span style="color:var(--muted);font-size:.8rem">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/receipt/show?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                                    <a href="<?= APP_URL ?>/receipt/preview?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">تفاصيل</a>
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

        <!-- ── Pagination ────────────────────────────────────────────── -->
        <?php if ($lastPage > 1): ?>
            <p class="pag-info">
                عرض <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> من <?= number_format($total) ?>
            </p>
            <nav class="pagination" aria-label="pagination">

                <?php if ($page > 1): ?>
                    <a href="<?= paginationUrl($page - 1) ?>">‹ السابق</a>
                <?php else: ?>
                    <span class="disabled">‹ السابق</span>
                <?php endif; ?>

                <?php
                $window = 2;
                $shown  = [];
                for ($i = 1; $i <= $lastPage; $i++) {
                    if ($i === 1 || $i === $lastPage || abs($i - $page) <= $window) {
                        $shown[] = $i;
                    }
                }
                $prev = null;
                foreach ($shown as $p):
                    if ($prev !== null && $p - $prev > 1): ?>
                        <span>…</span>
                    <?php endif;
                    if ($p === $page): ?>
                        <span class="active"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= paginationUrl($p) ?>"><?= $p ?></a>
                    <?php endif;
                    $prev = $p;
                endforeach; ?>

                <?php if ($page < $lastPage): ?>
                    <a href="<?= paginationUrl($page + 1) ?>">التالي ›</a>
                <?php else: ?>
                    <span class="disabled">التالي ›</span>
                <?php endif; ?>

            </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
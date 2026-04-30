<?php // views/dashboard/branch_manager.php
require ROOT . '/views/includes/layout_top.php';
?>

<style>
<?php include ROOT . '/views/dashboard/shared_dashboard.css'; ?>

/* ── Branch identity badge ──────────────────────────────────── */
.branch-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: .45rem .95rem;
    font-size: .95rem;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 1.4rem;
}
</style>

<!-- ══ PAGE HEADER ══════════════════════════════════════════════════════ -->
<div class="page-header">
    <div>
        <h1 class="page-title">📊 لوحة التحكم</h1>
        <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?> · <?= date('l، d F Y') ?></p>
    </div>
</div>

<div class="branch-badge">🏢 <?= htmlspecialchars($branchName) ?></div>

<!-- ══ SECTION: الإيصالات ══════════════════════════════════════════════ -->
<div class="section-title">🧾 إيصالات الفرع</div>
<div class="stats-grid">

    <div class="stat-card accent-blue">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">إجمالي الإيصالات</div>
        <div class="stat-value"><?= number_format($stats['receipts']['total'] ?? 0) ?></div>
        <div class="stat-sub">منذ بداية النظام</div>
    </div>

    <div class="stat-card accent-green">
        <div class="stat-icon">✅</div>
        <div class="stat-label">مكتملة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['completed'] ?? 0) ?></div>
        <?php $pct = ($stats['receipts']['total'] ?? 0) ? round($stats['receipts']['completed'] / $stats['receipts']['total'] * 100) : 0; ?>
        <div class="stat-sub"><?= $pct ?>% من الإجمالي</div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $pct ?>%"></div></div>
    </div>

    <div class="stat-card accent-red">
        <div class="stat-icon">❌</div>
        <div class="stat-label">غير مكتملة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['not_completed'] ?? 0) ?></div>
        <?php $pct2 = ($stats['receipts']['total'] ?? 0) ? round($stats['receipts']['not_completed'] / $stats['receipts']['total'] * 100) : 0; ?>
        <div class="stat-sub"><?= $pct2 ?>% من الإجمالي</div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $pct2 ?>%;background:#ef4444"></div></div>
    </div>

    <div class="stat-card accent-yellow">
        <div class="stat-icon">⏳</div>
        <div class="stat-label">معلّقة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['pending'] ?? 0) ?></div>
        <div class="stat-sub">تحتاج متابعة</div>
    </div>

    <div class="stat-card accent-teal">
        <div class="stat-icon">📅</div>
        <div class="stat-label">إيصالات اليوم</div>
        <div class="stat-value"><?= number_format($stats['receipts']['today'] ?? 0) ?></div>
        <div class="stat-sub">جديد اليوم</div>
    </div>

    <div class="stat-card accent-purple">
        <div class="stat-icon">🗓️</div>
        <div class="stat-label">إيصالات هذا الشهر</div>
        <div class="stat-value"><?= number_format($stats['receipts']['this_month'] ?? 0) ?></div>
        <div class="stat-sub"><?= date('F Y') ?></div>
    </div>

</div>

<!-- ══ SECTION: المالية ════════════════════════════════════════════════ -->
<div class="section-title">💳 المعاملات المالية</div>
<div class="stats-grid">

    <div class="stat-card accent-green">
        <div class="stat-icon">💰</div>
        <div class="stat-label">إجمالي المدفوعات</div>
        <div class="stat-value"><?= number_format($stats['transactions']['total_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه مصري</div>
    </div>

    <div class="stat-card accent-blue">
        <div class="stat-icon">🔢</div>
        <div class="stat-label">عدد المعاملات</div>
        <div class="stat-value"><?= number_format($stats['transactions']['count'] ?? 0) ?></div>
        <div class="stat-sub">معاملة مسجّلة</div>
    </div>

    <div class="stat-card accent-teal">
        <div class="stat-icon">📆</div>
        <div class="stat-label">مدفوعات اليوم</div>
        <div class="stat-value"><?= number_format($stats['transactions']['today_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه اليوم</div>
    </div>

    <div class="stat-card accent-purple">
        <div class="stat-icon">📊</div>
        <div class="stat-label">مدفوعات هذا الشهر</div>
        <div class="stat-value"><?= number_format($stats['transactions']['month_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه — <?= date('F Y') ?></div>
    </div>

    <div class="stat-card accent-orange">
        <div class="stat-icon">📈</div>
        <div class="stat-label">متوسط قيمة المعاملة</div>
        <div class="stat-value"><?= number_format($stats['transactions']['avg_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه لكل معاملة</div>
    </div>

</div>

<!-- ══ Receipts + Transactions ════════════════════════════════════════ -->
<div class="dash-row">

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🧾 آخر الإيصالات</h3>
            <a href="<?= APP_URL ?>/receipts?branch_ids[]=<?= $branchId ?>">عرض الكل ←</a>
        </div>
        <?php if (!empty($recentReceipts)): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الكابتن</th>
                    <th>أول جلسة</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $statusMap = [
                'completed'     => ['badge-success', 'مكتمل'],
                'not_completed' => ['badge-danger',  'غير مكتمل'],
                'pending'       => ['badge-warning', 'معلّق'],
            ];
            foreach ($recentReceipts as $r):
                [$cls, $lbl] = $statusMap[$r['receipt_status']] ?? ['badge-secondary', $r['receipt_status']];
            ?>
                <tr>
                    <td style="color:var(--muted);font-size:.85rem"><?= $r['id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['client_name'] ?? '—') ?></strong></td>
                    <td style="font-size:.88rem"><?= htmlspecialchars($r['captain_name'] ?? '—') ?></td>
                    <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($r['first_session'] ?? '—') ?></td>
                    <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد إيصالات حديثة</div><?php endif; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>💳 آخر المعاملات</h3>
        </div>
        <?php if (!empty($recentTransactions)): ?>
        <table>
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>المبلغ</th>
                    <th>طريقة الدفع</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentTransactions as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['client_name'] ?? '—') ?></strong></td>
                    <td style="color:#22c55e;font-weight:800;font-size:1rem"><?= number_format($t['amount'] ?? 0) ?> ج</td>
                    <td style="font-size:.88rem"><?= htmlspecialchars($t['payment_method'] ?? '—') ?></td>
                    <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($t['created_at'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد معاملات</div><?php endif; ?>
    </div>

</div>

<!-- ══ Captains + Top Clients ════════════════════════════════════════ -->
<div class="dash-row">

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🏊 أداء الكابتنات</h3>
        </div>
        <?php if (!empty($captainStats)): ?>
        <table>
            <thead><tr><th>الكابتن</th><th>الإيصالات</th><th>مكتمل</th><th>النسبة</th></tr></thead>
            <tbody>
            <?php
            $maxCap = max(array_column($captainStats, 'receipt_count') ?: [1]);
            foreach ($captainStats as $cap):
                $cpct    = $maxCap ? round($cap['receipt_count'] / $maxCap * 100) : 0;
                $compPct = $cap['receipt_count'] ? round($cap['completed_count'] / $cap['receipt_count'] * 100) : 0;
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cap['captain_name']) ?></strong></td>
                    <td><?= number_format($cap['receipt_count']) ?></td>
                    <td><span class="badge badge-success"><?= $compPct ?>%</span></td>
                    <td style="min-width:90px">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $cpct ?>%;background:#a855f7"></div>
                        </div>
                        <div style="font-size:.75rem;color:var(--muted);margin-top:.2rem"><?= $cpct ?>%</div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا يوجد كابتنات مرتبطون بالفرع</div><?php endif; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🥇 أعلى العملاء دفعاً</h3>
            <a href="<?= APP_URL ?>/clients">عرض العملاء ←</a>
        </div>
        <?php if (!empty($topClients)): ?>
        <table>
            <thead><tr><th>#</th><th>العميل</th><th>الهاتف</th><th>إجمالي المدفوع</th><th>الإيصالات</th></tr></thead>
            <tbody>
            <?php foreach ($topClients as $i => $cl): ?>
                <tr>
                    <td style="color:var(--muted);font-size:.8rem;font-weight:700"><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($cl['client_name']) ?></strong></td>
                    <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($cl['phone'] ?? '—') ?></td>
                    <td style="color:#22c55e;font-weight:800;font-size:1rem"><?= number_format($cl['total_paid'] ?? 0) ?> ج</td>
                    <td><?= number_format($cl['receipt_count']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد بيانات</div><?php endif; ?>
    </div>

</div>

<!-- ══ Employees + Audit log ══════════════════════════════════════════ -->
<div class="dash-row">

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>👨‍💼 نشاط الموظفين في الفرع</h3>
            <a href="<?= APP_URL ?>/users">إدارة الموظفين ←</a>
        </div>
        <?php if (!empty($userStats)): ?>
        <table>
            <thead><tr><th>الموظف</th><th>الإيصالات</th><th>هذا الشهر</th></tr></thead>
            <tbody>
            <?php foreach ($userStats as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                    <td><?= number_format($u['total_receipts']) ?></td>
                    <td>
                        <span class="badge <?= $u['month_receipts'] > 0 ? 'badge-success' : 'badge-secondary' ?>">
                            <?= number_format($u['month_receipts']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد بيانات</div><?php endif; ?>
    </div>

    <?php if (!empty($recentAuditLog)): ?>
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🕐 آخر التعديلات</h3>
        </div>
        <ul class="activity-list">
            <?php foreach ($recentAuditLog as $log): ?>
            <li>
                <div class="activity-icon">✏️</div>
                <div class="activity-text">
                    <strong><?= htmlspecialchars($log['changed_by_name'] ?? 'مستخدم') ?></strong>
                    عدّل إيصال #<?= $log['receipt_id'] ?>
                    — <?= htmlspecialchars($log['field_name'] ?? '') ?>
                    <?php if (!empty($log['old_value']) && !empty($log['new_value'])): ?>
                        من <em><?= htmlspecialchars($log['old_value']) ?></em>
                        إلى <em><?= htmlspecialchars($log['new_value']) ?></em>
                    <?php endif; ?>
                    <div class="activity-time"><?= htmlspecialchars($log['changed_at'] ?? '') ?></div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
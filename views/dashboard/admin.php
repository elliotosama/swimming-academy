<?php // views/dashboard/index.php
require ROOT . '/views/includes/layout_top.php';





?>

<style>
/* ── Stats Grid ─────────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem 1.4rem;
    display: flex;
    flex-direction: column;
    gap: .4rem;
    position: relative;
    overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0,0,0,.08);
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 4px; height: 100%;
    border-radius: 0 12px 12px 0;
    background: var(--accent-color, var(--primary));
}
.stat-icon {
    font-size: 1.6rem;
    line-height: 1;
    margin-bottom: .2rem;
}
.stat-label {
    font-size: .75rem;
    color: var(--muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text);
    line-height: 1.1;
}
.stat-sub {
    font-size: .78rem;
    color: var(--muted);
    margin-top: .1rem;
}
.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    font-size: .72rem;
    padding: .15rem .45rem;
    border-radius: 999px;
    font-weight: 600;
    margin-top: .2rem;
    width: fit-content;
}
.stat-badge.up   { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.stat-badge.down { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.stat-badge.neutral { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

/* ── Section title ──────────────────────────────────────────── */
.section-title {
    font-size: .8rem;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .07em;
    margin: 1.5rem 0 .75rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* ── Two-column layout ──────────────────────────────────────── */
.dash-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}
@media (max-width: 800px) {
    .dash-row { grid-template-columns: 1fr; }
}

/* ── Cards (tables/lists inside) ───────────────────────────── */
.dash-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}
.dash-card-header {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dash-card-header h3 {
    font-size: .9rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.dash-card-header a {
    font-size: .78rem;
    color: var(--primary);
    text-decoration: none;
}
.dash-card table {
    width: 100%;
    border-collapse: collapse;
    font-size: .84rem;
}
.dash-card table th {
    padding: .55rem 1rem;
    text-align: right;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--muted);
    background: var(--bg);
    border-bottom: 1px solid var(--border);
}
.dash-card table td {
    padding: .6rem 1rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    vertical-align: middle;
}
.dash-card table tr:last-child td { border-bottom: none; }
.dash-card table tr:hover td { background: var(--bg); }

/* ── Progress bar ───────────────────────────────────────────── */
.progress-bar-wrap {
    height: 6px;
    background: var(--border);
    border-radius: 999px;
    overflow: hidden;
    margin-top: .3rem;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 999px;
    background: var(--primary);
    transition: width .4s ease;
}

/* ── Status dots ────────────────────────────────────────────── */
.dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    margin-left: .4rem;
}
.dot.green  { background: #22c55e; }
.dot.red    { background: #ef4444; }
.dot.yellow { background: #f59e0b; }

/* ── Activity list ──────────────────────────────────────────── */
.activity-list { list-style: none; margin: 0; padding: 0; }
.activity-list li {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .75rem 1.25rem;
    border-bottom: 1px solid var(--border);
    font-size: .83rem;
}
.activity-list li:last-child { border-bottom: none; }
.activity-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
    background: var(--bg);
    border: 1px solid var(--border);
}
.activity-text { flex: 1; }
.activity-text strong { display: block; font-weight: 600; }
.activity-time { font-size: .73rem; color: var(--muted); margin-top: .1rem; }

/* ── Accent colors per card ─────────────────────────────────── */
.accent-blue   { --accent-color: #3b82f6; }
.accent-green  { --accent-color: #22c55e; }
.accent-purple { --accent-color: #a855f7; }
.accent-orange { --accent-color: #f97316; }
.accent-pink   { --accent-color: #ec4899; }
.accent-teal   { --accent-color: #14b8a6; }
.accent-yellow { --accent-color: #eab308; }
.accent-red    { --accent-color: #ef4444; }

/* ── Empty state ────────────────────────────────────────────── */
.dash-empty {
    padding: 2rem;
    text-align: center;
    color: var(--muted);
    font-size: .85rem;
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">📊 لوحة التحكم</h1>
        <p class="breadcrumb">مرحباً، <?= htmlspecialchars($_SESSION['username'] ?? 'المشرف') ?> · <?= date('l، d F Y') ?></p>
    </div>
    <div style="display:flex;gap:.6rem">
        <a href="<?= APP_URL ?>/receipt/export" class="btn btn-secondary">⬇️ تصدير تقرير</a>
        <a href="<?= APP_URL ?>/receipt/create" class="btn btn-primary">+ إيصال جديد</a>
    </div>
</div>

<!-- ══ SECTION: الإيصالات ══════════════════════════════════════════════ -->
<div class="section-title">🧾 الإيصالات</div>
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
        <div class="stat-sub">
            <?php $pct = $stats['receipts']['total'] ? round($stats['receipts']['completed'] / $stats['receipts']['total'] * 100) : 0; ?>
            <?= $pct ?>% من الإجمالي
        </div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $pct ?>%"></div></div>
    </div>

    <div class="stat-card accent-red">
        <div class="stat-icon">❌</div>
        <div class="stat-label">غير مكتملة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['not_completed'] ?? 0) ?></div>
        <?php $pct2 = $stats['receipts']['total'] ? round($stats['receipts']['not_completed'] / $stats['receipts']['total'] * 100) : 0; ?>
        <div class="stat-sub"><?= $pct2 ?>% من الإجمالي</div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $pct2 ?>;background:#ef4444"></div></div>
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
        <div class="stat-sub">إيصال جديد اليوم</div>
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

<!-- ══ SECTION: العملاء والموظفون ══════════════════════════════════════ -->
<div class="section-title">👥 العملاء والموظفون</div>
<div class="stats-grid">

    <div class="stat-card accent-blue">
        <div class="stat-icon">👤</div>
        <div class="stat-label">إجمالي العملاء</div>
        <div class="stat-value"><?= number_format($stats['clients']['total'] ?? 0) ?></div>
        <div class="stat-sub">عميل مسجّل</div>
    </div>

    <div class="stat-card accent-green">
        <div class="stat-icon">🆕</div>
        <div class="stat-label">عملاء هذا الشهر</div>
        <div class="stat-value"><?= number_format($stats['clients']['this_month'] ?? 0) ?></div>
        <div class="stat-sub">عميل جديد</div>
    </div>

    <div class="stat-card accent-orange">
        <div class="stat-icon">🏊</div>
        <div class="stat-label">الكابتنات</div>
        <div class="stat-value"><?= number_format($stats['captains']['total'] ?? 0) ?></div>
        <div class="stat-sub">كابتن نشط</div>
    </div>

    <div class="stat-card accent-purple">
        <div class="stat-icon">👨‍💼</div>
        <div class="stat-label">الموظفون</div>
        <div class="stat-value"><?= number_format($stats['users']['total'] ?? 0) ?></div>
        <div class="stat-sub">مستخدم في النظام</div>
    </div>

    <div class="stat-card accent-teal">
        <div class="stat-icon">🏢</div>
        <div class="stat-label">الفروع</div>
        <div class="stat-value"><?= number_format($stats['branches']['total'] ?? 0) ?></div>
        <div class="stat-sub">فرع مسجّل</div>
    </div>

    <div class="stat-card accent-pink">
        <div class="stat-icon">📋</div>
        <div class="stat-label">خطط الأسعار</div>
        <div class="stat-value"><?= number_format($stats['plans']['total'] ?? 0) ?></div>
        <div class="stat-sub">خطة متاحة</div>
    </div>

</div>

<!-- ══ TABLES ROW 1 ════════════════════════════════════════════════════ -->
<div class="dash-row">

    <!-- Recent Receipts -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🧾 آخر الإيصالات</h3>
            <a href="<?= APP_URL ?>/receipts">عرض الكل ←</a>
        </div>
        <?php if (!empty($recentReceipts)): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الفرع</th>
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
                    <td style="color:var(--muted);font-size:.8rem"><?= $r['id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['client_name'] ?? '—') ?></strong></td>
                    <td style="font-size:.8rem"><?= htmlspecialchars($r['branch_name'] ?? '—') ?></td>
                    <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="dash-empty">لا توجد إيصالات حديثة</div>
        <?php endif; ?>
    </div>

    <!-- Recent Transactions -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>💳 آخر المعاملات</h3>
            <a href="<?= APP_URL ?>/transactions">عرض الكل ←</a>
        </div>
        <?php if (!empty($recentTransactions)): ?>
        <table>
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentTransactions as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['client_name'] ?? '—') ?></strong></td>
                    <td style="color:#16a34a;font-weight:700"><?= number_format($t['amount'] ?? 0) ?> ج</td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($t['created_at'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="dash-empty">لا توجد معاملات حديثة</div>
        <?php endif; ?>
    </div>

</div>

<!-- ══ TABLES ROW 2 ════════════════════════════════════════════════════ -->
<div class="dash-row">

    <!-- Top Branches -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🏢 أداء الفروع</h3>
            <a href="<?= APP_URL ?>/branches">إدارة الفروع ←</a>
        </div>
        <?php if (!empty($branchStats)): ?>
        <table>
            <thead>
                <tr>
                    <th>الفرع</th>
                    <th>الإيصالات</th>
                    <th>المدفوعات</th>
                    <th>النسبة</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $maxReceipts = max(array_column($branchStats, 'receipt_count') ?: [1]);
            foreach ($branchStats as $b):
                $pct = $maxReceipts ? round($b['receipt_count'] / $maxReceipts * 100) : 0;
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                    <td><?= number_format($b['receipt_count']) ?></td>
                    <td style="color:#16a34a;font-weight:600"><?= number_format($b['total_paid'] ?? 0) ?> ج</td>
                    <td style="min-width:80px">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="dash-empty">لا توجد بيانات للفروع</div>
        <?php endif; ?>
    </div>

    <!-- Top Captains -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🏊 أداء الكابتنات</h3>
            <a href="<?= APP_URL ?>/captains">إدارة الكابتنات ←</a>
        </div>
        <?php if (!empty($captainStats)): ?>
        <table>
            <thead>
                <tr>
                    <th>الكابتن</th>
                    <th>الإيصالات</th>
                    <th>مكتمل</th>
                    <th>النسبة</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $maxCap = max(array_column($captainStats, 'receipt_count') ?: [1]);
            foreach ($captainStats as $cap):
                $cpct = $maxCap ? round($cap['receipt_count'] / $maxCap * 100) : 0;
                $compPct = $cap['receipt_count'] ? round($cap['completed_count'] / $cap['receipt_count'] * 100) : 0;
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cap['captain_name']) ?></strong></td>
                    <td><?= number_format($cap['receipt_count']) ?></td>
                    <td>
                        <span class="badge badge-success"><?= $compPct ?>%</span>
                    </td>
                    <td style="min-width:80px">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $cpct ?>%;background:#a855f7"></div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="dash-empty">لا توجد بيانات للكابتنات</div>
        <?php endif; ?>
    </div>

</div>

<!-- ══ TABLES ROW 3 ════════════════════════════════════════════════════ -->
<div class="dash-row">

    <!-- Top Clients by payments -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🥇 أعلى العملاء دفعاً</h3>
            <a href="<?= APP_URL ?>/clients">عرض العملاء ←</a>
        </div>
        <?php if (!empty($topClients)): ?>
        <table>
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>إجمالي المدفوع</th>
                    <th>الإيصالات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($topClients as $i => $cl): ?>
                <tr>
                    <td>
                        <span style="color:var(--muted);font-size:.75rem;margin-left:.3rem"><?= $i+1 ?>.</span>
                        <strong><?= htmlspecialchars($cl['client_name']) ?></strong>
                    </td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($cl['phone'] ?? '—') ?></td>
                    <td style="color:#16a34a;font-weight:700"><?= number_format($cl['total_paid'] ?? 0) ?> ج</td>
                    <td><?= number_format($cl['receipt_count']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="dash-empty">لا توجد بيانات</div>
        <?php endif; ?>
    </div>

    <!-- Creator (employee) activity -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>👨‍💼 نشاط الموظفين</h3>
            <a href="<?= APP_URL ?>/users">إدارة الموظفين ←</a>
        </div>
        <?php if (!empty($userStats)): ?>
        <table>
            <thead>
                <tr>
                    <th>الموظف</th>
                    <th>الإيصالات</th>
                    <th>هذا الشهر</th>
                </tr>
            </thead>
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
        <?php else: ?>
            <div class="dash-empty">لا توجد بيانات</div>
        <?php endif; ?>
    </div>

</div>

<!-- ══ Recent Activity Log ═════════════════════════════════════════════ -->
<?php if (!empty($recentAuditLog)): ?>
<div class="section-title">🕐 آخر التعديلات والنشاطات</div>
<div class="dash-card" style="margin-bottom:1.5rem">
    <ul class="activity-list">
        <?php foreach ($recentAuditLog as $log): ?>
        <li>
            <div class="activity-icon">✏️</div>
            <div class="activity-text">
                <strong><?= htmlspecialchars($log['changed_by_name'] ?? 'مستخدم') ?></strong>
                عدّل إيصال رقم #<?= $log['receipt_id'] ?>
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

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
<?php // views/dashboard/area_manager.php
require ROOT . '/views/includes/layout_top.php';
?>

<style>
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1.25rem 1.4rem;display:flex;flex-direction:column;gap:.4rem;position:relative;overflow:hidden;transition:transform .15s,box-shadow .15s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(0,0,0,.08)}
.stat-card::before{content:'';position:absolute;top:0;right:0;width:4px;height:100%;border-radius:0 12px 12px 0;background:var(--accent-color,var(--primary))}
.stat-icon{font-size:1.6rem;line-height:1;margin-bottom:.2rem}
.stat-label{font-size:.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.stat-value{font-size:2rem;font-weight:800;color:var(--text);line-height:1.1}
.stat-sub{font-size:.78rem;color:var(--muted);margin-top:.1rem}
.progress-bar-wrap{height:6px;background:var(--border);border-radius:999px;overflow:hidden;margin-top:.3rem}
.progress-bar-fill{height:100%;border-radius:999px;background:var(--primary);transition:width .4s ease}
.section-title{font-size:.8rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin:1.5rem 0 .75rem;display:flex;align-items:center;gap:.5rem}
.section-title::after{content:'';flex:1;height:1px;background:var(--border)}
.dash-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
@media(max-width:800px){.dash-row{grid-template-columns:1fr}}
.dash-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.dash-card-header{padding:.85rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.dash-card-header h3{font-size:.9rem;font-weight:700;margin:0;display:flex;align-items:center;gap:.4rem}
.dash-card-header a{font-size:.78rem;color:var(--primary);text-decoration:none}
.dash-card table{width:100%;border-collapse:collapse;font-size:.84rem}
.dash-card table th{padding:.55rem 1rem;text-align:right;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);background:var(--bg);border-bottom:1px solid var(--border)}
.dash-card table td{padding:.6rem 1rem;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
.dash-card table tr:last-child td{border-bottom:none}
.dash-card table tr:hover td{background:var(--bg)}
.dash-empty{padding:2rem;text-align:center;color:var(--muted);font-size:.85rem}
.activity-list{list-style:none;margin:0;padding:0}
.activity-list li{display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid var(--border);font-size:.83rem}
.activity-list li:last-child{border-bottom:none}
.activity-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;background:var(--bg);border:1px solid var(--border)}
.activity-text{flex:1}
.activity-text strong{display:block;font-weight:600}
.activity-time{font-size:.73rem;color:var(--muted);margin-top:.1rem}
.accent-blue{--accent-color:#3b82f6}
.accent-green{--accent-color:#22c55e}
.accent-purple{--accent-color:#a855f7}
.accent-orange{--accent-color:#f97316}
.accent-teal{--accent-color:#14b8a6}
.accent-yellow{--accent-color:#eab308}
.accent-red{--accent-color:#ef4444}
.branches-strip{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.25rem}
.branch-chip{display:inline-flex;align-items:center;gap:.35rem;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:.3rem .8rem;font-size:.82rem;font-weight:600;color:var(--text);text-decoration:none}
.branch-chip:hover{border-color:var(--primary);color:var(--primary)}
.wide-card{margin-bottom:1rem}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">📊 لوحة التحكم — مدير المنطقة</h1>
        <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?> · <?= date('l، d F Y') ?></p>
    </div>
    <div style="display:flex;gap:.6rem">
        <a href="<?= APP_URL ?>/receipt/export" class="btn btn-secondary">⬇️ تصدير التقرير</a>
        <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">🧾 كل الإيصالات</a>
    </div>
</div>

<?php if (empty($branches)): ?>
    <div class="dash-card"><div class="dash-empty">لا توجد فروع مرتبطة بحسابك. تواصل مع المسؤول.</div></div>
    <?php require ROOT . '/views/includes/layout_bottom.php'; return; ?>
<?php endif; ?>

<!-- Branch quick-links -->
<div class="branches-strip">
    <?php foreach ($branches as $b): ?>
        <a class="branch-chip" href="<?= APP_URL ?>/receipts?branch_ids[]=<?= $b['id'] ?>">
            🏢 <?= htmlspecialchars($b['branch_name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- ══ SECTION: الإيصالات (aggregate) ════════════════════════════════ -->
<div class="section-title">🧾 إجمالي الإيصالات — كل الفروع</div>
<div class="stats-grid">

    <div class="stat-card accent-blue">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">إجمالي الإيصالات</div>
        <div class="stat-value"><?= number_format($stats['receipts']['total'] ?? 0) ?></div>
        <div class="stat-sub">عبر <?= count($branches) ?> فرع</div>
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
<div class="section-title">💳 المعاملات المالية — كل الفروع</div>
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
        <div class="stat-label">متوسط المعاملة</div>
        <div class="stat-value"><?= number_format($stats['transactions']['avg_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه لكل معاملة</div>
    </div>

</div>

<!-- ══ Per-branch breakdown (full width) ══════════════════════════════ -->
<div class="section-title">🏢 أداء كل فرع</div>
<div class="dash-card wide-card">
    <?php if (!empty($branchStats)): ?>
    <table>
        <thead>
            <tr>
                <th>الفرع</th>
                <th>إجمالي الإيصالات</th>
                <th>مكتملة</th>
                <th>إجمالي المدفوع</th>
                <th>النسبة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $maxPaid = max(array_column($branchStats, 'total_paid') ?: [1]);
        foreach ($branchStats as $b):
            $paidPct  = $maxPaid ? round($b['total_paid'] / $maxPaid * 100) : 0;
            $compPct  = $b['receipt_count'] ? round($b['completed_count'] / $b['receipt_count'] * 100) : 0;
        ?>
            <tr>
                <td><strong><?= htmlspecialchars($b['branch_name']) ?></strong></td>
                <td><?= number_format($b['receipt_count']) ?></td>
                <td><span class="badge badge-success"><?= $compPct ?>%</span></td>
                <td style="color:#16a34a;font-weight:700"><?= number_format($b['total_paid'] ?? 0) ?> ج</td>
                <td style="min-width:100px">
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:<?= $paidPct ?>%"></div>
                    </div>
                </td>
                <td>
                    <a href="<?= APP_URL ?>/receipts?branch_ids[]=<?= htmlspecialchars(array_column($branches, 'id', 'branch_name')[$b['branch_name']] ?? '') ?>" class="btn btn-sm btn-secondary">عرض الإيصالات</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?><div class="dash-empty">لا توجد بيانات للفروع</div><?php endif; ?>
</div>

<!-- ══ TABLES ROW: Captains + Recent receipts ═════════════════════════ -->
<div class="dash-row">

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🏊 أداء الكابتنات</h3>
        </div>
        <?php if (!empty($captainStats)): ?>
        <table>
            <thead><tr><th>الكابتن</th><th>الفرع</th><th>الإيصالات</th><th>مكتمل %</th></tr></thead>
            <tbody>
            <?php foreach ($captainStats as $cap):
                $cPct = $cap['receipt_count'] ? round($cap['completed_count'] / $cap['receipt_count'] * 100) : 0;
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cap['captain_name']) ?></strong></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($cap['branch_name'] ?? '—') ?></td>
                    <td><?= number_format($cap['receipt_count']) ?></td>
                    <td><span class="badge badge-success"><?= $cPct ?>%</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد بيانات</div><?php endif; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🧾 آخر الإيصالات</h3>
            <a href="<?= APP_URL ?>/receipts">عرض الكل ←</a>
        </div>
        <?php if (!empty($recentReceipts)): ?>
        <table>
            <thead><tr><th>#</th><th>العميل</th><th>الفرع</th><th>الحالة</th></tr></thead>
            <tbody>
            <?php
            $statusMap = ['completed' => ['badge-success','مكتمل'], 'not_completed' => ['badge-danger','غير مكتمل'], 'pending' => ['badge-warning','معلّق']];
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
        <?php else: ?><div class="dash-empty">لا توجد إيصالات حديثة</div><?php endif; ?>
    </div>

</div>

<!-- ══ TABLES ROW: Top clients + Employees ════════════════════════════ -->
<div class="dash-row">

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🥇 أعلى العملاء دفعاً</h3>
        </div>
        <?php if (!empty($topClients)): ?>
        <table>
            <thead><tr><th>#</th><th>العميل</th><th>إجمالي المدفوع</th><th>الإيصالات</th></tr></thead>
            <tbody>
            <?php foreach ($topClients as $i => $cl): ?>
                <tr>
                    <td style="color:var(--muted);font-size:.75rem"><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($cl['client_name']) ?></strong>
                        <br><small style="color:var(--muted)"><?= htmlspecialchars($cl['phone'] ?? '') ?></small></td>
                    <td style="color:#16a34a;font-weight:700"><?= number_format($cl['total_paid'] ?? 0) ?> ج</td>
                    <td><?= number_format($cl['receipt_count']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد بيانات</div><?php endif; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-header">
            <h3>👨‍💼 نشاط الموظفين</h3>
        </div>
        <?php if (!empty($userStats)): ?>
        <table>
            <thead><tr><th>الموظف</th><th>الإيصالات</th><th>هذا الشهر</th></tr></thead>
            <tbody>
            <?php foreach ($userStats as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                    <td><?= number_format($u['total_receipts']) ?></td>
                    <td><span class="badge <?= $u['month_receipts'] > 0 ? 'badge-success' : 'badge-secondary' ?>"><?= number_format($u['month_receipts']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لا توجد بيانات</div><?php endif; ?>
    </div>

</div>

<!-- ══ Recent audit log (full width) ══════════════════════════════════ -->
<?php if (!empty($recentAuditLog)): ?>
<div class="section-title">🕐 آخر التعديلات</div>
<div class="dash-card wide-card">
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

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
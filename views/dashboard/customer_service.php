<?php // views/dashboard/customer_service.php
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
.dash-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:1rem}
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

/* ── Week activity bar chart ────────────────────────────────── */
.week-chart{display:flex;align-items:flex-end;gap:6px;height:80px;padding:0 1.25rem 1rem;border-top:1px solid var(--border);margin-top:.5rem}
.week-bar-wrap{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1}
.week-bar{width:100%;border-radius:4px 4px 0 0;background:var(--primary);opacity:.8;min-height:4px;transition:opacity .2s}
.week-bar:hover{opacity:1}
.week-label{font-size:.65rem;color:var(--muted);text-align:center}
.week-count{font-size:.68rem;color:var(--text);font-weight:600}

/* ── Tab-style switcher for my receipts vs my edits ────────── */
.tab-bar{display:flex;border-bottom:1px solid var(--border);padding:0 1.25rem;gap:.25rem}
.tab-btn{padding:.55rem .85rem;font-size:.83rem;font-weight:600;border:none;background:none;color:var(--muted);border-bottom:2px solid transparent;margin-bottom:-1px;cursor:pointer;transition:color .15s,border-color .15s}
.tab-btn.active{color:var(--primary);border-bottom-color:var(--primary)}
.tab-pane{display:none}
.tab-pane.active{display:block}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">📋 لوحتي</h1>
        <p class="breadcrumb">مرحباً، <?= htmlspecialchars($_SESSION['username'] ?? '') ?> · <?= date('l، d F Y') ?></p>
    </div>
    <div style="display:flex;gap:.6rem">
        <a href="<?= APP_URL ?>/receipt/create" class="btn btn-primary">+ إيصال جديد</a>
    </div>
</div>

<!-- ══ SECTION: إحصائياتي ══════════════════════════════════════════════ -->
<div class="section-title">📊 إحصائياتي</div>
<div class="stats-grid">

    <div class="stat-card accent-blue">
        <div class="stat-icon">🧾</div>
        <div class="stat-label">إيصالات أنشأتها</div>
        <div class="stat-value"><?= number_format($stats['receipts']['total'] ?? 0) ?></div>
        <div class="stat-sub">إجمالي منذ البداية</div>
    </div>

    <div class="stat-card accent-green">
        <div class="stat-icon">✅</div>
        <div class="stat-label">مكتملة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['completed'] ?? 0) ?></div>
        <?php $pct = ($stats['receipts']['total'] ?? 0) ? round($stats['receipts']['completed'] / $stats['receipts']['total'] * 100) : 0; ?>
        <div class="stat-sub"><?= $pct ?>% من إيصالاتي</div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $pct ?>%"></div></div>
    </div>

    <div class="stat-card accent-yellow">
        <div class="stat-icon">⏳</div>
        <div class="stat-label">معلّقة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['pending'] ?? 0) ?></div>
        <div class="stat-sub">تحتاج متابعة</div>
    </div>

    <div class="stat-card accent-red">
        <div class="stat-icon">❌</div>
        <div class="stat-label">غير مكتملة</div>
        <div class="stat-value"><?= number_format($stats['receipts']['not_completed'] ?? 0) ?></div>
        <?php $pct2 = ($stats['receipts']['total'] ?? 0) ? round($stats['receipts']['not_completed'] / $stats['receipts']['total'] * 100) : 0; ?>
        <div class="stat-sub"><?= $pct2 ?>%</div>
    </div>

    <div class="stat-card accent-teal">
        <div class="stat-icon">📅</div>
        <div class="stat-label">أنشأت اليوم</div>
        <div class="stat-value"><?= number_format($stats['receipts']['today'] ?? 0) ?></div>
        <div class="stat-sub">إيصال اليوم</div>
    </div>

    <div class="stat-card accent-purple">
        <div class="stat-icon">🗓️</div>
        <div class="stat-label">هذا الشهر</div>
        <div class="stat-value"><?= number_format($stats['receipts']['this_month'] ?? 0) ?></div>
        <div class="stat-sub"><?= date('F Y') ?></div>
    </div>

    <div class="stat-card accent-orange">
        <div class="stat-icon">✏️</div>
        <div class="stat-label">إيصالات عدّلتها</div>
        <div class="stat-value"><?= number_format($stats['updated_only'] ?? 0) ?></div>
        <div class="stat-sub">لم أنشئها أنا</div>
    </div>

    <div class="stat-card accent-green">
        <div class="stat-icon">💰</div>
        <div class="stat-label">مدفوعات إيصالاتي</div>
        <div class="stat-value"><?= number_format($stats['transactions']['total_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه إجمالي</div>
    </div>

    <div class="stat-card accent-teal">
        <div class="stat-icon">📆</div>
        <div class="stat-label">مدفوعات اليوم</div>
        <div class="stat-value"><?= number_format($stats['transactions']['today_amount'] ?? 0) ?></div>
        <div class="stat-sub">جنيه اليوم</div>
    </div>

    <div class="stat-card accent-blue">
        <div class="stat-icon">🔢</div>
        <div class="stat-label">معاملات هذا الشهر</div>
        <div class="stat-value"><?= number_format($stats['transactions']['count'] ?? 0) ?></div>
        <div class="stat-sub">معاملة مسجّلة</div>
    </div>

</div>

<!-- ══ Week activity mini-chart ════════════════════════════════════════ -->
<?php if (!empty($weekActivity)): ?>
<div class="dash-card" style="margin-bottom:1.5rem">
    <div class="dash-card-header">
        <h3>📈 نشاطي — آخر 7 أيام</h3>
    </div>
    <?php
    // Fill in missing days
    $dayMap = [];
    foreach ($weekActivity as $w) $dayMap[$w['day']] = (int) $w['cnt'];
    $maxCnt = max(array_values($dayMap) ?: [1]);
    $days = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $days[] = ['date' => $d, 'cnt' => $dayMap[$d] ?? 0];
    }
    ?>
    <div class="week-chart">
        <?php foreach ($days as $d):
            $barPct = $maxCnt ? round($d['cnt'] / $maxCnt * 100) : 0;
        ?>
        <div class="week-bar-wrap">
            <div class="week-count"><?= $d['cnt'] ?: '' ?></div>
            <div class="week-bar" style="height:<?= max(4, $barPct * 0.6) ?>px" title="<?= $d['date'] ?>: <?= $d['cnt'] ?> إيصال"></div>
            <div class="week-label"><?= date('D', strtotime($d['date'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ══ My receipts + My edits (tabbed) ════════════════════════════════ -->
<div class="dash-card">
    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('created', this)">🧾 إيصالات أنشأتها (<?= count($myReceipts) ?>)</button>
        <button class="tab-btn" onclick="switchTab('edited', this)">✏️ إيصالات عدّلتها (<?= count($myEdits) ?>)</button>
    </div>

    <!-- Tab: Created -->
    <div id="tab-created" class="tab-pane active">
        <?php if (!empty($myReceipts)): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الفرع</th>
                    <th>الكابتن</th>
                    <th>أول جلسة</th>
                    <th>آخر جلسة</th>
                    <th>إجمالي المدفوع</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $statusMap = ['completed' => ['badge-success','مكتمل'], 'not_completed' => ['badge-danger','غير مكتمل'], 'pending' => ['badge-warning','معلّق']];
            foreach ($myReceipts as $r):
                [$cls, $lbl] = $statusMap[$r['receipt_status']] ?? ['badge-secondary', $r['receipt_status']];
            ?>
                <tr>
                    <td style="color:var(--muted);font-size:.8rem"><?= $r['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r['client_name'] ?? '—') ?></strong>
                        <?php if (!empty($r['phone'])): ?>
                            <br><small style="color:var(--muted)"><?= htmlspecialchars($r['phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.8rem"><?= htmlspecialchars($r['branch_name'] ?? '—') ?></td>
                    <td style="font-size:.8rem"><?= htmlspecialchars($r['captain_name'] ?? '—') ?></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($r['first_session'] ?? '—') ?></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($r['last_session'] ?? '—') ?></td>
                    <td style="color:#16a34a;font-weight:700"><?= number_format($r['total_paid'] ?? 0) ?> ج</td>
                    <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                    <td>
                        <a href="<?= APP_URL ?>/receipt/show?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لم تُنشئ أي إيصالات بعد</div><?php endif; ?>
    </div>

    <!-- Tab: Edited -->
    <div id="tab-edited" class="tab-pane">
        <?php if (!empty($myEdits)): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الفرع</th>
                    <th>الحالة</th>
                    <th>آخر تعديل مني</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($myEdits as $r):
                [$cls, $lbl] = $statusMap[$r['receipt_status']] ?? ['badge-secondary', $r['receipt_status']];
            ?>
                <tr>
                    <td style="color:var(--muted);font-size:.8rem"><?= $r['id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['client_name'] ?? '—') ?></strong></td>
                    <td style="font-size:.8rem"><?= htmlspecialchars($r['branch_name'] ?? '—') ?></td>
                    <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($r['last_edit'] ?? '—') ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/receipt/show?id=<?= $r['id'] ?>" class="btn btn-sm btn-secondary">عرض</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><div class="dash-empty">لم تُعدّل أي إيصالات لم تُنشئها</div><?php endif; ?>
    </div>
</div>

<!-- ══ My detailed audit trail ════════════════════════════════════════ -->
<?php if (!empty($myAuditLog)): ?>
<div class="section-title">🕐 سجل تعديلاتي</div>
<div class="dash-card">
    <ul class="activity-list">
        <?php foreach ($myAuditLog as $log): ?>
        <li>
            <div class="activity-icon">✏️</div>
            <div class="activity-text">
                <strong>إيصال #<?= $log['receipt_id'] ?></strong>
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

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
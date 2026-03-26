<?php $pageTitle = 'الإيصالات والمعاملات'; ?>
<?php include __DIR__ . '/_layout_head.php'; ?>

<div class="page">

    <div class="breadcrumb">
        <a href="index.php">الفروع</a>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <span>فرع #<?= $branchId ?></span>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <span>الإيصالات</span>
    </div>

    <div class="page-header">
        <div>
            <h1 class="page-header__title">إحصائيات الفرع</h1>
        </div>
        <a onclick="history.back()" style="cursor:pointer" class="btn btn-secondary">رجوع</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card__label">إجمالي الإيصالات</div>
            <div class="stat-card__value"><?= $stats['total_receipts'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">الإجمالي الكلي</div>
            <div class="stat-card__value"><?= number_format($stats['total_amount'], 2) ?> <small style="font-size:13px;color:var(--ink-4);">ج.م</small></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">إجمالي المدفوعات</div>
            <div class="stat-card__value" style="color:var(--green)"><?= number_format($stats['total_payments'], 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">إجمالي المستردات</div>
            <div class="stat-card__value" style="color:var(--red)"><?= number_format($stats['total_refunds'], 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">أعلى معاملة</div>
            <div class="stat-card__value"><?= number_format($stats['max_transaction'], 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">أدنى معاملة</div>
            <div class="stat-card__value"><?= number_format($stats['min_transaction'], 2) ?></div>
        </div>
    </div>

    <!-- Receipts Table -->
    <h2 class="section-title">ملخص الإيصالات</h2>
    <div class="card" style="margin-bottom:28px;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم الإيصال</th>
                        <th>الحالة</th>
                        <th>المبلغ الكلي</th>
                        <th>تاريخ الإنشاء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($receipts): ?>
                        <?php foreach ($receipts as $r): ?>
                        <tr>
                            <td>#<?= $r['receipt_id'] ?></td>
                            <td>
                                <?php $s = $r['receipt_status']; ?>
                                <span class="badge <?= $s === 'completed' ? 'badge-green' : 'badge-red' ?>">
                                    <?= $s === 'completed' ? 'مكتمل' : 'غير مكتمل' ?>
                                </span>
                            </td>
                            <td><?= number_format($r['receipt_total'], 2) ?> ج.م</td>
                            <td><?= $r['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4"><div class="empty-state"><p>لا توجد إيصالات</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transactions Table -->
    <h2 class="section-title">جميع المعاملات</h2>
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الإيصال</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>النوع</th>
                        <th>التاريخ</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions): ?>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= $t['transaction_id'] ?></td>
                            <td>#<?= $t['receipt_id'] ?></td>
                            <td><?= number_format($t['amount'], 2) ?> ج.م</td>
                            <td><?= htmlspecialchars($t['payment_method']) ?></td>
                            <td>
                                <span class="badge <?= $t['type'] === 'payment' ? 'badge-green' : 'badge-amber' ?>">
                                    <?= $t['type'] === 'payment' ? 'دفعة' : 'استرداد' ?>
                                </span>
                            </td>
                            <td><?= $t['created_at'] ?></td>
                            <td style="color:var(--ink-3)"><?= htmlspecialchars($t['notes'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7"><div class="empty-state"><p>لا توجد معاملات</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
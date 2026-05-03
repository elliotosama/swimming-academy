<?php // views/admin/receipts/show.php
require ROOT . '/views/includes/layout_top.php';

$statusMap = [
    'completed'     => ['badge-success', 'مكتمل'],
    'not_completed' => ['badge-danger',  'غير مكتمل'],
    'pending'       => ['badge-warning', 'معلّق'],
];
[$sCls, $sLabel] = $statusMap[$receipt['receipt_status']] ?? ['badge-secondary', $receipt['receipt_status']];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🧾 إيصال #<?= $receipt['id'] ?></h1>
        <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <div style="display:flex;gap:.5rem">
        <a href="<?= APP_URL ?>/receipt/edit?id=<?= $receipt['id'] ?>" class="btn btn-warning">تعديل</a>
        <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">← رجوع</a>
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

<!-- ─── بطاقة تفاصيل الإيصال ─────────────────────────────────────────── -->
<div class="card" style="margin-bottom:1.5rem">
    <h2 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--text)">تفاصيل الإيصال</h2>

    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">العميل</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['client_name'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">الفرع</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['branch_name'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">الكابتن</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['captain_name'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">المنشئ</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['creator_name'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">الخطة</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['plan_name'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">المستوى</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['level'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">وقت التمرين</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['exercise_time'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">أول جلسة</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['first_session'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">آخر جلسة</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['last_session'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">جلسة التجديد</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['renewal_session'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">نوع التجديد</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['renewal_type'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">تاريخ الإنشاء</span>
            <span class="detail-value"><?= htmlspecialchars($receipt['created_at'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">الحالة</span>
            <span class="detail-value"><span class="badge <?= $sCls ?>"><?= $sLabel ?></span></span>
        </div>
        <?php if (!empty($receipt['pdf_path'])): ?>
        <div class="detail-item">
            <span class="detail-label">ملف PDF</span>
            <span class="detail-value">
                <a href="<?= htmlspecialchars('/uploads/receipts/' . $receipt['pdf_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">📄 عرض الملف</a>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ─── المعاملات المالية ──────────────────────────────────────────────── -->
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <h2 style="font-size:1rem;font-weight:600;color:var(--text)">
            💳 المعاملات المالية
        </h2>
        <div style="display:flex;align-items:center;gap:1rem">
            <span style="font-size:.9rem;color:var(--muted)">
                إجمالي المدفوع:
                <strong style="color:var(--text)"><?= number_format($totalPaid, 2) ?></strong>
            </span>
            <a href="<?= APP_URL ?>/transaction/create?receipt_id=<?= $receipt['id'] ?>" class="btn btn-sm btn-primary">
                + إضافة معاملة
            </a>
        </div>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="empty-state" style="padding:1.5rem 0">
            <p style="color:var(--muted)">لا توجد معاملات مالية لهذا الإيصال بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>النوع</th>
                        <th>طريقة الدفع</th>
                        <th>المبلغ</th>
                        <th>المنشئ</th>
                        <th>التاريخ</th>
                        <th>ملاحظات</th>
                        <th>الإثبات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <?php
                        $typeMap = [
                            'payment'  => ['badge-success', 'دفعة'],
                            'refund'   => ['badge-danger',  'استرداد'],
                            'discount' => ['badge-warning', 'خصم'],
                        ];
                        [$tCls, $tLabel] = $typeMap[$t['type']] ?? ['badge-secondary', $t['type']];

                        // Determine the attachment column name — may be stored as
                        // 'attachment', 'transaction_evidence', or 'evidence'
                        $evidence = $t['attachment'] ?? $t['transaction_evidence'] ?? $t['evidence'] ?? null;
                        ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $t['id'] ?></td>
                            <td><span class="badge <?= $tCls ?>"><?= $tLabel ?></span></td>
                            <td><?= htmlspecialchars($t['payment_method'] ?? '—') ?></td>
                            <td><strong><?= number_format($t['amount'], 2) ?></strong></td>
                            <td style="font-size:.85rem"><?= htmlspecialchars($t['creator_name'] ?? '—') ?></td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($t['created_at'] ?? '—') ?></td>
                            <td style="font-size:.82rem;color:var(--muted);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                <?= htmlspecialchars($t['notes'] ?? '—') ?>
                            </td>
                            <td>
                                <?php if (!empty($evidence)): ?>
                                    <?php
                                    $ext = strtolower(pathinfo($evidence, PATHINFO_EXTENSION));
                                    $isPdf = ($ext === 'pdf');
                                    ?>
                                    <a href="<?= htmlspecialchars($evidence) ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-secondary"
                                       title="عرض الإثبات">
                                        <?= $isPdf ? '📄 PDF' : '🖼️ صورة' ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--muted);font-size:.8rem">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/transaction/edit?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <form method="POST" action="<?= APP_URL ?>/transaction/delete?id=<?= $t['id'] ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
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

<!-- ─── سجل التدقيق ───────────────────────────────────────────────────── -->
<div class="card">
    <h2 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--text)">📋 سجل التعديلات</h2>

    <?php if (empty($auditLogs)): ?>
        <div class="empty-state" style="padding:1.5rem 0">
            <p style="color:var(--muted)">لا يوجد سجل تعديلات لهذا الإيصال بعد.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>الحقل</th>
                        <th>القيمة القديمة</th>
                        <th>القيمة الجديدة</th>
                        <th>بواسطة</th>
                        <th>الدور</th>
                        <th>التاريخ والوقت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditLogs as $log): ?>
                        <tr>
                            <td><code style="font-size:.8rem"><?= htmlspecialchars($log['field_name']) ?></code></td>
                            <td style="color:var(--danger);font-size:.82rem"><?= htmlspecialchars($log['old_value'] ?? '—') ?></td>
                            <td style="color:var(--success);font-size:.82rem"><?= htmlspecialchars($log['new_value'] ?? '—') ?></td>
                            <td style="font-size:.85rem"><?= htmlspecialchars($log['changer_name'] ?? '—') ?>)</td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($log['role']) ?></td>
                            <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($log['changed_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.detail-item {
    display: flex;
    flex-direction: column;
    gap: .25rem;
}
.detail-label {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--muted);
    font-weight: 600;
}
.detail-value {
    font-size: .92rem;
    color: var(--text);
}
</style>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
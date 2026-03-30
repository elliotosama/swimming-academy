<?php // views/admin/transactions/create.php  (also used as edit.php)
require ROOT . '/views/includes/layout_top.php';

$formTitle = $isEdit ? 'تعديل المعاملة' : 'معاملة جديدة';
$action    = $isEdit
    ? APP_URL . '/transaction/edit?id=' . $transaction['id']
    : APP_URL . '/transaction/create';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $formTitle ?></h1>
        <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="<?= APP_URL ?>/transactions" class="btn btn-secondary">← رجوع</a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
            <div>⚠️ <?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($receipt): ?>
    <div class="alert" style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.88rem">
        🧾 مرتبطة بالإيصال
        <a href="<?= APP_URL ?>/receipt/show?id=<?= $receipt['id'] ?>" style="color:var(--primary);font-weight:600">
            #<?= $receipt['id'] ?> — <?= htmlspecialchars($receipt['client_name'] ?? '') ?>
        </a>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="<?= $action ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="receipt_id" value="<?= htmlspecialchars($transaction['receipt_id'] ?? '') ?>">

        <div class="form-grid">

            <!-- النوع -->
            <div class="form-group">
                <label class="form-label">نوع المعاملة <span class="required">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="payment"  <?= ($transaction['type'] ?? '') === 'payment'  ? 'selected' : '' ?>>دفعة</option>
                    <option value="refund"   <?= ($transaction['type'] ?? '') === 'refund'   ? 'selected' : '' ?>>استرداد</option>
                    <option value="discount" <?= ($transaction['type'] ?? '') === 'discount' ? 'selected' : '' ?>>خصم</option>
                </select>
            </div>

            <!-- طريقة الدفع -->
            <div class="form-group">
                <label class="form-label">طريقة الدفع <span class="required">*</span></label>
                <select name="payment_method" class="form-control" required>
                    <option value="">— اختر —</option>
                    <?php foreach (['نقداً', 'بطاقة ائتمان', 'تحويل بنكي', 'محفظة إلكترونية', 'شيك'] as $pm): ?>
                        <option value="<?= $pm ?>"
                            <?= ($transaction['payment_method'] ?? '') === $pm ? 'selected' : '' ?>>
                            <?= $pm ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- المبلغ -->
            <div class="form-group">
                <label class="form-label">المبلغ <span class="required">*</span></label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required
                       value="<?= htmlspecialchars($transaction['amount'] ?? '') ?>"
                       placeholder="0.00">
            </div>

            <!-- المرفق -->
            <div class="form-group">
                <label class="form-label">مسار المرفق</label>
                <input type="text" name="attachment" class="form-control"
                       value="<?= htmlspecialchars($transaction['attachment'] ?? '') ?>"
                       placeholder="/uploads/transactions/file.pdf">
            </div>

            <!-- ملاحظات -->
            <div class="form-group form-group--full">
                <label class="form-label">ملاحظات</label>
                <textarea name="notes" class="form-control" rows="3"
                          placeholder="أي ملاحظات إضافية..."><?= htmlspecialchars($transaction['notes'] ?? '') ?></textarea>
            </div>

        </div><!-- /.form-grid -->

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? '💾 حفظ التعديلات' : '➕ إضافة المعاملة' ?>
            </button>
            <?php if (!empty($transaction['receipt_id'])): ?>
                <a href="<?= APP_URL ?>/receipt/show?id=<?= $transaction['receipt_id'] ?>" class="btn btn-secondary">إلغاء</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/transactions" class="btn btn-secondary">إلغاء</a>
            <?php endif; ?>
        </div>

    </form>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
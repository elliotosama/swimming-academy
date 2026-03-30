<?php // views/admin/receipts/create.php  (also used as edit.php with $isEdit = true)
require ROOT . '/views/includes/layout_top.php';

$title = $isEdit ? 'تعديل الإيصال' : 'إيصال جديد';
$action = $isEdit
    ? APP_URL . '/receipt/edit?id=' . $receipt['id']
    : APP_URL . '/receipt/create';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $title ?></h1>
        <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">← رجوع</a>
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

<div class="card">
    <form method="POST" action="<?= $action ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-grid">

            <!-- العميل -->
            <div class="form-group">
                <label class="form-label">العميل <span class="required">*</span></label>
                <select name="client_id" class="form-control" required>
                    <option value="">— اختر العميل —</option>
                    <?php foreach (($clients ?? []) as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= ($receipt['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- الفرع -->
            <div class="form-group">
                <label class="form-label">الفرع <span class="required">*</span></label>
                <select name="branch_id" class="form-control" required>
                    <option value="">— اختر الفرع —</option>
                    <?php foreach (($branches ?? []) as $b): ?>
                        <option value="<?= $b['id'] ?>"
                            <?= ($receipt['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['branch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- الكابتن -->
            <div class="form-group">
                <label class="form-label">الكابتن</label>
                <select name="captain_id" class="form-control">
                    <option value="">— اختر الكابتن —</option>
                    <?php foreach (($captains ?? []) as $u): ?>
                        <option value="<?= $u['id'] ?>"
                            <?= ($receipt['captain_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- الخطة -->
            <div class="form-group">
                <label class="form-label">الخطة</label>
                <select name="plan_id" class="form-control">
                    <option value="">— اختر الخطة —</option>
                    <?php foreach (($plans ?? []) as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            <?= ($receipt['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['plan_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- المستوى -->
            <div class="form-group">
                <label class="form-label">المستوى</label>
                <input type="number" name="level" class="form-control" min="1" max="9"
                       value="<?= htmlspecialchars($receipt['level'] ?? '') ?>"
                       placeholder="1 - 9">
            </div>

            <!-- وقت التمرين -->
            <div class="form-group">
                <label class="form-label">وقت التمرين</label>
                <input type="time" name="exercise_time" class="form-control"
                       value="<?= htmlspecialchars($receipt['exercise_time'] ?? '') ?>">
            </div>

            <!-- أول جلسة -->
            <div class="form-group">
                <label class="form-label">تاريخ أول جلسة</label>
                <input type="date" name="first_session" class="form-control"
                       value="<?= htmlspecialchars($receipt['first_session'] ?? '') ?>">
            </div>

            <!-- آخر جلسة -->
            <div class="form-group">
                <label class="form-label">تاريخ آخر جلسة</label>
                <input type="date" name="last_session" class="form-control"
                       value="<?= htmlspecialchars($receipt['last_session'] ?? '') ?>">
            </div>

            <!-- جلسة التجديد -->
            <div class="form-group">
                <label class="form-label">تاريخ جلسة التجديد</label>
                <input type="date" name="renewal_session" class="form-control"
                       value="<?= htmlspecialchars($receipt['renewal_session'] ?? '') ?>">
            </div>

            <!-- نوع التجديد -->
            <div class="form-group">
                <label class="form-label">نوع التجديد</label>
                <select name="renewal_type" class="form-control">
                    <option value="">— اختر —</option>
                    <?php foreach (['شهري', 'ربع سنوي', 'نصف سنوي', 'سنوي'] as $rt): ?>
                        <option value="<?= $rt ?>"
                            <?= ($receipt['renewal_type'] ?? '') === $rt ? 'selected' : '' ?>>
                            <?= $rt ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- حالة الإيصال -->
            <div class="form-group">
                <label class="form-label">حالة الإيصال</label>
                <select name="receipt_status" class="form-control">
                    <option value="not_completed" <?= ($receipt['receipt_status'] ?? '') === 'not_completed' ? 'selected' : '' ?>>غير مكتمل</option>
                    <option value="pending"       <?= ($receipt['receipt_status'] ?? '') === 'pending'       ? 'selected' : '' ?>>معلّق</option>
                    <option value="completed"     <?= ($receipt['receipt_status'] ?? '') === 'completed'     ? 'selected' : '' ?>>مكتمل</option>
                </select>
            </div>

            <!-- مسار PDF -->
            <div class="form-group form-group--full">
                <label class="form-label">مسار ملف PDF</label>
                <input type="text" name="pdf_path" class="form-control"
                       value="<?= htmlspecialchars($receipt['pdf_path'] ?? '') ?>"
                       placeholder="/uploads/receipts/filename.pdf">
            </div>

        </div><!-- /.form-grid -->

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? '💾 حفظ التعديلات' : '➕ إنشاء الإيصال' ?>
            </button>
            <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">إلغاء</a>
        </div>

    </form>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
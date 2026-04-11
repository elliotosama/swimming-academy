<?php
// views/admin/branches/_form.php
// Required vars: $branch, $errors, $isEdit, $action, $pageTitle, $breadcrumb

$days = [
    'Sunday'    => 'الأحد',
    'Monday'    => 'الاثنين',
    'Tuesday'   => 'الثلاثاء',
    'Wednesday' => 'الأربعاء',
    'Thursday'  => 'الخميس',
    'Friday'    => 'الجمعة',
    'Saturday'  => 'السبت',
];

function dayChecked(mixed $field, string $day): bool {
    if (empty($field)) return false;
    if (is_array($field)) return in_array($day, $field, true);
    return in_array($day, explode(',', $field), true);
}

require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $isEdit ? '✏️ تعديل الفرع' : '➕ فرع جديد' ?></h1>
        <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="<?= APP_URL ?>/admin/branches" class="btn btn-secondary">← رجوع</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        ⚠️ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card">
    <form method="POST" action="<?= APP_URL . $action ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-body">

            <!-- ── المعلومات الأساسية ── -->
            <p class="section-title">المعلومات الأساسية</p>

            <div class="form-row">
                <div class="field">
                    <label for="branch_name">اسم الفرع <span class="required">*</span></label>
                    <div class="input-wrap">
                        <input type="text" id="branch_name" name="branch_name"
                               placeholder="مثال: فرع الرياض الرئيسي"
                               value="<?= htmlspecialchars($branch['branch_name'] ?? '') ?>"
                               required>
                        <span class="icon">🏢</span>
                    </div>
                </div>
<div class="field">
    <label for="country">الدولة <span class="required">*</span></label>
    <div class="input-wrap">
        <select id="country" name="country" required>
            <option value="">— اختر الدولة —</option>
            <?php foreach ($countries as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>"
                    <?= (($branch['country'] ?? '') === $c['country']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['country']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="icon">🌍</span>
    </div>
</div>
            </div>

            <!-- ── الحالة ── -->
            <div class="field">
                <label>حالة الفرع</label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="visible" value="1"
                            <?= (($branch['visible'] ?? 1) == 1) ? 'checked' : '' ?>>
                        ✅ نشط
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="visible" value="0"
                            <?= (($branch['visible'] ?? 1) == 0) ? 'checked' : '' ?>>
                        ❌ معطّل
                    </label>
                </div>
            </div>

            <!-- ── أيام العمل ── -->
            <p class="section-title" style="margin-top:1.6rem">أيام العمل</p>
            <p style="font-size:.82rem;color:var(--muted);margin-bottom:1.2rem">
                حدّد أيام العمل لكل وردية. اترك الوردية فارغة إن لم تكن مستخدمة.
            </p>

            <div class="shifts-grid">
                <?php foreach ([1, 2, 3] as $shift):
                    $field = $branch["working_days{$shift}"] ?? [];
                ?>
                    <div class="shift-block">
                        <span class="shift-name">وردية <?= $shift ?></span>
                        <div class="day-checks">
                            <?php foreach ($days as $en => $ar): ?>
                                <label class="day-check-label">
                                    <input type="checkbox"
                                           name="working_days<?= $shift ?>[]"
                                           value="<?= $en ?>"
                                        <?= dayChecked($field, $en) ? 'checked' : '' ?>>
                                    <?= $ar ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ── الإجراءات ── -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? '💾 حفظ التعديلات' : '✅ إنشاء الفرع' ?>
                </button>
                <a href="<?= APP_URL ?>/admin/branches" class="btn btn-secondary">إلغاء</a>
            </div>

        </div>
    </form>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
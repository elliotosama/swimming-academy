<?php // views/admin/captains/create.php
require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🧑‍✈️ إضافة كابتن جديد</h1>
        <p class="breadcrumb">لوحة التحكم · الكباتن · إضافة كابتن جديد</p>
    </div>
    <a href="<?= APP_URL ?>/admin/captains" class="btn btn-secondary">
        → رجوع
    </a>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card" style="max-width:540px;">
    <form method="POST" action="<?= APP_URL ?>/admin/captains/create">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-group">
            <label class="form-label" for="captain_name">
                اسم الكابتن <span style="color:var(--danger)">*</span>
            </label>
            <input type="text"
                   id="captain_name"
                   name="captain_name"
                   class="form-control"
                   value="<?= htmlspecialchars($captain['captain_name'] ?? '') ?>"
                   required
                   minlength="2"
                   placeholder="أدخل اسم الكابتن">
        </div>

        <div class="form-group">
            <label class="form-label" for="phone_number">رقم الهاتف</label>
            <input type="tel"
                   id="phone_number"
                   name="phone_number"
                   class="form-control"
                   value="<?= htmlspecialchars($captain['phone_number'] ?? '') ?>"
                   placeholder="مثال: 0501234567">
        </div>

        <div class="form-group">
            <label class="form-label" for="visible">الحالة</label>
            <select id="visible" name="visible" class="form-control">
                <option value="1" <?= ($captain['visible'] ?? 1) == 1 ? 'selected' : '' ?>>نشط</option>
                <option value="0" <?= ($captain['visible'] ?? 1) == 0 ? 'selected' : '' ?>>معطّل</option>
            </select>
        </div>

        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary">💾 حفظ</button>
            <a href="<?= APP_URL ?>/admin/captains" class="btn btn-secondary">إلغاء</a>
        </div>

    </form>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
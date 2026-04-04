<?php // views/admin/captains/edit.php
require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">✏️ تعديل الكابتن</h1>
    <p class="breadcrumb">لوحة التحكم · الكباتن · تعديل</p>
  </div>
  <a href="<?= APP_URL ?>/admin/captains" class="btn btn-secondary">
    → رجوع
  </a>
</div>

<div style="margin: 10px;">

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card" style="padding: 10px;">
    <form method="POST" action="<?= APP_URL ?>/admin/captains/edit?id=<?= (int) $captain['id'] ?>">
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

        <!-- ── Branch assignment ──────────────────────────────────────────── -->
        <div class="form-group">
            <label class="form-label">الفروع المُعيَّنة</label>
            <div style="display:flex;flex-direction:column;gap:6px;padding:8px;
                        border:1px solid var(--border);border-radius:6px;
                        max-height:200px;overflow-y:auto;">
                <?php if (empty($branches)): ?>
                    <span style="color:var(--muted);font-size:.85rem">لا توجد فروع مسجّلة.</span>
                <?php else: ?>
                    <?php foreach ($branches as $branch): ?>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox"
                                   name="branch_ids[]"
                                   value="<?= $branch['id'] ?>"
                                   <?= in_array($branch['id'], $assignedIds ?? []) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($branch['branch_name']) ?>
                            <?php if (!$branch['visible']): ?>
                                <span style="font-size:.75rem;color:var(--muted)">(معطّل)</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
            <a href="<?= APP_URL ?>/admin/captains" class="btn btn-secondary">إلغاء</a>
        </div>

    </form>
</div>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
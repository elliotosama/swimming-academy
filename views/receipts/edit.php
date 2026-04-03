<?php
// views/receipts/edit.php
require ROOT . '/views/includes/layout_top.php';

/*
 * Role gate — set $isAdmin = true anywhere upstream (session, auth helper, etc.)
 * Example: $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
 *
 * Fields editable by anyone : first_session, branch_id, captain_id
 * Fields editable by admin  : everything else
 */
$isAdmin = $isAdmin ?? false;          // passed in by the controller
$action  = APP_URL . '/receipt/edit?id=' . $receipt['id'];

/* Helper — returns 'disabled' string when the current user cannot edit this field */
function dis(bool $adminOnly, bool $isAdmin): string {
    return ($adminOnly && !$isAdmin) ? 'disabled' : '';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:          #0f1117;
    --surface:     #181c27;
    --surface-2:   #1e2334;
    --border:      #2a3047;
    --border-focus:#4f7cff;
    --accent:      #4f7cff;
    --accent-dim:  #2a3f7a;
    --success:     #22c55e;
    --danger:      #ef4444;
    --warning:     #f59e0b;
    --text:        #e8eaf0;
    --text-muted:  #7a84a0;
    --text-label:  #a0a9c0;
    --radius:      10px;
    --transition:  0.2s ease;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Cairo', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    direction: rtl;
  }

  /* ── Page wrapper ── */
  .receipt-page {
    max-width: 980px;
    margin: 0 auto;
    padding: 32px 20px 60px;
  }

  /* ── Header ── */
  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
  }
  .page-header h1 { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -0.3px; }
  .breadcrumb     { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

  .role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
  }
  .role-badge.admin { background: rgba(79,124,255,0.15); color: var(--accent); border: 1px solid var(--accent-dim); }
  .role-badge.user  { background: rgba(122,132,160,0.12); color: var(--text-muted); border: 1px solid var(--border); }

  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-muted);
    font-family: 'Cairo', sans-serif;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    transition: all var(--transition);
  }
  .btn-back:hover { background: var(--surface); color: var(--text); border-color: var(--accent); }

  /* ── Alerts ── */
  .alert {
    padding: 14px 18px;
    border-radius: var(--radius);
    margin-bottom: 20px;
    font-size: 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.6;
  }
  .alert-error   { background: #2a1515; border: 1px solid #5a2020; color: #fca5a5; }
  .alert-success { background: #0f2a1a; border: 1px solid #1a5c30; color: #86efac; }
  .alert-info    { background: rgba(79,124,255,0.08); border: 1px solid var(--accent-dim); color: var(--text-muted); }

  /* ── Section cards ── */
  .form-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    margin-bottom: 20px;
    overflow: hidden;
    animation: slideUp 0.35s ease both;
  }
  .form-section:nth-child(1) { animation-delay: 0.05s; }
  .form-section:nth-child(2) { animation-delay: 0.10s; }
  .form-section:nth-child(3) { animation-delay: 0.15s; }
  .form-section:nth-child(4) { animation-delay: 0.20s; }
  @keyframes slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 22px;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
  }
  .section-icon  { width: 32px; height: 32px; border-radius: 8px; background: var(--accent-dim); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
  .section-title { font-size: 14px; font-weight: 600; color: var(--text); }

  /* Lock badge shown next to section title when user can't edit that section */
  .section-lock {
    margin-right: auto;   /* pushes it to the left (RTL = right side visually) */
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: var(--text-muted);
    background: var(--border);
    padding: 3px 10px;
    border-radius: 999px;
  }

  .section-body  { padding: 22px; }

  /* ── Grid ── */
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 22px; }
  .form-grid .full { grid-column: 1 / -1; }
  @media (max-width: 640px) {
    .form-grid      { grid-template-columns: 1fr; }
    .form-grid .full { grid-column: 1; }
  }

  /* ── Field ── */
  .form-field { display: flex; flex-direction: column; gap: 7px; }

  .form-label {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-label);
    letter-spacing: 0.3px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .form-label .req   { color: var(--danger); margin-right: 3px; }
  .form-label .lock  { font-size: 11px; opacity: 0.5; }

  .form-control {
    width: 100%;
    padding: 10px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text);
    font-family: 'Cairo', sans-serif;
    font-size: 14px;
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition);
    appearance: none;
  }
  .form-control:focus {
    border-color: var(--border-focus);
    box-shadow: 0 0 0 3px rgba(79,124,255,0.15);
  }
  .form-control::placeholder { color: var(--text-muted); }

  select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a84a0' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 12px center;
    padding-left: 34px;
  }

  /* ── Disabled / locked field styling ── */
  .form-control:disabled,
  .form-control[disabled] {
    background: rgba(26,29,45,0.6);
    border-color: rgba(42,48,71,0.5);
    color: var(--text-muted);
    cursor: not-allowed;
    opacity: 0.75;
  }
  select.form-control:disabled {
    /* keep the chevron but dim it */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234a526a' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  }
  /* Wrapper that adds a lock stripe on the left for disabled fields */
  .field-locked .form-control:disabled {
    border-right: 2px solid var(--border);
  }

  /* ── Hint text ── */
  .field-hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

  /* ── Day error ── */
  .day-error { display: none; align-items: center; gap: 8px; padding: 10px 14px; background: #2a1515; border: 1px solid #5a2020; border-radius: var(--radius); color: #fca5a5; font-size: 13px; margin-top: 8px; }
  .day-error.visible { display: flex; }

  /* ── Payment evidence toggle ── */
  #evidence-field         { display: none; }
  #evidence-field.visible { display: flex; }

  /* ── Toggle switch ── */
  .toggle-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: border-color var(--transition);
  }
  .toggle-row:not(.locked) { cursor: pointer; user-select: none; }
  .toggle-row:not(.locked):hover { border-color: var(--accent); }
  .toggle-row.locked { opacity: 0.6; cursor: not-allowed; }
  .toggle-row input[type="checkbox"] { display: none; }
  .toggle-thumb {
    width: 38px; height: 20px;
    background: var(--border);
    border-radius: 999px;
    position: relative;
    flex-shrink: 0;
    transition: background var(--transition);
  }
  .toggle-thumb::after {
    content: '';
    position: absolute;
    top: 3px; right: 3px;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: #fff;
    transition: transform var(--transition);
  }
  .toggle-row input:checked + .toggle-thumb             { background: var(--accent); }
  .toggle-row input:checked + .toggle-thumb::after      { transform: translateX(-18px); }
  .toggle-label { font-size: 13px; color: var(--text-muted); }

  /* ── Computed fields ── */
  .computed-field .form-control {
    background: rgba(79,124,255,0.05);
    border-color: var(--accent-dim);
    color: var(--accent);
    font-weight: 600;
  }

  /* ── Submit area ── */
  .form-actions { display: flex; gap: 12px; justify-content: flex-end; padding: 24px 0 0; }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 26px;
    border-radius: var(--radius);
    font-family: 'Cairo', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all var(--transition);
    text-decoration: none;
  }
  .btn-primary   { background: var(--accent); color: #fff; box-shadow: 0 4px 20px rgba(79,124,255,0.35); }
  .btn-primary:hover { background: #3a68e8; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(79,124,255,0.45); }
  .btn-secondary { background: var(--surface-2); color: var(--text-muted); border: 1px solid var(--border); }
  .btn-secondary:hover { color: var(--text); border-color: var(--accent); }
</style>
</head>
<body>

<div class="receipt-page">

  <!-- ── Header ── -->
  <div class="page-header">
    <div>
      <h1>تعديل الإيصال</h1>
      <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
      <span class="role-badge <?= $isAdmin ? 'admin' : 'user' ?>">
        <?= $isAdmin ? '🔓 مدير — تعديل كامل' : '🔒 مستخدم — تعديل محدود' ?>
      </span>
    </div>
    <a href="<?= APP_URL ?>/receipts" class="btn-back">← رجوع</a>
  </div>

  <!-- ── Info banner for non-admins ── -->
  <?php if (!$isAdmin): ?>
  <div class="alert alert-info">
    ℹ️ يمكنك تعديل <strong>أول جلسة</strong> و<strong>الفرع</strong> و<strong>الكابتن</strong> فقط.
    للتعديل على بيانات أخرى يرجى التواصل مع الإدارة.
  </div>
  <?php endif; ?>

  <!-- ── Flash / Validation alerts ── -->
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

  <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" id="receiptForm">

    <!-- ══════════════════════════════════
         § 1 — بيانات العميل  (admin only)
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">👤</div>
        <span class="section-title">بيانات العميل</span>
        <?php if (!$isAdmin): ?><span class="section-lock">🔒 للقراءة فقط</span><?php endif; ?>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              اسم العميل <?= $isAdmin ? '<span class="req">*</span>' : '<span class="lock">🔒</span>' ?>
            </label>
            <input type="text" name="client_name" id="client_name_input" class="form-control"
                   placeholder="الاسم الكامل (3 كلمات على الأقل)"
                   value="<?= htmlspecialchars($receipt['client_name'] ?? '') ?>"
                   <?= dis(true, $isAdmin) ?>
                   <?= $isAdmin ? 'required' : '' ?>>
            <?php if ($isAdmin): ?>
              <span class="field-hint">يجب إدخال 3 كلمات على الأقل</span>
            <?php endif; ?>
          </div>

          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              هاتف العميل <?= $isAdmin ? '<span class="req">*</span>' : '<span class="lock">🔒</span>' ?>
            </label>
            <input type="text" name="phone" class="form-control"
                   placeholder="رقم الهاتف"
                   pattern="[0-9]{8,11}"
                   value="<?= htmlspecialchars($receipt['phone_number'] ?? '') ?>"
                   <?= dis(true, $isAdmin) ?>
                   <?= $isAdmin ? 'required' : '' ?>>
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         § 2 — تفاصيل الاشتراك
         branch_id + captain_id = editable by all
         plan_id + level        = admin only
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">📋</div>
        <span class="section-title">تفاصيل الاشتراك</span>
        <?php if (!$isAdmin): ?><span class="section-lock">🔒 الخطة والمستوى للقراءة فقط</span><?php endif; ?>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <!-- الفرع — editable by all -->
          <div class="form-field">
            <label class="form-label">الفرع <span class="req">*</span></label>
            <select name="branch_id" id="branch" class="form-control" required>
              <option value="">— اختر الفرع —</option>
              <?php foreach (($branches ?? []) as $b): ?>
                <option value="<?= $b['id'] ?>"
                  <?= ($receipt['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($b['branch_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- الخطة — admin only -->
          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              الخطة / العرض <?= $isAdmin ? '<span class="req">*</span>' : '<span class="lock">🔒</span>' ?>
            </label>
            <select name="plan_id" id="price" class="form-control"
                    <?= dis(true, $isAdmin) ?>
                    <?= $isAdmin ? 'required' : '' ?>>
              <option value="">— اختر الخطة —</option>
              <?php foreach (($plans ?? []) as $p): ?>
                <option value="<?= $p['id'] ?>"
                  data-price="<?= (float) $p['price'] ?>"
                  data-number_of_sessions="<?= (int) $p['number_of_sessions'] ?>"
                  <?= ($receipt['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['description']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- الكابتن — editable by all -->
          <div class="form-field">
            <label class="form-label">الكابتن</label>
            <select name="captain_id" id="captain" class="form-control">
              <option value="">— اختر الكابتن —</option>
              <?php foreach (($captains ?? []) as $ca): ?>
                <option value="<?= $ca['id'] ?>"
                  <?= ($receipt['captain_id'] ?? '') == $ca['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($ca['captain_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- المستوى — admin only -->
          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              المستوى <?= !$isAdmin ? '<span class="lock">🔒</span>' : '' ?>
            </label>
            <input type="number" name="level" class="form-control"
                   min="1" max="9" placeholder="1 – 9"
                   value="<?= htmlspecialchars($receipt['level'] ?? '') ?>"
                   <?= dis(true, $isAdmin) ?>>
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         § 3 — الجلسات
         first_session = editable by all
         exercise_time = admin only
         renewal/last  = computed (always readonly)
         double toggle = admin only
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">📅</div>
        <span class="section-title">الجلسات</span>
        <?php if (!$isAdmin): ?><span class="section-lock">🔒 وقت التمرين للقراءة فقط</span><?php endif; ?>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <!-- أول جلسة — editable by all -->
          <div class="form-field">
            <label class="form-label">تاريخ أول جلسة <span class="req">*</span></label>
            <input type="date" name="first_session" id="start_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['first_session'] ?? '') ?>" required>
          </div>

          <!-- وقت التمرين — admin only -->
          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              وقت التمرين <?= !$isAdmin ? '<span class="lock">🔒</span>' : '' ?>
            </label>
            <input type="time" name="exercise_time" class="form-control"
                   value="<?= htmlspecialchars($receipt['exercise_time'] ?? '') ?>"
                   <?= dis(true, $isAdmin) ?>>
          </div>

          <!-- جلسة التجديد — always computed/readonly -->
          <div class="form-field computed-field">
            <label class="form-label">تاريخ جلسة التجديد</label>
            <input type="text" name="renewal_session" id="renewal_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['renewal_session'] ?? '') ?>" readonly>
          </div>

          <!-- آخر جلسة — always computed/readonly -->
          <div class="form-field computed-field">
            <label class="form-label">تاريخ آخر جلسة</label>
            <input type="text" name="last_session" id="last_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['last_session'] ?? '') ?>" readonly>
          </div>

          <!-- Double sessions — admin only -->
          <div class="form-field full">
            <?php if ($isAdmin): ?>
              <label class="toggle-row" for="double">
                <input type="checkbox" name="double" id="double"
                       <?= !empty($receipt['double']) ? 'checked' : '' ?>>
                <span class="toggle-thumb"></span>
                <span class="toggle-label">جلستان في اليوم (Double Session)</span>
              </label>
            <?php else: ?>
              <label class="toggle-row locked">
                <input type="checkbox" name="double" id="double"
                       <?= !empty($receipt['double']) ? 'checked' : '' ?> disabled>
                <span class="toggle-thumb"></span>
                <span class="toggle-label">جلستان في اليوم (Double Session) 🔒</span>
              </label>
            <?php endif; ?>
          </div>

          <div class="day-error full" id="day_error">
            ❌ هذا الفرع لا يعمل في اليوم المختار
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         § 4 — الدفع  (admin only)
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">💳</div>
        <span class="section-title">الدفع</span>
        <?php if (!$isAdmin): ?><span class="section-lock">🔒 للقراءة فقط</span><?php endif; ?>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              المبلغ المدفوع <?= $isAdmin ? '<span class="req">*</span>' : '<span class="lock">🔒</span>' ?>
            </label>
            <input type="number" name="amount" id="paidAmount" class="form-control"
                   placeholder="0" min="0"
                   value="<?= htmlspecialchars($receipt['amount'] ?? 0) ?>"
                   <?= dis(true, $isAdmin) ?>
                   <?= $isAdmin ? 'required' : '' ?>>
          </div>

          <div class="form-field computed-field">
            <label class="form-label">المتبقي</label>
            <input type="number" name="remaining" id="remainingAmount" class="form-control"
                   value="<?= htmlspecialchars($receipt['remaining'] ?? 0) ?>" min="0" readonly>
          </div>

          <div class="form-field <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              طريقة الدفع <?= $isAdmin ? '<span class="req">*</span>' : '<span class="lock">🔒</span>' ?>
            </label>
            <select name="payment_method" id="payment_method" class="form-control"
                    <?= dis(true, $isAdmin) ?>
                    <?= $isAdmin ? 'required' : '' ?>>
              <option value="">— اختر —</option>
              <option value="cash"          <?= ($receipt['payment_method'] ?? '') === 'cash'          ? 'selected' : '' ?>>نقداً</option>
              <option value="instapay"      <?= ($receipt['payment_method'] ?? '') === 'instapay'      ? 'selected' : '' ?>>InstaPay</option>
              <option value="vodafone_cash" <?= ($receipt['payment_method'] ?? '') === 'vodafone_cash' ? 'selected' : '' ?>>Vodafone Cash</option>
              <option value="bank_transfer" <?= ($receipt['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>تحويل بنكي</option>
            </select>
          </div>

          <!-- إثبات الدفع — visible only for non-cash; admin only -->
          <div class="form-field" id="evidence-field">
            <label class="form-label">
              إثبات الدفع <?= $isAdmin ? '<span class="req">*</span>' : '<span class="lock">🔒</span>' ?>
            </label>
            <input type="file" name="transaction_evidence" id="transaction_evidence"
                   class="form-control" accept="image/*,application/pdf"
                   <?= dis(true, $isAdmin) ?>>
            <span class="field-hint">صورة أو ملف PDF</span>
            <?php if (!empty($receipt['transaction_evidence'])): ?>
              <span class="field-hint">
                الملف الحالي:
                <a href="<?= htmlspecialchars($receipt['transaction_evidence']) ?>"
                   target="_blank" style="color:var(--accent)">عرض الملف</a>
              </span>
            <?php endif; ?>
          </div>

          <div class="form-field full <?= !$isAdmin ? 'field-locked' : '' ?>">
            <label class="form-label">
              ملاحظات <?= !$isAdmin ? '<span class="lock">🔒</span>' : '' ?>
            </label>
            <input type="text" name="notes" class="form-control"
                   placeholder="أي ملاحظات إضافية..."
                   value="<?= htmlspecialchars($receipt['notes'] ?? '') ?>"
                   <?= dis(true, $isAdmin) ?>>
          </div>

        </div>
      </div>
    </div>

    <!-- ── Actions ── -->
    <div class="form-actions">
      <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">إلغاء</a>
      <button type="submit" class="btn btn-primary">💾 حفظ التعديلات</button>
    </div>

  </form>
</div>



</body>
</html>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
<?php
// views/receipts/create.php  (also used as edit.php with $isEdit = true)
require ROOT . '/views/includes/layout_top.php';

$formTitle = $isEdit ? 'تعديل الإيصال' : 'إيصال جديد';
$action    = $isEdit
    ? APP_URL . '/receipt/edit?id=' . $receipt['id']
    : APP_URL . '/receipt/create';

$db = get_db();
$minPaymentRow    = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'min_payment_amount' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$minPaymentAmount = $minPaymentRow ? (float)$minPaymentRow['setting_value'] : 400;

$todayDate = date('Y-m-d');
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
  .receipt-page { max-width: 980px; margin: 0 auto; padding: 32px 20px 60px; }

  .page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 32px; padding-bottom: 20px; border-bottom: 1px solid var(--border);
  }
  .page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
  .breadcrumb { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

  .btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; background: var(--surface-2);
    border: 1px solid var(--border); border-radius: var(--radius);
    color: var(--text-muted); font-family: 'Cairo', sans-serif;
    font-size: 13px; cursor: pointer; text-decoration: none;
    transition: all var(--transition);
  }
  .btn-back:hover { background: var(--surface); color: var(--text); border-color: var(--accent); }

  .alert { padding: 14px 18px; border-radius: var(--radius); margin-bottom: 20px; font-size: 14px; line-height: 1.6; }
  .alert-error   { background: #2a1515; border: 1px solid #5a2020; color: #fca5a5; }
  .alert-success { background: #0f2a1a; border: 1px solid #1a5c30; color: #86efac; }

  .form-section {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 14px; margin-bottom: 20px; overflow: hidden;
    animation: slideUp 0.35s ease both;
  }
  .form-section:nth-child(1){animation-delay:.05s}
  .form-section:nth-child(2){animation-delay:.10s}
  .form-section:nth-child(3){animation-delay:.15s}
  .form-section:nth-child(4){animation-delay:.20s}
  .form-section:nth-child(5){animation-delay:.25s}
  @keyframes slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .section-header {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 22px; border-bottom: 1px solid var(--border);
    background: var(--surface-2);
  }
  .section-icon {
    width: 32px; height: 32px; border-radius: 8px;
    background: var(--accent-dim); display: flex;
    align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0;
  }
  .section-title { font-size: 14px; font-weight: 600; }
  .section-body  { padding: 22px; }

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 22px; }
  .form-grid .full { grid-column: 1 / -1; }
  @media (max-width: 640px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-grid .full { grid-column: 1; }
  }

  .form-field  { display: flex; flex-direction: column; gap: 7px; }
  .form-label  { font-size: 12.5px; font-weight: 600; color: var(--text-label); letter-spacing: 0.3px; text-transform: uppercase; }
  .form-label .req { color: var(--danger); margin-right: 3px; }

  .form-control {
    width: 100%; padding: 10px 14px;
    background: var(--surface-2); border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--text);
    font-family: 'Cairo', sans-serif; font-size: 14px;
    outline: none; transition: border-color var(--transition), box-shadow var(--transition);
    appearance: none;
  }
  .form-control:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(79,124,255,0.15); }
  .form-control::placeholder { color: var(--text-muted); }
  .form-control:disabled { opacity: 0.45; cursor: not-allowed; }

  select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a84a0' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: left 12px center; padding-left: 34px;
  }

  .phone-row { display: flex; gap: 8px; align-items: stretch; }
  .phone-prefix {
    display: flex; align-items: center; justify-content: center;
    min-width: 68px; padding: 10px 12px;
    background: var(--accent-dim); border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--accent);
    font-family: 'Cairo', sans-serif; font-size: 13px; font-weight: 700;
    letter-spacing: 0.5px; flex-shrink: 0;
    transition: all var(--transition); white-space: nowrap;
  }
  .phone-row .form-control { flex: 1; }

  .field-hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

  .inline-error {
    display: none; align-items: center; gap: 8px;
    padding: 10px 14px; background: #2a1515;
    border: 1px solid #5a2020; border-radius: var(--radius);
    color: #fca5a5; font-size: 13px; margin-top: 8px;
  }
  .inline-error.visible { display: flex; }

  .pay-warn {
    display: none; align-items: center; gap: 8px;
    padding: 10px 14px; background: #2a1a00;
    border: 1px solid #6b4800; border-radius: var(--radius);
    color: #fcd34d; font-size: 13px; margin-top: 8px;
  }
  .pay-warn.visible { display: flex; }

  .no-plans-notice {
    display: none; align-items: center; gap: 8px;
    padding: 10px 14px; background: #1a1a2a;
    border: 1px solid #3a3a6a; border-radius: var(--radius);
    color: #a0a9ff; font-size: 13px; margin-top: 8px;
  }
  .no-plans-notice.visible { display: flex; }

  #evidence-field { display: none; }
  #evidence-field.visible { display: flex; }

  .toggle-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; background: var(--surface-2);
    border: 1px solid var(--border); border-radius: var(--radius);
    cursor: pointer; user-select: none; transition: border-color var(--transition);
  }
  .toggle-row:hover { border-color: var(--accent); }
  .toggle-row input[type="checkbox"] { display: none; }
  .toggle-thumb {
    width: 38px; height: 20px; background: var(--border);
    border-radius: 999px; position: relative; flex-shrink: 0;
    transition: background var(--transition);
  }
  .toggle-thumb::after {
    content: ''; position: absolute; top: 3px; right: 3px;
    width: 14px; height: 14px; border-radius: 50%;
    background: #fff; transition: transform var(--transition);
  }
  .toggle-row input:checked + .toggle-thumb { background: var(--accent); }
  .toggle-row input:checked + .toggle-thumb::after { transform: translateX(-18px); }
  .toggle-label { font-size: 13px; color: var(--text-muted); }

  .computed-field .form-control {
    background: rgba(79,124,255,0.05);
    border-color: var(--accent-dim);
    color: var(--accent);
    font-weight: 600;
  }

  .form-actions { display: flex; gap: 12px; justify-content: flex-end; padding: 24px 0 0; }
  .btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 26px; border-radius: var(--radius);
    font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: 600;
    cursor: pointer; border: none; transition: all var(--transition); text-decoration: none;
  }
  .btn-primary {
    background: var(--accent); color: #fff;
    box-shadow: 0 4px 20px rgba(79,124,255,0.35);
  }
  .btn-primary:hover { background: #3a68e8; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(79,124,255,0.45); }
  .btn-secondary { background: var(--surface-2); color: var(--text-muted); border: 1px solid var(--border); }
  .btn-secondary:hover { color: var(--text); border-color: var(--accent); }
  .btn-email {
    background: #0f2a1a; color: #86efac;
    border: 1px solid #1a5c30;
    box-shadow: 0 4px 16px rgba(34,197,94,0.2);
  }
  .btn-email:hover { background: #163d26; border-color: #22c55e; transform: translateY(-1px); }
  .btn-email:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

  /* ── Email modal overlay ── */
  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.7); z-index: 1000;
    align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
  }
  .modal-overlay.open { display: flex; }
  .modal-box {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 16px; padding: 28px 32px; width: 100%; max-width: 460px;
    animation: slideUp 0.25s ease;
  }
  .modal-title {
    font-size: 17px; font-weight: 700; margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px;
  }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
  .modal-status {
    margin-top: 14px; padding: 10px 14px; border-radius: var(--radius);
    font-size: 13px; display: none;
  }
  .modal-status.success { display: block; background: #0f2a1a; border: 1px solid #1a5c30; color: #86efac; }
  .modal-status.error   { display: block; background: #2a1515; border: 1px solid #5a2020; color: #fca5a5; }
  .spinner {
    display: inline-block; width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff; border-radius: 50%;
    animation: spin 0.7s linear infinite; vertical-align: middle; margin-left: 6px;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div class="receipt-page">

<?php if (!empty($isRenewal)): ?>
<!-- § 0 — Client Search (Renewal only) -->
<div class="form-section" style="margin-bottom:20px;">
  <div class="section-header">
    <div class="section-icon">🔍</div>
    <span class="section-title">البحث عن العميل</span>
  </div>
  <div class="section-body">
    <form method="GET" action="<?= APP_URL ?>/receipt/renew"
          style="display:flex;gap:10px;align-items:flex-end;">
      <div class="form-field" style="flex:1;">
        <label class="form-label">ابحث بالاسم أو رقم الهاتف</label>
        <input type="text" name="search" class="form-control"
               placeholder="مثال: أحمد محمد أو 01012345678"
               value="<?= htmlspecialchars($search ?? '') ?>">
      </div>
      <button type="submit" class="btn btn-primary" style="height:42px;">🔍 بحث</button>
    </form>

    <?php if (!empty($search) && empty($client)): ?>
      <div class="alert alert-error" style="margin-top:12px;">
        ⚠️ لم يتم العثور على عميل بهذا الاسم أو الرقم.
      </div>
    <?php endif; ?>

    <?php if (!empty($client)): ?>
      <div style="margin-top:16px;padding:14px;background:var(--surface-2);
                  border:1px solid var(--border);border-radius:var(--radius);
                  display:flex;gap:24px;flex-wrap:wrap;">
        <div>
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:3px;">الاسم</div>
          <div style="font-weight:700;"><?= htmlspecialchars($client['client_name']) ?></div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:3px;">الهاتف</div>
          <div style="font-weight:700;"><?= htmlspecialchars($client['phone']) ?></div>
        </div>
        <?php if (!empty($client['age'])): ?>
        <div>
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:3px;">العمر</div>
          <div style="font-weight:700;"><?= htmlspecialchars($client['age']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($client['gender'])): ?>
        <div>
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:3px;">الجنس</div>
          <div style="font-weight:700;"><?= htmlspecialchars($client['gender']) ?></div>
        </div>
        <?php endif; ?>
        <div style="margin-right:auto;align-self:center;">
          <span style="background:#0f2a1a;border:1px solid #1a5c30;color:#86efac;
                       padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;">
            ✅ تم العثور على العميل
          </span>
        </div>
      </div>
      <input type="hidden" name="client_id" value="<?= (int)$client['id'] ?>">
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1><?= $formTitle ?></h1>
      <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="<?= APP_URL ?>/receipts" class="btn-back">← رجوع</a>
  </div>

  <!-- Alerts -->
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

    <?php if (empty($isRenewal)): ?>
      <input type="hidden" name="renewal_type"
             value="<?= htmlspecialchars($receipt['renewal_type'] ?? 'new') ?>">
    <?php endif; ?>

    <!-- § 1 — بيانات العميل -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">👤</div>
        <span class="section-title">بيانات العميل</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <div class="form-field">
            <label class="form-label">اسم العميل <span class="req">*</span></label>
            <input type="text" name="client_name" id="client_name_input" class="form-control"
                   placeholder="الاسم الكامل (3 كلمات على الأقل)"
                   value="<?= htmlspecialchars($receipt['client_name'] ?? '') ?>" required>
            <span class="field-hint">يجب إدخال 3 كلمات على الأقل</span>
          </div>

          <div class="form-field">
            <label class="form-label">هاتف العميل <span class="req">*</span></label>
            <div class="phone-row">
              <span class="phone-prefix" id="phone_prefix_badge">
                <?= htmlspecialchars($receipt['country_code'] ?? '—') ?>
              </span>
              <input type="hidden" name="country_code" id="country_code_input"
                     value="<?= htmlspecialchars($receipt['country_code'] ?? '') ?>">
              <input type="hidden" name="full_phone" id="full_phone_input"
                     value="<?= htmlspecialchars($receipt['phone'] ?? '') ?>">
              <input type="text" name="phone_local" id="phone_input" class="form-control"
                     placeholder="رقم الهاتف بدون كود الدولة"
                     inputmode="numeric"
                     value="<?= htmlspecialchars($receipt['phone_local'] ?? '') ?>"
                     required>
            </div>
            <span class="field-hint">كود الدولة يُحدَّد تلقائياً عند اختيار الفرع</span>
          </div>

          <!-- Email — full width -->
          <div class="form-field full">
            <label class="form-label">البريد الإلكتروني للعميل</label>
            <input type="email" name="client_email" id="client_email_input" class="form-control"
                   placeholder="example@email.com"
                   value="<?= htmlspecialchars($receipt['client_email'] ?? '') ?>">
            <span class="field-hint">اختياري — يُستخدم لإرسال الإيصال بالبريد الإلكتروني</span>
          </div>

        </div>
      </div>
    </div>

    <!-- § 2 — تفاصيل الاشتراك -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">📋</div>
        <span class="section-title">تفاصيل الاشتراك</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <div class="form-field">
            <label class="form-label">الفرع <span class="req">*</span></label>
            <select name="branch_id" id="branch" class="form-control" required>
              <option value="">— اختر الفرع —</option>
              <?php foreach (($branches ?? []) as $b): ?>
                <option value="<?= $b['id'] ?>"
                  data-country-id="<?= (int)($b['country_id'] ?? 0) ?>"
                  data-country-code="<?= htmlspecialchars($b['country_code'] ?? '') ?>"
                  <?= ($receipt['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($b['branch_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-field">
            <label class="form-label">الخطة / العرض <span class="req">*</span></label>
            <select name="plan_id" id="price" class="form-control" required>
              <option value="">— اختر الفرع أولاً —</option>
            </select>
            <div class="no-plans-notice" id="no_plans_notice">
              ℹ️ لا توجد خطط مرتبطة ببلد هذا الفرع بعد.
            </div>
          </div>

          <div class="form-field">
            <label class="form-label">الكابتن</label>
            <select name="captain_id" id="captain" class="form-control">
              <option value="">— اختر الفرع أولاً —</option>
            </select>
          </div>

          <div class="form-field">
            <label class="form-label">المستوى</label>
            <select name="level" class="form-control" id="level">
              <?php for ($i = 1; $i <= 6; $i++): ?>
                <option value="<?= $i ?>" <?= ($receipt['level'] ?? 1) == $i ? 'selected' : '' ?>><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>

        </div>
      </div>
    </div>

    <!-- § 3 — الجلسات -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">📅</div>
        <span class="section-title">الجلسات</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <div class="form-field">
            <label class="form-label">تاريخ أول جلسة <span class="req">*</span></label>
            <input type="date" name="first_session" id="start_date" class="form-control"
                   min="<?= $todayDate ?>"
                   value="<?= htmlspecialchars($receipt['first_session'] ?? '') ?>" required>
            <span class="field-hint">لا يمكن اختيار تاريخ في الماضي</span>
          </div>

          <div class="form-field">
            <label class="form-label">وقت التمرين</label>
            <input type="time" name="exercise_time" class="form-control"
                   value="<?= htmlspecialchars($receipt['exercise_time'] ?? '') ?>">
          </div>

          <div class="form-field computed-field">
            <label class="form-label">تاريخ جلسة التجديد</label>
            <input type="text" name="renewal_session" id="renewal_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['renewal_session'] ?? '') ?>" readonly>
          </div>

          <div class="form-field computed-field">
            <label class="form-label">تاريخ آخر جلسة</label>
            <input type="text" name="last_session" id="last_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['last_session'] ?? '') ?>" readonly>
          </div>

          <div class="form-field full">
            <label class="toggle-row">
              <input type="checkbox" name="double" id="double">
              <span class="toggle-thumb"></span>
              <span class="toggle-label">مكثف (جلستان في اليوم)</span>
            </label>
          </div>

          <div class="inline-error full" id="day_error">
            ❌ هذا الفرع لا يعمل في اليوم المختار — أيام العمل:
            <span id="day_error_hint" style="font-weight:600; margin-right:4px;"></span>
          </div>

          <div class="inline-error full" id="past_date_error">
            ❌ لا يمكن اختيار تاريخ في الماضي. يرجى اختيار اليوم أو تاريخ مستقبلي.
          </div>

        </div>
      </div>
    </div>

    <!-- § 4 — الدفع -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">💳</div>
        <span class="section-title">الدفع</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <div class="form-field">
            <label class="form-label">المبلغ المدفوع <span class="req">*</span></label>
            <input type="number" name="amount" id="paidAmount" class="form-control"
                   placeholder="0"
                   value="<?= htmlspecialchars($receipt['amount'] ?? '0') ?>"
                   min="<?= $minPaymentAmount ?>" step="0.01" required>
            <div class="pay-warn" id="pay_warn">
              ⚠️ الحد الأدنى للدفع هو
              <strong id="min_pay_display"><?= number_format($minPaymentAmount, 0) ?></strong>
              جنيه. لا يمكن المتابعة بمبلغ أقل.
            </div>
          </div>

          <div class="form-field computed-field">
            <label class="form-label">المتبقي</label>
            <input type="number" name="remaining" id="remainingAmount" class="form-control"
                   value="<?= htmlspecialchars($receipt['remaining'] ?? '0') ?>" min="0" readonly>
          </div>

          <div class="form-field">
            <label class="form-label">طريقة الدفع <span class="req">*</span></label>
            <select name="payment_method" id="payment_method" class="form-control" required>
              <option value="">— اختر —</option>
              <option value="cash"          <?= ($receipt['payment_method'] ?? '') === 'cash'          ? 'selected' : '' ?>>نقداً</option>
              <option value="instapay"      <?= ($receipt['payment_method'] ?? '') === 'instapay'      ? 'selected' : '' ?>>InstaPay</option>
              <option value="vodafone_cash" <?= ($receipt['payment_method'] ?? '') === 'vodafone_cash' ? 'selected' : '' ?>>Vodafone Cash</option>
              <option value="bank_transfer" <?= ($receipt['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>تحويل بنكي</option>
            </select>
          </div>

          <div class="form-field" id="evidence-field">
            <label class="form-label">إثبات الدفع <span class="req">*</span></label>
            <input type="file" name="transaction_evidence" id="transaction_evidence"
                   class="form-control" accept="image/*,application/pdf">
            <span class="field-hint">صورة أو ملف PDF</span>
          </div>

          <div class="form-field full">
            <label class="form-label">ملاحظات</label>
            <input type="text" name="notes" class="form-control"
                   placeholder="أي ملاحظات إضافية..."
                   value="<?= htmlspecialchars($receipt['notes'] ?? '') ?>">
          </div>

        </div>
      </div>
    </div>

    <div class="form-actions">
      <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">إلغاء</a>
      <?php if ($isEdit && !empty($receipt['id'])): ?>
        <!-- Send email button — only visible when editing an existing receipt -->
        <button type="button" class="btn btn-email" id="sendEmailBtn"
                onclick="openEmailModal()">
          📧 إرسال الإيصال بالبريد
        </button>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary" id="submitBtn">
        <?= $isEdit ? '💾 حفظ التعديلات' : '➕ إنشاء الإيصال' ?>
      </button>
    </div>

  </form>
</div>

<!-- ══════════════════════════════════════════════════════════════
     Email modal
     Shown after save (redirect) OR when editing an existing receipt.
     On new receipt: the preview page (preview.php) shows it there.
════════════════════════════════════════════════════════════════ -->
<?php if ($isEdit && !empty($receipt['id'])): ?>
<div class="modal-overlay" id="emailModal">
  <div class="modal-box">
    <div class="modal-title">📧 إرسال الإيصال بالبريد الإلكتروني</div>

    <div class="form-field" style="margin-bottom:14px;">
      <label class="form-label">البريد الإلكتروني للمستلم <span class="req">*</span></label>
      <input type="email" id="modalEmailInput" class="form-control"
             placeholder="example@email.com"
             value="<?= htmlspecialchars($receipt['client_email'] ?? '') ?>">
      <span class="field-hint">يمكنك تغيير العنوان قبل الإرسال</span>
    </div>

    <div class="modal-status" id="modalStatus"></div>

    <div class="modal-actions">
      <button type="button" class="btn btn-secondary" onclick="closeEmailModal()">إلغاء</button>
      <button type="button" class="btn btn-email" id="modalSendBtn" onclick="sendReceiptEmail()">
        📤 إرسال
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// ═══════════════════════════════════════════════════════════════
//  PHP → JS  data injection
// ═══════════════════════════════════════════════════════════════

/**
 * BRANCH_META[branchId] = { country_id, country_code, days[] }
 * country is NOT stored here anymore — we key plans by country_id directly.
 * This avoids the "Undefined array key country" warning caused by PDO
 * returning c.id overwriting b.id when both are fetched without aliases.
 */
const BRANCH_META = {};
<?php foreach (($branches ?? []) as $b):
    $days = [];
    foreach (['working_days1','working_days2','working_days3'] as $slot) {
        if (!empty($b[$slot])) {
            foreach (array_map('trim', explode(',', $b[$slot])) as $d) {
                if ($d !== '') $days[] = $d;
            }
        }
    }
    $days = array_values(array_unique($days));
    // Safely read country_id — the controller aliases it as 'country_id'
    $countryId = isset($b['country_id']) ? (int)$b['country_id'] : 0;
    $countryCode = isset($b['country_code']) ? $b['country_code'] : '';
?>
BRANCH_META[<?= (int)$b['id'] ?>] = {
    country_id:   <?= $countryId ?>,
    country_code: <?= json_encode($countryCode) ?>,
    days:         <?= json_encode($days) ?>
};
<?php endforeach; ?>

// CAPTAINS_BY_BRANCH[branchId] = [{id, name}, ...]
const CAPTAINS_BY_BRANCH = <?= json_encode($captainsByBranch ?? new stdClass()) ?>;

/**
 * PLANS_BY_COUNTRY_ID[countryId] = [{id, label, price, sessions}, ...]
 * Requires formDropdowns() plans query to JOIN countries and select
 * p.country_id (already present in prices table schema).
 */
const PLANS_BY_COUNTRY_ID = {};
<?php foreach (($plans ?? []) as $p):
    $cid = (int)($p['country_id'] ?? 0);
    if (!$cid) continue;
?>
PLANS_BY_COUNTRY_ID[<?= $cid ?>] = PLANS_BY_COUNTRY_ID[<?= $cid ?>] || [];
PLANS_BY_COUNTRY_ID[<?= $cid ?>].push({
    id:       <?= (int)$p['id'] ?>,
    label:    <?= json_encode($p['description']) ?>,
    price:    <?= (float)$p['price'] ?>,
    sessions: <?= (int)$p['number_of_sessions'] ?>
});
<?php endforeach; ?>

const MIN_PAYMENT  = <?= (float)$minPaymentAmount ?>;
const TODAY        = <?= json_encode($todayDate) ?>;
const SAVED_PLAN_ID    = <?= json_encode((string)($receipt['plan_id']    ?? '')) ?>;
const SAVED_CAPTAIN_ID = <?= json_encode((string)($receipt['captain_id'] ?? '')) ?>;
const RECEIPT_ID   = <?= json_encode((int)($receipt['id'] ?? 0)) ?>;
const SEND_EMAIL_URL = <?= json_encode(APP_URL . '/receipt/send-email') ?>;

// ═══════════════════════════════════════════════════════════════
//  DOM refs
// ═══════════════════════════════════════════════════════════════
const branchSel        = document.getElementById('branch');
const planSel          = document.getElementById('price');
const captainSel       = document.getElementById('captain');
const paidInput        = document.getElementById('paidAmount');
const remainingIn      = document.getElementById('remainingAmount');
const startDateIn      = document.getElementById('start_date');
const renewalIn        = document.getElementById('renewal_date');
const lastDateIn       = document.getElementById('last_date');
const doubleChk        = document.getElementById('double');
const dayErrorEl       = document.getElementById('day_error');
const dayErrorHint     = document.getElementById('day_error_hint');
const pastDateErrorEl  = document.getElementById('past_date_error');
const payMethodSel     = document.getElementById('payment_method');
const evidenceField    = document.getElementById('evidence-field');
const evidenceIn       = document.getElementById('transaction_evidence');
const payWarnEl        = document.getElementById('pay_warn');
const noPlansNotice    = document.getElementById('no_plans_notice');
const minPayDisplay    = document.getElementById('min_pay_display');
const submitBtn        = document.getElementById('submitBtn');
const form             = document.getElementById('receiptForm');
const clientNameIn     = document.getElementById('client_name_input');
const clientEmailIn    = document.getElementById('client_email_input');
const phonePrefixBadge = document.getElementById('phone_prefix_badge');
const countryCodeIn    = document.getElementById('country_code_input');
const phoneLocalIn     = document.getElementById('phone_input');
const fullPhoneIn      = document.getElementById('full_phone_input');

// ═══════════════════════════════════════════════════════════════
//  Helpers
// ═══════════════════════════════════════════════════════════════
function branchMeta() {
    return branchSel.value ? (BRANCH_META[branchSel.value] || null) : null;
}
function selectedSessions() {
    const opt = planSel.options[planSel.selectedIndex];
    return parseInt(opt?.dataset.sessions) || 0;
}
function selectedPrice() {
    const opt = planSel.options[planSel.selectedIndex];
    return parseFloat(opt?.dataset.price) || 0;
}
function formatLocalDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

// ═══════════════════════════════════════════════════════════════
//  Country code badge
// ═══════════════════════════════════════════════════════════════
function updateCountryCode() {
    const meta   = branchMeta();
    const prefix = (meta && meta.country_code) ? meta.country_code : '—';
    phonePrefixBadge.textContent = prefix;
    countryCodeIn.value          = prefix !== '—' ? prefix : '';
    assembleFullPhone();
}

function assembleFullPhone() {
    const prefix = countryCodeIn.value;
    let local    = phoneLocalIn.value.trim();
    if (prefix && local.startsWith('0')) local = local.slice(1);
    fullPhoneIn.value = prefix ? (prefix + local) : local;
}

// ═══════════════════════════════════════════════════════════════
//  Plans dropdown — filtered by branch country_id
// ═══════════════════════════════════════════════════════════════
function populatePlans() {
    const meta      = branchMeta();
    const countryId = meta ? meta.country_id : null;
    const plans     = (countryId && PLANS_BY_COUNTRY_ID[countryId])
                        ? PLANS_BY_COUNTRY_ID[countryId] : [];

    if (noPlansNotice) {
        noPlansNotice.classList.toggle('visible', meta !== null && plans.length === 0);
    }

    planSel.innerHTML = plans.length
        ? '<option value="">— اختر الخطة —</option>'
        : '<option value="">— لا توجد خطط لهذا الفرع —</option>';

    plans.forEach(p => {
        const o = document.createElement('option');
        o.value            = p.id;
        o.dataset.price    = p.price;
        o.dataset.sessions = p.sessions;
        o.textContent      = `${p.label} — ${p.price} (${p.sessions} جلسة)`;
        if (String(p.id) === SAVED_PLAN_ID) o.selected = true;
        planSel.appendChild(o);
    });

    calculateRemaining();
    updateSessionDates();
}

// ═══════════════════════════════════════════════════════════════
//  Captains dropdown
// ═══════════════════════════════════════════════════════════════
function populateCaptains() {
    const branchId = branchSel.value;
    const captains = branchId ? (CAPTAINS_BY_BRANCH[branchId] || []) : [];

    captainSel.innerHTML = captains.length
        ? '<option value="">— اختر الكابتن —</option>'
        : '<option value="">— لا يوجد كباتن لهذا الفرع —</option>';

    captains.forEach(c => {
        const o = document.createElement('option');
        o.value       = c.id;
        o.textContent = c.name;
        if (String(c.id) === SAVED_CAPTAIN_ID) o.selected = true;
        captainSel.appendChild(o);
    });
}

// ═══════════════════════════════════════════════════════════════
//  Payment remaining + validation
// ═══════════════════════════════════════════════════════════════
function calculateRemaining() {
    const price = selectedPrice();
    const paid  = parseFloat(paidInput.value) || 0;
    price > 0 ? paidInput.setAttribute('max', price) : paidInput.removeAttribute('max');
    remainingIn.value = price > 0 ? Math.max(price - paid, 0) : 0;
    validatePayment(paid);
}

function validatePayment(paid) {
    if (paid > 0 && paid < MIN_PAYMENT) {
        payWarnEl.classList.add('visible');
        if (minPayDisplay) minPayDisplay.textContent = MIN_PAYMENT.toLocaleString('ar-EG');
        submitBtn.disabled = true;
    } else {
        payWarnEl.classList.remove('visible');
        if (!dayErrorEl.classList.contains('visible') &&
            !pastDateErrorEl.classList.contains('visible')) {
            submitBtn.disabled = false;
        }
    }
}

// ═══════════════════════════════════════════════════════════════
//  Session date logic
// ═══════════════════════════════════════════════════════════════
function pickActiveDays(startDayName, allowedDays, totalSessions, isDouble) {
    const idx = allowedDays.indexOf(startDayName);
    if (idx === -1) return [];
    const pairStart = idx % 2 === 0 ? idx : idx - 1;
    const pair1 = allowedDays.slice(pairStart, pairStart + 2);
    if (pair1[0] !== startDayName) pair1.reverse();
    if (!isDouble) return totalSessions >= 8 ? pair1 : [startDayName];
    if (totalSessions >= 8) {
        const pair2Start = pairStart === 0 ? 2 : 0;
        return [...new Set([...pair1, ...allowedDays.slice(pair2Start, pair2Start + 2)])];
    }
    return pair1;
}

function buildSessionDates(firstSession, allowedDays, totalSessions, isDouble) {
    const sessionsPerVisit = isDouble ? 2 : 1;
    const totalVisits      = Math.ceil(totalSessions / sessionsPerVisit);
    const start            = new Date(firstSession + 'T00:00:00');
    const startDayName     = start.toLocaleDateString('en-US', { weekday: 'long' });
    const activeDays       = pickActiveDays(startDayName, allowedDays, totalSessions, isDouble);
    if (!activeDays.length) return { renewal: '', last: '' };
    const dates = []; const cursor = new Date(start); let safety = 0;
    while (dates.length < totalVisits && safety < 365) {
        if (activeDays.includes(cursor.toLocaleDateString('en-US', { weekday: 'long' })))
            dates.push(formatLocalDate(cursor));
        cursor.setDate(cursor.getDate() + 1); safety++;
    }
    if (dates.length < 2) return { renewal: '', last: dates[0] ?? '' };
    return { renewal: dates[dates.length - 2], last: dates[dates.length - 1] };
}

function updateSessionDates() {
    const startDate = startDateIn.value;
    renewalIn.value = ''; lastDateIn.value = '';
    dayErrorEl.classList.remove('visible');
    pastDateErrorEl.classList.remove('visible');
    dayErrorHint.textContent = '';
    if (!startDate || !branchSel.value) return;
    if (startDate < TODAY) { pastDateErrorEl.classList.add('visible'); submitBtn.disabled = true; return; }
    const meta = branchMeta();
    if (!meta || !meta.days.length) return;
    const startDayName = new Date(startDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long' });
    if (!meta.days.includes(startDayName)) {
        dayErrorHint.textContent = meta.days.join('، ');
        dayErrorEl.classList.add('visible'); submitBtn.disabled = true; return;
    }
    submitBtn.disabled = false;
    validatePayment(parseFloat(paidInput.value) || 0);
    const total = selectedSessions();
    if (!total) return;
    const result = buildSessionDates(startDate, meta.days, total, doubleChk.checked);
    renewalIn.value = result.renewal; lastDateIn.value = result.last;
}

// ═══════════════════════════════════════════════════════════════
//  Payment evidence toggle
// ═══════════════════════════════════════════════════════════════
function toggleEvidence() {
    const m = payMethodSel.value;
    if (m && m !== 'cash') {
        evidenceField.classList.add('visible'); evidenceIn.required = true;
    } else {
        evidenceField.classList.remove('visible'); evidenceIn.required = false; evidenceIn.value = '';
    }
}

// ═══════════════════════════════════════════════════════════════
//  Email modal
// ═══════════════════════════════════════════════════════════════
function openEmailModal() {
    // Sync the modal email input with whatever is currently in the form field
    const modalInput = document.getElementById('modalEmailInput');
    if (modalInput && clientEmailIn) {
        modalInput.value = clientEmailIn.value;
    }
    const statusEl = document.getElementById('modalStatus');
    if (statusEl) { statusEl.className = 'modal-status'; statusEl.textContent = ''; }
    document.getElementById('emailModal').classList.add('open');
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.remove('open');
}

async function sendReceiptEmail() {
    const emailInput = document.getElementById('modalEmailInput');
    const statusEl   = document.getElementById('modalStatus');
    const sendBtn    = document.getElementById('modalSendBtn');
    const email      = emailInput.value.trim();

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        statusEl.className = 'modal-status error';
        statusEl.textContent = '⚠️ يرجى إدخال بريد إلكتروني صحيح.';
        return;
    }

    sendBtn.disabled = true;
    sendBtn.innerHTML = '⏳ جارٍ الإرسال... <span class="spinner"></span>';
    statusEl.className = 'modal-status'; statusEl.textContent = '';

    try {
        const res  = await fetch(SEND_EMAIL_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ receipt_id: RECEIPT_ID, email: email })
        });
        const data = await res.json();

        if (data.success) {
            statusEl.className = 'modal-status success';
            statusEl.textContent = '✅ تم إرسال الإيصال بنجاح إلى ' + email;
            // Update the email field in the main form so it gets saved on next edit
            if (clientEmailIn) clientEmailIn.value = email;
            sendBtn.innerHTML = '✅ تم الإرسال';
        } else {
            statusEl.className = 'modal-status error';
            statusEl.textContent = '❌ فشل الإرسال: ' + (data.message || 'خطأ غير معروف');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '📤 إرسال';
        }
    } catch (err) {
        statusEl.className = 'modal-status error';
        statusEl.textContent = '❌ تعذّر الاتصال بالخادم. حاول مرة أخرى.';
        sendBtn.disabled = false;
        sendBtn.innerHTML = '📤 إرسال';
    }
}

// Close modal on overlay click
document.getElementById('emailModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEmailModal();
});

// ═══════════════════════════════════════════════════════════════
//  Event listeners
// ═══════════════════════════════════════════════════════════════
branchSel.addEventListener('change', () => {
    updateCountryCode(); populatePlans(); populateCaptains(); updateSessionDates();
});
planSel.addEventListener('change',    () => { calculateRemaining(); updateSessionDates(); });
doubleChk.addEventListener('change',  updateSessionDates);
paidInput.addEventListener('input',   calculateRemaining);
startDateIn.addEventListener('change', updateSessionDates);
payMethodSel.addEventListener('change', toggleEvidence);
phoneLocalIn.addEventListener('input',  assembleFullPhone);

form.addEventListener('submit', e => {
    if (clientNameIn.value.trim().split(/\s+/).length < 3) {
        e.preventDefault(); alert('⚠️ يجب أن يحتوي اسم العميل على 3 كلمات على الأقل.');
        clientNameIn.focus(); return;
    }
    if (startDateIn.value && startDateIn.value < TODAY) {
        e.preventDefault(); alert('⚠️ لا يمكن اختيار تاريخ في الماضي.');
        startDateIn.focus(); return;
    }
    const paid = parseFloat(paidInput.value) || 0;
    if (paid > 0 && paid < MIN_PAYMENT) {
        e.preventDefault(); alert(`⚠️ الحد الأدنى للدفع هو ${MIN_PAYMENT} جنيه.`);
        paidInput.focus(); return;
    }
    assembleFullPhone();
});

// ═══════════════════════════════════════════════════════════════
//  Init
// ═══════════════════════════════════════════════════════════════
(function init() {
    if (branchSel.value) { updateCountryCode(); populatePlans(); populateCaptains(); }
    toggleEvidence();
    calculateRemaining();
    updateSessionDates();
    assembleFullPhone();
    if (minPayDisplay) minPayDisplay.textContent = MIN_PAYMENT.toLocaleString('ar-EG');
})();
</script>

</body>
</html>
<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
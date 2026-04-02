<?php
// views/receipts/create.php  (also used as edit.php with $isEdit = true)
require ROOT . '/views/includes/layout_top.php';

$formTitle  = $isEdit ? 'تعديل الإيصال' : 'إيصال جديد';
$action     = $isEdit
    ? APP_URL . '/receipt/edit?id=' . $receipt['id']
    : APP_URL . '/receipt/create';
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
  .page-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.3px;
  }
  .breadcrumb {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
  }
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
  .btn-back:hover {
    background: var(--surface);
    color: var(--text);
    border-color: var(--accent);
  }

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
  .form-section:nth-child(5) { animation-delay: 0.25s; }
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
  .section-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--accent-dim);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
  }
  .section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
  }

  .section-body {
    padding: 22px;
  }

  /* ── Grid ── */
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 22px;
  }
  .form-grid .full { grid-column: 1 / -1; }
  @media (max-width: 640px) {
    .form-grid { grid-template-columns: 1fr; }
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
  }
  .form-label .req { color: var(--danger); margin-right: 3px; }

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

  /* ── Hint text ── */
  .field-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
  }

  /* ── Day error ── */
  .day-error {
    display: none;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #2a1515;
    border: 1px solid #5a2020;
    border-radius: var(--radius);
    color: #fca5a5;
    font-size: 13px;
    margin-top: 8px;
  }
  .day-error.visible { display: flex; }

  /* ── Payment evidence toggle ── */
  #evidence-field { display: none; }
  #evidence-field.visible { display: flex; }

  /* ── Checkbox toggle ── */
  .toggle-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    user-select: none;
    transition: border-color var(--transition);
  }
  .toggle-row:hover { border-color: var(--accent); }
  .toggle-row input[type="checkbox"] { display: none; }
  .toggle-thumb {
    width: 38px;
    height: 20px;
    background: var(--border);
    border-radius: 999px;
    position: relative;
    flex-shrink: 0;
    transition: background var(--transition);
  }
  .toggle-thumb::after {
    content: '';
    position: absolute;
    top: 3px;
    right: 3px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #fff;
    transition: transform var(--transition);
  }
  .toggle-row input:checked + .toggle-thumb { background: var(--accent); }
  .toggle-row input:checked + .toggle-thumb::after { transform: translateX(-18px); }
  .toggle-label { font-size: 13px; color: var(--text-muted); }

  /* ── Computed fields ── */
  .computed-field .form-control {
    background: rgba(79,124,255,0.05);
    border-color: var(--accent-dim);
    color: var(--accent);
    font-weight: 600;
  }

  /* ── Submit area ── */
  .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding: 24px 0 0;
  }

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
  .btn-primary {
    background: var(--accent);
    color: #fff;
    box-shadow: 0 4px 20px rgba(79,124,255,0.35);
  }
  .btn-primary:hover { background: #3a68e8; transform: translateY(-1px); box-shadow: 0 6px 28px rgba(79,124,255,0.45); }
  .btn-secondary {
    background: var(--surface-2);
    color: var(--text-muted);
    border: 1px solid var(--border);
  }
  .btn-secondary:hover { color: var(--text); border-color: var(--accent); }
</style>
</head>
<body>

<div class="receipt-page">

  <!-- ── Header ── -->
  <div class="page-header">
    <div>
      <h1><?= $formTitle ?></h1>
      <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="<?= APP_URL ?>/receipts" class="btn-back">← رجوع</a>
  </div>

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
         § 1 — بيانات العميل
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">👤</div>
        <span class="section-title">بيانات العميل</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <!-- اسم العميل -->
          <div class="form-field">
            <label class="form-label">اسم العميل <span class="req">*</span></label>
            <input type="text" name="client_name" id="client_name_input" class="form-control"
                   placeholder="الاسم الكامل (3 كلمات على الأقل)"
                   value="<?= htmlspecialchars($receipt['client_name'] ?? '') ?>" required>
            <span class="field-hint">يجب إدخال 3 كلمات على الأقل</span>
          </div>

          <!-- هاتف العميل -->
          <div class="form-field">
            <label class="form-label">هاتف العميل <span class="req">*</span></label>
            <input type="text" name="phone" class="form-control"
                   placeholder="رقم الهاتف بدون كود الدولة"
                   pattern="[0-9]{8,11}"
                   value="<?= htmlspecialchars($receipt['phone_number'] ?? '') ?>" required>
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         § 2 — تفاصيل الاشتراك
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">📋</div>
        <span class="section-title">تفاصيل الاشتراك</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <!-- الفرع -->
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

          <!-- الخطة -->
          <div class="form-field">
            <label class="form-label">الخطة / العرض <span class="req">*</span></label>
            <select name="plan_id" id="price" class="form-control" required>
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

          <!-- الكابتن -->
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

          <!-- المستوى -->
          <div class="form-field">
            <label class="form-label">المستوى</label>
            <input type="number" name="level" class="form-control"
                   min="1" max="9" placeholder="1 – 9"
                   value="<?= htmlspecialchars($receipt['level'] ?? '') ?>">
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         § 3 — الجلسات
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">📅</div>
        <span class="section-title">الجلسات</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <!-- أول جلسة -->
          <div class="form-field">
            <label class="form-label">تاريخ أول جلسة <span class="req">*</span></label>
            <input type="date" name="first_session" id="start_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['first_session'] ?? '') ?>" required>
          </div>

          <!-- وقت التمرين -->
          <div class="form-field">
            <label class="form-label">وقت التمرين</label>
            <input type="time" name="exercise_time" class="form-control"
                   value="<?= htmlspecialchars($receipt['exercise_time'] ?? '') ?>">
          </div>

          <!-- جلسة التجديد (computed) -->
          <div class="form-field computed-field">
            <label class="form-label">تاريخ جلسة التجديد</label>
            <input type="text" name="renewal_session" id="renewal_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['renewal_session'] ?? '') ?>" readonly>
          </div>

          <!-- آخر جلسة (computed) -->
          <div class="form-field computed-field">
            <label class="form-label">تاريخ آخر جلسة</label>
            <input type="text" name="last_session" id="last_date" class="form-control"
                   value="<?= htmlspecialchars($receipt['last_session'] ?? '') ?>" readonly>
          </div>

          <!-- Double sessions toggle -->
          <div class="form-field full">
            <label class="toggle-row">
              <input type="checkbox" name="double" id="double">
              <span class="toggle-thumb"></span>
              <span class="toggle-label">جلستان في اليوم (Double Session)</span>
            </label>
          </div>

          <!-- Day error -->
          <div class="day-error full" id="day_error">
            ❌ هذا الفرع لا يعمل في اليوم المختار
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         § 4 — الدفع
    ══════════════════════════════════ -->
    <div class="form-section">
      <div class="section-header">
        <div class="section-icon">💳</div>
        <span class="section-title">الدفع</span>
      </div>
      <div class="section-body">
        <div class="form-grid">

          <!-- المبلغ المدفوع -->
          <div class="form-field">
            <label class="form-label">المبلغ المدفوع <span class="req">*</span></label>
            <input type="number" name="amount" id="paidAmount" class="form-control"
                   placeholder="0" value="0" min="0" required>
          </div>

          <!-- المتبقي -->
          <div class="form-field computed-field">
            <label class="form-label">المتبقي</label>
            <input type="number" name="remaining" id="remainingAmount" class="form-control"
                   value="0" min="0" readonly>
          </div>

          <!-- طريقة الدفع -->
          <div class="form-field">
            <label class="form-label">طريقة الدفع <span class="req">*</span></label>
            <select name="payment_method" id="payment_method" class="form-control" required>
              <option value="">— اختر —</option>
              <option value="cash"           <?= ($receipt['payment_method'] ?? '') === 'cash'           ? 'selected' : '' ?>>نقداً</option>
              <option value="instapay"       <?= ($receipt['payment_method'] ?? '') === 'instapay'       ? 'selected' : '' ?>>InstaPay</option>
              <option value="vodafone_cash"  <?= ($receipt['payment_method'] ?? '') === 'vodafone_cash'  ? 'selected' : '' ?>>Vodafone Cash</option>
              <option value="bank_transfer"  <?= ($receipt['payment_method'] ?? '') === 'bank_transfer'  ? 'selected' : '' ?>>تحويل بنكي</option>
            </select>
          </div>

          <!-- إثبات الدفع (يظهر فقط إذا الطريقة ليست نقداً) -->
          <div class="form-field" id="evidence-field">
            <label class="form-label">إثبات الدفع <span class="req">*</span></label>
            <input type="file" name="transaction_evidence" id="transaction_evidence"
                   class="form-control" accept="image/*,application/pdf">
            <span class="field-hint">صورة أو ملف PDF</span>
          </div>

          <!-- ملاحظات -->
          <div class="form-field full">
            <label class="form-label">ملاحظات</label>
            <input type="text" name="notes" class="form-control"
                   placeholder="أي ملاحظات إضافية..."
                   value="<?= htmlspecialchars($receipt['notes'] ?? '') ?>">
          </div>

        </div>
      </div>
    </div>

    <!-- ── Actions ── -->
    <div class="form-actions">
      <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">إلغاء</a>
      <button type="submit" class="btn btn-primary">
        <?= $isEdit ? '💾 حفظ التعديلات' : '➕ إنشاء الإيصال' ?>
      </button>
    </div>

  </form>
</div>

<script>
// ═══════════════════════════════════════════════════════
//  Helpers — copied / adapted from your original JS files
// ═══════════════════════════════════════════════════════

function calculateWorkingDays(firstSession, workingDays, planDays, numberOfSessions = 2) {
  let sessionCount = 2;
  let sessionsDate = [firstSession];
  let date = new Date(firstSession);
  let dayOneName = date.toLocaleDateString('en-US', { weekday: 'long' });

  if (numberOfSessions === 2) {
    let dayTwo = workingDays.daysArray[0];
    if (dayTwo === dayOneName) dayTwo = workingDays.daysArray[1];
    while (sessionCount <= planDays) {
      date.setDate(date.getDate() + 1);
      let cur = date.toLocaleDateString('en-US', { weekday: 'long' });
      if (cur === dayTwo || cur === dayOneName) {
        sessionsDate.push(date.toISOString().split('T')[0]);
        sessionCount++;
      }
    }
  } else {
    while (sessionCount <= planDays) {
      date.setDate(date.getDate() + 1);
      let cur = date.toLocaleDateString('en-US', { weekday: 'long' });
      if (cur === dayOneName) {
        sessionsDate.push(date.toISOString().split('T')[0]);
        sessionCount++;
      }
    }
  }
  console.log(sessionsDate);
  return {
    renewal: sessionsDate[sessionsDate.length - 2],
    last:    sessionsDate[sessionsDate.length - 1],
  };
}
// error here
// async function checkWorkingDays(branchId, selectedDate) {
//   const res  = await fetch(`/app/ajax/get_branch_days.php?id=${branchId}`);
//   const data = await res.json();
//   if (!data.days) return false;
//   const selectedDay  = new Date(selectedDate);
//   const dayOneName   = selectedDay.toLocaleDateString('en-US', { weekday: 'long' });
//   for (let key in data.days) {
//     const value = data.days[key];
//     if (!value) continue;
//     const daysArray = value.split(',').map(d => d.trim());
//     if (daysArray.includes(dayOneName)) return { daysArray };
//   }
//   return false;
// }

// ═══════════════════════════════════════════════════════
//  DOM refs
// ═══════════════════════════════════════════════════════
const priceSelect      = document.getElementById('price');
const branchSelect     = document.getElementById('branch');
const paidInput        = document.getElementById('paidAmount');
const remainingInput   = document.getElementById('remainingAmount');
const startDateInput   = document.getElementById('start_date');
const renewalInput     = document.getElementById('renewal_date');
const lastDateInput    = document.getElementById('last_date');
const doubleCheckbox   = document.getElementById('double');
const dayErrorEl       = document.getElementById('day_error');
const paymentMethodSel = document.getElementById('payment_method');
const evidenceField    = document.getElementById('evidence-field');
const evidenceInput    = document.getElementById('transaction_evidence');
const form             = document.getElementById('receiptForm');
const clientNameInput  = document.getElementById('client_name_input');

let numberOfSessionsPerWeek = 1;

// ── getters ──
function getSelectedSessions() {
  const opt = priceSelect.options[priceSelect.selectedIndex];
  return parseInt(opt?.dataset.number_of_sessions) || 0;
}
function getSelectedPrice() {
  const opt = priceSelect.options[priceSelect.selectedIndex];
  return parseFloat(opt?.dataset.price) || 0;
}

// ── remaining calc ──
function calculateRemaining() {
  const price = getSelectedPrice();
  const paid  = parseFloat(paidInput.value) || 0;
  paidInput.setAttribute('max', price);
  remainingInput.value = Math.max(price - paid, 0);
}

// ── session dates ──

// error here
// async function updateSessionDates() {
//   const startDate = startDateInput.value;
//   const branchId  = branchSelect.value;
//   if (!startDate || !branchId) return;

//   const workingDays = await checkWorkingDays(branchId, startDate);
//   if (!workingDays) {
//     dayErrorEl.classList.add('visible');
//     renewalInput.value  = '';
//     lastDateInput.value = '';
//     return;
//   }
//   dayErrorEl.classList.remove('visible');

//   const totalSessions = getSelectedSessions();
//   const result = calculateWorkingDays(startDate, workingDays, totalSessions, numberOfSessionsPerWeek);
//   if (!result) return;
//   renewalInput.value  = result.renewal;
//   lastDateInput.value = result.last;
// }

// ── payment evidence toggle ──
function toggleEvidence() {
  const method = paymentMethodSel.value;
  if (method && method !== 'cash') {
    evidenceField.classList.add('visible');
    evidenceInput.required = true;
  } else {
    evidenceField.classList.remove('visible');
    evidenceInput.required = false;
    evidenceInput.value = '';
  }
}

// ── double sessions toggle ──
doubleCheckbox.addEventListener('change', () => {
  const sessions = getSelectedSessions();
  if (doubleCheckbox.checked) {
    numberOfSessionsPerWeek = sessions === 4 ? 2 : 4;
  } else {
    numberOfSessionsPerWeek = sessions === 8 ? 2 : 1;
  }
  updateSessionDates();
});

// ── price change: reset sessions-per-week & recalc ──
priceSelect.addEventListener('change', () => {
  numberOfSessionsPerWeek = getSelectedSessions() === 8 ? 2 : 1;
  calculateRemaining();
  updateSessionDates();
});

paidInput.addEventListener('input', calculateRemaining);
startDateInput.addEventListener('input', updateSessionDates);
branchSelect.addEventListener('change', updateSessionDates);
paymentMethodSel.addEventListener('change', toggleEvidence);

// ── form submit: validate client name ──
function countWords(str) {
  return str.trim().split(/\s+/).length >= 3;
}
form.addEventListener('submit', e => {
  if (!countWords(clientNameInput.value)) {
    e.preventDefault();
    alert('⚠️ يجب أن يحتوي اسم العميل على 3 كلمات على الأقل.');
    clientNameInput.focus();
  }
});

// ── init ──
toggleEvidence();
calculateRemaining();
</script>

</body>
</html>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
<?php // views/receipts/refund.php
require ROOT . '/views/includes/layout_top.php';
?>
<style>
  /* reuse same styles from payment.php */
  .search-wrap  { max-width:860px; margin:0 auto; padding:32px 20px 60px; }
  .receipt-pick { display:flex; flex-direction:column; gap:12px; margin-top:20px; }
  .receipt-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; overflow:hidden; cursor:pointer;
    transition:border-color .2s;
  }
  .receipt-card:hover, .receipt-card.selected { border-color:var(--danger); }
  .receipt-card-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 18px; background:var(--surface-2);
    border-bottom:1px solid var(--border);
  }
  .receipt-card-body { padding:16px 18px; display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
  @media(max-width:600px){ .receipt-card-body{ grid-template-columns:1fr 1fr; } }
  .rc-item label { display:block; font-size:11px; color:var(--text-muted); margin-bottom:2px; }
  .rc-item span  { font-size:13px; font-weight:600; }
  .refund-form {
    margin-top:24px; background:var(--surface);
    border:1px solid #5a2020; border-radius:12px; overflow:hidden;
  }
  .refund-form-header {
    padding:14px 20px; background:#2a1515;
    border-bottom:1px solid #5a2020;
    font-size:14px; font-weight:600; color:#fca5a5;
  }
  .refund-form-body { padding:22px; }
  .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
  @media(max-width:600px){ .form-grid-3{ grid-template-columns:1fr; } }
</style>

<div class="search-wrap">
  <div class="page-header">
    <div>
      <h1 class="page-title">↩️ استرداد مبلغ</h1>
      <p class="breadcrumb"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="<?= APP_URL ?>/receipts" class="btn btn-secondary">→ رجوع</a>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <!-- Client search -->
  <div class="form-section">
    <div class="section-header">
      <div class="section-icon">🔍</div>
      <span class="section-title">البحث عن العميل</span>
    </div>
    <div class="section-body">
      <form method="GET" action="<?= APP_URL ?>/receipt/refund"
            style="display:flex;gap:10px;align-items:flex-end;">
        <div class="form-field" style="flex:1;z-index:1;">
          <label class="form-label">ابحث بالاسم أو رقم الهاتف</label>
          <input type="text" name="search" class="form-control"
                 placeholder="مثال: أحمد محمد أو 01012345678"
                 value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="height:42px;">🔍 بحث</button>
      </form>

      <?php if (!empty($search) && empty($client)): ?>
        <div class="alert alert-error" style="margin-top:12px;">
          ⚠️ لم يتم العثور على عميل.
        </div>
      <?php endif; ?>

      <?php if (!empty($client)): ?>
        <div style="margin-top:16px;padding:14px;background:var(--surface-2);
                    border:1px solid var(--border);border-radius:var(--radius);
                    display:flex;gap:24px;flex-wrap:wrap;align-items:center;">
          <div>
            <div style="font-size:11px;color:var(--text-muted)">الاسم</div>
            <div style="font-weight:700;"><?= htmlspecialchars($client['client_name']) ?></div>
          </div>
          <div>
            <div style="font-size:11px;color:var(--text-muted)">الهاتف</div>
            <div style="font-weight:700;"><?= htmlspecialchars($client['phone']) ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($receipts)): ?>
  <div style="font-size:13px;color:var(--text-muted);margin-bottom:8px;">
    اختر الإيصال الذي تريد استرداد مبلغ منه:
  </div>
  <div class="receipt-pick" id="receiptPick">
    <?php foreach ($receipts as $r): ?>
    <div class="receipt-card" data-id="<?= $r['id'] ?>"
         onclick="selectReceipt(<?= $r['id'] ?>)">
      <div class="receipt-card-header">
        <span style="font-weight:700;">#<?= $r['id'] ?> — <?= htmlspecialchars($r['plan_name'] ?? '—') ?></span>
        <span style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($r['branch_name'] ?? '—') ?></span>
      </div>
      <div class="receipt-card-body">
        <div class="rc-item">
          <label>أول جلسة</label>
          <span><?= htmlspecialchars($r['first_session'] ?? '—') ?></span>
        </div>
        <div class="rc-item">
          <label>آخر جلسة</label>
          <span><?= htmlspecialchars($r['last_session'] ?? '—') ?></span>
        </div>
        <div class="rc-item">
          <label>الخطة</label>
          <span><?= htmlspecialchars($r['plan_name'] ?? '—') ?></span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Refund form -->
  <form method="POST" action="<?= APP_URL ?>/receipt/refund"
        id="refundForm" style="display:none;">
    <input type="hidden" name="receipt_id" id="selectedReceiptId">
    <input type="hidden" name="search" value="<?= htmlspecialchars($search ?? '') ?>">

    <div class="refund-form">
      <div class="refund-form-header">↩️ تفاصيل الاسترداد</div>
      <div class="refund-form-body">
        <div class="form-grid-3">

          <div class="form-field">
            <label class="form-label">المبلغ المُسترَد <span style="color:var(--danger)">*</span></label>
            <input type="number" name="amount" class="form-control"
                   placeholder="0" min="1" required>
          </div>

          <div class="form-field">
            <label class="form-label">طريقة الاسترداد <span style="color:var(--danger)">*</span></label>
            <select name="payment_method" class="form-control" required>
              <option value="">— اختر —</option>
              <option value="cash">نقداً</option>
              <option value="instapay">InstaPay</option>
              <option value="vodafone_cash">Vodafone Cash</option>
              <option value="bank_transfer">تحويل بنكي</option>
            </select>
          </div>

          <div class="form-field">
            <label class="form-label">سبب الاسترداد</label>
            <input type="text" name="notes" class="form-control"
                   placeholder="اختياري...">
          </div>

        </div>

        <div style="margin-top:18px;display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary"
                  style="background:var(--danger);box-shadow:0 4px 20px rgba(239,68,68,.35);">
            ↩️ تأكيد الاسترداد
          </button>
          <button type="button" class="btn btn-secondary"
                  onclick="document.getElementById('refundForm').style.display='none'">
            إلغاء
          </button>
        </div>
      </div>
    </div>
  </form>
  <?php endif; ?>
</div>

<script>
function selectReceipt(id) {
    document.querySelectorAll('.receipt-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.receipt-card[data-id="${id}"]`).classList.add('selected');
    document.getElementById('selectedReceiptId').value = id;
    document.getElementById('refundForm').style.display = 'block';
    document.getElementById('refundForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
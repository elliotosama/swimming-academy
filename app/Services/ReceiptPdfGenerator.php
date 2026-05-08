<?php
// app/Services/ReceiptPdfGenerator.php

require_once ROOT . '/vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class ReceiptPdfGenerator {

    /**
     * Generate and stream a receipt PDF to the browser.
     * $receipt must contain all fields from findById() + plan_price.
     * $totalPaid and $remaining are calculated from transactions.
     */
    public static function generate(array $receipt, float $totalPaid, float $remaining, string $paymentMethod): void {

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A5',
            'margin_top'    => 10,
            'margin_bottom' => 10,
            'margin_left'   => 12,
            'margin_right'  => 12,
            'direction'     => 'rtl',
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang   = true;
        $mpdf->autoLangToFont     = true;
        $mpdf->baseScript         = 1;
        $mpdf->autoVietnamese     = true;
        $mpdf->autoArabic         = true;

        $html = self::buildHtml($receipt, $totalPaid, $remaining, $paymentMethod);

        $mpdf->WriteHTML($html);

        $filename = 'receipt_' . $receipt['id'] . '_' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, 'I'); // 'I' = inline in browser, 'D' = download
    }

    /**
     * Save PDF to disk and return the path.
     * Used by store() to save pdf_path on the receipt row.
     */
    public static function save(array $receipt, float $totalPaid, float $remaining, string $paymentMethod, string $saveDir): string {

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A5',
            'margin_top'    => 10,
            'margin_bottom' => 10,
            'margin_left'   => 12,
            'margin_right'  => 12,
            'direction'     => 'rtl',
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;
        $mpdf->autoArabic       = true;

        $html = self::buildHtml($receipt, $totalPaid, $remaining, $paymentMethod);
        $mpdf->WriteHTML($html);

        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0775, true);
        }

        $filename = 'receipt_' . $receipt['id'] . '_' . date('Ymd') . '.pdf';
        $fullPath = rtrim($saveDir, '/') . '/' . $filename;
        $mpdf->Output($fullPath, 'F');

        return $filename;
    }

    private static function buildHtml(array $receipt, float $totalPaid, float $remaining, string $paymentMethod): string {

        $id          = htmlspecialchars($receipt['id']              ?? '—');
        $clientName  = htmlspecialchars($receipt['client_name']     ?? '—');
        $phone       = htmlspecialchars($receipt['phone_number']    ?? '—');
        $branchName  = htmlspecialchars($receipt['branch_name']     ?? '—');
        $captainName = htmlspecialchars($receipt['captain_name']    ?? '—');
        $planName    = htmlspecialchars($receipt['plan_name']       ?? '—');
        $firstSess   = htmlspecialchars($receipt['first_session']   ?? '—');
        $lastSess    = htmlspecialchars($receipt['last_session']    ?? '—');
        $renewalSess = htmlspecialchars($receipt['renewal_session'] ?? '—');
        $exTime      = htmlspecialchars($receipt['exercise_time']   ?? '—');
        $level       = htmlspecialchars((string)($receipt['level'] ?? '—'));
        $createdAt   = htmlspecialchars($receipt['created_at']      ?? '—');
        $creatorName = htmlspecialchars($receipt['creator_name']    ?? '—');

        $paymentMethodLabels = [
            'cash'          => 'نقداً',
            'instapay'      => 'instapay',
            'vodafone_cash' => 'Vodafone Cash',
            'bank_transfer' => 'تحويل بنكي',
        ];
        $payLabel = htmlspecialchars($paymentMethodLabels[$paymentMethod] ?? $paymentMethod);

        $totalPaidFmt = number_format($totalPaid, 0);
        $remainingFmt = number_format($remaining, 0);
        $logoPath = ROOT . '/public/assets/images/logo.png';
$logoSrc  = file_exists($logoPath)
    ? 'file://' . $logoPath
    : '';

        return <<<HTML
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Cairo', 'Arial', sans-serif;
    font-size: 11px;
    color: #1a1a2e;
    direction: rtl;
    background: #fff;
  }


  .page { width: 100%; padding: 4px; }

  /* ── Header ── */
.header {
    text-align: center;
    padding-bottom: 10px;
    border-bottom: 2px solid #1a3a6b;
    margin-bottom: 10px;
}

.logo {
    width: 75px;
    height: 75px;
    margin: 0 auto 6px;
    display: block;
    object-fit: contain;
}
  .logo-name {
    font-size: 18px;
    font-weight: 800;
    color: #1a3a6b;
    letter-spacing: 1px;
  }
  .logo-name span { color: #c0392b; }
  .receipt-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 6px 0 4px;
  }
  .receipt-number {
    font-size: 13px;
    color: #c0392b;
    font-weight: 700;
  }

  /* ── Two-column info grid ── */
  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
  }
  .info-table td {
    padding: 5px 8px;
    font-size: 11px;
    vertical-align: middle;
    border-bottom: 1px solid #e8ecf0;
  }
  .info-table tr:last-child td { border-bottom: none; }

  .label-cell {
    color: #555;
    font-weight: 600;
    width: 28%;
    white-space: nowrap;
  }
  .value-cell {
    color: #1a1a2e;
    font-weight: 700;
    width: 22%;
  }

  /* Highlight rows */
  .row-highlight { background: #f0f4ff; }
  .row-amount    { background: #fff8e1; }
  .row-remaining { background: #ffeaea; }

  /* ── Divider ── */
  .divider {
    border: none;
    border-top: 1.5px solid #1a3a6b;
    margin: 10px 0;
  }

  /* ── Footer instructions ── */
  .footer {
    background: #f8f9fc;
    border: 1px solid #dde2ee;
    border-radius: 6px;
    padding: 10px 14px;
    margin-top: 10px;
  }
  .footer-title {
    font-size: 12px;
    font-weight: 800;
    color: #c0392b;
    margin-bottom: 6px;
  }
  .footer-subtitle {
    font-size: 11px;
    font-weight: 700;
    color: #1a3a6b;
    margin-bottom: 4px;
  }
  .footer li {
    font-size: 10.5px;
    color: #444;
    margin-bottom: 3px;
    list-style: none;
    padding-right: 8px;
  }
  .footer li::before { content: "- "; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
     

    <img src="{$logoSrc}" class="logo">

    <div class="logo-name">Adults Swimming <span>Academy</span></div>  



  <div class="receipt-title">إيصال استلام نقدية</div>
    <div class="receipt-number">رقم الايصال: {$id}</div>
  </div>

  <!-- Main info grid -->
  <table class="info-table">
    <tr class="row-highlight">
      <td class="label-cell">اسم العميل:</td>
      <td class="value-cell" colspan="3">{$clientName}</td>
    </tr>
    <tr>
      <td class="label-cell">رقم الموبايل:</td>
      <td class="value-cell">{$phone}</td>
      <td class="label-cell">ميعاد التمرين:</td>
      <td class="value-cell">{$exTime}</td>
    </tr>
    <tr>
      <td class="label-cell">الحصة الأولى:</td>
      <td class="value-cell">{$firstSess}</td>
      <td class="label-cell">تاريخ التجديد:</td>
      <td class="value-cell">{$renewalSess}</td>
    </tr>
    <tr>
      <td class="label-cell">الحصة الأخيرة:</td>
      <td class="value-cell">{$lastSess}</td>
      <td class="label-cell">المستوى:</td>
      <td class="value-cell">{$level}</td>
    </tr>
    <tr>
      <td class="label-cell">نوع الاشتراك:</td>
      <td class="value-cell" colspan="3">{$planName}</td>
    </tr>
    <tr>
      <td class="label-cell">الفرع:</td>
      <td class="value-cell">{$branchName}</td>
      <td class="label-cell">الكابتن:</td>
      <td class="value-cell">{$captainName}</td>
    </tr>
    <tr class="row-amount">
      <td class="label-cell">المبلغ المدفوع:</td>
      <td class="value-cell">{$totalPaidFmt}</td>
      <td class="label-cell">طريقة الدفع:</td>
      <td class="value-cell">{$payLabel}</td>
    </tr>
    <tr class="row-remaining">
      <td class="label-cell">المتبقي:</td>
      <td class="value-cell">{$remainingFmt}</td>
      <td class="label-cell">المستلم:</td>
      <td class="value-cell">{$creatorName}</td>
    </tr>
    <tr>
      <td class="label-cell">تاريخ الإنشاء:</td>
      <td class="value-cell" colspan="3">{$createdAt}</td>
    </tr>
  </table>

  <hr class="divider">

  <!-- Footer instructions -->
  <div class="footer">
    <div class="footer-title">تعليمات هامة:</div>
    <div class="footer-subtitle">سياسة الإسترجاع:</div>
    <ul>
      <li>يتم خصم 30% من قيمة الاشتراك من بعد الحصة الأولى</li>
      <li>يتم خصم 50% من قيمة الاشتراك من بعد الحصة الثانية</li>
      <li>يتم التعويض عن الغياب بحد أقصى حصة وذلك فقط للمدة المتبقية في الاشتراك</li>
    </ul>
  </div>

</div>
</body>
</html>
HTML;
    }
}
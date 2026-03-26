<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title><?= $pageTitle ?? 'الفروع' ?> — لوحة الإدارة</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════
   DESIGN TOKENS
═══════════════════════════════════════════ */
:root {
    --ink:       #0d1117;
    --ink-2:     #24292f;
    --ink-3:     #57606a;
    --ink-4:     #8c959f;
    --line:      #d0d7de;
    --line-2:    #eaeef2;
    --surface:   #ffffff;
    --canvas:    #f6f8fa;
    --canvas-2:  #eff1f3;
    --accent:    #1a6eea;
    --accent-bg: #dbeafe;
    --green:     #1a7f37;
    --green-bg:  #dafbe1;
    --red:       #cf222e;
    --red-bg:    #ffebe9;
    --amber:     #9a6700;
    --amber-bg:  #fff8c5;
    --r-sm:      8px;
    --r-md:      12px;
    --r-lg:      16px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
    --font: 'IBM Plex Sans Arabic', sans-serif;
    --transition: .18s cubic-bezier(.4,0,.2,1);
}

/* ═══════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: var(--font);
    background: var(--canvas);
    color: var(--ink);
    min-height: 100vh;
    font-size: 14px;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

a { color: var(--accent); text-decoration: none; }
a:hover { text-decoration: underline; }

/* ═══════════════════════════════════════════
   PAGE WRAPPER
═══════════════════════════════════════════ */
.page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 20px 60px;
    animation: fadeUp .35s ease both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ═══════════════════════════════════════════
   PAGE HEADER
═══════════════════════════════════════════ */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}

.page-header__title { font-size: 22px; font-weight: 700; color: var(--ink); }
.page-header__sub   { font-size: 13px; color: var(--ink-4); margin-top: 2px; }

/* ═══════════════════════════════════════════
   BREADCRUMB / BACK
═══════════════════════════════════════════ */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--ink-3);
    margin-bottom: 20px;
}

.breadcrumb a { color: var(--ink-3); }
.breadcrumb a:hover { color: var(--accent); text-decoration: none; }
.breadcrumb svg { width: 14px; height: 14px; flex-shrink: 0; transform: rotate(180deg); }

/* ═══════════════════════════════════════════
   CARD
═══════════════════════════════════════════ */
.card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--r-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--line-2);
    background: var(--canvas);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.card-header h2 { font-size: 16px; font-weight: 600; color: var(--ink); }
.card-body  { padding: 20px; }

/* ═══════════════════════════════════════════
   FORM ELEMENTS
═══════════════════════════════════════════ */
.form-group { margin-bottom: 18px; }

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-2);
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    padding: 10px 13px;
    border: 1px solid var(--line);
    border-radius: var(--r-sm);
    background: var(--surface);
    font-family: var(--font);
    font-size: 14px;
    color: var(--ink);
    transition: border-color var(--transition), box-shadow var(--transition);
    outline: none;
}

.form-control:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(26,110,234,.12);
}

select.form-control { appearance: none; cursor: pointer; }

.form-select-wrap { position: relative; }
.form-select-wrap::after {
    content: '';
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    width: 0; height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid var(--ink-3);
    pointer-events: none;
}

/* ═══════════════════════════════════════════
   TOGGLE (checkbox)
═══════════════════════════════════════════ */
.toggle-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    cursor: pointer;
    user-select: none;
    width: fit-content;
}

.toggle-row input[type="checkbox"] { display: none; }

.toggle-track {
    width: 40px; height: 22px;
    background: var(--line);
    border-radius: 99px;
    position: relative;
    transition: background var(--transition);
    flex-shrink: 0;
}

.toggle-track::after {
    content: '';
    position: absolute;
    top: 3px; right: 3px;
    width: 16px; height: 16px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    transition: transform var(--transition);
}

.toggle-row input:checked ~ .toggle-track { background: var(--accent); }
.toggle-row input:checked ~ .toggle-track::after { transform: translateX(-18px); }

.toggle-label { font-size: 13px; font-weight: 500; color: var(--ink-2); }

/* ═══════════════════════════════════════════
   DAY PILLS
═══════════════════════════════════════════ */
.days-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.day-pill input { display: none; }
.day-pill label {
    display: block;
    padding: 7px 14px;
    border: 1px solid var(--line);
    border-radius: 99px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-3);
    background: var(--canvas);
    transition: all var(--transition);
    user-select: none;
}
.day-pill label:hover { border-color: var(--accent); color: var(--accent); }
.day-pill input:checked + label {
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
}
.day-pill input:disabled + label { opacity: .35; cursor: not-allowed; }

/* Day group block */
.day-group {
    border: 1px solid var(--line-2);
    border-radius: var(--r-md);
    padding: 14px 16px;
    background: var(--canvas);
    margin-bottom: 10px;
    transition: border-color var(--transition);
}
.day-group.at-limit { border-color: #93c5fd; background: #eff6ff; }
.day-group__title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--ink-4);
    margin-bottom: 10px;
    display: flex;
    gap: 6px;
    align-items: center;
}
.day-group__note { font-size: 11px; color: var(--accent); min-height: 16px; margin-top: 8px; }

/* ═══════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════ */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--r-sm);
    font-family: var(--font);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    transition: all var(--transition);
    white-space: nowrap;
    line-height: 1.4;
}

.btn-primary   { background: var(--accent); color: #fff; border-color: var(--accent); }
.btn-primary:hover { filter: brightness(1.08); text-decoration: none; }

.btn-secondary { background: var(--surface); color: var(--ink-2); border-color: var(--line); }
.btn-secondary:hover { background: var(--canvas-2); text-decoration: none; }

.btn-danger    { background: var(--red-bg); color: var(--red); border-color: #ffc1be; }
.btn-danger:hover { background: #ffd7d5; text-decoration: none; }

.btn-success   { background: var(--green-bg); color: var(--green); border-color: #aceebb; }

.btn-sm { padding: 5px 11px; font-size: 12px; }

.btn-full { width: 100%; justify-content: center; padding: 11px; font-size: 15px; }

/* ═══════════════════════════════════════════
   BADGE
═══════════════════════════════════════════ */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 600;
}
.badge-green  { background: var(--green-bg);  color: var(--green); }
.badge-red    { background: var(--red-bg);    color: var(--red); }
.badge-blue   { background: var(--accent-bg); color: var(--accent); }
.badge-gray   { background: var(--canvas-2);  color: var(--ink-3); }
.badge-amber  { background: var(--amber-bg);  color: var(--amber); }

/* ═══════════════════════════════════════════
   ALERT
═══════════════════════════════════════════ */
.alert {
    padding: 12px 14px;
    border-radius: var(--r-sm);
    font-size: 13px;
    margin-bottom: 18px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.alert-error { background: var(--red-bg); color: var(--red); border: 1px solid #ffc1be; }
.alert-success { background: var(--green-bg); color: var(--green); border: 1px solid #aceebb; }

/* ═══════════════════════════════════════════
   TABLE
═══════════════════════════════════════════ */
.table-wrap { overflow-x: auto; }

table.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead tr { background: var(--canvas); }

.data-table th,
.data-table td {
    padding: 11px 14px;
    text-align: right;
    border-bottom: 1px solid var(--line-2);
    font-size: 13px;
}

.data-table th {
    font-size: 11px;
    font-weight: 600;
    color: var(--ink-4);
    text-transform: uppercase;
    letter-spacing: .05em;
}

.data-table tbody tr { transition: background var(--transition); }
.data-table tbody tr:hover { background: var(--canvas); }
.data-table tbody tr:last-child td { border-bottom: none; }

.td-actions {
    display: flex;
    gap: 6px;
    justify-content: flex-start;
}

/* ═══════════════════════════════════════════
   STAT CARDS
═══════════════════════════════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--r-md);
    padding: 16px;
    box-shadow: var(--shadow-sm);
}

.stat-card__label { font-size: 12px; color: var(--ink-4); font-weight: 500; }
.stat-card__value { font-size: 22px; font-weight: 700; margin-top: 4px; color: var(--ink); }

/* ═══════════════════════════════════════════
   INFO LIST (show page)
═══════════════════════════════════════════ */
.info-list { list-style: none; }
.info-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 0;
    border-bottom: 1px solid var(--line-2);
    gap: 12px;
}
.info-list li:last-child { border-bottom: none; }
.info-list__key   { font-size: 13px; color: var(--ink-4); font-weight: 500; flex-shrink: 0; }
.info-list__value { font-size: 14px; font-weight: 600; color: var(--ink); text-align: left; }

/* ═══════════════════════════════════════════
   ACTION LINKS ROW (show page)
═══════════════════════════════════════════ */
.action-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--line-2);
}

/* ═══════════════════════════════════════════
   FILTER ROW
═══════════════════════════════════════════ */
.filter-bar {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--r-md);
    padding: 14px 16px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}

.filter-bar .form-group { margin-bottom: 0; flex: 1 1 160px; }
.filter-bar__actions { display: flex; gap: 8px; align-self: flex-end; }

/* ═══════════════════════════════════════════
   EMPTY STATE
═══════════════════════════════════════════ */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: var(--ink-4);
}
.empty-state p { margin-top: 8px; font-size: 13px; }

/* ═══════════════════════════════════════════
   SECTION TITLE
═══════════════════════════════════════════ */
.section-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 28px 0 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--line-2);
}

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header__actions { width: 100%; display: flex; gap: 8px; flex-wrap: wrap; }
    .data-table th, .data-table td { padding: 9px 10px; }
}
</style>
</head>
<body>
<?php include '../includes/admin_navbar.php'; ?>
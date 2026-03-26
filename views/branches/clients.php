<?php $pageTitle = 'عملاء الفرع'; ?>
<?php include __DIR__ . '/_layout_head.php'; ?>

<div class="page">

    <div class="breadcrumb">
        <a href="index.php">الفروع</a>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <span>عملاء الفرع #<?= $branchId ?></span>
    </div>

    <div class="page-header">
        <div>
            <h1 class="page-header__title">عملاء الفرع</h1>
            <p class="page-header__sub">الفرع رقم <?= $branchId ?></p>
        </div>
        <a onclick="history.back()" style="cursor:pointer" class="btn btn-secondary">رجوع</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>رقم الهاتف</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clients): ?>
                        <?php foreach ($clients as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td style="font-weight:600"><?= htmlspecialchars($c['client_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                            <td><?= $c['created_at'] ?? '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4"><div class="empty-state"><p>لا يوجد عملاء لهذا الفرع</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
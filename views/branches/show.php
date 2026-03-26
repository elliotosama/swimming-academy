<?php $pageTitle = htmlspecialchars($branch['branch_name']); ?>
<?php include __DIR__ . '/_layout_head.php'; ?>

<?php
$dayMap = [
    'Saturday'  => 'السبت',
    'Sunday'    => 'الأحد',
    'Monday'    => 'الإثنين',
    'Tuesday'   => 'الثلاثاء',
    'Wednesday' => 'الأربعاء',
    'Thursday'  => 'الخميس',
    'Friday'    => 'الجمعة',
];

$dayGroups = array_filter([
    $branch['working_days1'],
    $branch['working_days2'],
    $branch['working_days3'],
]);

$allDayLabels = [];
foreach ($dayGroups as $grp) {
    foreach (explode(',', $grp) as $d) {
        $d = trim($d);
        $allDayLabels[] = $dayMap[$d] ?? $d;
    }
}
?>

<div class="page" style="max-width:680px;">

    <div class="breadcrumb">
        <a href="index.php">الفروع</a>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <span><?= htmlspecialchars($branch['branch_name']) ?></span>
    </div>

    <div class="card">

        <div class="card-header">
            <h2><?= htmlspecialchars($branch['branch_name']) ?></h2>
            <?php if ($branch['visible']): ?>
                <span class="badge badge-green">ظاهر</span>
            <?php else: ?>
                <span class="badge badge-gray">مخفي</span>
            <?php endif; ?>
        </div>

        <div class="card-body">

            <ul class="info-list">
                <li>
                    <span class="info-list__key">الدولة</span>
                    <span class="info-list__value"><?= htmlspecialchars($branch['country']) ?></span>
                </li>
                <li>
                    <span class="info-list__key">أيام الدراسة</span>
                    <span class="info-list__value" style="display:flex;flex-wrap:wrap;gap:5px;justify-content:flex-end;">
                        <?php foreach ($allDayLabels as $d): ?>
                            <span class="badge badge-blue"><?= htmlspecialchars($d) ?></span>
                        <?php endforeach; ?>
                    </span>
                </li>
                <li>
                    <span class="info-list__key">تاريخ الإنشاء</span>
                    <span class="info-list__value"><?= date('j F Y', strtotime($branch['created_at'])) ?></span>
                </li>
            </ul>

            <div class="action-links">
                <a href="edit.php?id=<?= $branch['id'] ?>" class="btn btn-primary">تعديل الفرع</a>
                <a href="receipts.php?id=<?= $branch['id'] ?>" class="btn btn-secondary">الإيصالات</a>
                <a href="clients.php?id=<?= $branch['id'] ?>" class="btn btn-secondary">العملاء</a>
                <a href="users.php?id=<?= $branch['id'] ?>" class="btn btn-secondary">المستخدمون</a>
                <a href="captains.php?id=<?= $branch['id'] ?>" class="btn btn-secondary">الكباتن</a>
            </div>

        </div>
    </div>

</div>
</body>
</html>
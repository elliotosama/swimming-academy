<?php $pageTitle = 'إنشاء فرع'; ?>
<?php include __DIR__ . '/_layout_head.php'; ?>

<?php
$allDays = [
    'Saturday'  => 'السبت',
    'Sunday'    => 'الأحد',
    'Monday'    => 'الإثنين',
    'Tuesday'   => 'الثلاثاء',
    'Wednesday' => 'الأربعاء',
    'Thursday'  => 'الخميس',
    'Friday'    => 'الجمعة',
];
?>

<div class="page" style="max-width:560px;">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">الفروع</a>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <span>إنشاء فرع</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>فرع جديد</h2>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert alert-error">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1Zm-.75 3.75a.75.75 0 0 1 1.5 0v4a.75.75 0 0 1-1.5 0v-4Zm.75 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/></svg>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label class="form-label">اسم الفرع <span style="color:var(--red)">*</span></label>
                    <input type="text" name="branch_name" class="form-control"
                           placeholder="مثال: مدينة نصر" required
                           value="<?= htmlspecialchars($_POST['branch_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">الدولة <span style="color:var(--red)">*</span></label>
                    <input type="text" name="country" class="form-control"
                           placeholder="مثال: مصر" required
                           value="<?= htmlspecialchars($_POST['country'] ?? '') ?>">
                </div>

                <label class="toggle-row">
                    <input type="checkbox" name="is_visible" id="visible" checked>
                    <span class="toggle-track"></span>
                    <span class="toggle-label">ظاهر للمستخدمين</span>
                </label>

                <div class="form-group">
                    <label class="form-label">أيام المحاضرات <span style="color:var(--red)">*</span></label>
                    <div class="days-grid">
                        <?php foreach ($allDays as $val => $label): ?>
                            <div class="day-pill">
                                <input type="checkbox" id="day-<?= $val ?>"
                                       name="days[]" value="<?= $val ?>">
                                <label for="day-<?= $val ?>"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
                    إنشاء الفرع
                </button>

            </form>
        </div>
    </div>

</div>
</body>
</html>
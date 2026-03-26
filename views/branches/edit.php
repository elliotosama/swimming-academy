<?php $pageTitle = 'تعديل الفرع'; ?>
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

$groups = [
    ['key' => 'working_days1', 'label' => 'المجموعة الأولى',  'checked' => $wd1Days],
    ['key' => 'working_days2', 'label' => 'المجموعة الثانية', 'checked' => $wd2Days],
    ['key' => 'working_days3', 'label' => 'المجموعة الثالثة', 'checked' => $wd3Days],
];
?>

<div class="page" style="max-width:580px;">

    <div class="breadcrumb">
        <a href="index.php">الفروع</a>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <a href="show.php?id=<?= $branch['id'] ?>"><?= htmlspecialchars($branch['branch_name']) ?></a>
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.275-.326.749.749 0 0 1 .215-.734L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"/></svg>
        <span>تعديل</span>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>تعديل الفرع</h2>
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
                    <input type="text" name="branch_name" class="form-control" required
                           value="<?= htmlspecialchars($branch['branch_name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">الدولة <span style="color:var(--red)">*</span></label>
                    <input type="text" name="country" class="form-control" required
                           value="<?= htmlspecialchars($branch['country']) ?>">
                </div>

                <label class="toggle-row">
                    <input type="checkbox" name="is_visible" id="visible"
                           <?= $branch['visible'] ? 'checked' : '' ?>>
                    <span class="toggle-track"></span>
                    <span class="toggle-label">ظاهر للمستخدمين</span>
                </label>

                <div class="form-group">
                    <label class="form-label">أيام المحاضرات <span style="color:var(--red)">*</span></label>

                    <?php foreach ($groups as $g): ?>
                        <div class="day-group" id="grp-<?= $g['key'] ?>">
                            <div class="day-group__title">
                                <?= $g['label'] ?>
                                <span class="badge badge-gray">حد أقصى يومان</span>
                            </div>
                            <div class="days-grid">
                                <?php foreach ($allDays as $val => $label): ?>
                                    <div class="day-pill">
                                        <input type="checkbox"
                                               id="<?= $g['key'] ?>-<?= $val ?>"
                                               name="<?= $g['key'] ?>[]"
                                               value="<?= $val ?>"
                                               class="day-cb"
                                               data-group="<?= $g['key'] ?>"
                                               <?= in_array($val, $g['checked']) ? 'checked' : '' ?>>
                                        <label for="<?= $g['key'] ?>-<?= $val ?>"><?= $label ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="day-group__note" id="note-<?= $g['key'] ?>"></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-full">حفظ التعديلات</button>

            </form>
        </div>
    </div>

</div>

<script>
function updateGroup(key) {
    const cbs     = document.querySelectorAll(`.day-cb[data-group="${key}"]`);
    const checked = [...cbs].filter(c => c.checked);
    const atLimit = checked.length >= 2;

    cbs.forEach(c => c.disabled = atLimit && !c.checked);

    document.getElementById('grp-'  + key).classList.toggle('at-limit', atLimit);
    document.getElementById('note-' + key).textContent =
        atLimit ? 'تم اختيار يومين — قم بإلغاء تحديد أحدهما للتغيير.' : '';
}

document.querySelectorAll('.day-cb').forEach(cb =>
    cb.addEventListener('change', () => updateGroup(cb.dataset.group))
);

['working_days1','working_days2','working_days3'].forEach(updateGroup);
</script>

</body>
</html>
<?php $pageTitle = 'الفروع'; ?>
<?php include __DIR__ . '/_layout_head.php'; ?>

<div class="page">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-header__title">الفروع</h1>
            <p class="page-header__sub">إدارة فروع الأكاديمية</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="create.php" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M8 2a.75.75 0 0 1 .75.75v4.5h4.5a.75.75 0 0 1 0 1.5h-4.5v4.5a.75.75 0 0 1-1.5 0v-4.5h-4.5a.75.75 0 0 1 0-1.5h4.5v-4.5A.75.75 0 0 1 8 2Z"/></svg>
                إضافة فرع
            </a>
            <a href="../index.php" class="btn btn-secondary">رجوع</a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET">
        <div class="filter-bar">
            <div class="form-group">
                <label class="form-label">الدولة</label>
                <div class="form-select-wrap">
                    <select name="country" class="form-control">
                        <option value="">جميع الدول</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $country === $c ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">الظهور</label>
                <div class="form-select-wrap">
                    <select name="visibility" class="form-control">
                        <option value="">الكل</option>
                        <option value="visible" <?= $visibility === 'visible' ? 'selected' : '' ?>>ظاهر</option>
                        <option value="hidden"  <?= $visibility === 'hidden'  ? 'selected' : '' ?>>مخفي</option>
                    </select>
                </div>
            </div>

            <div class="filter-bar__actions">
                <button type="submit" class="btn btn-primary">تطبيق</button>
                <a href="index.php" class="btn btn-secondary">إعادة ضبط</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>اسم الفرع</th>
                        <th>الدولة</th>
                        <th>أيام الدراسة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$branches): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                                <p>لا توجد فروع</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($branches as $b): ?>
                    <?php
                        $allDays = array_filter([
                            $b['working_days1'],
                            $b['working_days2'],
                            $b['working_days3'],
                        ]);
                        $dayLabels = [];
                        foreach ($allDays as $group) {
                            foreach (explode(',', $group) as $d) {
                                $dayLabels[] = trim($d);
                            }
                        }
                    ?>
                    <tr>
                        <td style="font-weight:600;"><?= htmlspecialchars($b['branch_name']) ?></td>
                        <td><?= htmlspecialchars($b['country']) ?></td>
                        <td>
                            <?php foreach ($dayLabels as $d): ?>
                                <span class="badge badge-blue" style="margin-left:3px;margin-bottom:2px;">
                                    <?= htmlspecialchars($d) ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php if ($b['visible']): ?>
                                <span class="badge badge-green">ظاهر</span>
                            <?php else: ?>
                                <span class="badge badge-gray">مخفي</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="td-actions">
                                <a href="show.php?id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">عرض</a>
                                <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">تعديل</a>
                                <a href="delete.php?id=<?= $b['id'] ?>"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا الفرع؟')"
                                   class="btn btn-danger btn-sm">حذف</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
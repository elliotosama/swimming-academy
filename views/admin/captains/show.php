<?php // views/admin/captains/show.php
require ROOT . '/views/includes/layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">🧑‍✈️ <?= htmlspecialchars($captain['captain_name']) ?></h1>
        <p class="breadcrumb">لوحة التحكم · الكباتن · <?= htmlspecialchars($captain['captain_name']) ?></p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= APP_URL ?>/admin/captains/edit?id=<?= $captain['id'] ?>" class="btn btn-warning">تعديل</a>
        <a href="<?= APP_URL ?>/admin/captains" class="btn btn-secondary">→ رجوع</a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table>
            <tbody>
                <tr>
                    <td style="color:var(--muted);width:40%">#</td>
                    <td style="color:var(--muted);font-size:.82rem"><?= $captain['id'] ?></td>
                </tr>
                <tr>
                    <td style="color:var(--muted)">اسم الكابتن</td>
                    <td><strong><?= htmlspecialchars($captain['captain_name']) ?></strong></td>
                </tr>
                <tr>
                    <td style="color:var(--muted)">رقم الهاتف</td>
                    <td style="font-size:.85rem;color:var(--muted)"><?= htmlspecialchars($captain['phone_number'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td style="color:var(--muted)">الحالة</td>
                    <td>
                        <?php if ($captain['visible']): ?>
                            <span class="badge badge-success">نشط</span>
                        <?php else: ?>
                            <span class="badge badge-danger">معطّل</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--muted)">تاريخ الإنشاء</td>
                    <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($captain['created_at'] ?? '—') ?></td>
                </tr>
                <tr>
    <td style="color:var(--muted)">الفروع</td>
    <td>
        <?php if (empty($assignedBranches)): ?>
            <span style="color:var(--muted)">—</span>
        <?php else: ?>
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                <?php foreach ($assignedBranches as $b): ?>
                    <span class="badge badge-secondary">
                        <?= htmlspecialchars($b['branch_name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </td>
</tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;display:flex;gap:8px;">
        <a href="<?= APP_URL ?>/admin/captains/edit?id=<?= $captain['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
        <form method="POST" action="<?= APP_URL ?>/admin/captains/delete?id=<?= $captain['id'] ?>"
              style="display:inline"
              onsubmit="return confirm('هل أنت متأكد من تعطيل هذا الكابتن؟')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-danger">تعطيل</button>
        </form>
    </div>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
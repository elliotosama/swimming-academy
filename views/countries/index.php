<?php $pageTitle = 'الدول'; ?>
<?php require ROOT . '/views/includes/layout_top.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">🌍 الدول</h1>
        <p class="breadcrumb">لوحة التحكم · الدول</p>
    </div>
    <a href="<?= APP_URL ?>/country/create" class="btn btn-primary">+ إضافة دولة</a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error">⚠️ <?= $_SESSION['flash_error'] ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card">
    <?php if (empty($countries)): ?>
        <div class="empty-state">
            <div class="empty-icon">🌍</div>
            <p>لا توجد دول مضافة حتى الآن.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الدولة</th>
                        <th>رمز الدولة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($countries as $i => $c): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:.82rem"><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:.75rem;">
                                    <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--gold),var(--accent));display:inline-flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                                        🌍
                                    </div>
                                    <div style="display:flex;flex-direction:column;">
                                        <strong style="font-size:.9rem"><?= htmlspecialchars($c['country']) ?></strong>
                                        <span style="font-size:.78rem;color:var(--muted)">أضيف: <?= htmlspecialchars($c['created_at'] ?? '—') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($c['country_code'])): ?>
                                    <span style="background:#00b4d820;color:var(--accent);border:1px solid #00b4d840;border-radius:6px;padding:2px 10px;font-size:.8rem;font-weight:600;">
                                        <?= htmlspecialchars($c['country_code']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--muted)">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($c['created_at'] ?? '—') ?></td>
                            <td>
                                <?php if ($c['visible']): ?>
                                    <span class="badge badge-success">ظاهر</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">مخفي</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="<?= APP_URL ?>/country/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                                    <a href="<?= APP_URL ?>/country/delete?id=<?= $c['id'] ?>"
                                       onclick="return confirm('هل أنت متأكد من إخفاء هذه الدولة؟')"
                                       class="btn btn-sm btn-danger">إخفاء</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require ROOT . '/views/includes/layout_bottom.php'; ?>
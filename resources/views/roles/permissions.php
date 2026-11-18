<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                    <i data-lucide="key" class="text-green" style="width: 28px; height: 28px;"></i>
                    صلاحيات الدور: <?= e($role['display_name_ar']) ?> (<?= e($role['name']) ?>)
                </h3>
                <p class="text-muted mb-0">حدد الصلاحيات المسموحة لهذا الدور في مختلف أقسام ووحدات المستشفى</p>
            </div>
            <a href="<?= url('/roles') ?>" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-right" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                العودة للأدوار
            </a>
        </div>

        <form action="<?= url("/roles/{$role['id']}/permissions") ?>" method="POST">
            <?= csrf_field() ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCheckboxes(true)">تحديد الكل</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCheckboxes(false)">إلغاء تحديد الكل</button>
            </div>

            <?php foreach ($groupedPermissions as $module => $perms): ?>
                <div class="card card-hms p-4 bg-white mb-4">
                    <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center justify-content-between">
                        <span>
                            <i data-lucide="folder" class="text-accent" style="width: 20px; height: 20px; display:inline-block; vertical-align:text-bottom;"></i>
                            وحدة: <?= e($module) ?>
                        </span>
                        <span class="badge bg-light text-secondary fs-6"><?= count($perms) ?> صلاحية</span>
                    </h5>

                    <div class="row g-3">
                        <?php foreach ($perms as $perm): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check p-2 border rounded bg-light-subtle h-100">
                                    <input class="form-check-input perm-checkbox ms-2"
                                           type="checkbox"
                                           name="permissions[]"
                                           value="<?= $perm['id'] ?>"
                                           id="perm_<?= $perm['id'] ?>"
                                           <?= in_array((int)$perm['id'], $rolePermissionIds) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold text-navy d-block" for="perm_<?= $perm['id'] ?>">
                                        <?= e($perm['name_ar']) ?>
                                    </label>
                                    <code class="text-secondary small d-block"><?= e($perm['code']) ?></code>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-end gap-2 pb-5">
                <a href="<?= url('/roles') ?>" class="btn btn-outline-secondary">إلغاء</a>
                <button type="submit" class="btn btn-hms-primary px-4">
                    <i data-lucide="save" style="width: 18px; height: 18px; display:inline-block; vertical-align:text-bottom;"></i>
                    حفظ مصفوفة الصلاحيات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAllCheckboxes(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
    }
</script>

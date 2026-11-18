<div class="row g-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="shield-alert" class="text-green" style="width: 28px; height: 28px;"></i>
                الأدوار والمجموعات الوظيفية (RBAC Matrix)
            </h3>
            <p class="text-muted mb-0">تحديد صلاحيات الوصول الدقيقة للأطباء والتمريض والصيادلة والمحاسبين</p>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الدور (العربية)</th>
                            <th>الاسم البرمجي (Code)</th>
                            <th>الوصف والمسؤوليات</th>
                            <th>عدد المستخدمين المعينين</th>
                            <th>نوع الدور</th>
                            <th class="text-center">إدارة الصلاحيات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td class="fw-bold text-navy"><?= e($r['display_name_ar']) ?></td>
                                <td><code><?= e($r['name']) ?></code></td>
                                <td class="text-muted small"><?= e($r['description'] ?? 'دور قياسي') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= $r['user_count'] ?> مستخدم
                                    </span>
                                </td>
                                <td>
                                    <?php if ($r['is_system']): ?>
                                        <span class="badge bg-secondary-subtle text-secondary">دور نظامي</span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info">مخصص</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url("/roles/{$r['id']}/permissions") ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <i data-lucide="sliders" style="width: 15px; height: 15px;"></i>
                                        تعديل الصلاحيات
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

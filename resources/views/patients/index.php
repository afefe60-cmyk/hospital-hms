<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="users" class="text-green" style="width: 28px; height: 28px;"></i>
                سجل المرضى والملف الطبي الموحد (MRN Directory)
            </h3>
            <p class="text-muted mb-0">البحث في السجلات الطبية، التحقق من السيرة المرضية، وتوليد أرقام MRN جديدة</p>
        </div>

        <?php if (can('patients.create')): ?>
            <a href="<?= url('/patients/create') ?>" class="btn btn-hms-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                فتح ملف مريض جديد
            </a>
        <?php endif; ?>
    </div>

    <!-- Search Form -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <form action="<?= url('/patients') ?>" method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">
                            <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control"
                               placeholder="بحث سريع برقم الملف (MRN)، الرقم الوطني، الاسم، أو رقم الهاتف..."
                               value="<?= e($searchQuery) ?>"
                               autofocus>
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-hms-secondary flex-grow-1">بحث</button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="<?= url('/patients') ?>" class="btn btn-outline-secondary">إلغاء</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients List Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الرقم الطبي (MRN)</th>
                            <th>اسم المريض</th>
                            <th>الرقم الوطني</th>
                            <th>العمر / النوع</th>
                            <th>فصيلة الدم</th>
                            <th>الهاتف الأساسي</th>
                            <th>الولاية / المدينة</th>
                            <th>تنبيه الحساسية</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i data-lucide="user-x" style="width: 40px; height: 40px; margin: 0 auto 8px; color: #94A3B8;"></i>
                                    لم يتم العثور على أي ملفات مطابقة لمعايير البحث.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $p): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url("/patients/{$p['id']}") ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                            <?= e($p['mrn']) ?>
                                        </a>
                                    </td>
                                    <td class="fw-bold text-navy">
                                        <?= e($p['first_name'] . ' ' . $p['second_name'] . ' ' . $p['last_name']) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['national_id'])): ?>
                                            <small class="font-monospace text-secondary"><?= e($p['national_id']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted small">غير مسجل</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $p['age'] ?> سنة
                                        <span class="badge <?= $p['gender'] === 'male' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?> ms-1">
                                            <?= $p['gender'] === 'male' ? 'ذكر' : 'أنثى' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($p['blood_group'] !== 'unknown'): ?>
                                            <span class="badge bg-danger-subtle text-danger fw-bold"><?= e($p['blood_group']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= e($p['phone_primary']) ?></small></td>
                                    <td><small><?= e($p['address_state']) ?> - <?= e($p['address_city']) ?></small></td>
                                    <td>
                                        <?php if ((int)$p['allergies_count'] > 0): ?>
                                            <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                                <i data-lucide="alert-triangle" style="width: 12px; height: 12px;"></i>
                                                <?= $p['allergies_count'] ?> تحسس مسجل
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">لا يوجد</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= url("/patients/{$p['id']}") ?>" class="btn btn-outline-primary" title="عرض الملف الكامل">
                                                <i data-lucide="file-text" style="width: 15px; height: 15px;"></i>
                                                الملف
                                            </a>
                                            <a href="<?= url("/appointments/create?patient_id={$p['id']}") ?>" class="btn btn-outline-success" title="حجز موعد">
                                                <i data-lucide="calendar-plus" style="width: 15px; height: 15px;"></i>
                                                موعد
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">إجمالي الملفات: <?= $totalRecords ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
                                <li class="page-item <?= $pg === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url("/patients?page={$pg}&q=" . urlencode($searchQuery)) ?>"><?= $pg ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

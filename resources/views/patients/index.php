<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="folder-heart" class="text-green" style="width: 28px; height: 28px;"></i>
                سجل المرضى والملف الطبي الموحد (MRN Master Index)
            </h3>
            <p class="text-muted mb-0">إدارة السجلات الطبية، التحقق من السيرة المرضية، والوصول الفوري للملفات السريرية</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-dark d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#quickSearchModal">
                <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                <span>بحث فوري</span>
                <kbd class="bg-secondary text-white px-1 rounded small">Ctrl+K</kbd>
            </button>
            <?php if (can('patients.create')): ?>
                <a href="<?= url('/patients/create') ?>" class="btn btn-hms-primary d-inline-flex align-items-center gap-2 shadow-sm">
                    <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                    <span>فتح ملف مريض جديد</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search & Filter Form -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <form action="<?= url('/patients') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9 col-lg-10">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control border-start-0 ps-0"
                               placeholder="ابحث برقم الملف الطبي (MRN)، الرقم الوطني، الاسم الرباعي، أو رقم الهاتف..."
                               value="<?= e($searchQuery) ?>"
                               autofocus>
                        <?php if (!empty($searchQuery)): ?>
                            <a href="<?= url('/patients') ?>" class="btn btn-outline-secondary">مسح الفلتر</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2">
                    <button type="submit" class="btn btn-hms-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                        <i data-lucide="filter" style="width: 16px; height: 16px;"></i>
                        <span>تطبيق البحث</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients List Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-navy small">
                    قائمة المرضى المسجلين 
                    <span class="badge bg-primary ms-1"><?= number_format($totalRecords) ?> ملف</span>
                </span>
                <span class="text-muted small">الصفحة <?= $currentPage ?> من <?= max(1, $totalPages) ?></span>
            </div>

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
                            <th class="text-center">إجراءات سريعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i data-lucide="user-x" style="width: 42px; height: 42px; margin: 0 auto 10px; color: #94A3B8;"></i>
                                    <div class="fw-bold">لم يتم العثور على أي ملفات مطابقة لمعايير البحث</div>
                                    <small>جرب البحث بكلمات أخرى أو تحقق من رقم الملف</small>
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
                                    <td>
                                        <a href="<?= url("/patients/{$p['id']}") ?>" class="fw-bold text-navy text-decoration-none d-block">
                                            <?= e($p['first_name'] . ' ' . $p['second_name'] . ' ' . $p['last_name']) ?>
                                        </a>
                                        <small class="text-muted">مسجل بالنظام</small>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['national_id'])): ?>
                                            <span class="font-monospace text-secondary small"><?= e($p['national_id']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span><?= $p['age'] ?> سنة</span>
                                        <span class="badge <?= $p['gender'] === 'male' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?> ms-1">
                                            <?= $p['gender'] === 'male' ? 'ذكر' : 'أنثى' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($p['blood_group'] !== 'unknown'): ?>
                                            <span class="badge bg-danger-subtle text-danger fw-bold border border-danger-subtle"><?= e($p['blood_group']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="small font-monospace" style="direction: ltr; display:inline-block;"><?= e($p['phone_primary']) ?></span>
                                    </td>
                                    <td><small class="text-muted"><?= e($p['address_state']) ?> - <?= e($p['address_city']) ?></small></td>
                                    <td>
                                        <?php if ((int)$p['allergies_count'] > 0): ?>
                                            <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                                <i data-lucide="alert-triangle" style="width: 12px; height: 12px;"></i>
                                                <?= $p['allergies_count'] ?> تحسس حرج
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">لا يوجد</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                                                <span>خيارات</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url("/patients/{$p['id']}") ?>">
                                                        <i data-lucide="folder-heart" style="width: 15px; height: 15px; color: #2563EB;"></i>
                                                        <span>عرض الملف الطبي الكامل</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url("/patients/{$p['id']}/print-card") ?>" target="_blank">
                                                        <i data-lucide="printer" style="width: 15px; height: 15px; color: #16856D;"></i>
                                                        <span>طباعة البطاقة الطبية</span>
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url("/appointments/create?patient_id={$p['id']}") ?>">
                                                        <i data-lucide="calendar-plus" style="width: 15px; height: 15px; color: #D97706;"></i>
                                                        <span>حجز موعد عيادة</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url("/emergency/create?patient_id={$p['id']}") ?>">
                                                        <i data-lucide="ambulance" style="width: 15px; height: 15px; color: #EF4444;"></i>
                                                        <span>إحالة للطوارئ ER</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url("/admissions/create?patient_id={$p['id']}") ?>">
                                                        <i data-lucide="bed" style="width: 15px; height: 15px; color: #0284C7;"></i>
                                                        <span>تنويم سريري IPD</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url("/lab/create?patient_id={$p['id']}") ?>">
                                                        <i data-lucide="test-tube" style="width: 15px; height: 15px; color: #8B5CF6;"></i>
                                                        <span>طلب فحص مخبري LIS</span>
                                                    </a>
                                                </li>
                                            </ul>
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
                <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">إجمالي الملفات: <?= number_format($totalRecords) ?></small>
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

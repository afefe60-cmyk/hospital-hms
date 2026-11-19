<?php
/** @var array $patient */
/** @var array $allergies */
/** @var array $conditions */
/** @var array $appointments */
/** @var array $visits */
/** @var array $admissions */
/** @var array $labOrders */
/** @var array $radiologyOrders */
/** @var array $invoices */
/** @var array $surgeries */

$fullName = trim($patient['first_name'] . ' ' . ($patient['second_name'] ?? '') . ' ' . ($patient['third_name'] ?? '') . ' ' . $patient['last_name']);
$age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
?>

<div class="row g-4">
    <!-- 1. Patient 360 Executive Header Bar -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white border-top border-4 border-primary">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle <?= $patient['gender'] === 'male' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?> border">
                        <i data-lucide="user" style="width: 36px; height: 36px;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h3 class="fw-bold text-navy mb-0"><?= e($fullName) ?></h3>
                            <span class="badge bg-navy font-monospace fs-6 px-3 py-1"><?= e($patient['mrn']) ?></span>
                            <span class="badge bg-danger fs-6 px-2 py-1">فصيلة: <?= e($patient['blood_group'] ?? 'غير معروف') ?></span>
                            <span class="badge bg-secondary-subtle text-secondary fs-7"><?= $age ?> سنة / <?= $patient['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                        </div>
                        <div class="text-muted mt-2 small d-flex flex-wrap align-items-center gap-3">
                            <span><i data-lucide="calendar" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i> ميلاد: <?= e($patient['date_of_birth']) ?></span>
                            <span><i data-lucide="phone" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i> <?= e($patient['phone_primary']) ?></span>
                            <span><i data-lucide="id-card" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i> الوطني: <?= e($patient['national_id'] ?? 'غير مسجل') ?></span>
                            <span><i data-lucide="map-pin" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i> <?= e($patient['address_state'] ?? 'الخرطوم') ?> - <?= e($patient['address_city'] ?? '') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Patient Actions Bar -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url("/patients/{$patient['id']}/print-card") ?>" target="_blank" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                        <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
                        <span>طباعة البطاقة الطبية</span>
                    </a>
                    <a href="<?= url("/appointments/create?patient_id={$patient['id']}") ?>" class="btn btn-hms-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                        <i data-lucide="calendar-plus" style="width: 16px; height: 16px;"></i>
                        <span>حجز موعد</span>
                    </a>
                    <a href="<?= url("/emergency/create?patient_id={$patient['id']}") ?>" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1">
                        <i data-lucide="ambulance" style="width: 16px; height: 16px;"></i>
                        <span>طوارئ ER</span>
                    </a>
                    <a href="<?= url("/admissions/create?patient_id={$patient['id']}") ?>" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1">
                        <i data-lucide="bed" style="width: 16px; height: 16px;"></i>
                        <span>تنويم IPD</span>
                    </a>
                    <a href="<?= url("/lab/create?patient_id={$patient['id']}") ?>" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center gap-1">
                        <i data-lucide="test-tube" style="width: 16px; height: 16px;"></i>
                        <span>طلب فحص LIS</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. CRITICAL CLINICAL SAFETY: Allergies Red Alert Banner -->
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3 <?= !empty($allergies) ? 'bg-danger-subtle border-start border-danger border-5' : 'bg-success-subtle border-start border-success border-5' ?>">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="<?= !empty($allergies) ? 'alert-triangle' : 'shield-check' ?>" style="width: 24px; height: 24px;" class="<?= !empty($allergies) ? 'text-danger' : 'text-success' ?>"></i>
                    <h5 class="fw-bold mb-0 <?= !empty($allergies) ? 'text-danger' : 'text-success' ?>">
                        <?= !empty($allergies) ? 'تحذير طبي حرج: توجد تحسسات مسجلة لهذا المريض' : 'لا يوجد تحسس طبي مسجل لهذا المريض' ?>
                    </h5>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addAllergyModal">
                    <i data-lucide="plus" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                    إضافة حساسية
                </button>
            </div>

            <?php if (!empty($allergies)): ?>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php foreach ($allergies as $alg): ?>
                        <?php
                        $badgeClass = match($alg['severity']) {
                            'life_threatening' => 'bg-danger text-white border-white',
                            'severe' => 'bg-danger-subtle text-danger border border-danger',
                            'moderate' => 'bg-warning-subtle text-warning-emphasis border border-warning',
                            default => 'bg-light text-dark border',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?> p-2 fs-6 d-inline-flex align-items-center gap-2">
                            <strong><?= e($alg['allergen_name']) ?></strong>
                            <small>(<?= e($alg['severity']) ?>)</small>
                            <?php if (!empty($alg['reaction_notes'])): ?>
                                <small class="opacity-75"> - <?= e($alg['reaction_notes']) ?></small>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Demographics Sidebar & Chronic Conditions -->
    <div class="col-lg-4">
        <!-- Demographics Details -->
        <div class="card card-hms p-4 bg-white mb-4">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                <i data-lucide="id-card" class="text-green" style="width: 20px; height: 20px;"></i>
                البيانات الثبوتية والديموغرافية
            </h5>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">الرقم الوطني:</span>
                    <span class="font-monospace fw-bold"><?= e($patient['national_id'] ?? 'غير متوفر') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">فصيلة الدم:</span>
                    <span class="badge bg-danger"><?= e($patient['blood_group'] ?? 'O+') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">الحالة الاجتماعية:</span>
                    <span class="fw-semibold"><?= e($patient['marital_status'] ?? 'غير محدد') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">هاتف إضافي:</span>
                    <span><?= e($patient['phone_secondary'] ?? 'غير متوفر') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">جهة الطوارئ:</span>
                    <span class="fw-semibold"><?= e($patient['emergency_contact_name']) ?> (<?= e($patient['emergency_contact_relation']) ?>)</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">هاتف الطوارئ:</span>
                    <span class="fw-bold text-navy"><?= e($patient['emergency_contact_phone']) ?></span>
                </li>
            </ul>
        </div>

        <!-- Chronic Conditions -->
        <div class="card card-hms p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="heart-pulse" class="text-accent" style="width: 20px; height: 20px;"></i>
                    الأمراض المزمنة
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addConditionModal">
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                </button>
            </div>

            <?php if (empty($conditions)): ?>
                <p class="text-muted small text-center py-3 mb-0">لا توجد أمراض مزمنة موثقة في الملف.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($conditions as $cond): ?>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold d-block"><?= e($cond['condition_name']) ?></span>
                                <?php if (!empty($cond['icd10_code'])): ?>
                                    <span class="badge bg-light text-muted border font-monospace">ICD-10: <?= e($cond['icd10_code']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border">مزمن</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Clinical Tabs: 360-Degree Medical History -->
    <div class="col-lg-8">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <!-- Tabs Header -->
            <ul class="nav nav-tabs nav-fill bg-light border-bottom" id="patientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold text-navy" id="visits-tab" data-bs-toggle="tab" data-bs-target="#visitsTab" type="button" role="tab">
                        <i data-lucide="activity" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                        الزيارات والمواعيد (<?= count($visits) + count($appointments) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-navy" id="lab-tab" data-bs-toggle="tab" data-bs-target="#labTab" type="button" role="tab">
                        <i data-lucide="test-tube" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                        المختبرات والأشعة (<?= count($labOrders ?? []) + count($radiologyOrders ?? []) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-navy" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipdTab" type="button" role="tab">
                        <i data-lucide="bed-double" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                        التنويم والعمليات (<?= count($admissions ?? []) + count($surgeries ?? []) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold text-navy" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoicesTab" type="button" role="tab">
                        <i data-lucide="receipt" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                        الفواتير والمالية (<?= count($invoices ?? []) ?>)
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content p-4" id="patientTabsContent">
                <!-- Tab 1: Visits & Appointments -->
                <div class="tab-pane fade show active" id="visitsTab" role="tabpanel">
                    <h6 class="fw-bold text-navy mb-3">سجل الزيارات السريرية والعيادات:</h6>
                    <?php if (empty($visits)): ?>
                        <p class="text-muted text-center py-4">لا توجد زيارات طبية سابقة لهذا المريض.</p>
                    <?php else: ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الزيارة</th>
                                        <th>المسار</th>
                                        <th>القسم</th>
                                        <th>الطبيب المعالج</th>
                                        <th>وقت الوصول</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($visits as $v): ?>
                                        <tr>
                                            <td><code><?= e($v['visit_number']) ?></code></td>
                                            <td><span class="badge bg-navy text-uppercase"><?= e($v['visit_type']) ?></span></td>
                                            <td><?= e($v['department_name']) ?></td>
                                            <td><?= e($v['doctor_name'] ?? 'طوارئ / مناوب') ?></td>
                                            <td><small class="text-muted"><?= e($v['arrival_time']) ?></small></td>
                                            <td><span class="badge bg-success-subtle text-success"><?= e($v['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h6 class="fw-bold text-navy mb-3 mt-4">المواعيد والحجوزات المجدولة:</h6>
                    <?php if (empty($appointments)): ?>
                        <p class="text-muted text-center py-3">لا توجد مواعيد مسجلة.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الوقت</th>
                                        <th>العيادة</th>
                                        <th>الطبيب</th>
                                        <th>رقم الدور</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $app): ?>
                                        <tr>
                                            <td class="fw-bold"><?= e($app['appointment_date']) ?></td>
                                            <td><?= e($app['appointment_time']) ?></td>
                                            <td><?= e($app['clinic_name']) ?></td>
                                            <td>د. <?= e($app['doctor_name']) ?></td>
                                            <td><span class="badge bg-primary">#<?= $app['token_number'] ?></span></td>
                                            <td><span class="badge bg-light text-dark border"><?= e($app['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab 2: Lab & Radiology -->
                <div class="tab-pane fade" id="labTab" role="tabpanel">
                    <h6 class="fw-bold text-navy mb-3">طلبات الفحوصات المخبرية (LIS):</h6>
                    <?php if (empty($labOrders)): ?>
                        <p class="text-muted text-center py-4">لا توجد طلبات تحاليل مخبرية سابقة.</p>
                    <?php else: ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>الفحوصات المطلوبة</th>
                                        <th>الأولوية</th>
                                        <th>الطبيب الطالب</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($labOrders as $lo): ?>
                                        <tr>
                                            <td class="fw-bold font-monospace"><?= e($lo['order_number']) ?></td>
                                            <td><?= e($lo['test_names'] ?? 'فحص عام') ?></td>
                                            <td><span class="badge <?= $lo['priority'] === 'stat' ? 'bg-danger' : 'bg-secondary' ?>"><?= e($lo['priority']) ?></span></td>
                                            <td class="small"><?= e($lo['doctor_name'] ?? '-') ?></td>
                                            <td class="small text-muted"><?= e(date('Y-m-d', strtotime($lo['created_at']))) ?></td>
                                            <td><span class="badge bg-info-subtle text-info"><?= e($lo['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h6 class="fw-bold text-navy mb-3 mt-4">طلبات الأشعة والتصوير الطبي (RIS):</h6>
                    <?php if (empty($radiologyOrders)): ?>
                        <p class="text-muted text-center py-4">لا توجد طلبات أشعة مسجلة.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>نوع الأشعة</th>
                                        <th>الخدمة</th>
                                        <th>الطبيب</th>
                                        <th>التاريخ</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($radiologyOrders as $ro): ?>
                                        <tr>
                                            <td class="fw-bold font-monospace"><?= e($ro['order_number']) ?></td>
                                            <td><span class="badge bg-primary text-uppercase"><?= e($ro['modality']) ?></span></td>
                                            <td><?= e($ro['service_name']) ?></td>
                                            <td class="small"><?= e($ro['doctor_name'] ?? '-') ?></td>
                                            <td class="small text-muted"><?= e(date('Y-m-d', strtotime($ro['created_at']))) ?></td>
                                            <td><span class="badge bg-secondary"><?= e($ro['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab 3: IPD & Surgeries -->
                <div class="tab-pane fade" id="ipdTab" role="tabpanel">
                    <h6 class="fw-bold text-navy mb-3">سجل التنويم والأجنحة (IPD):</h6>
                    <?php if (empty($admissions)): ?>
                        <p class="text-muted text-center py-4">لا توجد سجلات تنويم داخلي سابقة.</p>
                    <?php else: ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الدخول</th>
                                        <th>الجناح / الغرفة</th>
                                        <th>السرير</th>
                                        <th>تاريخ الدخول</th>
                                        <th>الطبيب المعالج</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admissions as $adm): ?>
                                        <tr>
                                            <td class="fw-bold font-monospace"><?= e($adm['admission_number']) ?></td>
                                            <td><?= e($adm['ward_name']) ?> (غرفة <?= e($adm['room_number'] ?? '-') ?>)</td>
                                            <td><span class="badge bg-light text-dark border"><?= e($adm['bed_number'] ?? '-') ?></span></td>
                                            <td><?= e($adm['admission_date']) ?></td>
                                            <td class="small"><?= e($adm['doctor_name'] ?? '-') ?></td>
                                            <td><span class="badge bg-success-subtle text-success"><?= e($adm['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h6 class="fw-bold text-navy mb-3 mt-4">العمليات الجراحية (Surgical Theater):</h6>
                    <?php if (empty($surgeries)): ?>
                        <p class="text-muted text-center py-4">لا توجد عمليات جراحية مسجلة.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم العملية</th>
                                        <th>الإجراء الجراحي</th>
                                        <th>غرفة العمليات</th>
                                        <th>الجراح الرئيسي</th>
                                        <th>الموعد</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($surgeries as $surg): ?>
                                        <tr>
                                            <td class="fw-bold font-monospace"><?= e($surg['surgery_number']) ?></td>
                                            <td><?= e($surg['procedure_name']) ?></td>
                                            <td><?= e($surg['operating_room'] ?? 'غرفة 1') ?></td>
                                            <td class="small"><?= e($surg['surgeon_name'] ?? '-') ?></td>
                                            <td class="small text-muted"><?= e($surg['scheduled_start']) ?></td>
                                            <td><span class="badge bg-secondary"><?= e($surg['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab 4: Invoices & Financials -->
                <div class="tab-pane fade" id="invoicesTab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-navy mb-0">سجل الفواتير والمطالبات المالية:</h6>
                        <a href="<?= url('/billing/create?patient_id=' . $patient['id']) ?>" class="btn btn-sm btn-outline-success fw-bold d-flex align-items-center gap-1.5 shadow-sm">
                            <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i>
                            <span>فاتورة جديدة لهذا المريض</span>
                        </a>
                    </div>
                    <?php if (empty($invoices)): ?>
                        <p class="text-muted text-center py-4">لا توجد فواتير سابقة مسجلة لهذا المريض.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم الفاتورة</th>
                                        <th>التاريخ</th>
                                        <th>الإجمالي (ج.س)</th>
                                        <th>التأمين</th>
                                        <th>المسدد (ج.س)</th>
                                        <th>المتبقي (ج.س)</th>
                                        <th>الحالة</th>
                                        <th class="text-center">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $inv): 
                                        $remaining = (float)$inv['total_patient_due'] - (float)$inv['paid_amount'];
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('/billing/' . $inv['id']) ?>" class="fw-bold font-monospace text-navy text-decoration-none hover-underline" title="معاينة تفاصيل الفاتورة">
                                                    <?= e($inv['invoice_number']) ?>
                                                </a>
                                            </td>
                                            <td class="small text-muted"><?= e(date('Y-m-d', strtotime($inv['created_at']))) ?></td>
                                            <td class="fw-bold"><?= number_format($inv['total_gross'], 2) ?></td>
                                            <td><?= number_format($inv['total_insurance'], 2) ?></td>
                                            <td class="text-success fw-bold"><?= number_format($inv['paid_amount'], 2) ?></td>
                                            <td class="<?= $remaining > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                <?= number_format($remaining, 2) ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $inv['status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= $inv['status'] === 'paid' ? 'مدفوعة' : ($inv['status'] === 'partial' ? 'سداد جزئي' : 'غير مدفوعة') ?>
                                                </span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= url('/billing/' . $inv['id'] . '/print') ?>" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5 d-inline-flex align-items-center gap-1.5 shadow-sm fw-bold" title="طباعة الفاتورة العلاجية">
                                                    <i data-lucide="printer" style="width: 14px; height: 14px;"></i>
                                                    <span>طباعة الفاتورة</span>
                                                </a>
                                                <a href="<?= url('/billing/' . $inv['id']) ?>" class="btn btn-sm btn-light border py-1 px-2 d-inline-flex align-items-center gap-1 shadow-sm text-navy" title="معاينة تفاصيل الفاتورة">
                                                    <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Allergy -->
<div class="modal fade" id="addAllergyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url("/patients/{$patient['id']}/allergies") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">إضافة تحسس طبي حرج (Allergy)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم الدواء أو المسبب <span class="text-danger">*</span></label>
                        <input type="text" name="allergen_name" class="form-control" placeholder="مثال: Penicillin, Aspirin, Sulfa, بيض..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">نوع الحساسية</label>
                        <select name="allergen_type" class="form-select">
                            <option value="drug">دوائية (Drug Allergy)</option>
                            <option value="food">غذائية (Food Allergy)</option>
                            <option value="environmental">بيئية (Environmental)</option>
                            <option value="other">أخرى (Other)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">درجة الخطورة <span class="text-danger">*</span></label>
                        <select name="severity" class="form-select" required>
                            <option value="life_threatening" class="text-danger fw-bold">مهددة للحياة (Life-Threatening)</option>
                            <option value="severe" selected>شديدة (Severe)</option>
                            <option value="moderate">متوسطة (Moderate)</option>
                            <option value="mild">خفيفة (Mild)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الأعراض السريرية السابقة</label>
                        <textarea name="reaction_notes" class="form-control" rows="2" placeholder="مثال: صعوبة تنفس، تورم بالوجه، طفح جلدي حاد..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">توثيق التحسس</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Condition -->
<div class="modal fade" id="addConditionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url("/patients/{$patient['id']}/conditions") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold">توثيق مرض مزمن</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم المرض المزمن <span class="text-danger">*</span></label>
                        <input type="text" name="condition_name" class="form-control" placeholder="مثال: داء السكري، ارتفاع ضغط الدم، أزمة ربوية..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">رمز ICD-10 المعتمد (اختياري)</label>
                        <input type="text" name="icd10_code" class="form-control" placeholder="مثال: E11, I10, J45">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">تاريخ التشخيص</label>
                        <input type="date" name="diagnosed_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hms-primary">حفظ الحالة المزمنة</button>
                </div>
            </form>
        </div>
    </div>
</div>

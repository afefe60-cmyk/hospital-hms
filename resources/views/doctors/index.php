<div class="row g-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="user-plus" class="text-green" style="width: 28px; height: 28px;"></i>
                سجل الأطباء والاستشاريين المعتمدين
            </h3>
            <p class="text-muted mb-0">بيانات الأطباء، التخصصات، رسوم الكشف بالجنيه السوداني (SDG)، وتراخيص المجلس الطبي</p>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الطبيب</th>
                            <th>الرقم الوظيفي</th>
                            <th>التخصص السريري</th>
                            <th>القسم</th>
                            <th>ترخيص المجلس الطبي</th>
                            <th>قيمة الكشفية (SDG)</th>
                            <th>نسبة الطبيب (%)</th>
                            <th>هاتف التواصل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td><?= $doc['id'] ?></td>
                                <td class="fw-bold text-navy">
                                    د. <?= e($doc['full_name']) ?>
                                    <small class="d-block text-muted"><?= e($doc['degree_title']) ?></small>
                                </td>
                                <td><code><?= e($doc['employee_code']) ?></code></td>
                                <td><span class="badge bg-green"><?= e($doc['specialty_name_ar']) ?></span></td>
                                <td><span class="badge bg-navy"><?= e($doc['department_name_ar']) ?></span></td>
                                <td><small class="font-monospace text-secondary"><?= e($doc['medical_license_number']) ?></small></td>
                                <td class="fw-bold text-navy"><?= format_currency($doc['consultation_fee']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $doc['commission_percentage'] ?>%</span></td>
                                <td><small><?= e($doc['phone']) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

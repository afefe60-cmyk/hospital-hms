<?php
$layout = 'layouts/main';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="h4 mb-1 fw-bold"><i class="bi bi-person-plus-fill text-danger me-2"></i>إدخال وتسكين مريض بالعناية المركزة (ICU)</h3>
                <p class="text-muted mb-0">تخصيص سرير مراقبة حثيثة وتوثيق سبب الدخول ومستوى الخطورة</p>
            </div>
            <a href="/icu" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>رجوع للوحة العناية
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="/icu/store">
                    <?= csrf_field() ?>

                    <h5 class="fw-bold mb-3 text-danger border-bottom pb-2">1. تحديد المريض والسرير الشاغر</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">اختر المريض <span class="text-danger">*</span></label>
                            <?php if ($patient): ?>
                                <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                <div class="form-control bg-light">
                                    <strong><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['second_name'] . ' ' . $patient['last_name']) ?></strong>
                                    (MRN: <?= htmlspecialchars($patient['mrn']) ?>)
                                </div>
                            <?php else: ?>
                                <select name="patient_id" class="form-select select2" required>
                                    <option value="">-- اختر المريض --</option>
                                    <?php foreach ($patients as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['name']) ?> (MRN: <?= htmlspecialchars($p['mrn']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">سرير العناية المركزة المتاح <span class="text-danger">*</span></label>
                            <select name="bed_id" class="form-select" required>
                                <option value="">-- اختر سرير شاغر بالعناية --</option>
                                <?php foreach ($beds as $b): ?>
                                    <option value="<?= $b['id'] ?>">
                                        <?= htmlspecialchars($b['bed_number']) ?> - (غرفة: <?= htmlspecialchars($b['room_number']) ?>) [شاغر]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($beds)): ?>
                                <div class="text-danger small mt-1">تنبيه: لا توجد أسرة عناية مركزة شاغرة حالياً!</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-danger border-bottom pb-2">2. الطبيب المعالج والتقييم السريري</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">استشاري / أخصائي العناية المركزة المتابع</label>
                            <select name="attending_intensivist_id" class="form-select">
                                <option value="">-- اختر الطبيب المعالج --</option>
                                <?php foreach ($intensivists as $doc): ?>
                                    <option value="<?= $doc['id'] ?>">د. <?= htmlspecialchars($doc['name']) ?> (<?= htmlspecialchars($doc['specialty_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">مقياس الخطورة (APACHE II)</label>
                            <input type="number" name="apache_score" class="form-control" placeholder="0 - 71" min="0" max="71">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">دعم التنفس الاصطناعي</label>
                            <select name="ventilator_status" class="form-select">
                                <option value="none">بدون جهاز تنفس (None)</option>
                                <option value="invasive">تنفس غازي بأنبوب رغامي (Invasive)</option>
                                <option value="non_invasive">قناع ضغط غير غازي (NIV / BiPAP)</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-danger border-bottom pb-2">3. سبب وتفاصيل التنويم بالعناية</h5>
                    <div class="mb-4">
                        <label class="form-label fw-bold">سبب الدخول والتشخيص الأولي <span class="text-danger">*</span></label>
                        <textarea name="admission_reason" class="form-control" rows="4" required placeholder="اكتب التشخيص الإكلينيكي المبرر للتنويم بالعناية المركزة (مثال: صدمة إنتانية، فشل تنفسي حاد، غيبوبة، ما بعد جراحة كبرى حرجة)..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/icu" class="btn btn-outline-secondary px-4">إلغاء</a>
                        <button type="submit" class="btn btn-danger px-4 fw-bold" <?= empty($beds) ? 'disabled' : '' ?>>
                            <i class="bi bi-check-lg me-1"></i>تأكيد الدخول للعناية المركزة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

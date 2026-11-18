<?php
$layout = 'layouts/main';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="h4 mb-1 fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>جدولة وحجز عملية جراحية</h3>
                <p class="text-muted mb-0">تحديد المريض، مسرح العمليات، الطاقم الجراحي والتخديري والتوقيت المجدول</p>
            </div>
            <a href="/surgery" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>رجوع للجدول
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="/surgery/store">
                    <?= csrf_field() ?>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">1. بيانات المريض</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">اختر المريض <span class="text-danger">*</span></label>
                            <?php if ($patient): ?>
                                <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                <div class="form-control bg-light">
                                    <strong><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['second_name'] . ' ' . $patient['last_name']) ?></strong>
                                    (MRN: <?= htmlspecialchars($patient['mrn']) ?>) - هاتف: <?= htmlspecialchars($patient['phone_primary']) ?>
                                </div>
                            <?php else: ?>
                                <select name="patient_id" class="form-select select2" required>
                                    <option value="">-- ابحث أو اختر المريض --</option>
                                    <?php foreach ($patients as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['name']) ?> (MRN: <?= htmlspecialchars($p['mrn']) ?>) - <?= htmlspecialchars($p['phone_primary']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">نوع الجراحة <span class="text-danger">*</span></label>
                            <select name="surgery_type" class="form-select" required>
                                <option value="elective">جراحة اختيارية مجدولة (Elective)</option>
                                <option value="emergency">جراحة طارئة (Emergency)</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">2. تفاصيل الإجراء وغرفة العمليات</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">اسم العملية / الإجراء الجراحي <span class="text-danger">*</span></label>
                            <input type="text" name="procedure_name" class="form-control" placeholder="مثال: استئصال المرارة بالمنظار Laparoscopic Cholecystectomy" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">غرفة العمليات / المسرح الجراحي <span class="text-danger">*</span></label>
                            <select name="operating_room_id" class="form-select" required>
                                <option value="">-- اختر غرفة العمليات --</option>
                                <?php foreach ($operatingRooms as $or): ?>
                                    <option value="<?= $or['id'] ?>" <?= $or['status'] !== 'available' ? 'class="text-muted"' : '' ?>>
                                        <?= htmlspecialchars($or['name_ar']) ?> (<?= htmlspecialchars($or['name_en']) ?>) - [الحالة: <?= $or['status'] === 'available' ? 'شاغرة' : 'مشغولة' ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">يقوم النظام تلقائياً بالتحقق من عدم وجود تضارب في المواعيد لنفس المسرح.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">وقت البداية المجدول <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_start" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">وقت النهاية التقديري <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_end" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+2 hours')) ?>" required>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">3. الطاقم الطبي والجراحي</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الجراح الرئيسي (Lead Surgeon) <span class="text-danger">*</span></label>
                            <select name="lead_surgeon_id" class="form-select" required>
                                <option value="">-- اختر الجراح الرئيسي --</option>
                                <?php foreach ($surgeons as $s): ?>
                                    <option value="<?= $s['id'] ?>">د. <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['specialty_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الجراح المساعد (Assistant Surgeon)</label>
                            <select name="assistant_surgeon_id" class="form-select">
                                <option value="">-- لا يوجد جراح مساعد (اختياري) --</option>
                                <?php foreach ($surgeons as $s): ?>
                                    <option value="<?= $s['id'] ?>">د. <?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">طبيب التخدير (Anesthesiologist)</label>
                            <select name="anesthesiologist_id" class="form-select">
                                <option value="">-- اختر طبيب التخدير --</option>
                                <?php foreach ($anesthesiologists as $a): ?>
                                    <option value="<?= $a['id'] ?>">د. <?= htmlspecialchars($a['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">تمريض العمليات والتعقيم (Scrub Nurse)</label>
                            <select name="scrub_nurse_id" class="form-select">
                                <option value="">-- اختر ممرض / ممرضة العمليات --</option>
                                <?php foreach ($nurses as $n): ?>
                                    <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">4. التشخيص والملاحظات المبدئية</h5>
                    <div class="mb-4">
                        <label class="form-label fw-bold">التشخيص قبل العملية (Pre-operative Diagnosis)</label>
                        <textarea name="pre_op_diagnosis" class="form-control" rows="3" placeholder="اكتب التشخيص الإكلينيكي وحالة المريض قبل الدخول للعملية..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/surgery" class="btn btn-outline-secondary px-4">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i>تأكيد جدولة العملية
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-gear-fill text-primary me-2"></i>إعدادات النظام العامة وتخصيص هوية المستشفى (System Settings)</h2>
        <p class="text-muted mb-0">تخصيص البيانات الرسمية، الإعدادات المالية وبنكك، المعايير السريرية، وسياسات الأمان</p>
    </div>
    <div>
        <a href="/" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة التحكم
        </a>
    </div>
</div>

<!-- Settings Navigation Tabs -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link <?= ($activeTab === 'hospital') ? 'active' : '' ?>" href="/settings?tab=hospital">
            <i class="bi bi-hospital me-1"></i>هوية وبيانات المستشفى
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($activeTab === 'financial') ? 'active' : '' ?>" href="/settings?tab=financial">
            <i class="bi bi-currency-dollar me-1"></i>الإعدادات المالية وحساب بنكك
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($activeTab === 'clinical') ? 'active' : '' ?>" href="/settings?tab=clinical">
            <i class="bi bi-heart-pulse me-1"></i>المعايير السريرية والأسرّة
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($activeTab === 'security') ? 'active' : '' ?>" href="/settings?tab=security">
            <i class="bi bi-shield-lock me-1"></i>سياسات الأمان والجلسات
        </a>
    </li>
</ul>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <?php if ($activeTab === 'hospital'): ?>
            <!-- Hospital Identity Settings Form -->
            <form action="/settings/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="group" value="hospital">
                
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                    <i class="bi bi-building me-2"></i>بيانات وهوية المنشأة الطبية الرسمية
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم المستشفى الرسمي (بالعربية) <span class="text-danger">*</span></label>
                        <input type="text" name="hospital_name_ar" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_name_ar']['setting_value'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم المستشفى بالإنجليزية (English) <span class="text-danger">*</span></label>
                        <input type="text" name="hospital_name_en" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_name_en']['setting_value'] ?? '') ?>" dir="ltr" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">رمز المنشأة الطبية</label>
                        <input type="text" name="hospital_code" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_code']['setting_value'] ?? '') ?>" dir="ltr">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">رقم ترخيص وزارة الصحة</label>
                        <input type="text" name="hospital_license_number" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_license_number']['setting_value'] ?? '') ?>" dir="ltr">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">البريد الإلكتروني الرسمي</label>
                        <input type="email" name="hospital_email" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_email']['setting_value'] ?? '') ?>" dir="ltr">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">هاتف البدالة الرئيسي</label>
                        <input type="text" name="hospital_phone" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_phone']['setting_value'] ?? '') ?>" dir="ltr">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">هاتف الطوارئ والإسعاف (24/7)</label>
                        <input type="text" name="hospital_emergency_phone" class="form-control" value="<?= htmlspecialchars($settings['hospital']['hospital_emergency_phone']['setting_value'] ?? '') ?>" dir="ltr">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">العنوان والمقر الجغرافي</label>
                    <textarea name="hospital_address" class="form-control" rows="2"><?= htmlspecialchars($settings['hospital']['hospital_address']['setting_value'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i>حفظ بيانات المنشأة
                </button>
            </form>

        <?php elseif ($activeTab === 'financial'): ?>
            <!-- Financial & Bankak Settings Form -->
            <form action="/settings/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="group" value="financial">
                
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-success">
                    <i class="bi bi-cash-coin me-2"></i>الإعدادات المالية وحسابات التحصيل الإلكتروني (بنكك)
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">العملة الأساسية للنظام</label>
                        <input type="text" name="currency_code" class="form-control" value="<?= htmlspecialchars($settings['financial']['currency_code']['setting_value'] ?? 'SDG') ?>" readonly>
                        <small class="text-muted">الجنيه السوداني (SDG)</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">رمز العملة المختصر</label>
                        <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($settings['financial']['currency_symbol']['setting_value'] ?? 'ج.س') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">نسبة ضريبة القيمة المضافة (%)</label>
                        <input type="number" step="0.01" name="tax_rate_percent" class="form-control" value="<?= htmlspecialchars($settings['financial']['tax_rate_percent']['setting_value'] ?? '0.00') ?>">
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="bi bi-phone fs-3 me-3 text-primary"></i>
                    <div>
                        <strong>تكامل تطبيق بنكك (Bankak - بنك الخرطوم):</strong>
                        <div class="small">بيانات حساب المستشفى التي تظهر على الفواتير وسندات القبض لتحويلات المرضى والتأمين.</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم حساب المستشفى المعتمد في بنكك <span class="text-danger">*</span></label>
                        <input type="text" name="bankak_account_number" class="form-control font-monospace" value="<?= htmlspecialchars($settings['financial']['bankak_account_number']['setting_value'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم الحساب المعتمد في بنكك <span class="text-danger">*</span></label>
                        <input type="text" name="bankak_account_name" class="form-control" value="<?= htmlspecialchars($settings['financial']['bankak_account_name']['setting_value'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">ملاحظات تذييل الفواتير والإيصالات الرسمية</label>
                    <textarea name="invoice_footer_notes" class="form-control" rows="2"><?= htmlspecialchars($settings['financial']['invoice_footer_notes']['setting_value'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-save me-1"></i>حفظ الإعدادات المالية
                </button>
            </form>

        <?php elseif ($activeTab === 'clinical'): ?>
            <!-- Clinical Settings Form -->
            <form action="/settings/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="group" value="clinical">
                
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-warning">
                    <i class="bi bi-sliders me-2"></i>المعايير السريرية وسياسات الأسرّة والصيدلية
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الطاقة التشغيلية للأسرّة (سرير)</label>
                        <input type="number" name="total_operational_beds" class="form-control" value="<?= htmlspecialchars($settings['clinical']['total_operational_beds']['setting_value'] ?? '150') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">نسبة تمريض العناية المركزة (ICU)</label>
                        <input type="text" name="icu_nurse_patient_ratio" class="form-control" value="<?= htmlspecialchars($settings['clinical']['icu_nurse_patient_ratio']['setting_value'] ?? '1:2') ?>" dir="ltr">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الحد الأقصى للمواعيد اليومية لكل عيادة</label>
                        <input type="number" name="max_daily_clinic_appointments" class="form-control" value="<?= htmlspecialchars($settings['clinical']['max_daily_clinic_appointments']['setting_value'] ?? '40') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">فترة تنبيه انتهاء صلاحية الأدوية (أيام مسبقة بنظام FEFO)</label>
                        <input type="number" name="default_fefo_expiry_alert_days" class="form-control" value="<?= htmlspecialchars($settings['clinical']['default_fefo_expiry_alert_days']['setting_value'] ?? '90') ?>">
                        <small class="text-muted">تنبيه النظام قبل حلول تاريخ الانتهاء</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">التحرير التلقائي للسرير عند خروج المريض</label>
                        <select name="auto_bed_release_on_discharge" class="form-select">
                            <option value="1" <?= (($settings['clinical']['auto_bed_release_on_discharge']['setting_value'] ?? '1') === '1') ? 'selected' : '' ?>>نعم - تحرير السرير فور اعتماد الخروج</option>
                            <option value="0" <?= (($settings['clinical']['auto_bed_release_on_discharge']['setting_value'] ?? '1') === '0') ? 'selected' : '' ?>>لا - تحرير يدوي بواسطة مشرفة التمريض</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-save me-1"></i>حفظ المعايير السريرية
                </button>
            </form>

        <?php elseif ($activeTab === 'security'): ?>
            <!-- Security & Environment Form -->
            <form action="/settings/update" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="group" value="security">
                
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-danger">
                    <i class="bi bi-shield-lock me-2"></i>سياسات الأمان والجلسات وسجلات التدقيق
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">المنطقة الزمنية للنظام</label>
                        <input type="text" name="system_timezone" class="form-control" value="<?= htmlspecialchars($settings['security']['system_timezone']['setting_value'] ?? 'Africa/Khartoum') ?>" dir="ltr">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">مهلة خمول جلسة المستخدم (بالدقائق)</label>
                        <input type="number" name="session_timeout_minutes" class="form-control" value="<?= htmlspecialchars($settings['security']['session_timeout_minutes']['setting_value'] ?? '120') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">فترة الاحتفاظ بسجلات التدقيق (أيام)</label>
                        <input type="number" name="audit_log_retention_days" class="form-control" value="<?= htmlspecialchars($settings['security']['audit_log_retention_days']['setting_value'] ?? '365') ?>">
                    </div>
                </div>

                <div class="alert alert-light border small text-muted mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    يقوم النظام بحفظ جميع الإجراءات الحساسة تلقائياً في سجل التدقيق الأمني (Audit Logs) لمنع التلاعب وتتبع التعديلات.
                </div>

                <button type="submit" class="btn btn-danger px-4">
                    <i class="bi bi-save me-1"></i>حفظ إعدادات الأمان
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php

use App\Core\Database;
use App\Models\SystemSetting;

class SystemSettingsSeeder
{
    public function run(): void
    {
        $settings = [
            // General & Hospital Identity
            [
                'key' => 'hospital_name_ar',
                'value' => 'مستشفى الخرطوم التخصصي ',
                'group' => 'hospital',
                'description' => 'اسم المستشفى الرسمي باللغة العربية',
            ],
            [
                'key' => 'hospital_name_en',
                'value' => 'Khartoum Specialized Teaching Hospital',
                'group' => 'hospital',
                'description' => 'Official Hospital Name in English',
            ],
            [
                'key' => 'hospital_code',
                'value' => 'HOSP-KRT-01',
                'group' => 'hospital',
                'description' => 'رمز المنشأة الطبية المسجل لدى وزارة الصحة',
            ],
            [
                'key' => 'hospital_phone',
                'value' => '+249183770000',
                'group' => 'hospital',
                'description' => 'هاتف البدالة الرئيسي',
            ],
            [
                'key' => 'hospital_emergency_phone',
                'value' => '+249912000000',
                'group' => 'hospital',
                'description' => 'طوارئ وإسعاف المستشفى (24/7)',
            ],
            [
                'key' => 'hospital_email',
                'value' => 'info@khartoum-hospital.sd',
                'group' => 'hospital',
                'description' => 'البريد الإلكتروني الرسمي',
            ],
            [
                'key' => 'hospital_address',
                'value' => 'الخرطوم - شارع القصر، تقاطع شارع المك نمر، ولاية الخرطوم، السودان',
                'group' => 'hospital',
                'description' => 'العنوان والموقع الجغرافي للمستشفى',
            ],
            [
                'key' => 'hospital_license_number',
                'value' => 'MOH-SD-2024-88401',
                'group' => 'hospital',
                'description' => 'رقم ترخيص المنشأة من وزارة الصحة الاتحادية',
            ],

            // Financial & Billing Settings (SDG)
            [
                'key' => 'currency_code',
                'value' => 'SDG',
                'group' => 'financial',
                'description' => 'رمز العملة الرسمية للمستشفى',
            ],
            [
                'key' => 'currency_symbol',
                'value' => 'ج.س',
                'group' => 'financial',
                'description' => 'رمز العملة المختصر',
            ],
            [
                'key' => 'enable_bankak_payment',
                'value' => '1',
                'group' => 'financial',
                'description' => 'تفعيل الدفع عبر تطبيق بنكك (بنك الخرطوم)',
            ],
            [
                'key' => 'bankak_account_number',
                'value' => '1820499',
                'group' => 'financial',
                'description' => 'رقم حساب المستشفى المعتمد في بنكك',
            ],
            [
                'key' => 'bankak_account_name',
                'value' => 'مستشفى الخرطوم التخصصي',
                'group' => 'financial',
                'description' => 'اسم صاحب الحساب في بنكك',
            ],
            [
                'key' => 'tax_rate_percent',
                'value' => '0.00',
                'group' => 'financial',
                'description' => 'نسبة ضريبة القيمة المضافة على الخدمات الطبية (%)',
            ],
            [
                'key' => 'invoice_footer_notes',
                'value' => 'نتمنى لكم دوام الصحة والعافية. الفاتورة الإلكترونية معتمدة لأغراض التأمين والاسترداد المالي.',
                'group' => 'financial',
                'description' => 'ملاحظات تذييل الفاتورة وسندات القبض',
            ],

            // Clinical & Bed Management Parameters
            [
                'key' => 'total_operational_beds',
                'value' => '150',
                'group' => 'clinical',
                'description' => 'الطاقة الاستيعابية الإجمالية للأسرّة التشغيلية',
            ],
            [
                'key' => 'icu_nurse_patient_ratio',
                'value' => '1:2',
                'group' => 'clinical',
                'description' => 'النسبة المعيارية لممرض العناية المركزة لكل مريض',
            ],
            [
                'key' => 'max_daily_clinic_appointments',
                'value' => '40',
                'group' => 'clinical',
                'description' => 'الحد الأقصى للمواعيد اليومية لكل عيادة تخصصية',
            ],
            [
                'key' => 'default_fefo_expiry_alert_days',
                'value' => '90',
                'group' => 'clinical',
                'description' => 'فترة التنبيه المسبق لقرب انتهاء صلاحية الأدوية (أيام)',
            ],
            [
                'key' => 'auto_bed_release_on_discharge',
                'value' => '1',
                'group' => 'clinical',
                'description' => 'التحرير التلقائي للسرير عند إتمام خروج المريض من التنويم',
            ],

            // System Security & Environment
            [
                'key' => 'system_timezone',
                'value' => 'Africa/Khartoum',
                'group' => 'security',
                'description' => 'المنطقة الزمنية الرسمية للنظام',
            ],
            [
                'key' => 'session_timeout_minutes',
                'value' => '120',
                'group' => 'security',
                'description' => 'مهلة انتهاء جلسة المستخدم الخاملة (بالدقائق)',
            ],
            [
                'key' => 'audit_log_retention_days',
                'value' => '365',
                'group' => 'security',
                'description' => 'فترة حفظ سجلات التدقيق والمراقبة الأمنية (أيام)',
            ],
        ];

        foreach ($settings as $s) {
            SystemSetting::set($s['key'], $s['value'], $s['group'], $s['description']);
        }
    }
}

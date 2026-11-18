<?php

class RolePermissionSeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. Seed Roles
        $roles = [
            ['name' => 'super_admin', 'display_name_ar' => 'مدير النظام الشامل', 'display_name_en' => 'Super Administrator', 'description' => 'كامل الصلاحيات وإدارة النظام', 'is_system' => 1],
            ['name' => 'hospital_director', 'display_name_ar' => 'مدير عام المستشفى', 'display_name_en' => 'Hospital Director', 'description' => 'إشراف إداري ومالي وتقارير عامة', 'is_system' => 1],
            ['name' => 'medical_director', 'display_name_ar' => 'المدير الطبي', 'display_name_en' => 'Medical Director', 'description' => 'إشراف سريري كامل واعتماد السياسات الطبية', 'is_system' => 1],
            ['name' => 'doctor', 'display_name_ar' => 'طبيب معالج', 'display_name_en' => 'Doctor', 'description' => 'معاينة وتشخيص ووصفات وفحوصات', 'is_system' => 1],
            ['name' => 'nurse', 'display_name_ar' => 'تمريض', 'display_name_en' => 'Nurse', 'description' => 'تسجيل العلامات الحيوية وإعطاء العلاج MAR', 'is_system' => 1],
            ['name' => 'receptionist', 'display_name_ar' => 'موظف استقبال', 'display_name_en' => 'Receptionist', 'description' => 'تسجيل المرضى وحجز المواعيد وتنظيم الطوابير', 'is_system' => 1],
            ['name' => 'lab_technician', 'display_name_ar' => 'فني مختبر', 'display_name_en' => 'Laboratory Technician', 'description' => 'استلام العينات وإدخال واعتماد النتائج', 'is_system' => 1],
            ['name' => 'radiologist', 'display_name_ar' => 'أخصائي أشعة', 'display_name_en' => 'Radiologist', 'description' => 'كتابة تقارير الأشعة واعتمادها', 'is_system' => 1],
            ['name' => 'pharmacist', 'display_name_ar' => 'صيدلي', 'display_name_en' => 'Pharmacist', 'description' => 'صرف الأدوية وإدارة مخزون الصيدلية', 'is_system' => 1],
            ['name' => 'accountant', 'display_name_ar' => 'محاسب مالي', 'display_name_en' => 'Accountant', 'description' => 'إدارة الحسابات العامة والقيود والتقارير المالية', 'is_system' => 1],
            ['name' => 'cashier', 'display_name_ar' => 'صراف / أمين صندوق', 'display_name_en' => 'Cashier', 'description' => 'تحصيل الفواتير وإدارة ورديات الصناديق', 'is_system' => 1],
            ['name' => 'insurance_officer', 'display_name_ar' => 'مسؤول التأمين الصحي', 'display_name_en' => 'Insurance Officer', 'description' => 'معالجة بطاقات ومطالبات شركات التأمين', 'is_system' => 1],
            ['name' => 'hr_manager', 'display_name_ar' => 'مدير الموارد البشرية', 'display_name_en' => 'HR Manager', 'description' => 'الموظفون، المناوبات والرواتب', 'is_system' => 1],
            ['name' => 'inventory_manager', 'display_name_ar' => 'أمين المستودع', 'display_name_en' => 'Inventory Manager', 'description' => 'حركة المخازن، الوارد والمنصرف', 'is_system' => 1],
            ['name' => 'procurement_officer', 'display_name_ar' => 'مسؤول المشتريات', 'display_name_en' => 'Procurement Officer', 'description' => 'أوامر الشراء ومتابعة الموردين', 'is_system' => 1],
        ];

        foreach ($roles as $r) {
            $existing = $db->fetchOne("SELECT id FROM `roles` WHERE `name` = :name", ['name' => $r['name']]);
            if (!$existing) {
                $db->query("INSERT INTO `roles` (`name`, `display_name_ar`, `display_name_en`, `description`, `is_system`)
                            VALUES (:name, :display_name_ar, :display_name_en, :description, :is_system)", $r);
            }
        }

        // 2. Seed Permissions
        $permissions = [
            // Users & Security
            ['code' => 'users.view', 'module' => 'Users', 'action' => 'view', 'name_ar' => 'عرض المستخدمين', 'name_en' => 'View Users'],
            ['code' => 'users.create', 'module' => 'Users', 'action' => 'create', 'name_ar' => 'إضافة مستخدم جديد', 'name_en' => 'Create User'],
            ['code' => 'users.edit', 'module' => 'Users', 'action' => 'edit', 'name_ar' => 'تعديل بيانات المستخدم', 'name_en' => 'Edit User'],
            ['code' => 'users.delete', 'module' => 'Users', 'action' => 'delete', 'name_ar' => 'تعطيل وحذف المستخدم', 'name_en' => 'Delete User'],
            ['code' => 'roles.manage', 'module' => 'Users', 'action' => 'approve', 'name_ar' => 'إدارة الأدوار والصلاحيات', 'name_en' => 'Manage Roles & Permissions'],
            ['code' => 'audit.view', 'module' => 'Users', 'action' => 'view', 'name_ar' => 'الاطلاع على سجل التدقيق', 'name_en' => 'View Audit Logs'],

            // Hospital Structure
            ['code' => 'hospital.manage', 'module' => 'Hospital Structure', 'action' => 'edit', 'name_ar' => 'إدارة الأقسام والعيادات والأسرة', 'name_en' => 'Manage Hospital Structure'],

            // Patients & Reception
            ['code' => 'patients.view', 'module' => 'Patients', 'action' => 'view', 'name_ar' => 'عرض سجلات المرضى', 'name_en' => 'View Patients'],
            ['code' => 'patients.create', 'module' => 'Patients', 'action' => 'create', 'name_ar' => 'تسجيل مريض جديد وتوليد MRN', 'name_en' => 'Create Patient MRN'],
            ['code' => 'patients.edit', 'module' => 'Patients', 'action' => 'edit', 'name_ar' => 'تعديل ملف المريض', 'name_en' => 'Edit Patient'],
            ['code' => 'appointments.manage', 'module' => 'Appointments', 'action' => 'create', 'name_ar' => 'حجز وإدارة المواعيد', 'name_en' => 'Manage Appointments'],

            // EMR & Clinical
            ['code' => 'emr.view', 'module' => 'EMR', 'action' => 'view', 'name_ar' => 'عرض السجل الطبي الموحد', 'name_en' => 'View EMR'],
            ['code' => 'emr.diagnoses.create', 'module' => 'EMR', 'action' => 'create', 'name_ar' => 'تسجيل التشخيص والكشف الطبي', 'name_en' => 'Create Diagnosis'],
            ['code' => 'emr.prescriptions.create', 'module' => 'EMR', 'action' => 'create', 'name_ar' => 'كتابة الروشتة الإلكترونية', 'name_en' => 'Create Prescription'],
            ['code' => 'emr.orders.create', 'module' => 'EMR', 'action' => 'create', 'name_ar' => 'طلب فحوصات المختبر والأشعة', 'name_en' => 'Order Lab/Radiology'],

            // Nursing & IPD
            ['code' => 'nursing.vitals.record', 'module' => 'Nursing', 'action' => 'create', 'name_ar' => 'تسجيل العلامات الحيوية', 'name_en' => 'Record Vital Signs'],
            ['code' => 'nursing.mar.administer', 'module' => 'Nursing', 'action' => 'approve', 'name_ar' => 'توثيق إعطاء الأدوية MAR', 'name_en' => 'Administer Medications'],
            ['code' => 'ipd.admissions.manage', 'module' => 'Inpatient', 'action' => 'edit', 'name_ar' => 'إدارة التنويم وتسكين الأسرة', 'name_en' => 'Manage Admissions'],

            // Diagnostics (LIS & RIS)
            ['code' => 'lab.orders.view', 'module' => 'Laboratory', 'action' => 'view', 'name_ar' => 'عرض طلبات الفحوصات', 'name_en' => 'View Lab Orders'],
            ['code' => 'lab.results.enter', 'module' => 'Laboratory', 'action' => 'create', 'name_ar' => 'إدخال نتائج التحاليل', 'name_en' => 'Enter Lab Results'],
            ['code' => 'lab.results.verify', 'module' => 'Laboratory', 'action' => 'approve', 'name_ar' => 'اعتماد نتائج الفحوصات', 'name_en' => 'Verify Lab Results'],
            ['code' => 'radiology.reports.write', 'module' => 'Radiology', 'action' => 'create', 'name_ar' => 'كتابة تقارير الأشعة', 'name_en' => 'Write Radiology Reports'],

            // Pharmacy & Inventory
            ['code' => 'pharmacy.dispense', 'module' => 'Pharmacy', 'action' => 'approve', 'name_ar' => 'صرف الأدوية للمرضى', 'name_en' => 'Dispense Medicines'],
            ['code' => 'inventory.manage', 'module' => 'Inventory', 'action' => 'edit', 'name_ar' => 'إدارة المخازن والجرد', 'name_en' => 'Manage Inventory'],

            // Billing & Finance
            ['code' => 'billing.invoices.create', 'module' => 'Billing', 'action' => 'create', 'name_ar' => 'إصدار الفواتير العلاجية', 'name_en' => 'Create Invoices'],
            ['code' => 'billing.payments.collect', 'module' => 'Billing', 'action' => 'create', 'name_ar' => 'تحصيل المدفوعات وإصدار سند قبض', 'name_en' => 'Collect Payments'],
            ['code' => 'accounting.gl.view', 'module' => 'Accounting', 'action' => 'view', 'name_ar' => 'عرض القيود ودفاتر الأستاذ', 'name_en' => 'View General Ledger'],
            ['code' => 'accounting.gl.post', 'module' => 'Accounting', 'action' => 'approve', 'name_ar' => 'ترحيل القيود المحاسبية والتسويات', 'name_en' => 'Post Journal Entries'],
            ['code' => 'insurance.claims.manage', 'module' => 'Insurance', 'action' => 'edit', 'name_ar' => 'إدارة مطالبات التأمين الصحي', 'name_en' => 'Manage Insurance Claims'],

            // Reports
            ['code' => 'reports.view_general', 'module' => 'Reports', 'action' => 'view', 'name_ar' => 'عرض التقارير التشغيلية والمالية', 'name_en' => 'View Reports'],
        ];

        foreach ($permissions as $p) {
            $existing = $db->fetchOne("SELECT id FROM `permissions` WHERE `code` = :code", ['code' => $p['code']]);
            if (!$existing) {
                $db->query("INSERT INTO `permissions` (`code`, `module`, `action`, `name_ar`, `name_en`)
                            VALUES (:code, :module, :action, :name_ar, :name_en)", $p);
            }
        }

        // 3. Grant ALL permissions to Super Administrator
        $superAdminRole = $db->fetchOne("SELECT id FROM `roles` WHERE `name` = 'super_admin'");
        if ($superAdminRole) {
            $allPerms = $db->fetchAll("SELECT id FROM `permissions`");
            foreach ($allPerms as $perm) {
                $db->query("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
                            VALUES (:role_id, :permission_id)", [
                    'role_id' => $superAdminRole['id'],
                    'permission_id' => $perm['id'],
                ]);
            }
        }
    }
}

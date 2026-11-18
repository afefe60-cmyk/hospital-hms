<?php

class EmrCatalogSeeder
{
    public function run(): void
    {
        $db = \App\Core\Database::getInstance();

        // 1. Seed ICD-10 Diagnoses Catalog
        $diagnoses = [
            ['icd10_code' => 'B54', 'description_en' => 'Unspecified malaria', 'description_ar' => 'الملاريا غير المحددة', 'is_infectious' => 1],
            ['icd10_code' => 'B50.9', 'description_en' => 'Plasmodium falciparum malaria', 'description_ar' => 'ملاريا المنجلية (فالسيبارم)', 'is_infectious' => 1],
            ['icd10_code' => 'A01.0', 'description_en' => 'Typhoid fever', 'description_ar' => 'حمى التيفوئيد', 'is_infectious' => 1],
            ['icd10_code' => 'A09', 'description_en' => 'Infectious gastroenteritis and colitis', 'description_ar' => 'النزلات المعوية الحادة والإسهالات', 'is_infectious' => 1],
            ['icd10_code' => 'E11.9', 'description_en' => 'Type 2 diabetes mellitus without complications', 'description_ar' => 'داء السكري من النوع الثاني بدون مضاعفات', 'is_infectious' => 0],
            ['icd10_code' => 'E10.9', 'description_en' => 'Type 1 diabetes mellitus without complications', 'description_ar' => 'داء السكري من النوع الأول المعتمد على الأنسولين', 'is_infectious' => 0],
            ['icd10_code' => 'I10', 'description_en' => 'Essential (primary) hypertension', 'description_ar' => 'ارتفاع ضغط الدم الشرياني الأولي', 'is_infectious' => 0],
            ['icd10_code' => 'I50.9', 'description_en' => 'Heart failure, unspecified', 'description_ar' => 'قصور عضلة القلب', 'is_infectious' => 0],
            ['icd10_code' => 'J18.9', 'description_en' => 'Pneumonia, unspecified organism', 'description_ar' => 'الالتهاب الرئوي الحاد', 'is_infectious' => 1],
            ['icd10_code' => 'J45.9', 'description_en' => 'Other and unspecified asthma', 'description_ar' => 'الربو الشعبي القصبي (الأزمة الربوية)', 'is_infectious' => 0],
            ['icd10_code' => 'K35.8', 'description_en' => 'Acute appendicitis, other and unspecified', 'description_ar' => 'التهاب الزائدة الدودية الحاد', 'is_infectious' => 0],
            ['icd10_code' => 'D64.9', 'description_en' => 'Anemia, unspecified', 'description_ar' => 'فقر الدم (الأنيميا)', 'is_infectious' => 0],
            ['icd10_code' => 'N39.0', 'description_en' => 'Urinary tract infection, site not specified', 'description_ar' => 'التهاب المسالك البولية', 'is_infectious' => 1],
            ['icd10_code' => 'K29.7', 'description_en' => 'Gastritis, unspecified', 'description_ar' => 'التهاب المعدة وسوء الهضم', 'is_infectious' => 0],
            ['icd10_code' => 'M54.5', 'description_en' => 'Low back pain', 'description_ar' => 'ألم أسفل الظهر الحاد والفقرات', 'is_infectious' => 0],
        ];

        foreach ($diagnoses as $diag) {
            $existing = $db->fetchOne("SELECT id FROM `diagnosis_catalog` WHERE `icd10_code` = :code", ['code' => $diag['icd10_code']]);
            if (!$existing) {
                $db->query("INSERT INTO `diagnosis_catalog` (`icd10_code`, `description_en`, `description_ar`, `is_infectious`)
                            VALUES (:icd10_code, :description_en, :description_ar, :is_infectious)", $diag);
            }
        }

        // 2. Seed Medical Procedures Catalog
        $procedures = [
            ['code' => 'PROC-IV-CAN', 'name_en' => 'IV Cannulation & Infusion', 'name_ar' => 'تركيب كانيولا وريدية ومحاليل', 'price' => 5000.00],
            ['code' => 'PROC-WOUND-DRES', 'name_en' => 'Wound Dressing & Cleaning', 'name_ar' => 'غيار وتنظيف وتطهير الجروح', 'price' => 8000.00],
            ['code' => 'PROC-SUTURE-MIN', 'name_en' => 'Minor Wound Suturing', 'name_ar' => 'خياطة جروح بسيطة وتخدير موضعي', 'price' => 18000.00],
            ['code' => 'PROC-ECG-12', 'name_en' => '12-Lead Electrocardiogram (ECG)', 'name_ar' => 'تخطيط رسم القلب 12 اتجاهاً', 'price' => 12000.00],
            ['code' => 'PROC-NEBULIZER', 'name_en' => 'Nebulizer Therapy Session', 'name_ar' => 'جلسة استنشاق بالرذاذ (نيبولايزر)', 'price' => 6000.00],
        ];

        foreach ($procedures as $proc) {
            $existing = $db->fetchOne("SELECT id FROM `procedure_catalog` WHERE `code` = :code", ['code' => $proc['code']]);
            if (!$existing) {
                $db->query("INSERT INTO `procedure_catalog` (`code`, `name_en`, `name_ar`, `price`)
                            VALUES (:code, :name_en, :name_ar, :price)", $proc);
            }
        }
    }
}

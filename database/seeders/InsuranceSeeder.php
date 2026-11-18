<?php

use App\Core\Database;

class InsuranceSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();

        // Account for insurance receivable (1104)
        $insAcc = $db->fetchOne("SELECT id FROM accounts WHERE account_code = '1104'");
        $insAccId = $insAcc ? (int)$insAcc['id'] : null;

        // 1. Seed Insurance Companies
        $companies = [
            [
                'code' => 'INS-SHEIKAN',
                'name_ar' => 'شركة شيكان للتأمين وإعادة التأمين المحدودة',
                'name_en' => 'Sheikan Insurance & Reinsurance Co.',
                'account_id' => $insAccId,
                'phone' => '+249183784400',
                'email' => 'medical@sheikan.sd',
                'address' => 'الخرطوم - شارع الجمهورية، برج شيكان',
                'default_coverage_rate' => 80.00,
            ],
            [
                'code' => 'INS-BARAKA',
                'name_ar' => 'شركة البركة للتأمين (السودان)',
                'name_en' => 'Al Baraka Insurance Company',
                'account_id' => $insAccId,
                'phone' => '+249183777770',
                'email' => 'claims@albaraka.sd',
                'address' => 'الخرطوم - شارع السيد عبد الرحمن',
                'default_coverage_rate' => 85.00,
            ],
            [
                'code' => 'INS-WATANIA',
                'name_ar' => 'شركة التأمين الوطنية المحدودة',
                'name_en' => 'National Insurance Co. Ltd',
                'account_id' => $insAccId,
                'phone' => '+249183778899',
                'email' => 'info@watania.sd',
                'address' => 'الخرطوم - تقاطع المك نمر مع البلدية',
                'default_coverage_rate' => 75.00,
            ],
            [
                'code' => 'INS-NHIF',
                'name_ar' => 'الصندوق القومي للتأمين الصحي (NHIF)',
                'name_en' => 'National Health Insurance Fund',
                'account_id' => $insAccId,
                'phone' => '+249183471000',
                'email' => 'support@nhif.gov.sd',
                'address' => 'الخرطوم - شارع أفريقيا (المطار)',
                'default_coverage_rate' => 75.00,
            ],
        ];

        foreach ($companies as $comp) {
            $existing = $db->fetchOne("SELECT id FROM insurance_companies WHERE code = :code", ['code' => $comp['code']]);
            if (!$existing) {
                $db->query("INSERT INTO insurance_companies (code, name_ar, name_en, account_id, phone, email, address, default_coverage_rate, is_active)
                            VALUES (:code, :name_ar, :name_en, :account_id, :phone, :email, :address, :default_coverage_rate, 1)", $comp);
            }
        }

        // 2. Seed Patient Insurance Policy
        $patient = $db->fetchOne("SELECT id FROM patients ORDER BY id ASC LIMIT 1");
        $patientId = $patient ? (int)$patient['id'] : 1;
        $sheikan = $db->fetchOne("SELECT id FROM insurance_companies WHERE code = 'INS-SHEIKAN'");
        $sheikanId = $sheikan ? (int)$sheikan['id'] : 1;

        $existingPolicy = $db->fetchOne("SELECT id FROM patient_insurance WHERE policy_number = 'SHK-POL-2026-9901'");
        if (!$existingPolicy) {
            $db->query("INSERT INTO patient_insurance (
                patient_id, company_id, policy_number, member_number, coverage_percentage,
                copay_limit, start_date, expiry_date, is_active
            ) VALUES (
                :pid, :cid, 'SHK-POL-2026-9901', 'MEM-88410', 80.00,
                200000.00, '2026-01-01', '2027-12-31', 1
            )", [
                'pid' => $patientId,
                'cid' => $sheikanId,
            ]);
        }

        // 3. Seed Sample Insurance Claim
        $invoice = $db->fetchOne("SELECT id, total_gross FROM invoices ORDER BY id ASC LIMIT 1");
        $invoiceId = $invoice ? (int)$invoice['id'] : 1;
        $adminUser = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
        $adminUserId = $adminUser ? (int)$adminUser['id'] : 1;

        $existingClaim = $db->fetchOne("SELECT id FROM insurance_claims WHERE claim_number = 'CLM-202611-00001'");
        if (!$existingClaim) {
            $claimAmt = $invoice ? round(((float)$invoice['total_gross'] * 0.8), 2) : 29600.00;
            $db->query("INSERT INTO insurance_claims (
                claim_number, company_id, patient_id, invoice_id, policy_number,
                claim_amount, approved_amount, rejected_amount, status,
                submission_date, settlement_date, notes, created_by
            ) VALUES (
                'CLM-202611-00001', :cid, :pid, :inv_id, 'SHK-POL-2026-9901',
                :claim_amt, :appr_amt, 0.00, 'approved',
                '2026-11-18', '2026-11-18', 'مطالبة تأمينية معتمدة ومطابقة لشروط وثيقة شركة شيكان للتأمين', :uid
            )", [
                'cid' => $sheikanId,
                'pid' => $patientId,
                'inv_id' => $invoiceId,
                'claim_amt' => $claimAmt,
                'appr_amt' => $claimAmt,
                'uid' => $adminUserId,
            ]);
        }
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\InsuranceCompany;
use App\Models\PatientInsurance;
use App\Models\InsuranceClaim;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\AuditLog;

class InsuranceController extends Controller
{
    private InsuranceCompany $companyModel;
    private PatientInsurance $policyModel;
    private InsuranceClaim $claimModel;
    private Database $db;

    public function __construct()
    {
        $this->companyModel = new InsuranceCompany();
        $this->policyModel = new PatientInsurance();
        $this->claimModel = new InsuranceClaim();
        $this->db = Database::getInstance();
    }

    public function index(Request $request, Response $response): string
    {
        $status = $request->query('status');
        $companyId = $request->query('company_id') ? (int)$request->query('company_id') : null;

        $claims = $this->claimModel->listWithDetails($companyId, $status, 50);
        $companies = $this->companyModel->activeCompanies();

        // Metrics
        $totalClaimed = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(claim_amount), 0) FROM insurance_claims WHERE status != 'rejected'");
        $totalApproved = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(approved_amount), 0) FROM insurance_claims WHERE status IN ('approved', 'partially_approved', 'settled')");
        $pendingCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM insurance_claims WHERE status IN ('submitted', 'under_review')");
        $companiesCount = count($companies);

        return $this->view('insurance/index', [
            'claims' => $claims,
            'companies' => $companies,
            'stats' => [
                'total_claimed' => $totalClaimed,
                'total_approved' => $totalApproved,
                'pending_count' => $pendingCount,
                'companies_count' => $companiesCount,
            ],
            'selectedStatus' => $status,
            'selectedCompany' => $companyId,
        ]);
    }

    public function companies(Request $request, Response $response): string
    {
        $companies = $this->db->fetchAll("SELECT ic.*,
            (SELECT COUNT(*) FROM patient_insurance WHERE company_id = ic.id AND is_active = 1) AS active_policies_count,
            (SELECT COUNT(*) FROM insurance_claims WHERE company_id = ic.id) AS total_claims_count,
            (SELECT COALESCE(SUM(claim_amount), 0) FROM insurance_claims WHERE company_id = ic.id) AS total_claims_amount
            FROM insurance_companies ic ORDER BY ic.id ASC");

        return $this->view('insurance/companies', [
            'companies' => $companies,
        ]);
    }

    public function storeCompany(Request $request, Response $response): void
    {
        $code = trim($request->input('code'));
        $nameAr = trim($request->input('name_ar'));
        $nameEn = trim($request->input('name_en'));
        $phone = trim($request->input('phone'));
        $email = trim($request->input('email'));
        $address = trim($request->input('address'));
        $rate = (float)$request->input('default_coverage_rate', 80.00);

        if (empty($code) || empty($nameAr) || empty($phone)) {
            Session::flash('error', 'يرجى إكمال الحقول الإلزامية لشركة التأمين (الكود، الاسم، الهاتف).');
            $this->redirect(url('/insurance/companies'));
            return;
        }

        $insAcc = $this->db->fetchOne("SELECT id FROM accounts WHERE account_code = '1104'");

        $this->companyModel->create([
            'code' => $code,
            'name_ar' => $nameAr,
            'name_en' => $nameEn ?: $nameAr,
            'account_id' => $insAcc ? $insAcc['id'] : null,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'default_coverage_rate' => $rate,
            'is_active' => 1,
        ]);

        Session::flash('success', "تم تسجيل شركة التأمين {$nameAr} بنجاح.");
        $this->redirect(url('/insurance/companies'));
    }

    public function policies(Request $request, Response $response): string
    {
        $companyId = $request->query('company_id') ? (int)$request->query('company_id') : null;
        $policies = $this->policyModel->listWithDetails($companyId);
        $companies = $this->companyModel->activeCompanies();
        $patients = $this->db->fetchAll("SELECT id, mrn, CONCAT(first_name, ' ', second_name, ' ', last_name) AS name FROM patients ORDER BY id DESC LIMIT 50");

        return $this->view('insurance/policies', [
            'policies' => $policies,
            'companies' => $companies,
            'patients' => $patients,
            'selectedCompany' => $companyId,
        ]);
    }

    public function storePolicy(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $companyId = (int)$request->input('company_id');
        $policyNumber = trim($request->input('policy_number'));
        $memberNumber = trim($request->input('member_number'));
        $coverage = (float)$request->input('coverage_percentage', 80.00);
        $copayLimit = $request->input('copay_limit') ? (float)$request->input('copay_limit') : null;
        $expiryDate = $request->input('expiry_date');

        if (!$patientId || !$companyId || empty($policyNumber) || empty($expiryDate)) {
            Session::flash('error', 'يرجى ملء جميع الحقول المطلوبة لوثيقة التأمين.');
            $this->redirect(url('/insurance/policies'));
            return;
        }

        $this->policyModel->create([
            'patient_id' => $patientId,
            'company_id' => $companyId,
            'policy_number' => $policyNumber,
            'member_number' => $memberNumber,
            'coverage_percentage' => $coverage,
            'copay_limit' => $copayLimit,
            'start_date' => date('Y-m-d'),
            'expiry_date' => $expiryDate,
            'is_active' => 1,
        ]);

        Session::flash('success', "تم ربط وثيقة التأمين رقم {$policyNumber} بملف المريض بنجاح.");
        $this->redirect(url('/insurance/policies'));
    }

    public function createClaim(Request $request, Response $response): string
    {
        $invoiceId = $request->query('invoice_id');
        $invoice = null;
        $policy = null;

        if ($invoiceId) {
            $invModel = new Invoice();
            $invoice = $invModel->detailed((int)$invoiceId);
            if ($invoice) {
                $policy = $this->policyModel->activePolicyForPatient((int)$invoice['patient_id']);
            }
        }

        $invoices = $this->db->fetchAll("SELECT inv.id, inv.invoice_number, inv.total_gross, inv.total_patient_due,
            pat.id AS patient_id, pat.mrn, CONCAT(pat.first_name, ' ', pat.second_name, ' ', pat.last_name) AS patient_name
            FROM invoices inv
            JOIN patients pat ON inv.patient_id = pat.id
            WHERE inv.status != 'cancelled'
            ORDER BY inv.id DESC LIMIT 30");

        $companies = $this->companyModel->activeCompanies();

        return $this->view('insurance/create_claim', [
            'invoice' => $invoice,
            'invoices' => $invoices,
            'policy' => $policy,
            'companies' => $companies,
        ]);
    }

    public function storeClaim(Request $request, Response $response): void
    {
        $invoiceId = (int)$request->input('invoice_id');
        $companyId = (int)$request->input('company_id');
        $policyNumber = trim($request->input('policy_number'));
        $claimAmount = (float)$request->input('claim_amount', 0);
        $notes = trim($request->input('notes', ''));

        $invModel = new Invoice();
        $invoice = $invModel->find($invoiceId);

        if (!$invoice || !$companyId || empty($policyNumber) || $claimAmount <= 0) {
            Session::flash('error', 'يرجى إكمال بيانات المطالبة واختيار الفاتورة وتحديد المبلغ المطالب به.');
            $this->redirect(url('/insurance/claims/create'));
            return;
        }

        // Check if invoice already has a claim
        $existing = $this->db->fetchOne("SELECT id, claim_number FROM insurance_claims WHERE invoice_id = :iid", ['iid' => $invoiceId]);
        if ($existing) {
            Session::flash('error', 'هذه الفاتورة مرفوع عليها مطالبة تأمينية مسبقاً برقم ' . $existing['claim_number']);
            $this->redirect(url('/insurance/claims/' . $existing['id']));
            return;
        }

        $claimNumber = $this->claimModel->generateClaimNumber();

        $claimData = [
            'claim_number' => $claimNumber,
            'company_id' => $companyId,
            'patient_id' => $invoice['patient_id'],
            'invoice_id' => $invoiceId,
            'policy_number' => $policyNumber,
            'claim_amount' => $claimAmount,
            'status' => 'submitted',
            'submission_date' => date('Y-m-d'),
            'notes' => $notes,
            'created_by' => auth()->id(),
        ];

        $claimId = $this->claimModel->create($claimData);

        // Update total_insurance on invoice and recalculate
        $invModel->update($invoiceId, ['total_insurance' => $claimAmount]);
        $invModel->recalculateTotals($invoiceId);

        AuditLog::log('insurance_claims', $claimId, 'CREATE', null, $claimData);

        Session::flash('success', "تم رفع وإصدار المطالبة التأمينية بنجاح برقم {$claimNumber}.");
        $this->redirect(url('/insurance/claims/' . $claimId));
    }

    public function showClaim(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $claim = $this->claimModel->detailed($id);

        if (!$claim) {
            Session::flash('error', 'المطالبة التأمينية غير موجودة.');
            $this->redirect(url('/insurance'));
            return '';
        }

        $invModel = new Invoice();
        $items = $invModel->items((int)$claim['invoice_id']);

        return $this->view('insurance/show_claim', [
            'claim' => $claim,
            'items' => $items,
        ]);
    }

    public function updateClaimStatus(Request $request, Response $response): void
    {
        $id = (int)$request->input('claim_id');
        $status = $request->input('status');
        $approvedAmount = $request->input('approved_amount') !== '' ? (float)$request->input('approved_amount') : null;
        $rejectedAmount = $request->input('rejected_amount') !== '' ? (float)$request->input('rejected_amount') : null;
        $reason = trim($request->input('rejection_reason', ''));

        $claim = $this->claimModel->find($id);
        if (!$claim) {
            Session::flash('error', 'المطالبة غير موجودة.');
            $this->redirect(url('/insurance'));
            return;
        }

        $updates = [
            'status' => $status,
            'approved_amount' => $approvedAmount,
            'rejected_amount' => $rejectedAmount,
            'rejection_reason' => $reason,
        ];

        if ($status === 'settled') {
            $updates['settlement_date'] = date('Y-m-d');
        }

        $this->claimModel->update($id, $updates);

        AuditLog::log('insurance_claims', $id, 'UPDATE_STATUS', $claim, $updates);

        Session::flash('success', 'تم تحديث حالة المطالبة التأمينية بنجاح.');
        $this->redirect(url('/insurance/claims/' . $id));
    }

    public function printClaim(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $claim = $this->claimModel->detailed($id);

        if (!$claim) {
            Session::flash('error', 'المطالبة غير موجودة.');
            $this->redirect(url('/insurance'));
            return '';
        }

        $invModel = new Invoice();
        $items = $invModel->items((int)$claim['invoice_id']);

        return $this->view('insurance/print_claim', [
            'claim' => $claim,
            'items' => $items,
        ], null);
    }

    public function printBatch(Request $request, Response $response): string
    {
        $companyId = $request->query('company_id') ? (int)$request->query('company_id') : null;
        $company = $companyId ? $this->companyModel->find($companyId) : null;
        $claims = $this->claimModel->listWithDetails($companyId, null, 100);

        return $this->view('insurance/print_batch', [
            'company' => $company,
            'claims' => $claims,
        ], null);
    }
}

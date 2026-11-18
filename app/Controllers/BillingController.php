<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\ServiceCatalog;
use App\Models\CashboxSession;
use App\Models\Patient;
use App\Models\AuditLog;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Account;

class BillingController extends Controller
{
    private Invoice $invoiceModel;
    private InvoiceItem $itemModel;
    private Payment $paymentModel;
    private ServiceCatalog $serviceModel;
    private CashboxSession $sessionModel;
    private Database $db;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->itemModel = new InvoiceItem();
        $this->paymentModel = new Payment();
        $this->serviceModel = new ServiceCatalog();
        $this->sessionModel = new CashboxSession();
        $this->db = Database::getInstance();
    }

    public function index(Request $request, Response $response): string
    {
        $status = $request->query('status');
        $patientId = $request->query('patient_id') ? (int)$request->query('patient_id') : null;

        $invoices = $this->invoiceModel->listWithDetails($status, $patientId, 50);

        // Financial Metrics
        $totalBilledToday = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_patient_due), 0) FROM invoices WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
        $totalCollectedToday = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(created_at) = CURDATE()");
        $totalBankakToday = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(created_at) = CURDATE() AND payment_method = 'bankak'");
        $totalReceivable = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_patient_due - paid_amount), 0) FROM invoices WHERE status IN ('issued', 'partially_paid')");

        return $this->view('billing/index', [
            'invoices' => $invoices,
            'stats' => [
                'billed_today' => $totalBilledToday,
                'collected_today' => $totalCollectedToday,
                'bankak_today' => $totalBankakToday,
                'total_receivable' => $totalReceivable,
            ],
            'selectedStatus' => $status,
        ]);
    }

    public function create(Request $request, Response $response): string
    {
        $patientId = $request->query('patient_id');
        $patient = null;
        if ($patientId) {
            $patientModel = new Patient();
            $patient = $patientModel->find((int)$patientId);
        }

        $patients = $this->db->fetchAll("SELECT id, mrn, CONCAT(first_name, ' ', second_name, ' ', last_name) AS name, phone_primary FROM patients ORDER BY id DESC LIMIT 50");
        $services = $this->serviceModel->activeServices();

        return $this->view('billing/create', [
            'patient' => $patient,
            'patients' => $patients,
            'services' => $services,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $discount = (float)$request->input('total_discount', 0);
        $insurance = (float)$request->input('total_insurance', 0);
        $notes = trim($request->input('notes', ''));

        $serviceIds = $request->input('service_id', []);
        $descriptions = $request->input('description', []);
        $quantities = $request->input('quantity', []);
        $prices = $request->input('unit_price', []);

        if (!$patientId || empty($descriptions)) {
            Session::flash('error', 'يرجى اختيار المريض وإضافة بند واحد على الأقل في الفاتورة.');
            $this->redirect(url('/billing/create'));
            return;
        }

        $invoiceNumber = $this->invoiceModel->generateInvoiceNumber();

        $gross = 0.00;
        $itemsToInsert = [];

        foreach ($descriptions as $index => $desc) {
            $desc = trim($desc);
            if (empty($desc)) continue;

            $qty = max(1.0, (float)($quantities[$index] ?? 1));
            $unitPrice = max(0.0, (float)($prices[$index] ?? 0));
            $subtotal = $qty * $unitPrice;
            $gross += $subtotal;

            $srvId = !empty($serviceIds[$index]) ? (int)$serviceIds[$index] : null;

            $itemsToInsert[] = [
                'service_id' => $srvId,
                'item_type' => 'service',
                'description' => $desc,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_amount' => $subtotal,
            ];
        }

        if (empty($itemsToInsert)) {
            Session::flash('error', 'لا يمكن حفظ فاتورة بدون بنود مالية صحيحة.');
            $this->redirect(url('/billing/create'));
            return;
        }

        $patientDue = max(0.00, $gross - $discount - $insurance);

        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'patient_id' => $patientId,
            'admission_id' => $request->input('admission_id') ? (int)$request->input('admission_id') : null,
            'total_gross' => $gross,
            'total_discount' => $discount,
            'total_insurance' => $insurance,
            'total_patient_due' => $patientDue,
            'paid_amount' => 0.00,
            'status' => 'issued',
            'created_by' => auth()->id(),
            'notes' => $notes,
        ];

        $invoiceId = $this->invoiceModel->create($invoiceData);

        foreach ($itemsToInsert as $item) {
            $item['invoice_id'] = $invoiceId;
            $this->itemModel->create($item);
        }

        AuditLog::log('invoices', $invoiceId, 'CREATE', null, $invoiceData);

        // Automatic Accounting Entry: Debit Accounts Receivable Patients (1103), Credit Revenue (4101)
        $accRec = $this->db->fetchOne("SELECT id FROM accounts WHERE account_code = '1103'");
        $accRev = $this->db->fetchOne("SELECT id FROM accounts WHERE account_code = '4101'");

        if ($accRec && $accRev && $patientDue > 0) {
            $jModel = new JournalEntry();
            $jdModel = new JournalDetail();
            $jvNum = $jModel->generateEntryNumber();

            $jvId = $jModel->create([
                'entry_number' => $jvNum,
                'entry_date' => date('Y-m-d'),
                'description' => "استحقاق فاتورة علاجية رقم {$invoiceNumber}",
                'source_document' => $invoiceNumber,
                'is_posted' => 1,
                'posted_by' => auth()->id(),
            ]);

            // Debit AR
            $jdModel->create([
                'journal_entry_id' => $jvId,
                'account_id' => $accRec['id'],
                'debit' => $patientDue,
                'credit' => 0.00,
            ]);

            // Credit Revenue
            $jdModel->create([
                'journal_entry_id' => $jvId,
                'account_id' => $accRev['id'],
                'debit' => 0.00,
                'credit' => $patientDue,
            ]);
        }

        Session::flash('success', "تم إصدار الفاتورة العلاجية بنجاح برقم {$invoiceNumber}.");
        $this->redirect(url('/billing/' . $invoiceId));
    }

    public function show(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $invoice = $this->invoiceModel->detailed($id);

        if (!$invoice) {
            Session::flash('error', 'الفاتورة المطلوبة غير موجودة.');
            $this->redirect(url('/billing'));
            return '';
        }

        $items = $this->invoiceModel->items($id);
        $payments = $this->invoiceModel->payments($id);

        // Check if current user has an open cashier session
        $userSession = $this->sessionModel->activeSessionForUser(auth()->id());
        $anyOpenSession = $this->db->fetchOne("SELECT * FROM cashbox_sessions WHERE status = 'open' LIMIT 1");

        return $this->view('billing/show', [
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
            'remainingDue' => max(0.00, $invoice['total_patient_due'] - $invoice['paid_amount']),
            'userSession' => $userSession,
            'openSession' => $anyOpenSession,
        ]);
    }

    public function addPayment(Request $request, Response $response): void
    {
        $invoiceId = (int)$request->input('invoice_id');
        $amount = (float)$request->input('amount', 0);
        $method = $request->input('payment_method', 'cash');
        $ref = trim($request->input('transaction_reference', ''));
        $notes = trim($request->input('notes', ''));

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            Session::flash('error', 'الفاتورة غير موجودة.');
            $this->redirect(url('/billing'));
            return;
        }

        $remaining = max(0.00, $invoice['total_patient_due'] - $invoice['paid_amount']);
        if ($amount <= 0) {
            Session::flash('error', 'يرجى إدخال مبلغ تحصيل صحيح.');
            $this->redirect(url('/billing/' . $invoiceId));
            return;
        }

        if ($amount > $remaining) {
            Session::flash('error', 'المبلغ المدخل يتجاوز المتبقي على الفاتورة (' . number_format($remaining, 2) . ' SDG).');
            $this->redirect(url('/billing/' . $invoiceId));
            return;
        }

        // Get session
        $sessionId = null;
        $userSession = $this->sessionModel->activeSessionForUser(auth()->id());
        if ($userSession) {
            $sessionId = (int)$userSession['id'];
        } else {
            $anyOpen = $this->db->fetchOne("SELECT id FROM cashbox_sessions WHERE status = 'open' LIMIT 1");
            if ($anyOpen) {
                $sessionId = (int)$anyOpen['id'];
            }
        }

        $receiptNumber = $this->paymentModel->generateReceiptNumber();

        $paymentData = [
            'receipt_number' => $receiptNumber,
            'invoice_id' => $invoiceId,
            'session_id' => $sessionId,
            'amount' => $amount,
            'payment_method' => $method,
            'transaction_reference' => $ref,
            'notes' => $notes,
            'collected_by' => auth()->id(),
        ];

        $paymentId = $this->paymentModel->create($paymentData);

        // Recalculate invoice paid amount & status
        $this->invoiceModel->recalculateTotals($invoiceId);

        AuditLog::log('payments', $paymentId, 'CREATE', null, $paymentData);

        // Accounting Journal Entry: Debit Cash (1101) or Bank (1102), Credit AR Patients (1103)
        $accCode = ($method === 'bankak' || $method === 'bank_transfer') ? '1102' : '1101';
        $accDebit = $this->db->fetchOne("SELECT id FROM accounts WHERE account_code = :c", ['c' => $accCode]);
        $accCredit = $this->db->fetchOne("SELECT id FROM accounts WHERE account_code = '1103'");

        if ($accDebit && $accCredit) {
            $jModel = new JournalEntry();
            $jdModel = new JournalDetail();
            $jvNum = $jModel->generateEntryNumber();

            $jvId = $jModel->create([
                'entry_number' => $jvNum,
                'entry_date' => date('Y-m-d'),
                'description' => "تحصيل سند قبض رقم {$receiptNumber} للفاتورة {$invoice['invoice_number']}",
                'source_document' => $receiptNumber,
                'is_posted' => 1,
                'posted_by' => auth()->id(),
            ]);

            // Debit Cash / Bank
            $jdModel->create([
                'journal_entry_id' => $jvId,
                'account_id' => $accDebit['id'],
                'debit' => $amount,
                'credit' => 0.00,
            ]);

            // Credit AR Patients
            $jdModel->create([
                'journal_entry_id' => $jvId,
                'account_id' => $accCredit['id'],
                'debit' => 0.00,
                'credit' => $amount,
            ]);
        }

        Session::flash('success', "تم تحصيل المبلغ بنجاح وإصدار سند القبض رقم {$receiptNumber}.");
        $this->redirect(url('/billing/' . $invoiceId));
    }

    public function printInvoice(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $invoice = $this->invoiceModel->detailed($id);

        if (!$invoice) {
            Session::flash('error', 'الفاتورة غير موجودة.');
            $this->redirect(url('/billing'));
            return '';
        }

        $items = $this->invoiceModel->items($id);
        $payments = $this->invoiceModel->payments($id);

        return $this->view('billing/print_invoice', [
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
        ], null);
    }

    public function printReceipt(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $payment = $this->paymentModel->detailed($id);

        if (!$payment) {
            Session::flash('error', 'سند القبض غير موجود.');
            $this->redirect(url('/billing'));
            return '';
        }

        return $this->view('billing/print_receipt', [
            'payment' => $payment,
        ], null);
    }

    public function services(Request $request, Response $response): string
    {
        $services = $this->serviceModel->activeServices();
        $departments = $this->db->fetchAll("SELECT * FROM departments ORDER BY name_ar ASC");

        return $this->view('billing/services', [
            'services' => $services,
            'departments' => $departments,
        ]);
    }

    public function storeService(Request $request, Response $response): void
    {
        $code = trim($request->input('code'));
        $nameAr = trim($request->input('name_ar'));
        $nameEn = trim($request->input('name_en'));
        $depId = (int)$request->input('department_id');
        $price = (float)$request->input('price_standard');

        if (empty($code) || empty($nameAr) || !$depId || $price < 0) {
            Session::flash('error', 'يرجى إكمال الحقول الإلزامية للخدمة الطبية.');
            $this->redirect(url('/billing/services'));
            return;
        }

        $acc = $this->db->fetchOne("SELECT id FROM accounts WHERE account_code = '4101'");

        $this->serviceModel->create([
            'code' => $code,
            'name_ar' => $nameAr,
            'name_en' => $nameEn ?: $nameAr,
            'department_id' => $depId,
            'revenue_account_id' => $acc ? $acc['id'] : null,
            'price_standard' => $price,
            'is_active' => 1,
        ]);

        Session::flash('success', "تمت إضافة الخدمة الطبية {$nameAr} إلى الدليل بنجاح.");
        $this->redirect(url('/billing/services'));
    }
}

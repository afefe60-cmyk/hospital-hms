<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Cashbox;
use App\Models\CashboxSession;
use App\Models\Payment;
use App\Models\AuditLog;

class CashboxController extends Controller
{
    private Cashbox $cashboxModel;
    private CashboxSession $sessionModel;
    private Database $db;

    public function __construct()
    {
        $this->cashboxModel = new Cashbox();
        $this->sessionModel = new CashboxSession();
        $this->db = Database::getInstance();
    }

    public function index(Request $request, Response $response): string
    {
        $cashboxes = $this->db->fetchAll("SELECT cb.*,
            (SELECT session_number FROM cashbox_sessions WHERE cashbox_id = cb.id AND status = 'open' LIMIT 1) AS active_session_number,
            (SELECT u.full_name FROM cashbox_sessions cs JOIN users u ON cs.cashier_user_id = u.id WHERE cs.cashbox_id = cb.id AND cs.status = 'open' LIMIT 1) AS active_cashier_name
            FROM cashboxes cb ORDER BY cb.id ASC");

        $activeSessions = $this->db->fetchAll("SELECT cs.*, cb.name_ar AS cashbox_name, u.full_name AS cashier_name,
            (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE session_id = cs.id) AS total_collected
            FROM cashbox_sessions cs
            JOIN cashboxes cb ON cs.cashbox_id = cb.id
            JOIN users u ON cs.cashier_user_id = u.id
            WHERE cs.status = 'open'
            ORDER BY cs.opened_at DESC");

        $closedSessions = $this->db->fetchAll("SELECT cs.*, cb.name_ar AS cashbox_name, u.full_name AS cashier_name,
            (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE session_id = cs.id) AS total_collected
            FROM cashbox_sessions cs
            JOIN cashboxes cb ON cs.cashbox_id = cb.id
            JOIN users u ON cs.cashier_user_id = u.id
            WHERE cs.status = 'closed'
            ORDER BY cs.closed_at DESC LIMIT 20");

        $myActiveSession = $this->sessionModel->activeSessionForUser(auth()->id());

        return $this->view('cashboxes/index', [
            'cashboxes' => $cashboxes,
            'activeSessions' => $activeSessions,
            'closedSessions' => $closedSessions,
            'myActiveSession' => $myActiveSession,
        ]);
    }

    public function openSession(Request $request, Response $response): void
    {
        $cashboxId = (int)$request->input('cashbox_id');
        $openingBalance = (float)$request->input('opening_balance', 0);
        $notes = trim($request->input('notes', ''));

        if (!$cashboxId) {
            Session::flash('error', 'يرجى اختيار الصندوق المراد فتح الوردية عليه.');
            $this->redirect(url('/cashboxes'));
            return;
        }

        // Check if user already has an open session
        $existing = $this->sessionModel->activeSessionForUser(auth()->id());
        if ($existing) {
            Session::flash('error', 'لديك بالفعل وردية صندوق مفتوحة حالياً برقم ' . $existing['session_number']);
            $this->redirect(url('/cashboxes'));
            return;
        }

        // Check if cashbox already has an open session by someone else
        $boxOpen = $this->db->fetchOne("SELECT id, session_number FROM cashbox_sessions WHERE cashbox_id = :cid AND status = 'open'", ['cid' => $cashboxId]);
        if ($boxOpen) {
            Session::flash('error', 'هذا الصندوق مشغول بالفعل بوردية مفتوحة حالياً برقم ' . $boxOpen['session_number']);
            $this->redirect(url('/cashboxes'));
            return;
        }

        $sessionNumber = $this->sessionModel->generateSessionNumber();

        $data = [
            'session_number' => $sessionNumber,
            'cashbox_id' => $cashboxId,
            'cashier_user_id' => auth()->id(),
            'opened_at' => date('Y-m-d H:i:s'),
            'opening_balance' => $openingBalance,
            'notes' => $notes,
            'status' => 'open',
        ];

        $sessionId = $this->sessionModel->create($data);

        AuditLog::log('cashbox_sessions', $sessionId, 'OPEN_SHIFT', null, $data);

        Session::flash('success', "تم فتح وردية الصندوق بنجاح برقم {$sessionNumber}.");
        $this->redirect(url('/cashboxes/sessions/' . $sessionId));
    }

    public function closeSession(Request $request, Response $response): void
    {
        $sessionId = (int)$request->input('session_id');
        $actualCash = (float)$request->input('actual_closing', 0);
        $notes = trim($request->input('notes', ''));

        $session = $this->sessionModel->find($sessionId);
        if (!$session || $session['status'] !== 'open') {
            Session::flash('error', 'وردية الصندوق غير موجودة أو تم إغلاقها مسبقاً.');
            $this->redirect(url('/cashboxes'));
            return;
        }

        // Sum cash payments collected during this shift
        $cashCollected = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE session_id = :sid AND payment_method = 'cash'", ['sid' => $sessionId]);
        $expectedCash = (float)$session['opening_balance'] + $cashCollected;
        $difference = $actualCash - $expectedCash;

        $updates = [
            'closed_at' => date('Y-m-d H:i:s'),
            'expected_closing' => $expectedCash,
            'actual_closing' => $actualCash,
            'difference' => $difference,
            'notes' => $notes ?: $session['notes'],
            'status' => 'closed',
        ];

        $this->sessionModel->update($sessionId, $updates);

        AuditLog::log('cashbox_sessions', $sessionId, 'CLOSE_SHIFT', $session, $updates);

        Session::flash('success', "تم إغلاق وتسليم وردية الصندوق بنجاح. الفارق المسجل: " . number_format($difference, 2) . " SDG.");
        $this->redirect(url('/cashboxes/sessions/' . $sessionId));
    }

    public function showSession(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $session = $this->sessionModel->detailed($id);

        if (!$session) {
            Session::flash('error', 'وردية الصندوق غير موجودة.');
            $this->redirect(url('/cashboxes'));
            return '';
        }

        $payments = $this->sessionModel->sessionPayments($id);

        $totalCash = 0.00;
        $totalBankak = 0.00;
        $totalCard = 0.00;
        $totalOther = 0.00;

        foreach ($payments as $p) {
            $amt = (float)$p['amount'];
            if ($p['payment_method'] === 'cash') $totalCash += $amt;
            elseif ($p['payment_method'] === 'bankak') $totalBankak += $amt;
            elseif ($p['payment_method'] === 'card') $totalCard += $amt;
            else $totalOther += $amt;
        }

        $totalCollected = $totalCash + $totalBankak + $totalCard + $totalOther;

        return $this->view('cashboxes/show', [
            'session' => $session,
            'payments' => $payments,
            'breakdown' => [
                'cash' => $totalCash,
                'bankak' => $totalBankak,
                'card' => $totalCard,
                'other' => $totalOther,
                'total' => $totalCollected,
            ],
        ]);
    }

    public function printSession(Request $request, Response $response, ?string $id = null): string
    {
        $id = (int)($id ?: $request->query('id'));
        $session = $this->sessionModel->detailed($id);

        if (!$session) {
            Session::flash('error', 'وردية الصندوق غير موجودة.');
            $this->redirect(url('/cashboxes'));
            return '';
        }

        $payments = $this->sessionModel->sessionPayments($id);

        $totalCash = 0.00;
        $totalBankak = 0.00;
        $totalCard = 0.00;
        foreach ($payments as $p) {
            if ($p['payment_method'] === 'cash') $totalCash += (float)$p['amount'];
            elseif ($p['payment_method'] === 'bankak') $totalBankak += (float)$p['amount'];
            elseif ($p['payment_method'] === 'card') $totalCard += (float)$p['amount'];
        }

        return $this->view('cashboxes/print_session', [
            'session' => $session,
            'payments' => $payments,
            'breakdown' => [
                'cash' => $totalCash,
                'bankak' => $totalBankak,
                'card' => $totalCard,
                'total' => $totalCash + $totalBankak + $totalCard,
            ],
        ], null);
    }
}

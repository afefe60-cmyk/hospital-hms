<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\PharmacyDispense;
use App\Models\PharmacyDispenseItem;
use App\Models\Prescription;
use App\Models\StockMovement;
use App\Models\Warehouse;

class PharmacyController extends Controller
{
    private PharmacyDispense $dispenseModel;
    private PharmacyDispenseItem $dispenseItemModel;
    private Prescription $rxModel;
    private Medicine $medicineModel;
    private MedicineBatch $batchModel;
    private Patient $patientModel;
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->dispenseModel = new PharmacyDispense();
        $this->dispenseItemModel = new PharmacyDispenseItem();
        $this->rxModel = new Prescription();
        $this->medicineModel = new Medicine();
        $this->batchModel = new MedicineBatch();
        $this->patientModel = new Patient();
        $this->warehouseModel = new Warehouse();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();

        // 1. Pending Prescriptions waiting for dispensing
        $sqlPendingRx = "SELECT rx.*,
                                p.mrn, p.gender,
                                CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                                TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS patient_age,
                                CONCAT(e.first_name, ' ', e.second_name, ' ', e.last_name) AS doctor_name,
                                COUNT(rxi.id) AS items_count
                         FROM `prescriptions` rx
                         INNER JOIN `patients` p ON rx.patient_id = p.id
                         INNER JOIN `doctors` doc ON rx.doctor_id = doc.id
                         INNER JOIN `employees` e ON doc.employee_id = e.id
                         LEFT JOIN `prescription_items` rxi ON rx.id = rxi.prescription_id
                         WHERE rx.status = 'pending'
                         GROUP BY rx.id
                         ORDER BY rx.created_at ASC";
        $pendingRx = $db->fetchAll($sqlPendingRx);

        // 2. Recent Completed Dispenses
        $sqlDispenses = "SELECT pd.*,
                                p.mrn,
                                CONCAT(p.first_name, ' ', p.second_name, ' ', p.last_name) AS patient_name,
                                w.name_ar AS warehouse_name,
                                u.full_name AS pharmacist_name,
                                COUNT(pdi.id) AS items_count
                         FROM `pharmacy_dispenses` pd
                         INNER JOIN `patients` p ON pd.patient_id = p.id
                         INNER JOIN `warehouses` w ON pd.warehouse_id = w.id
                         INNER JOIN `users` u ON pd.pharmacist_id = u.id
                         LEFT JOIN `pharmacy_dispense_items` pdi ON pd.id = pdi.pharmacy_dispense_id
                         GROUP BY pd.id
                         ORDER BY pd.created_at DESC LIMIT 50";
        $dispenses = $db->fetchAll($sqlDispenses);

        // 3. Today's metrics
        $today = date('Y-m-d');
        $todaySales = (float)$db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0.00) FROM `pharmacy_dispenses` WHERE DATE(created_at) = :today", ['today' => $today]);
        $dispenseCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM `pharmacy_dispenses` WHERE DATE(created_at) = :today", ['today' => $today]);

        return $this->view('pharmacy/index', [
            'pageTitle' => 'إدارة الصيدلية وصرف الأدوية (Pharmacy Dispensing & FEFO)',
            'pendingRx' => $pendingRx,
            'dispenses' => $dispenses,
            'todaySales' => $todaySales,
            'dispenseCount' => $dispenseCount,
        ]);
    }

    public function dispense(Request $request, Response $response, ?string $prescriptionId = null): string
    {
        $rx = null;
        $rxItems = [];
        $patient = null;

        if ($prescriptionId) {
            $rx = $this->rxModel->detailed((int)$prescriptionId);
            if ($rx) {
                $rxItems = $this->rxModel->items((int)$prescriptionId);
                $patient = $this->patientModel->find($rx['patient_id']);
            }
        }

        $patients = $this->patientModel->all('first_name', 'ASC');
        $warehouses = $this->warehouseModel->where('is_active', 1);
        $medicines = $this->medicineModel->where('is_active', 1);

        // For each medicine, get available FEFO batches
        $db = \App\Core\Database::getInstance();
        $batches = $db->fetchAll("SELECT b.*, m.commercial_name, m.scientific_name
                                  FROM `medicine_batches` b
                                  INNER JOIN `medicines` m ON b.medicine_id = m.id
                                  WHERE b.status = 'active' AND b.quantity_on_hand > 0
                                  ORDER BY b.expiry_date ASC");

        return $this->view('pharmacy/dispense', [
            'pageTitle' => 'شاشة صرف الأدوية (Dispense Medicine - FEFO)',
            'rx' => $rx,
            'rxItems' => $rxItems,
            'patient' => $patient,
            'patients' => $patients,
            'warehouses' => $warehouses,
            'medicines' => $medicines,
            'batches' => $batches,
        ]);
    }

    public function storeDispense(Request $request, Response $response): void
    {
        $patientId = (int)$request->input('patient_id');
        $warehouseId = (int)$request->input('warehouse_id');
        $prescriptionId = $request->input('prescription_id') ? (int)$request->input('prescription_id') : null;
        $batchIds = $request->input('batch_id', []);
        $quantities = $request->input('quantity', []);
        $notes = trim((string)$request->input('notes'));

        if ($patientId <= 0 || $warehouseId <= 0 || empty($batchIds)) {
            Session::flash('error', 'يرجى اختيار المريض والمخزن وتحديد الأدوية المصروفة.');
            $this->redirect(url('/pharmacy/dispense'));
            return;
        }

        $db = \App\Core\Database::getInstance();
        $totalAmount = 0.00;
        $itemsToDispense = [];

        // Validate stock before proceeding
        for ($i = 0; $i < count($batchIds); $i++) {
            $bid = (int)($batchIds[$i] ?? 0);
            $qty = max(1, (int)($quantities[$i] ?? 1));

            if ($bid > 0) {
                $batch = $this->batchModel->find($bid);
                if (!$batch || $batch['quantity_on_hand'] < $qty) {
                    Session::flash('error', "الكمية المطلوبة غير متوفرة في التشغيلة ({$batch['batch_number']}). المتوفر: {$batch['quantity_on_hand']}");
                    $this->redirect(url('/pharmacy/dispense'));
                    return;
                }

                $itemTotal = $qty * (float)$batch['sale_price'];
                $totalAmount += $itemTotal;

                $itemsToDispense[] = [
                    'medicine_id' => $batch['medicine_id'],
                    'batch_id' => $bid,
                    'quantity' => $qty,
                    'unit_price' => $batch['sale_price'],
                    'total_price' => $itemTotal,
                    'remaining' => $batch['quantity_on_hand'] - $qty,
                ];
            }
        }

        // Create dispense header
        $dispenseNumber = $this->dispenseModel->generateDispenseNumber();
        $dispenseId = $this->dispenseModel->create([
            'dispense_number' => $dispenseNumber,
            'prescription_id' => $prescriptionId,
            'patient_id' => $patientId,
            'warehouse_id' => $warehouseId,
            'pharmacist_id' => auth()->id() ?? 1,
            'total_amount' => $totalAmount,
            'payment_status' => 'paid',
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Process items and stock deduction
        foreach ($itemsToDispense as $it) {
            $this->dispenseItemModel->create([
                'pharmacy_dispense_id' => $dispenseId,
                'medicine_id' => $it['medicine_id'],
                'batch_id' => $it['batch_id'],
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'total_price' => $it['total_price'],
            ]);

            // Deduct from batch
            $newStatus = ($it['remaining'] <= 0) ? 'depleted' : 'active';
            $this->batchModel->update($it['batch_id'], [
                'quantity_on_hand' => $it['remaining'],
                'status' => $newStatus,
            ]);

            // Log Stock Movement
            StockMovement::log(
                'dispense',
                $it['medicine_id'],
                $it['batch_id'],
                $warehouseId,
                -$it['quantity'],
                'pharmacy_dispenses',
                (int)$dispenseId,
                "صرف للمريض عبر فاتورة {$dispenseNumber}"
            );
        }

        // If from prescription, mark prescription as dispensed
        if ($prescriptionId) {
            $this->rxModel->update($prescriptionId, ['status' => 'dispensed']);
        }

        AuditLog::log('DISPENSE_PHARMACY', 'pharmacy_dispenses', $dispenseId, null, [
            'dispense_number' => $dispenseNumber,
            'total' => $totalAmount,
        ]);

        Session::flash('success', "تم صرف الأدوية بنجاح بالفاتورة رقم ({$dispenseNumber}) بمبلغ " . number_format($totalAmount, 2) . " SDG.");
        $this->redirect(url("/pharmacy/receipt/{$dispenseId}"));
    }

    public function receipt(Request $request, Response $response, string $id): string
    {
        $dispense = $this->dispenseModel->detailed((int)$id);
        if (!$dispense) {
            Session::flash('error', 'إيصال الصرف غير موجود.');
            $this->redirect(url('/pharmacy'));
        }

        $items = $this->dispenseModel->items((int)$id);

        return $this->view('pharmacy/receipt', [
            'pageTitle' => "إيصال صرف أدوية - {$dispense['dispense_number']}",
            'dispense' => $dispense,
            'items' => $items,
        ]);
    }

    public function printReceipt(Request $request, Response $response, string $id): string
    {
        $dispense = $this->dispenseModel->detailed((int)$id);
        if (!$dispense) {
            $response->redirect(url('/pharmacy'));
        }

        $items = $this->dispenseModel->items((int)$id);

        return $this->view('pharmacy/print_receipt', [
            'pageTitle' => "فاتورة صيدلية - {$dispense['dispense_number']}",
            'dispense' => $dispense,
            'items' => $items,
        ], null);
    }
}

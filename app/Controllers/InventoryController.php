<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;

class InventoryController extends Controller
{
    private Medicine $medicineModel;
    private MedicineBatch $batchModel;
    private MedicineCategory $categoryModel;
    private Warehouse $warehouseModel;
    private Supplier $supplierModel;
    private PurchaseOrder $poModel;
    private PurchaseOrderItem $poItemModel;

    public function __construct()
    {
        $this->medicineModel = new Medicine();
        $this->batchModel = new MedicineBatch();
        $this->categoryModel = new MedicineCategory();
        $this->warehouseModel = new Warehouse();
        $this->supplierModel = new Supplier();
        $this->poModel = new PurchaseOrder();
        $this->poItemModel = new PurchaseOrderItem();
    }

    public function index(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();

        // 1. Total inventory valuation in SDG
        $totalValuation = (float)$db->fetchColumn("SELECT COALESCE(SUM(quantity_on_hand * cost_price), 0.00) FROM `medicine_batches` WHERE `status` = 'active'");

        // 2. Low stock count
        $lowStockCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM (
            SELECT m.id, m.minimum_stock_alert, COALESCE(SUM(b.quantity_on_hand), 0) AS total_qty
            FROM `medicines` m
            LEFT JOIN `medicine_batches` b ON m.id = b.medicine_id AND b.status = 'active'
            WHERE m.is_active = 1
            GROUP BY m.id
            HAVING total_qty <= m.minimum_stock_alert
        ) AS low_stock_medicines");

        // 3. Expiring within 90 days
        $expiringCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM `medicine_batches`
            WHERE `status` = 'active' AND `quantity_on_hand` > 0 AND `expiry_date` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)");

        // 4. Warehouse stock breakdown
        $sqlWarehouses = "SELECT w.*,
                                 COUNT(DISTINCT b.medicine_id) AS distinct_medicines,
                                 COALESCE(SUM(b.quantity_on_hand), 0) AS total_units,
                                 COALESCE(SUM(b.quantity_on_hand * b.cost_price), 0.00) AS warehouse_value
                          FROM `warehouses` w
                          LEFT JOIN `medicine_batches` b ON w.id = b.warehouse_id AND b.status = 'active'
                          WHERE w.is_active = 1
                          GROUP BY w.id";
        $warehouseStats = $db->fetchAll($sqlWarehouses);

        // 5. Recent stock movements
        $sqlMovements = "SELECT sm.*, m.commercial_name, b.batch_number, w.name_ar AS warehouse_name, u.full_name AS user_name
                         FROM `stock_movements` sm
                         INNER JOIN `medicines` m ON sm.medicine_id = m.id
                         INNER JOIN `medicine_batches` b ON sm.batch_id = b.id
                         INNER JOIN `warehouses` w ON sm.warehouse_id = w.id
                         INNER JOIN `users` u ON sm.created_by = u.id
                         ORDER BY sm.created_at DESC LIMIT 15";
        $movements = $db->fetchAll($sqlMovements);

        return $this->view('inventory/index', [
            'pageTitle' => 'المخازن والمستودعات الطبية (Inventory & Warehouse Management)',
            'totalValuation' => $totalValuation,
            'lowStockCount' => $lowStockCount,
            'expiringCount' => $expiringCount,
            'warehouseStats' => $warehouseStats,
            'movements' => $movements,
        ]);
    }

    public function medicines(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $categoryId = $request->query('category_id');
        $filter = $request->query('filter');

        $where = "WHERE m.is_active = 1";
        $params = [];

        if ($categoryId) {
            $where .= " AND m.category_id = :cid";
            $params['cid'] = $categoryId;
        }

        $sql = "SELECT m.*, mc.name_ar AS category_name,
                       COALESCE(SUM(b.quantity_on_hand), 0) AS total_stock
                FROM `medicines` m
                INNER JOIN `medicine_categories` mc ON m.category_id = mc.id
                LEFT JOIN `medicine_batches` b ON m.id = b.medicine_id AND b.status = 'active'
                {$where}
                GROUP BY m.id
                ORDER BY m.commercial_name ASC";
        $medicines = $db->fetchAll($sql, $params);

        if ($filter === 'low_stock') {
            $medicines = array_filter($medicines, fn($m) => $m['total_stock'] <= $m['minimum_stock_alert']);
        }

        $categories = $this->categoryModel->where('is_active', 1);

        return $this->view('inventory/medicines', [
            'pageTitle' => 'دليل الأدوية والمستحضرات الطبية (Medicines Directory)',
            'medicines' => $medicines,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'filter' => $filter,
        ]);
    }

    public function createMedicine(Request $request, Response $response): string
    {
        $categories = $this->categoryModel->where('is_active', 1);

        return $this->view('inventory/medicine_create', [
            'pageTitle' => 'إضافة صنف دوائي جديد',
            'categories' => $categories,
        ]);
    }

    public function storeMedicine(Request $request, Response $response): void
    {
        $code = trim((string)$request->input('code'));
        $commercial = trim((string)$request->input('commercial_name'));
        $scientific = trim((string)$request->input('scientific_name'));
        $categoryId = (int)$request->input('category_id');
        $form = $request->input('dosage_form', 'tablet');
        $strength = trim((string)$request->input('strength'));
        $unit = trim((string)$request->input('unit', 'strip'));
        $minStock = (int)$request->input('minimum_stock_alert', 20);
        $reorder = (int)$request->input('reorder_level', 50);

        if (empty($code) || empty($commercial) || empty($scientific) || $categoryId <= 0) {
            Session::flash('error', 'يرجى استيفاء الحقول الإجبارية للصنف الدوائي.');
            $this->redirect(url('/inventory/medicines/create'));
            return;
        }

        $medId = $this->medicineModel->create([
            'category_id' => $categoryId,
            'code' => $code,
            'commercial_name' => $commercial,
            'scientific_name' => $scientific,
            'dosage_form' => $form,
            'strength' => $strength,
            'unit' => $unit,
            'minimum_stock_alert' => $minStock,
            'reorder_level' => $reorder,
            'is_prescription_required' => $request->input('is_prescription_required') ? 1 : 0,
            'is_narcotic' => $request->input('is_narcotic') ? 1 : 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('CREATE_MEDICINE', 'medicines', $medId, null, ['code' => $code, 'name' => $commercial]);

        Session::flash('success', "تم إضافة الصنف الدوائي ({$commercial}) بنجاح.");
        $this->redirect(url('/inventory/medicines'));
    }

    public function batches(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $warehouseId = $request->query('warehouse_id');

        $where = "WHERE 1=1";
        $params = [];

        if ($warehouseId) {
            $where .= " AND b.warehouse_id = :wid";
            $params['wid'] = $warehouseId;
        }

        // FEFO Ordering
        $sql = "SELECT b.*,
                       m.code AS med_code, m.commercial_name, m.scientific_name, m.strength, m.dosage_form,
                       w.name_ar AS warehouse_name,
                       DATEDIFF(b.expiry_date, CURDATE()) AS days_to_expiry
                FROM `medicine_batches` b
                INNER JOIN `medicines` m ON b.medicine_id = m.id
                INNER JOIN `warehouses` w ON b.warehouse_id = w.id
                {$where}
                ORDER BY b.expiry_date ASC";
        $batches = $db->fetchAll($sql, $params);

        $warehouses = $this->warehouseModel->where('is_active', 1);

        return $this->view('inventory/batches', [
            'pageTitle' => 'تتبع التشغيلات وتواريخ الصلاحية (FEFO Batches Tracker)',
            'batches' => $batches,
            'warehouses' => $warehouses,
            'selectedWarehouse' => $warehouseId,
        ]);
    }

    public function createBatch(Request $request, Response $response): string
    {
        $medicines = $this->medicineModel->where('is_active', 1);
        $warehouses = $this->warehouseModel->where('is_active', 1);

        return $this->view('inventory/batch_create', [
            'pageTitle' => 'تسجيل توريد وتشغيلة دوائية جديدة (Batch Inflow)',
            'medicines' => $medicines,
            'warehouses' => $warehouses,
        ]);
    }

    public function storeBatch(Request $request, Response $response): void
    {
        $medicineId = (int)$request->input('medicine_id');
        $warehouseId = (int)$request->input('warehouse_id');
        $batchNumber = trim((string)$request->input('batch_number'));
        $expiryDate = $request->input('expiry_date');
        $costPrice = (float)$request->input('cost_price');
        $salePrice = (float)$request->input('sale_price');
        $qty = (int)$request->input('quantity_on_hand');

        if ($medicineId <= 0 || $warehouseId <= 0 || empty($batchNumber) || empty($expiryDate) || $qty <= 0) {
            Session::flash('error', 'يرجى استيفاء بيانات التشغيلة والكمية وتاريخ الصلاحية.');
            $this->redirect(url('/inventory/batches/create'));
            return;
        }

        $batchId = $this->batchModel->create([
            'medicine_id' => $medicineId,
            'warehouse_id' => $warehouseId,
            'batch_number' => $batchNumber,
            'expiry_date' => $expiryDate,
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'quantity_on_hand' => $qty,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Log Stock Movement
        StockMovement::log(
            'purchase_receipt',
            $medicineId,
            (int)$batchId,
            $warehouseId,
            $qty,
            'medicine_batches',
            (int)$batchId,
            "إدخال تشغيلة دوائية جديدة برقم {$batchNumber}"
        );

        AuditLog::log('CREATE_BATCH', 'medicine_batches', $batchId, null, [
            'batch_number' => $batchNumber,
            'qty' => $qty,
        ]);

        Session::flash('success', "تم تسجيل التشغيلة الدوائية ({$batchNumber}) وتوريد المخزون بنجاح.");
        $this->redirect(url('/inventory/batches'));
    }

    public function suppliers(Request $request, Response $response): string
    {
        $suppliers = $this->supplierModel->all('name', 'ASC');

        return $this->view('inventory/suppliers', [
            'pageTitle' => 'دليل موردي الأدوية والمستلزمات (Suppliers Directory)',
            'suppliers' => $suppliers,
        ]);
    }

    public function storeSupplier(Request $request, Response $response): void
    {
        $name = trim((string)$request->input('name'));
        $code = trim((string)$request->input('code'));
        $phone = trim((string)$request->input('phone'));
        $contact = trim((string)$request->input('contact_person'));
        $address = trim((string)$request->input('address'));

        if (empty($name) || empty($code) || empty($phone)) {
            Session::flash('error', 'يرجى إدخال اسم المورد والكود ورقم الهاتف.');
            $this->redirect(url('/inventory/suppliers'));
            return;
        }

        $this->supplierModel->create([
            'code' => $code,
            'name' => $name,
            'contact_person' => $contact,
            'phone' => $phone,
            'address' => $address,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', "تم إضافة المورد ({$name}) بنجاح.");
        $this->redirect(url('/inventory/suppliers'));
    }

    public function purchaseOrders(Request $request, Response $response): string
    {
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT po.*, s.name AS supplier_name, w.name_ar AS warehouse_name, u.full_name AS creator_name,
                       COUNT(poi.id) AS items_count
                FROM `purchase_orders` po
                INNER JOIN `suppliers` s ON po.supplier_id = s.id
                INNER JOIN `warehouses` w ON po.warehouse_id = w.id
                INNER JOIN `users` u ON po.created_by = u.id
                LEFT JOIN `purchase_order_items` poi ON po.id = poi.purchase_order_id
                GROUP BY po.id
                ORDER BY po.order_date DESC";
        $orders = $db->fetchAll($sql);

        $suppliers = $this->supplierModel->where('status', 'active');
        $warehouses = $this->warehouseModel->where('is_active', 1);

        return $this->view('inventory/purchase_orders', [
            'pageTitle' => 'أوامر الشراء والتوريد (Purchase Orders & Procurement)',
            'orders' => $orders,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }
}

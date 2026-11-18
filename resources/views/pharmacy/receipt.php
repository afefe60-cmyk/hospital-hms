<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="receipt" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    إيصال صرف أدوية صيدلية (Pharmacy Receipt)
                </h5>
                <div class="d-flex gap-2">
                    <a href="<?= url("/pharmacy/receipt/{$dispense['id']}/print") ?>" target="_blank" class="btn btn-sm btn-light fw-bold">
                        🖨️ طباعة الفاتورة
                    </a>
                    <a href="<?= url('/pharmacy') ?>" class="btn btn-sm btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="p-4">
                <!-- Patient and Dispense Header -->
                <div class="p-3 bg-light rounded border mb-4">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <strong>اسم المريض:</strong> <?= e($dispense['patient_name']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>رقم الفاتورة:</strong> <span class="badge bg-navy font-monospace"><?= e($dispense['dispense_number']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>السجل الطبي (MRN):</strong> <code><?= e($dispense['mrn']) ?></code>
                        </div>
                        <div class="col-md-6">
                            <strong>التاريخ والوقت:</strong> <?= date('d/m/Y H:i', strtotime($dispense['created_at'])) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>الصيدلية:</strong> <?= e($dispense['warehouse_name']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>الصيدلي المنفذ:</strong> <?= e($dispense['pharmacist_name']) ?>
                        </div>
                        <?php if ($dispense['prescription_code']): ?>
                            <div class="col-12 mt-2 pt-2 border-top">
                                <strong>كود الروشتة المرتبطة:</strong> <span class="badge bg-secondary"><?= e($dispense['prescription_code']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الصنف الدوائي</th>
                                <th>رقم التشغيلة (Batch)</th>
                                <th>تاريخ الصلاحية</th>
                                <th>الكمية</th>
                                <th>السعر الفردي</th>
                                <th class="text-end">الإجمالي (SDG)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $idx => $it): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($it['commercial_name']) ?></div>
                                        <small class="text-muted"><?= e($it['scientific_name']) ?> - <?= e($it['strength']) ?></small>
                                    </td>
                                    <td><code><?= e($it['batch_number']) ?></code></td>
                                    <td class="small"><?= date('m/Y', strtotime($it['expiry_date'])) ?></td>
                                    <td class="fw-bold"><?= $it['quantity'] ?></td>
                                    <td><?= number_format($it['unit_price'], 2) ?></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($it['total_price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end fs-5">المبلغ الإجمالي المدفوع:</th>
                                <th class="text-end fs-5 text-success font-monospace"><?= number_format($dispense['total_amount'], 2) ?> SDG</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($dispense['notes'])): ?>
                    <div class="alert alert-light border small mb-0">
                        <strong>ملاحظات:</strong> <?= e($dispense['notes']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

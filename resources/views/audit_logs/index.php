<div class="row g-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="clipboard-list" class="text-green" style="width: 28px; height: 28px;"></i>
                سجل التدقيق والمراقبة الشامل (Audit Trail)
            </h3>
            <p class="text-muted mb-0">توثيق غير قابل للتعديل لجميع العمليات الحساسة، التعديلات، والدخول للنظام</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <form action="<?= url('/audit-logs') ?>" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-muted">نوع العملية (Action)</label>
                    <input type="text" name="action" class="form-control form-control-sm" placeholder="مثال: CREATE_USER, LOGIN, UPDATE..." value="<?= e($filters['action']) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted">الجدول المتأثر (Table Name)</label>
                    <input type="text" name="table_name" class="form-control form-control-sm" placeholder="مثال: users, patients, prescriptions..." value="<?= e($filters['table_name']) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-hms-secondary flex-grow-1">تطبيق الفلتر</button>
                    <?php if (!empty($filters['action']) || !empty($filters['table_name'])): ?>
                        <a href="<?= url('/audit-logs') ?>" class="btn btn-sm btn-outline-secondary">إلغاء</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>المستخدم المنفذ</th>
                            <th>نوع العملية</th>
                            <th>الجدول</th>
                            <th>معرف السجل</th>
                            <th>عنوان IP</th>
                            <th>التوقيت الدقيق</th>
                            <th class="text-center">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    لا توجد سجلات تدقيق مسجلة حتى الآن.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td>
                                        <?php if (!empty($log['username'])): ?>
                                            <span class="fw-semibold text-navy"><?= e($log['full_name']) ?></span>
                                            <small class="d-block text-muted">@<?= e($log['username']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">مجهول / نظام</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-navy font-monospace"><?= e($log['action']) ?></span>
                                    </td>
                                    <td><code><?= e($log['table_name']) ?></code></td>
                                    <td>#<?= $log['record_id'] ?></td>
                                    <td><small class="font-monospace text-secondary"><?= e($log['ip_address']) ?></small></td>
                                    <td class="small text-muted"><?= e($log['created_at']) ?></td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info"
                                                onclick='showAuditDetails(<?= json_encode($log, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                                            عرض
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">إجمالي السجلات: <?= $totalRecords ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url("/audit-logs?page={$p}&action=" . urlencode($filters['action']) . '&table_name=' . urlencode($filters['table_name'])) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for JSON Details -->
<div class="modal fade" id="auditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title fw-bold" id="modalTitle">تفاصيل العملية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold d-block text-danger">القيم السابقة (Old Values):</label>
                    <pre id="modalOldValues" class="bg-light p-3 rounded text-dark" style="max-height: 200px; overflow-y:auto;"></pre>
                </div>
                <div>
                    <label class="fw-bold d-block text-success">القيم الجديدة (New Values):</label>
                    <pre id="modalNewValues" class="bg-light p-3 rounded text-dark" style="max-height: 200px; overflow-y:auto;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showAuditDetails(log) {
        document.getElementById('modalTitle').innerText = 'تفاصيل: ' + log.action + ' (' + log.table_name + ' #' + log.record_id + ')';
        document.getElementById('modalOldValues').innerText = log.old_values ? JSON.stringify(JSON.parse(log.old_values), null, 2) : 'لا توجد بيانات سابقة (إنشاء)';
        document.getElementById('modalNewValues').innerText = log.new_values ? JSON.stringify(JSON.parse(log.new_values), null, 2) : 'لا توجد بيانات جديدة (حذف أو قراءة)';
        new bootstrap.Modal(document.getElementById('auditModal')).show();
    }
</script>

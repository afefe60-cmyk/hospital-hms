<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-safe text-primary me-2"></i>ورديات ونقاط الصناديق (Cashier Shifts)</h2>
        <p class="text-muted mb-0">إدارة ورديات الصرافين، عهدة البداية، مطابقة الخزينة وإغلاق وتسليم الورديات</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($myActiveSession): ?>
            <a href="/cashboxes/sessions/<?= $myActiveSession['id'] ?>" class="btn btn-warning d-flex align-items-center gap-2">
                <i class="bi bi-door-open-fill"></i>
                <span>ورديتي المفتوحة حالياً (<?= htmlspecialchars($myActiveSession['session_number']) ?>)</span>
            </a>
        <?php else: ?>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#openSessionModal">
                <i class="bi bi-plus-circle"></i>
                <span>فتح وردية صندوق جديدة</span>
            </button>
        <?php endif; ?>
        <a href="/billing" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i>رجوع للفوترة
        </a>
    </div>
</div>

<!-- Cashboxes Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($cashboxes as $cb): ?>
        <?php
            $isOpen = !empty($cb['active_session_number']);
        ?>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3 <?= $isOpen ? 'border-top border-3 border-success' : '' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($cb['name_ar']) ?></h6>
                        <span class="badge <?= $isOpen ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $isOpen ? 'وردية مفتوحة' : 'مغلق' ?>
                        </span>
                    </div>
                    <small class="text-muted font-monospace d-block mb-3"><?= htmlspecialchars($cb['code']) ?></small>
                    <?php if ($isOpen): ?>
                        <div class="alert alert-success py-2 px-3 small mb-0">
                            <div>الوردية: <strong class="font-monospace"><?= htmlspecialchars($cb['active_session_number']) ?></strong></div>
                            <div>الصراف: <?= htmlspecialchars($cb['active_cashier_name'] ?? 'الصراف المناوب') ?></div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>الصندوق جاهز لفتح وردية جديدة.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Active Shifts Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-light">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-play-circle text-success me-2"></i>الورديات المفتوحة حالياً (Active Shifts)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الوردية</th>
                        <th>الصندوق</th>
                        <th>الصراف المسؤول</th>
                        <th>توقيت الفتح</th>
                        <th class="text-end">عهدة البداية (SDG)</th>
                        <th class="text-end">المتحصلات خلال الوردية (SDG)</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activeSessions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">لا توجد ورديات مفتوحة حالياً.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activeSessions as $as): ?>
                            <tr>
                                <td class="fw-bold font-monospace text-primary">
                                    <a href="/cashboxes/sessions/<?= $as['id'] ?>"><?= htmlspecialchars($as['session_number']) ?></a>
                                </td>
                                <td><?= htmlspecialchars($as['cashbox_name']) ?></td>
                                <td><?= htmlspecialchars($as['cashier_name']) ?></td>
                                <td><?= date('Y-m-d h:i A', strtotime($as['opened_at'])) ?></td>
                                <td class="text-end font-monospace"><?= number_format($as['opening_balance'], 2) ?></td>
                                <td class="text-end font-monospace fw-bold text-success"><?= number_format($as['total_collected'], 2) ?></td>
                                <td class="text-center">
                                    <a href="/cashboxes/sessions/<?= $as['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> تفاصيل وتسليم
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Closed Shifts History -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-light">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-archive text-secondary me-2"></i>سجل الورديات السابقة المغلقة</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الوردية</th>
                        <th>الصندوق</th>
                        <th>الصراف</th>
                        <th>توقيت الفتح</th>
                        <th>توقيت الإغلاق</th>
                        <th class="text-end">الرصيد الفعلي</th>
                        <th class="text-end">الفارق (عجز/زيادة)</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($closedSessions)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لا توجد ورديات مغلقة مسجلة.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($closedSessions as $cs): ?>
                            <?php
                                $diff = (float)$cs['difference'];
                            ?>
                            <tr>
                                <td class="fw-bold font-monospace text-muted"><?= htmlspecialchars($cs['session_number']) ?></td>
                                <td><?= htmlspecialchars($cs['cashbox_name']) ?></td>
                                <td><?= htmlspecialchars($cs['cashier_name']) ?></td>
                                <td><?= date('m-d h:i A', strtotime($cs['opened_at'])) ?></td>
                                <td><?= $cs['closed_at'] ? date('m-d h:i A', strtotime($cs['closed_at'])) : '-' ?></td>
                                <td class="text-end font-monospace fw-bold"><?= number_format($cs['actual_closing'], 2) ?> SDG</td>
                                <td class="text-end font-monospace <?= $diff < 0 ? 'text-danger fw-bold' : ($diff > 0 ? 'text-success fw-bold' : 'text-muted') ?>">
                                    <?= ($diff > 0 ? '+' : '') . number_format($diff, 2) ?> SDG
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/cashboxes/sessions/<?= $cs['id'] ?>" class="btn btn-outline-secondary">عرض</a>
                                        <a href="/cashboxes/sessions/<?= $cs['id'] ?>/print" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Open Session -->
<div class="modal fade" id="openSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/cashboxes/open">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-safe me-2"></i>فتح وردية صندوق جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر الصندوق <span class="text-danger">*</span></label>
                        <select name="cashbox_id" class="form-select" required>
                            <option value="">-- اختر نقطة الصندوق --</option>
                            <?php foreach ($cashboxes as $cb): ?>
                                <option value="<?= $cb['id'] ?>" <?= !empty($cb['active_session_number']) ? 'disabled' : '' ?>>
                                    <?= htmlspecialchars($cb['name_ar']) ?> <?= !empty($cb['active_session_number']) ? '(مشغول حالياً)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">عهدة البداية النقدية (Opening Float SDG) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="opening_balance" class="form-control font-monospace" value="0.00" required>
                        <small class="text-muted">المبلغ النقدي المسلم للصراف كعهدة فكة بالصندوق.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات فتح الوردية</label>
                        <input type="text" name="notes" class="form-control" placeholder="وردية الصباح، وردية المساء...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">تأكيد فتح الوردية</button>
                </div>
            </form>
        </div>
    </div>
</div>

<header class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="p-3 bg-success rounded-circle">
            <i data-lucide="activity" style="width: 40px; height: 40px; color: #fff;"></i>
        </div>
        <div>
            <h1 class="fw-bold mb-0"><?= e($clinic['name_ar']) ?></h1>
            <h4 class="text-warning mb-0">غرفة الكشف: <?= e($clinic['room_number']) ?></h4>
        </div>
    </div>
    <div class="text-end">
        <h2 class="fw-bold mb-0 text-info" id="clock">--:--:--</h2>
        <span class="badge bg-secondary fs-6"><?= date('Y-m-d') ?></span>
    </div>
</header>

<main class="row g-4 flex-grow-1 align-items-center">
    <!-- Current Calling Token Banner -->
    <div class="col-lg-7 text-center">
        <div class="card p-5 border-0 shadow-lg h-100 justify-content-center" style="background: linear-gradient(135deg, #173B59 0%, #0d273d 100%); border-radius: 24px; border: 3px solid #16856D !important;">
            <span class="text-uppercase tracking-wider fs-4 text-warning mb-2 fw-bold">الرقم الحالي قيد الكشف</span>
            <?php if ($currentCall): ?>
                <div class="display-1 fw-bold text-success my-3" style="font-size: 8rem; text-shadow: 0 0 30px rgba(22, 133, 109, 0.6);">
                    #<?= $currentCall['queue_number'] ?>
                </div>
                <h3 class="fw-bold text-light mb-1"><?= e($currentCall['patient_name']) ?></h3>
                <span class="badge bg-dark text-info fs-5 p-2 font-monospace">MRN: <?= e($currentCall['mrn']) ?></span>
            <?php else: ?>
                <div class="display-1 fw-bold text-secondary my-3" style="font-size: 6rem;">
                    --
                </div>
                <h3 class="fw-bold text-muted">في انتظار نداء المريض القادم</h3>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Waiting Queue -->
    <div class="col-lg-5">
        <div class="card p-4 border-0 h-100" style="background-color: #132F4C; border-radius: 20px;">
            <h4 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3 d-flex align-items-center gap-2">
                <i data-lucide="users" class="text-warning"></i>
                قائمة الانتظار القادمة
            </h4>

            <div class="list-group list-group-flush">
                <?php
                $waiting = array_filter($activeQueue, fn($q) => $q['status'] === 'waiting');
                ?>
                <?php if (empty($waiting)): ?>
                    <p class="text-muted text-center py-5 fs-5">لا يوجد مرضى في قائمة الانتظار حالياً.</p>
                <?php else: ?>
                    <?php $i = 0; foreach ($waiting as $q): if (++$i > 5) break; ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent text-white border-secondary py-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary fs-3 px-3 py-2">#<?= $q['queue_number'] ?></span>
                                <div>
                                    <h5 class="mb-0 fw-bold"><?= e($q['patient_name']) ?></h5>
                                    <small class="text-muted">MRN: <?= e($q['mrn']) ?></small>
                                </div>
                            </div>
                            <span class="badge bg-warning text-dark fs-6">في الانتظار</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<footer class="mt-4 pt-3 border-top border-secondary text-center text-muted">
    <div class="d-flex justify-content-between align-items-center">
        <span>يرجى التوجه إلى غرفة الكشف عند ظهور رقم التذكرة الخاص بك على الشاشة.</span>
        <span class="badge bg-success">الشاشة تتحدث تلقائياً</span>
    </div>
</footer>

<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleTimeString('ar-EG');
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Auto reload queue screen every 10 seconds
    setTimeout(() => {
        location.reload();
    }, 10000);
</script>

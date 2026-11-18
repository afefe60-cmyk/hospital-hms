<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="users" class="text-green" style="width: 28px; height: 28px;"></i>
                إدارة المستخدمين وحسابات النظام
            </h3>
            <p class="text-muted mb-0">عرض والتحكم بحسابات الكادر الطبي والإداري وتوزيع الأدوار الوظيفية</p>
        </div>

        <?php if (can('users.create')): ?>
            <a href="<?= url('/users/create') ?>" class="btn btn-hms-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                إضافة مستخدم جديد
            </a>
        <?php endif; ?>
    </div>

    <!-- Search & Filter Bar -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <form action="<?= url('/users') ?>" method="GET" class="row g-3 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">
                            <i data-lucide="search" style="width: 18px; height: 18px;"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="بحث بالاسم، اسم المستخدم، البريد أو الهاتف..."
                               value="<?= e($search) ?>">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-hms-secondary flex-grow-1">بحث</button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">إلغاء</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم الكامل</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الأدوار الوظيفية</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i data-lucide="inbox" style="width: 36px; height: 36px; display:block; margin: 0 auto 8px;"></i>
                                    لا يوجد مستخدمون مطابقون لمعايير البحث.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $index => $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td class="fw-bold text-navy"><?= e($u['full_name']) ?></td>
                                    <td><code>@<?= e($u['username']) ?></code></td>
                                    <td><?= e($u['email']) ?></td>
                                    <td>
                                        <?php if (!empty($u['roles_ar'])): ?>
                                            <span class="badge bg-navy"><?= e($u['roles_ar']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">بدون دور</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['is_active']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">مفعل</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">معطل</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted"><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <?php if (can('users.edit')): ?>
                                                <a href="<?= url("/users/{$u['id']}/edit") ?>" class="btn btn-outline-primary" title="تعديل">
                                                    <i data-lucide="edit-3" style="width: 15px; height: 15px;"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (can('users.delete') && (int)$u['id'] !== auth()->id()): ?>
                                                <form action="<?= url("/users/{$u['id']}/toggle-status") ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من تغيير حالة هذا الحساب؟');" style="display:inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $u['is_active'] ? 'تعطيل الحساب' : 'تفعيل الحساب' ?>">
                                                        <i data-lucide="<?= $u['is_active'] ? 'user-x' : 'user-check' ?>" style="width: 15px; height: 15px;"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
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
                                    <a class="page-link" href="<?= url("/users?page={$p}&search=" . urlencode($search)) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

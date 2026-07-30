<?php
require_once '../app/core/init.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != "admin") {
    header("Location: index");
    exit();
}

$tab = $_GET['tab'] ?? 'dashboard';

$usersCount = query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'")[0]['count'] ?? 0;
$writersCount = query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'writer'")[0]['count'] ?? 0;
$adminsCount = query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'")[0]['count'] ?? 0;

if ($tab === 'dashboard') {
    $latestUsers = query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 6");
} elseif ($tab === 'users') {
    $allUsers = query($conn, "SELECT * FROM users ORDER BY created_at DESC");
} elseif ($tab === 'profile') {
    $query = "SELECT * FROM `users` WHERE id = :id";
    $adminProfile = query($conn, $query, ["id" => $_SESSION['user']['id']]);
    $adminProfile = $adminProfile[0] ?? null;

    $error = null;
    $message = null;

    if ($_POST) {
        $newPassword = $_POST["new_password"];
        $confirmPassword = $_POST["confirm_password"];

        if (empty($newPassword) || empty($confirmPassword)) {
            $error = "يرجى إدخال كلمة مرور";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "تأكد من إدخال كلمة المرور بشكل صحيح";
        } else {
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE `users` SET `password`= :password WHERE id = :id";
            execute($conn, $sql, ["password" => $hashed_password, "id" => $_SESSION['user']['id']]);
            $message = "تم تغيير كلمة المرور بنجاح";
        }
    }
}


?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | منصة الروايات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>

    <div class="d-flex" id="wrapper">
        <div class="sidebar" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 fs-4 fw-bold">
                <i class="fa-solid fa-feather-pointed me-2"></i> سرد
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="admin?tab=dashboard" class="list-group-item list-group-item-action bg-transparent <?= $tab === 'dashboard' ? 'active' : 'fw-bold' ?>">
                    <i class="fas fa-tachometer-alt ms-2"></i> الرئيسية
                </a>
                <a href="admin?tab=users" class="list-group-item list-group-item-action bg-transparent <?= $tab === 'users' ? 'active' : 'fw-bold' ?>">
                    <i class="fas fa-users-cog ms-2"></i> إدارة المستخدمين
                </a>
                <a href="admin?tab=novels" class="list-group-item list-group-item-action bg-transparent <?= $tab === 'novels' ? 'active' : 'fw-bold' ?>">
                    <i class="fas fa-book-open ms-2"></i> إدارة الروايات
                </a>
                <a href="logout" class="list-group-item list-group-item-action bg-transparent fw-bold text-danger mt-5">
                    <i class="fas fa-sign-out-alt ms-2"></i> تسجيل الخروج
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 px-4 border-bottom shadow-sm d-flex justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bars primary-text fs-4 ms-3" id="menu-toggle"></i>
                    <h2 class="fs-4 m-0 header-title ms-3">لوحة الإدارة</h2>
                </div>

                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="text-dark dropdown-toggle d-flex align-items-center text-decoration-none" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="fw-bold me-2">المدير العام</span>
                            <img src="https://ui-avatars.com/api/?name=Admin&background=8b5a2b&color=fff" alt="Admin" class="rounded-circle" width="35" height="35">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-start text-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="admin?tab=profile">الملف الشخصي</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout">تسجيل الخروج</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 pt-4 pb-5">
                <?php if ($tab === 'dashboard'): ?>
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <div class="p-4 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card border-bottom-success">
                                <div>
                                    <p class="fs-6 text-muted mb-1">إجمالي المستخدمين</p>
                                    <h3 class="fs-2 mb-0 fw-bold"><?= $usersCount ?></h3>
                                </div>
                                <i class="fas fa-users fs-1 text-success bg-light p-3 rounded-circle"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-4 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card border-bottom-warning">
                                <div>
                                    <p class="fs-6 text-muted mb-1">الكتاب (Writers)</p>
                                    <h3 class="fs-2 mb-0 fw-bold"><?= $writersCount ?></h3>
                                </div>
                                <i class="fas fa-feather fs-1 text-warning bg-light p-3 rounded-circle"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-4 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card border-bottom-info">
                                <div>
                                    <p class="fs-6 text-muted mb-1">المديرين (Admins)</p>
                                    <h3 class="fs-2 mb-0 fw-bold"><?= $adminsCount ?></h3>
                                </div>
                                <i class="fas fa-user-shield fs-1 text-info bg-light p-3 rounded-circle"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-4 bg-white shadow-sm d-flex justify-content-between align-items-center rounded stat-card border-bottom-primary">
                                <div>
                                    <p class="fs-6 text-muted mb-1">إجمالي الروايات</p>
                                    <h3 class="fs-2 mb-0 fw-bold">0</h3>
                                </div>
                                <i class="fas fa-book fs-1 text-primary-accent bg-light p-3 rounded-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold header-title"><i class="fas fa-user-plus ms-2 text-primary-accent"></i> أحدث المستخدمين المسجلين</h5>
                                    <a href="#" class="btn btn-sm btn-outline-primary px-3 rounded-pill">عرض الكل</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-muted">
                                                <tr>
                                                    <th class="ps-4 py-3">المستخدم</th>
                                                    <th class="py-3">البريد الإلكتروني</th>
                                                    <th class="py-3">الدور (Role)</th>
                                                    <th class="py-3">تاريخ التسجيل</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($latestUsers)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4 text-muted">لا يوجد مستخدمين بعد.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($latestUsers as $u): ?>
                                                        <tr>
                                                            <td class="ps-4 py-3 d-flex align-items-center">
                                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['username']) ?>&background=random&color=fff" alt="<?= htmlspecialchars($u['username']) ?>" class="rounded-circle ms-3 me-3" width="40" height="40">
                                                                <span class="fw-bold"><?= htmlspecialchars($u['username']) ?></span>
                                                            </td>
                                                            <td class="py-3 text-muted"><?= htmlspecialchars($u['email']) ?></td>
                                                            <td class="py-3">
                                                                <?php if ($u['role'] == 'admin'): ?>
                                                                    <span class="badge bg-danger rounded-pill px-3 py-2">مدير</span>
                                                                <?php elseif ($u['role'] == 'writer'): ?>
                                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">كاتب</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary rounded-pill px-3 py-2">مستخدم</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="py-3 text-muted small" dir="ltr" style="text-align: right;">
                                                                <?= date('Y-m-d h:i A', strtotime($u['created_at'])) ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($tab === 'users'): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold header-title"><i class="fas fa-users ms-2 text-primary-accent"></i> جميع المستخدمين المسجلين بالموقع</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-muted">
                                                <tr>
                                                    <th class="ps-4 py-3">المعرف</th>
                                                    <th class="py-3">المستخدم</th>
                                                    <th class="py-3">البريد الإلكتروني</th>
                                                    <th class="py-3">الدور (Role)</th>
                                                    <th class="py-3">تاريخ التسجيل</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($allUsers)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">لا يوجد مستخدمين.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($allUsers as $u): ?>
                                                        <tr>
                                                            <td class="ps-4 py-3 text-muted">#<?= $u['id'] ?></td>
                                                            <td class="py-3 d-flex align-items-center">
                                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['username']) ?>&background=random&color=fff" alt="<?= htmlspecialchars($u['username']) ?>" class="rounded-circle ms-3 me-3" width="40" height="40">
                                                                <span class="fw-bold"><?= htmlspecialchars($u['username']) ?></span>
                                                            </td>
                                                            <td class="py-3 text-muted"><?= htmlspecialchars($u['email']) ?></td>
                                                            <td class="py-3">
                                                                <?php if ($u['role'] == 'admin'): ?>
                                                                    <span class="badge bg-danger rounded-pill px-3 py-2">مدير</span>
                                                                <?php elseif ($u['role'] == 'writer'): ?>
                                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">كاتب</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary rounded-pill px-3 py-2">مستخدم</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="py-3 text-muted small" dir="ltr" style="text-align: right;">
                                                                <?= date('Y-m-d h:i A', strtotime($u['created_at'])) ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($tab === 'novels'): ?>
                    <div class="row">
                        <div class="col-12 text-center text-muted mt-5 pt-5">
                            <i class="fa-solid fa-book-open-reader fs-1 mb-3 text-primary-accent opacity-50" style="font-size: 5rem !important;"></i>
                            <h3 class="fw-bold mt-3" style="font-family: 'Amiri', serif;">إدارة الروايات</h3>
                            <p class="fs-5 opacity-75">هذا القسم جاهز للربط بجدول الروايات. لا توجد بيانات لعرضها حالياً.</p>
                        </div>
                    </div>
                <?php elseif ($tab === 'profile'): ?>
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden h-100">
                                <div class="p-4" style="background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%); height: 120px;"></div>
                                <div class="card-body mt-n5 position-relative">
                                    <img src="https://ui-avatars.com/api/?name=Admin&background=fff&color=8b5a2b" alt="Admin Profile" class="rounded-circle border border-4 border-white shadow-sm mb-3 position-relative" width="120" height="120" style="margin-top: -80px; z-index: 2;">

                                    <h4 class="fw-bold mb-1" style="font-family: 'Amiri', serif;"><?= $adminProfile['username'] ?></h4>
                                    <p class="text-muted mb-3"><?= $adminProfile['email'] ?></p>
                                    <span class="badge bg-danger rounded-pill px-4 py-2 fs-6 mb-3 shadow-sm">مدير عام</span>

                                    <hr class="text-muted opacity-10 mx-4">
                                    <div class="d-flex justify-content-around mt-4 mb-2">
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">نشط</h5>
                                            <span class="text-muted small">حالة الحساب</span>
                                        </div>
                                        <div class="border-end"></div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">الآن</h5>
                                            <span class="text-muted small">آخر دخول</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Details Form -->
                        <div class="col-lg-8 mb-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white py-4 border-0 border-bottom">
                                    <h5 class="mb-0 fw-bold header-title"><i class="fas fa-user-edit ms-2 text-primary-accent"></i> المعلومات الشخصية</h5>
                                </div>
                                <div class="card-body p-4 pt-5">
                                    <form method="post">
                                        <?php if ($message) { ?>
                                            <div class="alert alert-success">
                                                <?= $message ?>
                                            </div>
                                        <?php } ?>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted fw-bold small mb-2">اسم المستخدم</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-0" value="<?= $adminProfile['username'] ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted fw-bold small mb-2">البريد الإلكتروني</label>
                                                <input type="email" class="form-control form-control-lg bg-light border-0" value="<?= $adminProfile['email'] ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted fw-bold small mb-2">الدور (Role)</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-0 text-danger fw-bold" value="<?= $adminProfile['role'] ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted fw-bold small mb-2">تاريخ الانضمام</label>
                                                <input type="text" class="form-control form-control-lg bg-light border-0 text-start" value="<?= $adminProfile['created_at'] ?>" dir="ltr" readonly>
                                            </div>

                                            <!-- Security Section -->
                                            <div class="col-12 mt-5">
                                                <h6 class="fw-bold mb-3 pb-2 text-dark"><i class="fas fa-lock ms-2 text-muted"></i> إعدادات الأمان</h6>
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label class="form-label text-muted fw-bold small mb-2">كلمة المرور الجديدة</label>
                                                <input type="password" class="form-control form-control-lg" placeholder="أدخل كلمة المرور الجديدة" name="new_password">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label class="form-label text-muted fw-bold small mb-2">تأكيد كلمة المرور</label>
                                                <input type="password" class="form-control form-control-lg" placeholder="أعد إدخال كلمة المرور" name="confirm_password">
                                            </div>
                                            <?php if ($error) { ?>
                                                <div class="alert alert-danger alert-dismissible fade show">
                                                    <?= $error ?>
                                                </div>
                                            <?php } ?>
                                            <div class="col-12 mt-5 text-start">
                                                <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-3 shadow-sm" style="background-color: var(--accent-color); border-color: var(--accent-color);">
                                                    <i class="fas fa-save ms-2"></i> حفظ التعديلات
                                                </button>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function() {
            el.classList.toggle("toggled");
        };
    </script>
</body>

</html>
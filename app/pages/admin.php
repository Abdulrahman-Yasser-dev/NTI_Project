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
    $writerRequests = query($conn, "SELECT wr.*, u.email, u.image, u.username FROM writer_requests wr JOIN users u ON wr.user_id = u.id WHERE wr.status = 'pending' ORDER BY wr.created_at ASC");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['approve_writer'])) {
            $userId = $_POST['user_id'];
            $reqId = $_POST['request_id'] ?? 0;
            execute($conn, "UPDATE `users` SET `role`= 'writer', `writer_request_status` = 'approved' WHERE id = :id", ["id" => $userId]);
            if ($reqId) execute($conn, "UPDATE `writer_requests` SET `status`= 'approved' WHERE id = :req_id", ["req_id" => $reqId]);
            // Create author record if doesn't exist
            $uRow = query($conn, "SELECT username FROM users WHERE id = :id", ["id" => $userId]);
            if (!empty($uRow)) {
                $uname = $uRow[0]['username'];
                $existingAuthor = query($conn, "SELECT id FROM authors WHERE name = :name LIMIT 1", ["name" => $uname]);
                if (empty($existingAuthor)) {
                    execute($conn, "INSERT INTO authors (name) VALUES (:name)", ["name" => $uname]);
                }
            }
        } elseif (isset($_POST['reject_writer'])) {
            $userId = $_POST['user_id'];
            $reqId = $_POST['request_id'] ?? 0;
            execute($conn, "UPDATE `users` SET `writer_request_status` = 'rejected' WHERE id = :id", ["id" => $userId]);
            if ($reqId) execute($conn, "UPDATE `writer_requests` SET `status`= 'rejected' WHERE id = :req_id", ["req_id" => $reqId]);
        } elseif (isset($_POST['role'])) {
            if ((int)$_POST['user_id'] !== (int)$_SESSION['user']['id']) {
                execute($conn, "UPDATE `users` SET `role`= :role WHERE id = :id", ["role" => $_POST['role'], "id" => $_POST['user_id']]);
            }
        }
        header("Location: admin?tab=users");
        exit();
    }
    if (isset($_GET["action"]) && $_GET["action"] == "delete") {
        execute($conn, "DELETE FROM `users` WHERE id = :id", ["id" => $_GET['id']]);
        header("Location: admin?tab=users");
        exit();
    }
} elseif ($tab === 'novels') {
    $publishedNovels = query($conn, "SELECT novels.*, authors.name AS author_name FROM novels JOIN authors ON novels.author_id = authors.id WHERE status != 'archived' ORDER BY created_at DESC");
    $novelRequests = query($conn, "SELECT novels.*, authors.name AS author_name FROM novels JOIN authors ON novels.author_id = authors.id WHERE status = 'archived' ORDER BY created_at ASC");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['approve_novel'])) {
            execute($conn, "UPDATE `novels` SET `status`= 'published' WHERE id = :id", ["id" => $_POST['novel_id']]);
        } elseif (isset($_POST['delete_novel'])) {
            execute($conn, "DELETE FROM `novels` WHERE id = :id", ["id" => $_POST['novel_id']]);
        }
        header("Location: admin?tab=novels");
        exit();
    }
} elseif ($tab === 'profile') {
    $query = "SELECT * FROM `users` WHERE id = :id";
    $adminProfile = query($conn, $query, ["id" => $_SESSION['user']['id']]);
    $adminProfile = $adminProfile[0] ?? null;

    $error = null;
    $message = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updates = [];
        $params = ["id" => $_SESSION['user']['id']];

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newName = time() . '_' . rand(100, 999) . '.' . $ext;
                $dest = '../public/assets/images/users/' . $newName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $updates[] = "`image` = :image";
                    $params['image'] = $newName;
                    $_SESSION['user']['image'] = $newName; // Update session
                }
            } else {
                $error = "صيغة الصورة غير مسموحة.";
            }
        }

        // Handle Password Change
        $newPassword = $_POST["new_password"] ?? '';
        $confirmPassword = $_POST["confirm_password"] ?? '';
        
        if (!empty($newPassword) || !empty($confirmPassword)) {
            if ($newPassword !== $confirmPassword) {
                $error = "تأكد من إدخال كلمة المرور بشكل صحيح.";
            } else {
                $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
                $updates[] = "`password` = :password";
                $params['password'] = $hashed_password;
            }
        }

        if (!$error && count($updates) > 0) {
            $sql = "UPDATE `users` SET " . implode(", ", $updates) . " WHERE id = :id";
            execute($conn, $sql, $params);
            $message = "تم حفظ التعديلات بنجاح.";
            
            // Refresh admin profile data
            $adminProfile = query($conn, $query, ["id" => $_SESSION['user']['id']])[0] ?? null;
        } elseif (!$error) {
            $message = "لم يتم إجراء أي تغييرات.";
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
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/admin.css">
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
                            <ul class="nav nav-pills mb-4" id="usersTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold" id="all-users-tab" data-bs-toggle="pill" data-bs-target="#all-users" type="button" role="tab" aria-controls="all-users" aria-selected="true">
                                        <i class="fas fa-users ms-2"></i> جميع المستخدمين
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold position-relative" id="writer-requests-tab" data-bs-toggle="pill" data-bs-target="#writer-requests" type="button" role="tab" aria-controls="writer-requests" aria-selected="false">
                                        <i class="fas fa-pen-nib ms-2"></i> طلبات الكتّاب
                                        <?php if(count($writerRequests) > 0): ?>
                                            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger">
                                                <?= count($writerRequests) ?>
                                            </span>
                                        <?php endif; ?>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="usersTabsContent">
                                <!-- All Users Tab -->
                                <div class="tab-pane fade show active" id="all-users" role="tabpanel" aria-labelledby="all-users-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
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
                                                            <th class="py-3">إجراءات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($allUsers)): ?>
                                                            <tr>
                                                                <td colspan="6" class="text-center py-4 text-muted">لا يوجد مستخدمين.</td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($allUsers as $u): ?>
                                                                <tr>
                                                                    <td class="ps-4 py-3 text-muted">#<?= $u['id'] ?></td>
                                                                    <td class="py-3 d-flex align-items-center">
                                                                        <img src="<?= !empty($u['image']) ? ROOT . 'assets/images/users/' . htmlspecialchars($u['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($u['username']) . '&background=random&color=fff' ?>" alt="<?= htmlspecialchars($u['username']) ?>" class="rounded-circle ms-3 me-3" width="40" height="40" style="object-fit:cover;">
                                                                        <span class="fw-bold"><?= htmlspecialchars($u['username']) ?></span>
                                                                    </td>
                                                                    <td class="py-3 text-muted"><?= htmlspecialchars($u['email']) ?></td>
                                                                    <td class="py-3">
                                                                        <?php if ($u['id'] == $_SESSION['user']['id']): ?>
                                                                            <select class="role-badge-select role-<?= $u['role'] ?>" disabled title="لا يمكنك تغيير دورك بنفسك">
                                                                                <option value="user" <?= $u['role'] == 'user'   ? 'selected' : '' ?>>مستخدم</option>
                                                                                <option value="writer" <?= $u['role'] == 'writer' ? 'selected' : '' ?>>كاتب</option>
                                                                                <option value="admin" <?= $u['role'] == 'admin'  ? 'selected' : '' ?>>مدير</option>
                                                                            </select>
                                                                        <?php else: ?>
                                                                            <form method="post" class="d-inline">
                                                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                                                <select name="role" class="role-badge-select role-<?= $u['role'] ?>" onchange="this.form.submit()">
                                                                                    <option value="user" <?= $u['role'] == 'user'   ? 'selected' : '' ?>>مستخدم</option>
                                                                                    <option value="writer" <?= $u['role'] == 'writer' ? 'selected' : '' ?>>كاتب</option>
                                                                                    <option value="admin" <?= $u['role'] == 'admin'  ? 'selected' : '' ?>>مدير</option>
                                                                                </select>
                                                                            </form>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="py-3 text-muted small" dir="ltr" style="text-align: right;">
                                                                        <?= date('Y-m-d', strtotime($u['created_at'])) ?>
                                                                    </td>
                                                                    <td class="py-3 text-muted" dir="ltr" style="text-align: right;">
                                                                        <a class="btn btn-sm btn-danger px-3 rounded-pill" href="admin?&tab=users&id=<?= $u['id'] ?>&action=delete" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">حذف</a>
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

                                <!-- Writer Requests Tab -->
                                <div class="tab-pane fade" id="writer-requests" role="tabpanel" aria-labelledby="writer-requests-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="bg-light text-muted">
                                                        <tr>
                                                            <th class="ps-4 py-3">الاسم الثلاثي</th>
                                                            <th class="py-3">اسم الرواية المقترحة</th>
                                                            <th class="py-3">البريد الإلكتروني</th>
                                                            <th class="py-3">تاريخ الطلب</th>
                                                            <th class="py-3">إجراءات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($writerRequests)): ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-5 text-muted">لا يوجد طلبات للكتّاب حالياً.</td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($writerRequests as $u): ?>
                                                                <tr>
                                                                    <td class="ps-4 py-3">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="<?= !empty($u['image']) ? ROOT . 'assets/images/users/' . htmlspecialchars($u['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($u['full_name'] ?? 'U') . '&background=7B5EA7&color=fff' ?>" class="rounded-circle me-2" width="38" height="38" style="object-fit:cover;">
                                                                            <div>
                                                                                <div class="fw-bold"><?= htmlspecialchars($u['full_name'] ?? 'بدون اسم') ?></div>
                                                                                <small class="text-muted">@<?= htmlspecialchars($u['username'] ?? '') ?></small>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="py-3">
                                                                        <span class="badge" style="background:#f3ede3; color:#7B5EA7; padding:6px 10px; border-radius:8px; font-size:0.85rem;"><?= htmlspecialchars($u['novel_name'] ?? 'غير محدد') ?></span>
                                                                    </td>
                                                                    <td class="py-3 text-muted small"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                                                    <td class="py-3 text-muted small" dir="ltr" style="text-align:right;">
                                                                        <?= date('Y-m-d', strtotime($u['created_at'])) ?>
                                                                    </td>
                                                                    <td class="py-3">
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary px-2 rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#writerModal<?= $u['id'] ?>"><i class="fas fa-eye"></i></button>
                                                                        <form method="post" class="d-inline">
                                                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                                            <input type="hidden" name="request_id" value="<?= $u['id'] ?>">
                                                                            <button type="submit" name="approve_writer" class="btn btn-sm btn-success px-3 rounded-pill me-1">قبول</button>
                                                                            <button type="submit" name="reject_writer" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="return confirm('رفض الطلب؟')">رفض</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>

                                                                
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($tab === 'novels'): ?>
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-pills mb-4" id="novelsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold" id="published-novels-tab" data-bs-toggle="pill" data-bs-target="#published-novels" type="button" role="tab" aria-controls="published-novels" aria-selected="true">
                                        <i class="fas fa-book ms-2"></i> الروايات المنشورة
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold position-relative" id="publishing-requests-tab" data-bs-toggle="pill" data-bs-target="#publishing-requests" type="button" role="tab" aria-controls="publishing-requests" aria-selected="false">
                                        <i class="fas fa-file-signature ms-2"></i> طلبات النشر
                                        <?php if(count($novelRequests) > 0): ?>
                                            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger">
                                                <?= count($novelRequests) ?>
                                            </span>
                                        <?php endif; ?>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="novelsTabsContent">
                                <!-- Published Novels Tab -->
                                <div class="tab-pane fade show active" id="published-novels" role="tabpanel" aria-labelledby="published-novels-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="bg-light text-muted">
                                                        <tr>
                                                            <th class="ps-4 py-3">الرواية</th>
                                                            <th class="py-3">المؤلف</th>
                                                            <th class="py-3">التصنيف</th>
                                                            <th class="py-3">تاريخ النشر</th>
                                                            <th class="py-3">إجراءات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($publishedNovels)): ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-muted">لا يوجد روايات منشورة.</td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($publishedNovels as $n): ?>
                                                                <tr>
                                                                    <td class="ps-4 py-3 d-flex align-items-center">
                                                                        <img src="<?= !empty($n['cover_image']) ? ROOT . 'assets/images/' . htmlspecialchars($n['cover_image']) : 'https://placehold.co/40x60/8b5a2b/ffffff?text=Novel' ?>" alt="Cover" class="rounded ms-3 me-3" width="40" height="60" style="object-fit:cover;">
                                                                        <span class="fw-bold"><?= htmlspecialchars($n['title']) ?></span>
                                                                    </td>
                                                                    <td class="py-3 text-muted"><?= htmlspecialchars($n['author_name']) ?></td>
                                                                    <td class="py-3"><span class="badge bg-primary px-2 py-1 rounded-pill">منشورة</span></td>
                                                                    <td class="py-3 text-muted small" dir="ltr" style="text-align: right;">
                                                                        <?= date('Y-m-d', strtotime($n['created_at'])) ?>
                                                                    </td>
                                                                    <td class="py-3 text-muted" dir="ltr" style="text-align: right;">
                                                                        <a href="<?= ROOT ?>BookDetails?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary px-3 rounded-pill me-2">عرض</a>
                                                                        <form method="post" class="d-inline">
                                                                            <input type="hidden" name="novel_id" value="<?= $n['id'] ?>">
                                                                            <button type="submit" name="delete_novel" class="btn btn-sm btn-danger px-3 rounded-pill" onclick="return confirm('هل أنت متأكد من حذف هذه الرواية؟')">حذف</button>
                                                                        </form>
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

                                <!-- Publishing Requests Tab -->
                                <div class="tab-pane fade" id="publishing-requests" role="tabpanel" aria-labelledby="publishing-requests-tab">
                                    <div class="card border-0 shadow-sm rounded-3">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="bg-light text-muted">
                                                        <tr>
                                                            <th class="ps-4 py-3">الرواية</th>
                                                            <th class="py-3">المؤلف</th>
                                                            <th class="py-3">قصة الرواية</th>
                                                            <th class="py-3">تاريخ الطلب</th>
                                                            <th class="py-3">إجراءات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($novelRequests)): ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-5 text-muted">لا يوجد طلبات نشر حالياً.</td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($novelRequests as $n): ?>
                                                                <tr>
                                                                    <td class="ps-4 py-3">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="<?= !empty($n['cover_image']) ? ROOT . 'assets/images/' . htmlspecialchars($n['cover_image']) : 'https://placehold.co/35x50/8b5a2b/ffffff?text=N' ?>" alt="Cover" class="rounded me-2" width="35" height="50" style="object-fit:cover; flex-shrink:0;">
                                                                            <div>
                                                                                <div class="fw-bold"><?= htmlspecialchars($n['title']) ?></div>
                                                                                <?php if(!empty($n['novel_type'])): ?><small class="text-muted"><?= htmlspecialchars($n['novel_type']) ?></small><?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="py-3 text-muted"><?= htmlspecialchars($n['author_name']) ?></td>
                                                                    <td class="py-3 text-muted small" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                                        <?= htmlspecialchars(mb_substr($n['description'] ?? '', 0, 60)) ?>...
                                                                        <button type="button" class="btn btn-sm btn-link p-0 d-block" data-bs-toggle="modal" data-bs-target="#novelDetailModal<?= $n['id'] ?>">عرض الكامل</button>
                                                                    </td>
                                                                    <td class="py-3 text-muted small" dir="ltr" style="text-align:right;">
                                                                        <?= date('Y-m-d', strtotime($n['created_at'])) ?>
                                                                    </td>
                                                                    <td class="py-3">
                                                                        <a href="<?= ROOT ?>BookDetails?id=<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary px-2 rounded-pill me-1">معاينة</a>
                                                                        <form method="post" class="d-inline">
                                                                            <input type="hidden" name="novel_id" value="<?= $n['id'] ?>">
                                                                            <button type="submit" name="approve_novel" class="btn btn-sm btn-success px-2 rounded-pill me-1">نشر</button>
                                                                            <button type="submit" name="delete_novel" class="btn btn-sm btn-outline-danger px-2 rounded-pill" onclick="return confirm('رفض وحذف الرواية؟')">رفض</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>

                                                                <!-- Novel Details Modal -->
                                                                <div class="modal fade" id="novelDetailModal<?= $n['id'] ?>" tabindex="-1" aria-hidden="true">
                                                                  <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                    <div class="modal-content">
                                                                      <div class="modal-header">
                                                                        <h5 class="modal-title"><i class="fas fa-book me-2 text-success"></i> بيانات طلب النشر — <?= htmlspecialchars($n['title']) ?></h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                      </div>
                                                                      <div class="modal-body">
                                                                        <div class="row">
                                                                            <div class="col-md-4 text-center">
                                                                                <img src="<?= !empty($n['cover_image']) ? ROOT . 'assets/images/' . htmlspecialchars($n['cover_image']) : 'https://placehold.co/160x230/8b5a2b/ffffff?text=Novel' ?>" style="max-width:100%; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); max-height:230px; object-fit:cover;">
                                                                            </div>
                                                                            <div class="col-md-8">
                                                                                <table class="table table-borderless">
                                                                                    <tr><th>عنوان الرواية</th><td class="fw-bold"><?= htmlspecialchars($n['title']) ?></td></tr>
                                                                                    <tr><th>اسم المؤلف</th><td><?= htmlspecialchars($n['author_name']) ?></td></tr>
                                                                                    <tr><th>تاريخ الإرسال</th><td><?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></td></tr>
                                                                                </table>
                                                                                <hr>
                                                                                <h6 class="fw-bold mb-2">قصة الرواية (Description):</h6>
                                                                                <div style="background:#f9f7f4; padding:14px; border-radius:8px; white-space:pre-wrap; font-size:0.95rem; max-height:180px; overflow-y:auto;"><?= htmlspecialchars($n['description'] ?? 'لا يوجد وصف.') ?></div>
                                                                            </div>
                                                                        </div>
                                                                      </div>
                                                                      <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                                        <form method="post" class="d-inline">
                                                                            <input type="hidden" name="novel_id" value="<?= $n['id'] ?>">
                                                                            <button type="submit" name="approve_novel" class="btn btn-success">نشر الرواية</button>
                                                                        </form>
                                                                      </div>
                                                                    </div>
                                                                  </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
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
                                    <form method="post" enctype="multipart/form-data">
                                        <?php if ($message) { ?>
                                            <div class="alert alert-success">
                                                <?= $message ?>
                                            </div>
                                        <?php } ?>
                                        <div class="row g-4">
                                            <div class="col-12 text-center mb-3">
                                                <div class="position-relative d-inline-block">
                                                    <img src="<?= !empty($adminProfile['image']) ? ROOT . 'assets/images/users/' . $adminProfile['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($adminProfile['username']) . '&background=8b5a2b&color=fff' ?>" alt="Admin" class="rounded-circle shadow-sm" width="100" height="100" style="object-fit: cover;" id="profileImagePreview">
                                                    <label for="adminImageInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2" style="cursor:pointer; background-color: var(--accent-color) !important;">
                                                        <i class="fas fa-camera"></i>
                                                    </label>
                                                    <input type="file" id="adminImageInput" name="image" class="d-none" accept="image/*" onchange="document.getElementById('profileImagePreview').src = window.URL.createObjectURL(this.files[0])">
                                                </div>
                                            </div>
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
                                            <div class="col-12 mt-4 text-start">
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
    <script src="<?= ROOT ?>assets/js/admin.js"></script>
    <!-- Writer Modals -->
    <?php if (!empty($writerRequests)): foreach ($writerRequests as $u): ?>
    <div class="modal fade" id="writerModal<?= $u['id'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-pen-nib me-2 text-primary"></i> طلب الانضمام — <?= htmlspecialchars($u['full_name'] ?? '') ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <table class="table table-borderless">
                <tr><th>الاسم الثلاثي</th><td><?= htmlspecialchars($u['full_name'] ?? '—') ?></td></tr>
                <tr><th>اسم الرواية</th><td><?= htmlspecialchars($u['novel_name'] ?? '—') ?></td></tr>
                <tr><th>البريد</th><td><?= htmlspecialchars($u['email'] ?? '—') ?></td></tr>
                <tr><th>تاريخ الطلب</th><td><?= date('Y-m-d H:i', strtotime($u['created_at'])) ?></td></tr>
            </table>
            <hr>
            <h6 class="fw-bold mb-2">نبذة وتفاصيل الطلب:</h6>
            <div style="background:#f9f7f4; padding:14px; border-radius:8px; white-space:pre-wrap; font-size:0.95rem; max-height:200px; overflow-y:auto;"><?= htmlspecialchars($u['request_details'] ?? 'لا يوجد تفاصيل.') ?></div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
    <!-- Publishing Modals -->
    <?php if (!empty($novelRequests)): foreach ($novelRequests as $n): ?>
    <div class="modal fade" id="novelModal<?= $n['id'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-book-open me-2 text-primary"></i> تفاصيل الرواية — <?= htmlspecialchars($n['title']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
            <img src="<?= !empty($n['cover_image']) ? ROOT . 'assets/images/' . htmlspecialchars($n['cover_image']) : 'https://placehold.co/150x220/8b5a2b/ffffff?text=Novel' ?>" alt="Cover" class="rounded mb-3 shadow-sm" width="120" style="max-height:180px; object-fit:cover;">
            <h5 class="fw-bold"><?= htmlspecialchars($n['title']) ?></h5>
            <p class="text-muted small mb-3">بقلم: <?= htmlspecialchars($n['author_name']) ?></p>
            <hr>
            <div class="text-start" style="background:#f9f7f4; padding:15px; border-radius:8px; white-space:pre-wrap; font-size:0.9rem; max-height:250px; overflow-y:auto;"><?= htmlspecialchars($n['description']) ?></div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</body>

</html>
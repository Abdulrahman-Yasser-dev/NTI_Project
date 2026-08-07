<?php
require_once "../app/core/init.php";

if (!isset($_SESSION['user'])) {
    header("Location: " . ROOT . "login");
    exit();
}

$user = $_SESSION['user'];

$user_data = query($conn, "SELECT * FROM `users` WHERE id = :id", ["id" => $user['id']])[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];

    $imageName = $user_data['image'];

    if (!empty($_FILES['image']['name'])) {
        !is_dir($dir = "../public/assets/images/users/") && mkdir($dir, 0777, true);
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $dir . $imageName);
    }

    $query = "UPDATE `users` SET `username`=:username, `email`=:email, `image`=:image";
    $params = [
        "username" => $username,
        "email" => $email,
        "image" => $imageName,
        "id" => $user['id']
    ];

    if (!empty($oldPassword) || !empty($newPassword)) {
        if (password_verify($oldPassword, $user_data['password'])) {
            $query .= ", `password`=:password";
            $params["password"] = password_hash($newPassword, PASSWORD_DEFAULT);
        } else {
            $_SESSION['error'] = "كلمة المرور القديمة غير صحيحة.";
            header("Location: " . ROOT . "profile?tab=settings");
            exit();
        }
    }

    $query .= " WHERE id = :id";
    execute($conn, $query, $params);

    $user['username'] = $username;
    $user['image'] = $imageName;
    $_SESSION['success'] = "تم تحديث البيانات بنجاح!";

    header("Location: " . ROOT . "profile?tab=settings");
    exit();
}

$avatarSrc = !empty($user_data['image']) ? ROOT . "assets/images/users/" . $user_data['image'] : ROOT . "assets/images/placeholder.jpg";


$errorMsg = $_SESSION['error'] ?? '';
$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);



$joinDate = date("Y", strtotime($user_data['created_at']));
$booksRead = query($conn, "SELECT COUNT(*) as count FROM user_library WHERE user_id = :user_id AND status = 'completed'", ["user_id" => $user['id']])[0]['count'] ?? 0;
$favoritesCount = query($conn, "SELECT COUNT(*) as count FROM user_library WHERE user_id = :user_id AND is_favorite = 1", ["user_id" => $user['id']])[0]['count'] ?? 0;
$reviewsCount = query($conn, "SELECT COUNT(*) as count FROM book_ratings WHERE user_id = :user_id", ["user_id" => $user['id']])[0]['count'] ?? 0;

$readingNowBooks = query($conn, "SELECT n.* FROM novels n JOIN user_library ul ON n.id = ul.novel_id WHERE ul.user_id = :user_id AND ul.status = 'reading_now'", ["user_id" => $user['id']]);
if (!$readingNowBooks) $readingNowBooks = [];

$favoritesBooks = query($conn, "SELECT n.* FROM novels n JOIN user_library ul ON n.id = ul.novel_id WHERE ul.user_id = :user_id AND ul.is_favorite = 1", ["user_id" => $user['id']]);
if (!$favoritesBooks) $favoritesBooks = [];

$myListBooks = query($conn, "SELECT n.* FROM novels n JOIN user_library ul ON n.id = ul.novel_id WHERE ul.user_id = :user_id AND ul.status = 'my_list'", ["user_id" => $user['id']]);
if (!$myListBooks) $myListBooks = [];

$myWorksBooks = [];
if ($user['role'] == 'writer') {
    $authorRow = query($conn, "SELECT id FROM authors WHERE name = :name LIMIT 1", ["name" => $user['username']]);
    if ($authorRow) {
        $myWorksBooks = query($conn, "SELECT * FROM novels WHERE author_id = :author_id", ["author_id" => $authorRow[0]['id']]);
        if (!$myWorksBooks) $myWorksBooks = [];
    }
}


?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>حسابي — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <link rel="stylesheet" href="<?= ROOT ?>assets/css/index.css" />

    <link rel="stylesheet" href="<?= ROOT ?>assets/css/profile.css" />

    <script>
        const ROOT_URL = "<?= ROOT ?>";
    </script>
</head>

<body>
    <nav class="navbar" id="navbar">
        <a href="<?= ROOT ?>index" class="nav-brand">
            <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد">
            <span>سرد</span>
        </a>
        <ul class="nav-links">
            <li><a href="<?= ROOT ?>index">الرئيسية</a></li>
            <li><a href="<?= ROOT ?>Browsebooks">تصفح الكتب</a></li>
            <li><a href="#">من نحن</a></li>
        </ul>
        <div class="nav-actions">
            <?php if ($_SESSION["user"]["role"] == "admin"): ?>
                <a href="<?= ROOT ?>admin" class="nav-btn glass">لوحة التحكم</a>
            <?php endif; ?>
            <div class="profile-dropdown">
                <button class="profile-toggle" onclick="toggleProfileMenu()">
                    <i class="fa-solid fa-user-circle"></i>
                    <span><?= htmlspecialchars($_SESSION["user"]["username"]) ?></span>
                    <i class="fa-solid fa-chevron-down text-sm"></i>
                </button>
                <div class="profile-menu" id="profileMenu">
                    <a href="<?= ROOT ?>profile"><i class="fa-solid fa-user"></i> حسابي</a>
                    <a href="<?= ROOT ?>logout"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
                </div>
            </div>
            <button class="nav-toggle" aria-label="القائمة">☰</button>
        </div>
    </nav>

    <header class="profile-header">
        <div class="profile-cover">
        </div>
        <div class="profile-info-container">
            <div class="profile-avatar">
                <img src="<?= $avatarSrc ?>" alt="صورة المستخدم" id="avatarImage">
                <button class="edit-avatar-btn" title="تغيير الصورة"><i class="fa-solid fa-camera"></i></button>
            </div>

            <div class="profile-details">
                <h1 class="profile-name"><?= htmlspecialchars($user['username']) ?></h1>
                <div class="profile-badges">
                    <span class="badge role-badge"><i class="fa-solid fa-book-open-reader"></i> <?= ($user['role'] == 'writer' ? 'كاتب مبدع' : 'قارئ نهم') ?></span>
                    <span class="badge join-badge"><i class="fa-regular fa-calendar"></i> انضم في <?= $joinDate ?></span>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-box">
                    <span class="stat-num"><?= $booksRead ?></span>
                    <span class="stat-label">كتاب قرأه</span>
                </div>
                <div class="stat-box">
                    <span class="stat-num"><?= $favoritesCount ?></span>
                    <span class="stat-label">المفضلة</span>
                </div>
                <div class="stat-box">
                    <span class="stat-num"><?= $reviewsCount ?></span>
                    <span class="stat-label">مراجعة</span>
                </div>
            </div>
        </div>
    </header>

    <main class="profile-main">
        <div class="profile-layout">

            <aside class="profile-sidebar">
                <ul class="profile-tabs" id="profileTabs">
                    <li class="tab-item active" data-tab="reading-now">
                        <i class="fa-solid fa-book-open"></i> أقرأ حالياً
                    </li>
                    <li class="tab-item" data-tab="favorites">
                        <i class="fa-solid fa-heart"></i> المفضلة
                    </li>
                    <li class="tab-item" data-tab="my-list">
                        <i class="fa-solid fa-bookmark"></i> قائمتي
                    </li>

                    <?php if ($user['role'] == 'writer'): ?>
                        <li class="tab-item" data-tab="my-works">
                            <i class="fa-solid fa-pen-nib"></i> أعمالي
                        </li>
                    <?php endif; ?>

                    <li class="tab-item" data-tab="settings">
                        <i class="fa-solid fa-gear"></i> إعدادات الحساب
                    </li>
                </ul>
            </aside>

            <section class="profile-content-area">
                <div class="tab-content active" id="tab-reading-now">
                    <h2 class="section-title">أقرأ حالياً</h2>
                    <?php if (!empty($readingNowBooks)): ?>
                        <div class="books-grid">
                            <?php foreach ($readingNowBooks as $book): ?>
                                <div class="book-card-mini">
                                    <img src="<?= ROOT ?>assets/images/novels/<?= htmlspecialchars($book['cover_image']) ?>" alt="غلاف الكتاب">
                                    <div class="book-card-info">
                                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                                        <p>قيد القراءة</p>
                                        <a href="<?= ROOT ?>novel/<?= $book['id'] ?>" class="btn-read-continue">متابعة القراءة</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-book-open"></i>
                            <p>لست تقرأ أي رواية حالياً.</p>
                            <a href="<?= ROOT ?>Browsebooks" class="nav-btn filled" style="margin-top:15px">تصفح الكتب</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-content" id="tab-favorites">
                    <h2 class="section-title">المفضلة</h2>
                    <?php if (!empty($favoritesBooks)): ?>
                        <div class="books-grid-simple">
                            <?php foreach ($favoritesBooks as $book): ?>
                                <div class="simple-book-item">
                                    <a href="<?= ROOT ?>novel/<?= $book['id'] ?>">
                                        <img src="<?= ROOT ?>assets/images/novels/<?= htmlspecialchars($book['cover_image']) ?>" alt="غلاف">
                                    </a>
                                    <h4><?= htmlspecialchars($book['title']) ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-regular fa-heart"></i>
                            <p>لا يوجد روايات في المفضلة.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-content" id="tab-my-list">
                    <h2 class="section-title">قائمتي (للقراءة لاحقاً)</h2>
                    <?php if (!empty($myListBooks)): ?>
                        <div class="books-grid-simple">
                            <?php foreach ($myListBooks as $book): ?>
                                <div class="simple-book-item">
                                    <a href="<?= ROOT ?>novel/<?= $book['id'] ?>">
                                        <img src="<?= ROOT ?>assets/images/novels/<?= htmlspecialchars($book['cover_image']) ?>" alt="غلاف">
                                    </a>
                                    <h4><?= htmlspecialchars($book['title']) ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-regular fa-bookmark"></i>
                            <p>القائمة فارغة حالياً. تصفح الكتب وأضفها إلى قائمتك!</p>
                            <a href="<?= ROOT ?>Browsebooks" class="nav-btn filled" style="margin-top:15px">تصفح الكتب</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($user['role'] == 'writer'): ?>
                    <div class="tab-content" id="tab-my-works">
                        <h2 class="section-title">أعمالي المنشورة</h2>
                        <div class="writer-actions">
                            <a href="<?= ROOT ?>add_novel" class="nav-btn filled" style="display:inline-block;"><i class="fa-solid fa-plus"></i> نشر رواية جديدة</a>
                        </div>
                        <?php if (!empty($myWorksBooks)): ?>
                            <div class="books-grid-simple">
                                <?php foreach ($myWorksBooks as $book): ?>
                                    <div class="simple-book-item">
                                        <a href="<?= ROOT ?>manage_novel_chapters/<?= $book['id'] ?>">
                                            <img src="<?= ROOT ?>assets/images/novels/<?= htmlspecialchars($book['cover_image']) ?>" alt="غلاف">
                                        </a>
                                        <h4><?= htmlspecialchars($book['title']) ?></h4>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa-solid fa-pen-nib"></i>
                                <p>لم تنشر أي روايات بعد.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="tab-content" id="tab-settings">
                    <h2 class="section-title">إعدادات الحساب</h2>
                    <form class="settings-form" action="" method="POST" enctype="multipart/form-data">
                        <?php if ($errorMsg): ?>
                            <div style="background: rgba(255,0,0,0.1); color: #ff6b6b; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(255,0,0,0.2);">
                                <?= $errorMsg ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($successMsg): ?>
                            <div style="background: rgba(0,255,0,0.1); color: #4ade80; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(0,255,0,0.2);">
                                <?= $successMsg ?>
                            </div>
                        <?php endif; ?>

                        <input type="file" name="image" id="profileImageInput" style="display:none;" accept="image/*">
                        <div class="form-group">
                            <label>اسم المستخدم</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user_data['username']) ?>">
                        </div>

                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>">
                        </div>

                        <div class="form-group">
                            <label>كلمة المرور القديمة</label>
                            <input type="password" name="old_password" placeholder="اترك الحقل فارغاً إذا لم ترد التغيير">
                        </div>

                        <div class="form-group">
                            <label>كلمة المرور الجديدة</label>
                            <input type="password" name="new_password" placeholder="اترك الحقل فارغاً إذا لم ترد التغيير">
                        </div>

                        <button type="submit" class="nav-btn filled submit-btn">حفظ التعديلات</button>
                    </form>
                </div>

            </section>
        </div>
    </main>

    <script src="<?= ROOT ?>assets/js/index.js"></script>
    <script src="<?= ROOT ?>assets/js/profile.js"></script>
    <script>
        // فتح تبويب الإعدادات إذا كان هناك رسالة أو تم التوجيه إليه
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'settings') {
            document.querySelector('.tab-item[data-tab="settings"]').click();
        }

        document.querySelector('.edit-avatar-btn').addEventListener('click', function() {
            document.querySelector('.tab-item[data-tab="settings"]').click();
            document.getElementById('profileImageInput').click();
        });

        document.getElementById('profileImageInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                document.getElementById('avatarImage').src = URL.createObjectURL(this.files[0]);
                alert("تم اختيار الصورة بنجاح! لا تنسَ الضغط على 'حفظ التعديلات' في أسفل الإعدادات.");
            }
        });
    </script>
</body>

</html>
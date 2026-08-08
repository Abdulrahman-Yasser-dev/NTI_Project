<?php
require_once "../app/core/init.php";

if (!isset($_SESSION['user'])) {
    header("Location: login");
    exit();
}

// Only writers can add novels
if ($_SESSION['user']['role'] !== 'writer' && $_SESSION['user']['role'] !== 'admin') {
    header("Location: author_dashboard");
    exit();
}

$categories = query($conn, "SELECT id, name_ar FROM categories", []);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $selectedCategories = $_POST['categories'] ?? [];

    if (empty($title) || empty($description) || empty($selectedCategories)) {
        $error = "من فضلك أكمل جميع الحقول واختر تصنيفاً واحداً على الأقل";
    } elseif (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== 0) {
        $error = "من فضلك ارفع صورة الرواية";
    } else {
        // add_novel.php is in app/pages/, public is at ../../public/
        $uploadDir = __DIR__ . "/../../public/assets/images/novels/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $uploadSuccess = move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $fileName);

        if (!$uploadSuccess) {
            $error = "فشل رفع الصورة. تأكد من صلاحيات المجلد.";
        } else {
            $coverImagePath = 'novels/' . $fileName;
            $slug = trim(preg_replace('/\s+/', '-', $title)) . '-' . time();

            $authorName = $_SESSION['user']['username'];
            $authorRow = query($conn, "SELECT id FROM authors WHERE name = :name LIMIT 1", ["name" => $authorName]);
            if (!empty($authorRow)) {
                $authorId = $authorRow[0]['id'];
            } else {
                execute($conn, "INSERT INTO authors (name) VALUES (:name)", ["name" => $authorName]);
                $authorId = $conn->lastInsertId();
            }

            $mainCategoryId = (int)$selectedCategories[0];

            $insertQuery = "INSERT INTO novels (author_id, category_id, title, slug, description, cover_image, status) 
                          VALUES (:author_id, :category_id, :title, :slug, :description, :cover_image, 'archived')";

            $success = execute($conn, $insertQuery, [
                "author_id"   => $authorId,
                "category_id" => $mainCategoryId,
                "title"       => $title,
                "slug"        => $slug,
                "description" => $description,
                "cover_image" => $coverImagePath,
            ]);

            if ($success) {
                $novelId = $conn->lastInsertId();
                foreach ($selectedCategories as $catId) {
                    execute($conn, "INSERT INTO novel_categories (novel_id, category_id) VALUES (:novel_id, :category_id)", [
                        "novel_id"    => $novelId,
                        "category_id" => (int)$catId
                    ]);
                }
                $message = "تم إرسال الرواية بنجاح! في انتظار موافقة الإدارة.";
            } else {
                $error = "حدث خطأ أثناء إضافة الرواية، حاول مرة أخرى";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة رواية جديدة — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Cairo:wght@300;400;500;600;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">

    <link rel="stylesheet" href="<?= ROOT ?>assets/css/add_novel.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/Browsebooks.css">

</head>

<body>
    <nav class="navbar-premium" id="navbar">
        <div class="navbar-premium-container">
            <div class="navbar-premium-brand">
                <a href="<?= ROOT ?>index" class="brand-premium-link">
                    <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد logo" class="brand-premium-logo">
                    <span class="brand-premium-name">سرد</span>
                </a>
            </div>
            <ul class="nav-premium-links">
                <li><a href="<?= ROOT ?>index">الرئيسية</a></li>
                <li><a href="<?= ROOT ?>Browsebooks">المكتبة</a></li>
                <?php if (isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><li><a href="<?= ROOT ?>author_dashboard" class="active">لوحة الكاتب</a></li><?php else: ?><li><a href="<?= ROOT ?>writer_application">كن كاتبا</a></li><?php endif; ?>
            </ul>
            <button class="nav-mobile-toggle" id="navMobileToggle" aria-label="القائمة">☰</button>
            <div class="nav-premium-actions">
                <?php if (!isset($_SESSION["user"])): ?>
                    <a href="<?= ROOT ?>login" class="nav-premium-btn nav-premium-btn-outline">تسجيل الدخول</a>
                    <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-filled">إنشاء حساب</a>
                <?php else: ?>
                    <?php if ($_SESSION["user"]["role"] == "admin"): ?>
                        <a href="<?= ROOT ?>admin" class="nav-premium-btn nav-premium-btn-outline">لوحة التحكم</a>
                    <?php endif; ?>
                    <div class="profile-dropdown">
                        <button class="profile-toggle" onclick="toggleProfileMenu()">
                            <?php if (!empty($_SESSION['user']['image'])): ?>
                                <img src="<?= ROOT ?>assets/images/users/<?= htmlspecialchars($_SESSION['user']['image']) ?>" alt="avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fa-solid fa-user-circle"></i>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($_SESSION["user"]["username"]) ?></span>
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </button>
                        <div class="profile-menu" id="profileMenu">
                            <a href="<?= ROOT ?>profile"><i class="fa-solid fa-user"></i> حسابي</a>
                            <a href="<?= ROOT ?>logout"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <h1 class="page-title" style="margin-top: 110px;">إضافة رواية جديدة</h1>

    <div class="page-wrapper">

        <?php if (isset($error)): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if (isset($message)): ?><p class="success"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>

        <form method="POST" action="add_novel" enctype="multipart/form-data" id="novelForm" novalidate>

            <div class="field">
                <span class="field-label">صورة الرواية</span>
                <input type="file" name="cover_image" id="cover_image" accept="image/*" required>
                <span class="required-note" id="err-cover"></span>
            </div>

            <div class="field">
                <span class="field-label">اسم الرواية بالعربية</span>
                <input type="text" name="title" id="title" maxlength="100" required>
                <span class="counter"><span id="title-count">0</span>/100</span>
                <span class="required-note" id="err-title"></span>
            </div>

            <div class="field">
                <span class="field-label">قصة الرواية</span>
                <textarea name="description" id="description" maxlength="2000" rows="6" required></textarea>
                <span class="counter"><span id="desc-count">0</span>/2000</span>
                <span class="required-note" id="err-desc"></span>
            </div>

            <div class="field">
                <span class="field-label">تصنيفات الرواية</span>
                <div class="dropdown" id="catDropdown">
                    <div class="dropdown-toggle" id="catToggle">
                        <span id="catToggleText">اختر التصنيفات</span>
                        <span class="arrow">▾</span>
                    </div>
                    <div class="dropdown-menu" id="catMenu">
                        <?php foreach ($categories as $cat): ?>
                            <label class="dropdown-item">
                                <?= htmlspecialchars($cat['name_ar']) ?>
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span class="required-note" id="err-cat"></span>
            </div>

            <button type="submit">إضافة</button>
        </form>
    </div>

    <script src="<?= ROOT ?>assets/js/add_novel.js"></script>
    <script>
    var navToggle = document.getElementById('navMobileToggle');
    var navLinks = document.querySelector('.nav-premium-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('is-open');
            navToggle.textContent = navLinks.classList.contains('is-open') ? '✕' : '☰';
        });
    }
    function toggleProfileMenu(e) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('profileMenu');
        if (menu) menu.classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
        var toggle = document.querySelector('.profile-toggle');
        var menu = document.getElementById('profileMenu');
        if (toggle && menu && !toggle.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
    </script>
</body>

</html>
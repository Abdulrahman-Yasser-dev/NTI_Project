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
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/add_novel.css">
</head>
<body>
    <nav class="navbar" id="navbar" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 15px 5%;">
        <a href="<?= ROOT ?>index" class="nav-brand" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد" style="height: 40px;">
            <span style="font-family: 'Aref Ruqaa', serif; font-size: 1.5rem; color: #8b5a2b; font-weight: bold;">سرد</span>
        </a>
        <ul class="nav-links" style="display: flex; gap: 20px; list-style: none; margin: 0; padding: 0;">
            <li><a href="<?= ROOT ?>index" style="text-decoration: none; color: #333; font-weight: 500;">الرئيسية</a></li>
            <li><a href="<?= ROOT ?>Browsebooks" style="text-decoration: none; color: #333; font-weight: 500;">تصفح الكتب</a></li>
            <li><a href="<?= ROOT ?>author_dashboard" style="text-decoration: none; color: #8b5a2b; font-weight: 600;">لوحة الكاتب</a></li>
        </ul>
        <div class="nav-actions" style="display: flex; gap: 15px; align-items: center;">
            <?php if($_SESSION["user"]["role"] == "admin"):?>
                <a href="<?= ROOT ?>admin" class="nav-btn glass" style="text-decoration: none; padding: 8px 20px; border-radius: 20px; border: 1px solid #8b5a2b; color: #8b5a2b;">لوحة التحكم</a>
            <?php endif; ?>
            <div style="position: relative;">
                <button onclick="this.nextElementSibling.classList.toggle('show')" style="background: none; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: inherit;">
                    <?php if(!empty($_SESSION['user']['image'])): ?>
                        <img src="<?= ROOT ?>assets/images/users/<?= htmlspecialchars($_SESSION['user']['image']) ?>" alt="avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fa-solid fa-user-circle" style="font-size: 1.5rem; color: #8b5a2b;"></i>
                    <?php endif; ?>
                    <span style="font-weight: 600; color: #333;"><?= htmlspecialchars($_SESSION["user"]["username"]) ?></span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: #666;"></i>
                </button>
                <div class="profile-menu" style="display: none; position: absolute; top: 100%; left: 0; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px; padding: 10px 0; min-width: 150px; flex-direction: column; z-index: 1001;">
                    <a href="<?= ROOT ?>profile" style="padding: 10px 20px; text-decoration: none; color: #333; display: block;"><i class="fa-solid fa-user" style="width: 20px;"></i> حسابي</a>
                    <a href="<?= ROOT ?>logout" style="padding: 10px 20px; text-decoration: none; color: #d32f2f; display: block;"><i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> تسجيل الخروج</a>
                </div>
            </div>
            <style>.profile-menu.show { display: flex !important; } .profile-menu a:hover { background: #f5f5f5; }</style>
        </div>
    </nav>

    <h1 class="page-title">إضافة رواية جديدة</h1>

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
</body>
</html>
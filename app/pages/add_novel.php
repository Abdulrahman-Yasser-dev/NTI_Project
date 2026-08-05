<?php
require_once "../app/core/init.php";

if (!isset($_SESSION['user'])) {
    header("Location: login");
    exit();
}

$categories = query($conn, "SELECT id, name_ar FROM categories", []);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $selectedCategories = $_POST['categories'] ?? [];
    $novelType = $_POST['novel_type'] ?? null;

    if (empty($title) || empty($description) || empty($selectedCategories)) {
        $error = "من فضلك أكملي جميع الحقول واختاري تصنيف واحد على الأقل";
    } elseif (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== 0) {
        $error = "من فضلك ارفعي صورة الرواية";
    } else {
        $uploadDir = "../public/assets/images/novels/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('novel_') . '.' . $ext;
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $fileName);
        $coverImagePath = "assets/images/novels/" . $fileName;

        $slug = trim(preg_replace('/\s+/', '-', $title)) . '-' . time();

        $authorName = $_SESSION['user']['username'];
        $authorRow = query($conn, "SELECT id FROM authors WHERE name = :name LIMIT 1", ["name" => $authorName]);
        if (!empty($authorRow)) {
            $authorId = $authorRow[0]['id'];
        } else {
            execute($conn, "INSERT INTO authors (name) VALUES (:name)", ["name" => $authorName]);
            $authorId = $conn->lastInsertId();
        }

        $mainCategoryId = $selectedCategories[0];

        $query = "INSERT INTO novels (author_id, category_id, title, slug, description, cover_image, novel_type, status) 
                  VALUES (:author_id, :category_id, :title, :slug, :description, :cover_image, :novel_type, 'draft')";

        $success = execute($conn, $query, [
            "author_id"   => $authorId,
            "category_id" => $mainCategoryId,
            "title"       => $title,
            "slug"        => $slug,
            "description" => $description,
            "cover_image" => $coverImagePath,
            "novel_type"  => $novelType
        ]);

        if ($success) {
            $novelId = $conn->lastInsertId();
            foreach ($selectedCategories as $catId) {
                execute($conn, "INSERT INTO novel_categories (novel_id, category_id) VALUES (:novel_id, :category_id)", [
                    "novel_id"    => $novelId,
                    "category_id" => $catId
                ]);
            }
            $message = "تم إرسال الرواية بنجاح، في انتظار موافقة الإدارة";
        } else {
            $error = "حدث خطأ أثناء إضافة الرواية، حاولي مرة أخرى";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة رواية جديدة</title>
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/add_novel.css">
</head>
<body>
    <h1 class="page-title">إضافة رواية جديدة</h1>

    <div class="page-wrapper">

        <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if (isset($message)): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>

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
                                <?= $cat['name_ar'] ?>
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span class="required-note" id="err-cat"></span>
            </div>

            <div class="field">
                <span class="field-label">نوع الرواية</span>
                <select name="novel_type">
                    <option value="">اختر نوع الرواية</option>
                    <option value="طويلة">رواية طويلة</option>
                    <option value="قصيرة">رواية قصيرة</option>
                    <option value="مجموعة قصصية">مجموعة قصصية</option>
                </select>
            </div>

            <button type="submit">إضافة</button>
        </form>
    </div>

    <script src="<?= ROOT ?>assets/js/add_novel.js"></script>
</body>
</html>
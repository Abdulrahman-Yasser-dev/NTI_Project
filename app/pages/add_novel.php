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
    } elseif (empty($_FILES['cover_image']['name'])) {
      $error = "من فضلك ارفعي صورة الرواية";
    } else {
      !is_dir($dir = "../public/assets/images/novels/") && mkdir($dir, 0777, true);
      $fileName = time() . "_" . basename($_FILES['cover_image']['name']);
      move_uploaded_file($_FILES['cover_image']['tmp_name'], $dir . $fileName);
      $coverImagePath = $fileName;
    }

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
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="ar" dir="rtl">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة رواية جديدة — سرد</title>
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
      body {
        background-color: var(--dark-bg);
        color: var(--cream);
        font-family: 'Tajawal', sans-serif;
        margin: 0;
        padding: 0;
      }

      .add-novel-container {
        max-width: 800px;
        margin: 80px auto;
        background: rgba(255, 255, 255, 0.03);
        padding: 40px;
        border-radius: 20px;
        border: 1px solid rgba(212, 175, 55, 0.1);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      }

      .add-novel-container h1 {
        color: var(--gold-primary);
        text-align: center;
        margin-bottom: 30px;
        font-family: 'Aref Ruqaa', serif;
        font-size: 2.5rem;
      }

      .form-group {
        margin-bottom: 25px;
      }

      .form-group label {
        display: block;
        margin-bottom: 10px;
        color: var(--gold-light);
        font-weight: 500;
        font-size: 1.1rem;
      }

      .form-group input[type="text"],
      .form-group textarea {
        width: 100%;
        padding: 15px;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(212, 175, 55, 0.3);
        color: white;
        font-family: 'Tajawal', sans-serif;
        font-size: 1rem;
        transition: all 0.3s ease;
      }

      .form-group input[type="text"]:focus,
      .form-group textarea:focus {
        outline: none;
        border-color: var(--gold-primary);
        background: rgba(0, 0, 0, 0.5);
      }

      .form-group input[type="file"] {
        color: white;
        padding: 10px 0;
      }

      .submit-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
        color: var(--dark-bg);
        border: none;
        border-radius: 10px;
        font-weight: bold;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
      }

      .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
      }

      .error-msg {
        background: rgba(255, 59, 48, 0.1);
        color: #ff3b30;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 25px;
        border: 1px solid rgba(255, 59, 48, 0.2);
        text-align: center;
        font-weight: 500;
      }

      .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        background: rgba(0, 0, 0, 0.2);
        padding: 20px;
        border-radius: 10px;
        border: 1px solid rgba(212, 175, 55, 0.1);
      }

      .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--cream);
        cursor: pointer;
      }

      .checkbox-item input {
        accent-color: var(--gold-primary);
        width: 18px;
        height: 18px;
      }

      .back-link {
        display: inline-block;
        color: var(--gold-primary);
        text-decoration: none;
        margin-bottom: 20px;
        font-weight: bold;
      }

      .back-link:hover {
        color: var(--gold-light);
      }
    </style>
  </head>

  <body>

    <div class="add-novel-container">
      <a href="<?= ROOT ?>profile" class="back-link"><i class="fa-solid fa-arrow-right"></i> العودة للبروفايل</a>

      <h1>إضافة رواية جديدة</h1>

      <?php if (!empty($error)): ?>
        <div class="error-msg"><?= $error ?></div>
      <?php endif; ?>

      <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label><i class="fa-solid fa-pen-nib"></i> عنوان الرواية</label>
          <input type="text" name="title" required placeholder="أدخل عنوان روايتك هنا...">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-align-right"></i> نبذة عن الرواية</label>
          <textarea name="description" rows="6" required placeholder="اكتب وصفاً مشوقاً يجذب القراء..."></textarea>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-image"></i> صورة الغلاف</label>
          <input type="file" name="cover_image" accept="image/*" required>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-tags"></i> التصنيفات (اختر تصنيف أو أكثر)</label>
          <div class="checkbox-grid">
            <?php if (!empty($categories)): ?>
              <?php foreach ($categories as $cat): ?>
                <label class="checkbox-item">
                  <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>">
                  <?= htmlspecialchars($cat['name_ar']) ?>
                </label>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="color: #aaa;">لا توجد تصنيفات متاحة.</p>
            <?php endif; ?>
          </div>
        </div>

        <button type="submit" class="submit-btn"><i class="fa-solid fa-magic"></i> إنشاء الرواية وبدء الكتابة</button>
      </form>
    </div>
  </body>

  </html>
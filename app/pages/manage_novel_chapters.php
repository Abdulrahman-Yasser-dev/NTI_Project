<?php
require_once "../app/core/init.php";

// Get novel_id from URL segment (/manage_novel_chapters/2) or query string
$url_parts = isset($_GET['url']) ? explode('/', trim($_GET['url'], '/')) : [];
$novel_id = isset($url_parts[1]) && (int)$url_parts[1] > 0
    ? (int)$url_parts[1]
    : (isset($_GET['id']) && (int)$_GET['id'] > 0 ? (int)$_GET['id'] : 1);

// Handle Settings Modal POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_novel') {
    $new_title = trim($_POST['novel_title'] ?? '');
    $new_category = (int)($_POST['novel_category'] ?? 0);
    
    if ($new_title) {
        execute($conn, "UPDATE novels SET title = :title, category_id = :category_id WHERE id = :id", [
            'title'       => $new_title,
            'category_id' => $new_category,
            'id'          => $novel_id,
        ]);
    }
    
    // Handle cover image upload
    if (!empty($_FILES['cover_image']['name'])) {
        $upload_dir = '../public/assets/images/';
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = 'cover_' . $novel_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $filename)) {
            execute($conn, "UPDATE novels SET cover_image = :cover_image WHERE id = :id", [
                'cover_image' => $filename,
                'id'          => $novel_id,
            ]);
        }
    }
    
    header("Location: " . ROOT . "manage_novel_chapters/" . $novel_id);
    die;
}

// Fetch Novel details (categories column is name_ar)
$novel_data = query($conn, "
    SELECT n.*, c.name_ar as category_name 
    FROM novels n 
    LEFT JOIN categories c ON n.category_id = c.id 
    WHERE n.id = :id
", ['id' => $novel_id]);

$novel = $novel_data ? $novel_data[0] : null;

if (!$novel) {
    die("الرواية غير موجودة.");
}

// Fetch all categories for the settings dropdown
$categories = query($conn, "SELECT id, name_ar FROM categories ORDER BY name_ar ASC", []);

// Fetch Chapters
$chapters = query($conn, "
    SELECT * 
    FROM chapters 
    WHERE novel_id = :novel_id 
    ORDER BY chapter_number ASC
", ['novel_id' => $novel_id]);

// Calculate published count
$published_count = count(array_filter($chapters, fn($ch) => $ch['isPublished'] == 1));

// Cover image path with fallback
$cover_image = !empty($novel['cover_image'])
    ? ROOT . "assets/images/" . $novel['cover_image']
    : "https://lh3.googleusercontent.com/aida-public/AB6AXuCoYgpJ4ZHMmFIVzlsmUEm6pssKLVOyV9HAzJ8PVnzWS9oogDRHYfMSui41dEUXizaQ41dJlEm9O3yVOkHVTI5PLRFEU6t5ylY2nDFdIWXapLc2cHf5JTylKmo6N4534F7gDIybFhMPRvbZtwmRV2VmAPnzGLq7FBBN5EGHAO5QULTzF6G2PVUNNWbcmuu6AgJw20ssHx1MTHgbPA0_x54Wp7yDHyCbRx6ulpQcFtbgvc9bIJBza08atcz8G_FSSMJWH5IRbMsLmsj0";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفصول — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Amiri:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/Browsebooks.css">
    <style>
        .chapters-premium-hall {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 2rem 60px;
            /* offset for fixed navbar */
            display: flex;
            gap: 40px;
            min-height: calc(100vh - 200px);
        }

        @media (max-width: 900px) {
            .chapters-premium-hall {
                flex-direction: column;
                padding-top: 100px;
            }
        }

        /* Sidebar */
        .premium-sidebar {
            flex: 0 0 320px;
            background: #FFFFFF;
            border-radius: 24px;
            padding: 32px;
            box-shadow: var(--shadow-premium);
            border: 1px solid rgba(44, 26, 14, 0.03);
            text-align: center;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .premium-sidebar .cover-container {
            width: 100%;
            margin: 0 0 24px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(44, 26, 14, 0.12);
            aspect-ratio: 2/3;
        }

        .premium-sidebar .cover-container img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .premium-sidebar h2 {
            font-family: 'Aref Ruqaa', serif;
            font-size: 1.8rem;
            color: var(--walnut);
            margin-bottom: 12px;
        }

        .premium-sidebar .badges {
            display: inline-flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-bottom: 30px;
        }

        .badge-premium {
            background: var(--cream);
            color: var(--gold);
            font-family: 'Cairo', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid rgba(212, 166, 74, 0.2);
        }

        .badge-premium-dark {
            background: var(--walnut);
            color: #FCF8F2;
            font-family: 'Cairo', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .premium-btn-block {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            border-radius: 40px;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 12px;
            cursor: pointer;
            border: none;
        }

        .btn-walnut {
            background: var(--walnut);
            color: #FCF8F2;
            box-shadow: 0 4px 15px rgba(59, 36, 23, 0.15);
        }

        .btn-walnut:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 36, 23, 0.25);
            background: var(--walnut-light);
        }

        .btn-outline-gold {
            background: transparent;
            border: 1.5px solid rgba(212, 166, 74, 0.3);
            color: var(--walnut);
        }

        .btn-outline-gold:hover {
            background: var(--gold-glow);
            border-color: var(--gold);
        }

        /* Chapters List */
        .premium-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(44, 26, 14, 0.05);
            margin-bottom: 8px;
        }

        .list-header h3 {
            font-family: 'Aref Ruqaa', serif;
            font-size: 2rem;
            color: var(--walnut);
        }

        .add-chapter-premium {
            background: rgba(255, 255, 255, 0.6);
            border: 2px dashed rgba(212, 166, 74, 0.4);
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }

        .add-chapter-premium:hover {
            background: #FFFFFF;
            border-color: var(--gold);
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .add-chapter-premium i {
            font-size: 1.5rem;
            color: var(--gold);
        }

        .add-chapter-premium span {
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            color: var(--walnut);
            font-size: 1.1rem;
        }

        .chapter-premium-item {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(44, 26, 14, 0.02);
            border: 1px solid rgba(44, 26, 14, 0.03);
            transition: all 0.3s ease;
        }

        .chapter-premium-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
            border-color: rgba(212, 166, 74, 0.3);
        }

        .chapter-info-wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .drag-handle {
            width: 36px;
            height: 36px;
            background: var(--cream);
            color: rgba(44, 26, 14, 0.25);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            border: 1px solid rgba(44, 26, 14, 0.06);
            cursor: grab;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .chapter-premium-item:hover .drag-handle {
            color: var(--gold);
            border-color: rgba(212, 166, 74, 0.25);
        }

        /* Dragging states */
        .chapter-premium-item.dragging {
            opacity: 0.4;
            transform: scale(0.98);
            border: 2px dashed var(--gold) !important;
            background: rgba(212, 166, 74, 0.03) !important;
        }

        .chapter-premium-item.drag-over {
            border-color: var(--gold) !important;
            box-shadow: 0 0 0 2px rgba(212, 166, 74, 0.2), var(--shadow-soft) !important;
            transform: translateY(-3px);
        }

        .chapter-details h4 {
            font-family: 'Cairo', sans-serif;
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .chapter-meta {
            display: flex;
            gap: 16px;
            font-size: 0.85rem;
            color: var(--text-soft);
        }

        .chapter-meta span i {
            color: var(--gold);
            margin-left: 4px;
        }

        .chapter-actions button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--cream);
            border: 1px solid rgba(44, 26, 14, 0.05);
            color: var(--walnut);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chapter-actions button:hover {
            background: var(--gold);
            color: #FCF8F2;
            border-color: var(--gold);
        }

        /* ---------------------------------------------------
           Sort Dropdown
        --------------------------------------------------- */
        .sort-wrapper {
            position: relative;
        }

        .sort-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: #FFFFFF;
            border-radius: 16px;
            padding: 8px;
            box-shadow: 0 12px 40px rgba(44, 26, 14, 0.1);
            border: 1px solid rgba(44, 26, 14, 0.05);
            min-width: 210px;
            z-index: 500;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease;
        }

        .sort-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .sort-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            font-size: 0.88rem;
            color: var(--text-dark);
            transition: all 0.2s ease;
            border: none;
            background: transparent;
            width: 100%;
            text-align: right;
        }

        .sort-option:hover {
            background: var(--cream);
            color: var(--walnut);
        }

        .sort-option.active {
            background: var(--gold-glow);
            color: var(--walnut);
            font-weight: 700;
        }

        .sort-option i {
            width: 18px;
            text-align: center;
            color: var(--gold);
            font-size: 0.85rem;
        }

        .sort-option .sort-dir {
            margin-right: auto;
            color: rgba(44, 26, 14, 0.25);
            font-size: 0.75rem;
            transition: color 0.2s;
        }

        .sort-option.active .sort-dir {
            color: var(--gold);
        }

        .sort-divider {
            height: 1px;
            background: rgba(44, 26, 14, 0.04);
            margin: 4px 0;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 26, 14, 0.4);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: #FCF8F2;
            border-radius: 24px;
            width: 90%;
            max-width: 500px;
            padding: 40px;
            box-shadow: 0 25px 60px rgba(44, 26, 14, 0.15);
            transform: translateY(20px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0) scale(1);
        }

        .modal-close {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(44, 26, 14, 0.05);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: var(--text-soft);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: rgba(211, 47, 47, 0.1);
            color: #D32F2F;
        }

        .modal-title {
            font-family: 'Aref Ruqaa', serif;
            font-size: 1.8rem;
            color: var(--walnut);
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }

        .form-group label {
            display: block;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            background: #FFFFFF;
            border: 1px solid rgba(44, 26, 14, 0.1);
            border-radius: 12px;
            padding: 12px 16px;
            font-family: 'Tajawal', sans-serif;
            color: var(--text-dark);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px var(--gold-glow);
        }

        .file-upload-wrapper {
            position: relative;
            background: #FFFFFF;
            border: 1px dashed rgba(212, 166, 74, 0.5);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-wrapper:hover {
            background: var(--gold-glow);
            border-color: var(--gold);
        }

        .file-upload-wrapper i {
            font-size: 2rem;
            color: var(--gold);
            margin-bottom: 10px;
            display: block;
        }

        .file-upload-wrapper p {
            font-family: 'Tajawal', sans-serif;
            color: var(--text-soft);
            font-size: 0.85rem;
            margin: 0;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR PREMIUM (Copied from Browsebooks.php) -->
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
                <li><a href="#">الكتّاب</a></li>
                <?php if(isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><li><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a></li><?php else: ?><li><a href="<?= ROOT ?>writer_application">كن كاتبا</a></li><?php endif; ?>
            </ul>
            <div class="nav-premium-actions">
                <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-outline">تسجيل الدخول</a>
                <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-filled">إنشاء حساب</a>
            </div>
        </div>
    </nav>

    <main class="chapters-premium-hall">
        <!-- Sidebar -->
        <aside class="premium-sidebar">
            <div class="cover-container">
                <img alt="غلاف رواية <?= htmlspecialchars($novel['title'] ?? '') ?>"
                    src="<?= htmlspecialchars($cover_image) ?>">
            </div>
            <h2><?= htmlspecialchars($novel['title'] ?? 'بدون عنوان') ?></h2>
            <div class="badges">
                <?php if (!empty($novel['category_name'])): ?>
                    <span class="badge-premium"><?= htmlspecialchars($novel['category_name']) ?></span>
                <?php endif; ?>
                <span class="badge-premium-dark"><?= $published_count ?> فصول منشورة</span>
            </div>

            <button class="premium-btn-block btn-outline-gold" id="openSettingsBtn">
                <i class="fas fa-cog"></i> إعدادات الرواية
            </button>
        </aside>

        <!-- Main Content -->
        <section class="premium-main">
            <div class="list-header">
                <h3>إدارة الفصول</h3>
                <div class="sort-wrapper">
                    <button class="premium-btn-block btn-outline-gold" id="sortToggleBtn"
                        style="width: auto; padding: 6px 16px; margin: 0;">
                        <i class="fas fa-sort"></i> <span id="sortLabel">ترتيب</span>
                    </button>
                    <div class="sort-dropdown" id="sortDropdown">
                        <button class="sort-option active" data-sort="manual">
                            <i class="fas fa-grip-vertical"></i>
                            ترتيب يدوي (Drag)
                            <span class="sort-dir"></span>
                        </button>
                        <div class="sort-divider"></div>
                        <button class="sort-option" data-sort="title-asc">
                            <i class="fas fa-sort-alpha-down"></i>
                            ترتيب أبجدي (أ ← ي)
                            <span class="sort-dir">أ→ي</span>
                        </button>
                        <button class="sort-option" data-sort="title-desc">
                            <i class="fas fa-sort-alpha-up"></i>
                            ترتيب أبجدي عكسي
                            <span class="sort-dir">ي→أ</span>
                        </button>
                        <div class="sort-divider"></div>
                        <button class="sort-option" data-sort="date-desc">
                            <i class="fas fa-calendar-alt"></i>
                            الأحدث أولاً
                            <span class="sort-dir">↓</span>
                        </button>
                        <button class="sort-option" data-sort="date-asc">
                            <i class="fas fa-calendar-alt"></i>
                            الأقدم أولاً
                            <span class="sort-dir">↑</span>
                        </button>
                        <div class="sort-divider"></div>
                        <button class="sort-option" data-sort="words-desc">
                            <i class="fas fa-pen-nib"></i>
                            أكثر كلمات أولاً
                            <span class="sort-dir">↓</span>
                        </button>
                        <button class="sort-option" data-sort="words-asc">
                            <i class="fas fa-pen-nib"></i>
                            أقل كلمات أولاً
                            <span class="sort-dir">↑</span>
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($novel['status'] === 'published'): ?>
                <a href="<?= ROOT ?>write_new_chapter_existing_novel/<?= $novel_id ?>" class="add-chapter-premium"
                    style="text-decoration: none;">
                    <i class="fas fa-plus-circle"></i>
                    <span>كتابة فصل جديد</span>
                </a>
            <?php else: ?>
                <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-clock text-warning" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <h4 style="color: #664d03; font-family: 'Aref Ruqaa', serif; margin-bottom: 5px;">قيد المراجعة</h4>
                    <p style="color: #664d03; margin: 0; font-size: 0.95rem;">هذه الرواية قيد المراجعة من قبل الإدارة. لا يمكنك إضافة فصول جديدة حتى يتم الموافقة عليها ونشرها.</p>
                </div>
            <?php endif; ?>

            <!-- Dynamic Chapters List -->
            <?php if (!empty($chapters)): ?>
                <?php foreach ($chapters as $chapter): 
                    $isDraft = $chapter['isPublished'] == 0;
                    $updatedDate = date('d F', strtotime($chapter['updated_at']));
                    $createdDate = date('d F', strtotime($chapter['created_at']));
                    $wordCount = number_format((int)$chapter['word_count']);
                    
                    // Draft styling
                    $itemStyle = $isDraft ? 'style="border: 2px dashed rgba(212, 166, 74, 0.4); background: rgba(255,255,255,0.5);"' : '';
                    $handleStyle = $isDraft ? 'style="background: transparent;"' : '';
                ?>
                <div class="chapter-premium-item" draggable="true" 
                     data-title="<?= htmlspecialchars($chapter['title']) ?>" 
                     data-date="<?= $chapter['created_at'] ?>"
                     data-words="<?= $chapter['word_count'] ?>"
                     <?= $itemStyle ?>>
                    <div class="chapter-info-wrapper">
                        <div class="drag-handle" <?= $handleStyle ?>><i class="fas fa-grip-vertical"></i></div>
                        <div class="chapter-details">
                            <h4>
                                <?= htmlspecialchars($chapter['title']) ?>
                                <?php if ($isDraft): ?>
                                    <span style="background: var(--gold-glow); color: var(--gold); padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; margin-right: 8px;">مسودة</span>
                                <?php endif; ?>
                            </h4>
                            <div class="chapter-meta">
                                <span><i class="fas fa-history"></i> آخر تعديل: <?= $updatedDate ?></span>
                                <span><i class="far fa-calendar-alt"></i> <?= $isDraft ? 'جاري الكتابة' : $createdDate ?></span>
                                <span><i class="fas fa-pen-nib"></i> <?= $wordCount ?> كلمة</span>
                            </div>
                        </div>
                    </div>
                    <div class="chapter-actions">
                        <a href="<?= ROOT ?>write_new_chapter_existing_novel/<?= $novel_id ?>/<?= $chapter['id'] ?>" class="btn" title="تعديل الفصل" style="background:none; border:none; color:inherit; cursor:pointer;">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-soft); padding: 40px; background: #fff; border-radius: 16px; border: 1px dashed rgba(44,26,14,0.1);">
                    لا يوجد فصول لهذه الرواية حتى الآن. ابدأ بكتابة الفصل الأول!
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- FOOTER PREMIUM (Copied from Browsebooks.php) -->
    <footer class="footer-premium">
        <div class="footer-premium-curve"></div>
        <div class="footer-premium-content">
            <div class="footer-premium-brand">
                <span class="footer-premium-logo">سرد</span>
                <p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p>
            </div>
            <div class="footer-premium-links">
                <div class="footer-premium-col">
                    <h4>روابط سريعة</h4><a href="<?= ROOT ?>index">الرئيسية</a><a
                        href="<?= ROOT ?>Browsebooks">المكتبة</a><a href="#">الكتّاب</a><?php if(isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a><?php else: ?><a href="<?= ROOT ?>writer_application">كن كاتبا</a><?php endif; ?>
                </div>
                <div class="footer-premium-col">
                    <h4>حسابك</h4><a href="<?= ROOT ?>signup">تسجيل الدخول</a><a href="<?= ROOT ?>signup">إنشاء حساب</a>
                </div>
                <div class="footer-premium-col">
                    <h4>تواصل معنا</h4><a href="#">الدعم الفني</a><a href="#">الأسئلة الشائعة</a><a href="#">سياسة
                        الخصوصية</a>
                </div>
            </div>
        </div>
        <div class="footer-premium-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <!-- Settings Modal -->
    <div class="modal-overlay" id="settingsModal">
        <div class="modal-content">
            <button class="modal-close" id="closeSettingsBtn"><i class="fas fa-times"></i></button>
            <h2 class="modal-title">إعدادات الرواية</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_novel">

                <div class="form-group">
                    <label>اسم الرواية</label>
                    <input type="text" name="novel_title" class="form-input"
                        value="<?= htmlspecialchars($novel['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>التصنيف</label>
                    <select name="novel_category" class="form-input">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= $cat['id'] == $novel['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name_ar']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>غلاف الرواية</label>
                    <div class="file-upload-wrapper">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p style="color: var(--text-soft); font-size: 0.9rem;">اسحب وأفلت الصورة هنا أو اضغط لاختيار ملف</p>
                        <input type="file" name="cover_image" accept="image/*">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="premium-btn-block btn-walnut" style="margin: 0; padding: 10px;">
                        <i class="fas fa-save"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Navbar Scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });

        // Modal Logic
        const modal = document.getElementById('settingsModal');
        const openBtn = document.getElementById('openSettingsBtn');
        const closeBtn = document.getElementById('closeSettingsBtn');
        openBtn.addEventListener('click', () => { modal.classList.add('active'); document.body.style.overflow = 'hidden'; });
        closeBtn.addEventListener('click', () => { modal.classList.remove('active'); document.body.style.overflow = ''; });
        modal.addEventListener('click', (e) => { if (e.target === modal) { modal.classList.remove('active'); document.body.style.overflow = ''; } });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('active')) { modal.classList.remove('active'); document.body.style.overflow = ''; } });

        // Drag & Drop Reorder
        const chapterList = document.querySelector('.premium-main');
        let dragSrc = null;

        function getDraggableItems() {
            return [...chapterList.querySelectorAll('.chapter-premium-item[draggable="true"]')];
        }

        chapterList.addEventListener('dragstart', (e) => {
            const item = e.target.closest('.chapter-premium-item[draggable]');
            if (!item) return;
            dragSrc = item;
            // Small delay so browser snapshot is taken before we add class
            requestAnimationFrame(() => item.classList.add('dragging'));
        });

        chapterList.addEventListener('dragend', (e) => {
            const item = e.target.closest('.chapter-premium-item[draggable]');
            if (!item) return;
            item.classList.remove('dragging');
            getDraggableItems().forEach(el => el.classList.remove('drag-over'));
            dragSrc = null;
        });

        chapterList.addEventListener('dragover', (e) => {
            e.preventDefault();
            const target = e.target.closest('.chapter-premium-item[draggable]');
            if (!target || target === dragSrc) return;
            getDraggableItems().forEach(el => el.classList.remove('drag-over'));
            target.classList.add('drag-over');
        });

        chapterList.addEventListener('dragleave', (e) => {
            const target = e.target.closest('.chapter-premium-item[draggable]');
            if (target) target.classList.remove('drag-over');
        });

        chapterList.addEventListener('drop', (e) => {
            e.preventDefault();
            const target = e.target.closest('.chapter-premium-item[draggable]');
            if (!target || !dragSrc || target === dragSrc) return;

            const items = getDraggableItems();
            const srcIdx = items.indexOf(dragSrc);
            const tgtIdx = items.indexOf(target);

            // Insert before or after based on position
            if (srcIdx < tgtIdx) {
                target.after(dragSrc);
            } else {
                target.before(dragSrc);
            }

            target.classList.remove('drag-over');

            // Brief highlight to confirm drop
            dragSrc.style.transition = 'box-shadow 0.3s ease';
            dragSrc.style.boxShadow = '0 0 0 3px rgba(212, 166, 74, 0.4)';
            setTimeout(() => { dragSrc.style.boxShadow = ''; }, 600);
        });

        // ---------------------------------------------------
        // Sort Dropdown & Logic
        // ---------------------------------------------------
        const sortToggleBtn = document.getElementById('sortToggleBtn');
        const sortDropdown = document.getElementById('sortDropdown');
        const sortLabel = document.getElementById('sortLabel');
        const sortOptions = document.querySelectorAll('.sort-option');

        // Toggle dropdown
        sortToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sortDropdown.classList.toggle('open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!sortToggleBtn.contains(e.target) && !sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('open');
            }
        });

        // Handle sort options
        sortOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const sortType = option.getAttribute('data-sort');

                // Update UI
                sortOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                sortDropdown.classList.remove('open');

                // Update label based on selection
                if (sortType === 'manual') sortLabel.textContent = 'ترتيب يدوي';
                else if (sortType.startsWith('title')) sortLabel.textContent = 'ترتيب أبجدي';
                else if (sortType.startsWith('date')) sortLabel.textContent = 'ترتيب بالزمن';
                else if (sortType.startsWith('words')) sortLabel.textContent = 'ترتيب بالكلمات';

                // Perform sorting
                sortChapters(sortType);
            });
        });

        function sortChapters(sortType) {
            // Get all items except the "Add New" button and list header
            const container = document.querySelector('.premium-main');
            const items = Array.from(container.querySelectorAll('.chapter-premium-item'));

            // Re-enable or disable drag based on mode
            const isManual = sortType === 'manual';
            items.forEach(item => {
                item.setAttribute('draggable', isManual);
                const handle = item.querySelector('.drag-handle');
                if (handle) {
                    handle.style.opacity = isManual ? '1' : '0.3';
                    handle.style.cursor = isManual ? 'grab' : 'not-allowed';
                }
            });

            if (isManual) {
                // We cannot "restore" manual order easily without saving it, 
                // but usually switching to manual just leaves them where they are 
                // and allows dragging again.
                return;
            }

            // Perform actual sort
            items.sort((a, b) => {
                // Drafts always at the bottom? Optional. For now we sort everything.
                if (sortType === 'title-asc') {
                    return a.dataset.title.localeCompare(b.dataset.title, 'ar');
                } else if (sortType === 'title-desc') {
                    return b.dataset.title.localeCompare(a.dataset.title, 'ar');
                } else if (sortType === 'date-asc') {
                    return new Date(a.dataset.date) - new Date(b.dataset.date);
                } else if (sortType === 'date-desc') {
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
                } else if (sortType === 'words-asc') {
                    return parseInt(a.dataset.words) - parseInt(b.dataset.words);
                } else if (sortType === 'words-desc') {
                    return parseInt(b.dataset.words) - parseInt(a.dataset.words);
                }
                return 0;
            });

            // Re-append in new order (DOM automatically moves them)
            items.forEach(item => container.appendChild(item));
        }
    </script>
</body>

</html>
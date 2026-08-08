<?php
// BrowseBooks.php — Premium Digital Library
// سرد (Sard) — Arabic Reading Platform

require_once __DIR__ . "/../core/init.php";

// ============================================================
// GET SEARCH QUERY
// ============================================================
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : 'الكل';

// ============================================================
// FETCH BOOKS FROM DATABASE WITH SEARCH
// ============================================================
try {
    $sql = "
        SELECT 
            n.id,
            n.title,
            n.slug,
            n.cover_image,
            n.spine_image,
            n.description,
            n.publish_year,
            n.pages,
            n.rating,
            n.is_featured,
            n.status,
            a.name AS author_name,
            a.bio AS author_bio,
            a.photo AS author_photo,
            c.name_ar AS category_name,
            c.name_en AS category_name_en,
            c.id AS category_id,
            p.name AS publisher_name
        FROM novels n
        JOIN authors a ON n.author_id = a.id
        JOIN categories c ON n.category_id = c.id
        LEFT JOIN publishers p ON n.publisher_id = p.id
        WHERE n.status = 'published'
    ";

    $params = [];

    // Apply search filter
    if (!empty($searchQuery)) {
        $sql .= " AND (n.title LIKE :search 
                  OR a.name LIKE :search 
                  OR n.description LIKE :search
                  OR n.keywords LIKE :search)";
        $params[':search'] = '%' . $searchQuery . '%';
    }

    // Apply category filter
    if ($categoryFilter !== 'الكل') {
        $sql .= " AND c.name_ar = :category";
        $params[':category'] = $categoryFilter;
    }

    // No LIMIT - show ALL books (7 per category)
    $sql .= " ORDER BY c.id, n.id";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $books = [];
    error_log("Database Error in BrowseBooks.php: " . $e->getMessage());
}

// ============================================================
// GROUP BOOKS BY CATEGORY
// ============================================================
$shelves = [];
$categoryMap = [
    1 => 'الروايات التاريخية',
    2 => 'روايات الغموض والإثارة',
    3 => 'روايات الفانتازيا والخيال'
];

foreach ($books as $book) {
    $categoryId = $book['category_id'];
    $categoryName = $categoryMap[$categoryId] ?? $book['category_name'];

    if (!isset($shelves[$categoryName])) {
        $shelves[$categoryName] = [];
    }
    $shelves[$categoryName][] = $book;
}

// ============================================================
// CATEGORIES FOR FILTERS (from database)
// ============================================================
try {
    $catQuery = "SELECT id, name_ar, name_en FROM categories ORDER BY id";
    $catStmt = $conn->prepare($catQuery);
    $catStmt->execute();
    $dbCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbCategories = [];
}

$categories = ['الكل'];
$categoryLabels = ['الكل' => 'جميع الكتب'];

foreach ($dbCategories as $cat) {
    $categories[] = $cat['name_ar'];
    $categoryLabels[$cat['name_ar']] = $cat['name_ar'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المكتبة — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <li><a href="<?= ROOT ?>Browsebooks" class="active">المكتبة</a></li>
                <?php if(isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><li><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a></li><?php else: ?><li><a href="<?= ROOT ?>writer_application">كن كاتبا</a></li><?php endif; ?>
            </ul>
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

    <!-- ============================================================
    SEARCH SECTION
    ============================================================ -->
    <section class="search-premium-section">
        <div class="search-premium-container">
            <div class="search-premium-wrapper">
                <i class="fas fa-search search-premium-icon"></i>
                <form method="GET" action="" style="width: 100%; display: flex; align-items: center;">
                    <input type="text"
                        name="search"
                        class="search-premium-input"
                        id="searchInput"
                        placeholder="ابحث عن كتاب، مؤلف، أو تصنيف..."
                        autocomplete="off"
                        value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <?php if (!empty($searchQuery)): ?>
                        <a href="<?= ROOT ?>Browsebooks" class="search-premium-clear" style="text-decoration: none;">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- ============================================================
    CATEGORY FILTERS — Horizontal Chips
    ============================================================ -->
    <section class="category-premium-section">
        <div class="category-premium-container">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= ROOT ?>Browsebooks<?php
                                                $params = [];
                                                if ($searchQuery) $params['search'] = $searchQuery;
                                                if ($cat !== 'الكل') $params['category'] = $cat;
                                                echo !empty($params) ? '?' . http_build_query($params) : '';
                                                ?>"
                    class="category-premium-chip <?php echo $cat === $categoryFilter ? 'active' : ''; ?>"
                    style="text-decoration: none; cursor: pointer;">
                    <?php echo $categoryLabels[$cat]; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================
    RESULTS COUNT
    ============================================================ -->
    <div style="text-align: center; padding: 10px 20px; color: #8a7a6a; font-size: 14px;">
        <?php if (!empty($searchQuery) || $categoryFilter !== 'الكل'): ?>
            عرض <?php echo count($books); ?> كتاب
            <?php if (!empty($searchQuery)): ?>
                عن "<?php echo htmlspecialchars($searchQuery); ?>"
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- ============================================================
BOOKSHELVES — Full Width Redesign
============================================================ -->
    <main class="library-premium-hall">

        <?php if (empty($books)): ?>
            <!-- Empty State -->
            <div class="empty-premium-state" id="emptyState" style="display: flex !important;">
                <div class="empty-premium-icon"><i class="fas fa-book-open"></i></div>
                <h3 class="empty-premium-title">لم يتم العثور على كتب</h3>
                <p class="empty-premium-text">حاول تغيير كلمات البحث أو التصنيف</p>
                <a href="<?= ROOT ?>Browsebooks" class="info-cta" style="margin-top: 20px; display: inline-block; padding: 12px 30px; background: #8B7355; color: white; border-radius: 8px; text-decoration: none;">
                    عرض جميع الكتب
                </a>
            </div>
        <?php else: ?>

            <?php foreach ($shelves as $shelfName => $shelfBooks):
                // Determine category key for filtering
                $categoryKey = '';
                if (strpos($shelfName, 'التاريخية') !== false) $categoryKey = 'رواية تاريخية';
                elseif (strpos($shelfName, 'الغموض') !== false) $categoryKey = 'رواية غموض';
                elseif (strpos($shelfName, 'الفانتازيا') !== false) $categoryKey = 'رواية فانتازيا';
            ?>
                <section class="shelf-premium-section" data-category="<?php echo $categoryKey; ?>">
                    <div class="shelf-premium-header">
                        <h2 class="shelf-premium-title"><?php echo $shelfName; ?></h2>
                        <span class="shelf-premium-line"></span>
                    </div>
                    <div class="shelf-premium-wrapper">
                        <div class="shelf-premium-wood">
                            <div class="shelf-premium-books">
                                <?php
                                $heights = [175, 185, 165, 190, 180, 170];
                                $rotations = [-1, 0, 2, -0.5, 1.5, -2];
                                $i = 0;
                                foreach ($shelfBooks as $book):
                                    // Build cover image path
                                    $coverPath = !empty($book['cover_image'])
                                        // ? ROOT . 'assets/images/novels/' . $book['cover_image'] 
                                        ? ROOT . 'assets/images/' . $book['cover_image']
                                        : ROOT . 'assets/images/sarrdd Logo.png';

                                    $h = $heights[$i % count($heights)] + rand(-3, 3);
                                    $r = $rotations[$i % count($rotations)] + (rand(-15, 15) / 100);
                                    $i++;
                                ?>
                                    <div class="book-premium-stand"
                                        data-category="<?php echo $book['category_name']; ?>"
                                        style="--book-height: <?php echo $h; ?>px; --book-rotation: <?php echo $r; ?>deg;"
                                        onclick="location.href='BookDetails?id=<?php echo $book['id']; ?>'"
                                        title="<?php echo htmlspecialchars($book['title']); ?>">
                                        <div class="book-premium-3d">
                                            <div class="book-premium-cover">
                                                <?php if (!empty($coverPath)): ?>
                                                    <img src="<?php echo htmlspecialchars($coverPath); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-premium-img" loading="lazy">
                                                <?php else: ?>
                                                    <div class="book-premium-placeholder">
                                                        <i class="fas fa-book"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="book-premium-spine"></div>
                                                <div class="book-premium-glow"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="shelf-premium-board">
                                <div class="shelf-premium-grain"></div>
                                <div class="shelf-premium-edge"></div>
                            </div>
                            <div class="shelf-premium-shadow"></div>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>

        <?php endif; ?>

    </main>

    <!-- ============================================================
    FOOTER — Premium with Curve
    ============================================================ -->
    <footer class="footer-premium">
        <div class="footer-premium-curve"></div>
        <div class="footer-premium-content">
            <div class="footer-premium-brand">
                <span class="footer-premium-logo">سرد</span>
                <p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p>
            </div>
            <div class="footer-premium-links">
                <div class="footer-premium-col">
                    <h4>روابط سريعة</h4><a href="<?= ROOT ?>index">الرئيسية</a><a href="<?= ROOT ?>Browsebooks">المكتبة</a><a href="#">الكتّاب</a><?php if(isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a><?php else: ?><a href="<?= ROOT ?>writer_application">كن كاتبا</a><?php endif; ?>
                </div>
                <div class="footer-premium-col">
                    <h4>حسابك</h4><a href="<?= ROOT ?>signup">تسجيل الدخول</a><a href="<?= ROOT ?>signup">إنشاء حساب</a>
                </div>
                <div class="footer-premium-col">
                    <h4>تواصل معنا</h4><a href="#">الدعم الفني</a><a href="#">الأسئلة الشائعة</a><a href="#">سياسة الخصوصية</a>
                </div>
            </div>
        </div>
        <div class="footer-premium-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <script src="<?= ROOT ?>assets/js/Browsebooks.js"></script>

</body>

</html>
<?php
// BrowseBooks.php — Premium Digital Library
// سرد (Sard) — Arabic Reading Platform

// ============================================================
// EXACTLY 18 BOOKS — 6 per category — ALL PATHS CORRECT ✅
// ============================================================

$books = [
    // ============================================================
    // 1. الروايات التاريخية (Historical Novels) — 6 books
    // ============================================================
    ["id" => 1, "title" => "أولاد الناس (ثلاثية المماليك)", "author" => "رضوى عاشور", "category" => "رواية تاريخية", "cover" => "../images/غلاف اولاد الناس.jpg"],
    ["id" => 2, "title" => "عزازيل", "author" => "يوسف زيدان", "category" => "رواية تاريخية", "cover" => "../images/غلاف عزازيل.png"],
    ["id" => 3, "title" => "واحة الغروب", "author" => "بهاء طاهر", "category" => "رواية تاريخية", "cover" => "../images/غلاف واحة الغراب.png"],
    ["id" => 4, "title" => "قمر على سمرقند", "author" => "أمين معلوف", "category" => "رواية تاريخية", "cover" => "../images/غلاف قمر على سمرقند.png"],
    ["id" => 5, "title" => "ثلاثية غرناطة", "author" => "رضوى عاشور", "category" => "رواية تاريخية", "cover" => "../images/غلاف ثلاثية غرناطة.png"],
    ["id" => 6, "title" => "الحب في المنفى", "author" => "بهاء طاهر", "category" => "رواية تاريخية", "cover" => "../images/غلاف الحب في المنفي.png"],
    
    // ============================================================
    // 2. روايات الغموض والإثارة (Mystery & Thriller) — 6 books
    // ============================================================
    ["id" => 7, "title" => "الفيل الأزرق", "author" => "أحمد مراد", "category" => "رواية غموض", "cover" => "../images/غلاف الفيل الأزرق.png"],
    ["id" => 8, "title" => "تراب الماس", "author" => "أحمد مراد", "category" => "رواية غموض", "cover" => "../images/غلاف تراب الماس.png"],
    ["id" => 9, "title" => "موسم صيد الغزلان", "author" => "أحمد مراد", "category" => "رواية غموض", "cover" => "../images/غلاف موسم صيد الغزلان.png"],
    ["id" => 10, "title" => "لوكاندة بير الوطاوي", "author" => "مصطفى محمود", "category" => "رواية غموض", "cover" => "../images/غلاف لوكاندة بير الوطاوي.png"],
    ["id" => 11, "title" => "يوتوبيا", "author" => "أحمد خالد توفيق", "category" => "رواية غموض", "cover" => "../images/غلاف يوتوبيا.png"],
    ["id" => 12, "title" => "في ممر الفئران", "author" => "أحمد خالد توفيق", "category" => "رواية غموض", "cover" => "../images/غلاف في ممر الفئران.png"],
    
    // ============================================================
    // 3. روايات الفانتازيا والخيال (Fantasy) — 6 books
    // ============================================================
    ["id" => 13, "title" => "أرض زيكولا", "author" => "عمرو عبد الحميد", "category" => "رواية فانتازيا", "cover" => "../images/غلاف أرض زيكولا.png"],
    ["id" => 14, "title" => "أماريتا", "author" => "عمرو عبد الحميد", "category" => "رواية فانتازيا", "cover" => "../images/غلاف أماريتا.png"],
    ["id" => 15, "title" => "وادي الذئاب المنسية", "author" => "أحمد خالد توفيق", "category" => "رواية فانتازيا", "cover" => "../images/غلاف وادي الذئاب المنسية.png"],
    ["id" => 16, "title" => "قواعد جارتين", "author" => "أحمد خالد توفيق", "category" => "رواية فانتازيا", "cover" => "../images/غلاف قواعد جارتين.png"],
    ["id" => 17, "title" => "دقات الشامو", "author" => "أحمد خالد توفيق", "category" => "رواية فانتازيا", "cover" => "../images/غلاف دقات الشامو.png"],
    ["id" => 18, "title" => "أمواج أكما", "author" => "نورا ناجي", "category" => "رواية فانتازيا", "cover" => "../images/غلاف أمواج أكما.png"],
];

// ============================================================
// GROUP BOOKS BY CATEGORY
// ============================================================
$shelves = [
    "الروايات التاريخية" => array_slice($books, 0, 6),
    "روايات الغموض والإثارة" => array_slice($books, 6, 6),
    "روايات الفانتازيا والخيال" => array_slice($books, 12, 6),
];

// ============================================================
// CATEGORIES FOR FILTERS
// ============================================================
$categories = ['الكل', 'رواية تاريخية', 'رواية غموض', 'رواية فانتازيا'];
$categoryLabels = [
    'الكل' => 'جميع الكتب',
    'رواية تاريخية' => 'الروايات التاريخية',
    'رواية غموض' => 'روايات الغموض والإثارة',
    'رواية فانتازيا' => 'روايات الفانتازيا والخيال'
];
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
    <link rel="stylesheet" href="../Style/BrowseBooks.css">
</head>
<body>

    <!-- ============================================================
    NAVBAR — Premium Floating
    ============================================================ -->
    <nav class="navbar-premium" id="navbar">
        <div class="navbar-premium-container">
            <div class="navbar-premium-brand">
                <a href="HomePage.php" class="brand-premium-link">
                    <img src="../images/sarrdd Logo.png" alt="سرد logo" class="brand-premium-logo">
                    <span class="brand-premium-name">سرد</span>
                </a>
            </div>
            <ul class="nav-premium-links">
                <li><a href="HomePage.php">الرئيسية</a></li>
                <li><a href="BrowseBooks.php" class="active">المكتبة</a></li>
                <li><a href="#">الكتّاب</a></li>
                <li><a href="#">من نحن</a></li>
            </ul>
            <div class="nav-premium-actions">
                <a href="signup.php" class="nav-premium-btn nav-premium-btn-outline">تسجيل الدخول</a>
                <a href="signup.php" class="nav-premium-btn nav-premium-btn-filled">إنشاء حساب</a>
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
                <input type="text" class="search-premium-input" id="searchInput" placeholder="ابحث عن كتاب، مؤلف، أو تصنيف..." autocomplete="off">
                <button class="search-premium-clear" id="searchClear"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </section>

    <!-- ============================================================
    CATEGORY FILTERS — Horizontal Chips
    ============================================================ -->
    <section class="category-premium-section">
        <div class="category-premium-container">
            <?php foreach ($categories as $cat): ?>
                <button class="category-premium-chip <?php echo $cat === 'الكل' ? 'active' : ''; ?>" data-category="<?php echo $cat; ?>">
                    <?php echo $categoryLabels[$cat]; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================
    BOOKSHELVES — Only One Visible at a Time
    ============================================================ -->
    <main class="library-premium-hall">
        
        <?php foreach ($shelves as $shelfName => $shelfBooks): 
            $categoryKey = '';
            if ($shelfName === 'الروايات التاريخية') $categoryKey = 'رواية تاريخية';
            elseif ($shelfName === 'روايات الغموض والإثارة') $categoryKey = 'رواية غموض';
            elseif ($shelfName === 'روايات الفانتازيا والخيال') $categoryKey = 'رواية فانتازيا';
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
                                $h = $heights[$i % count($heights)] + rand(-3, 3);
                                $r = $rotations[$i % count($rotations)] + (rand(-15, 15) / 100);
                                $i++;
                            ?>
                                <div class="book-premium-stand" 
                                     data-category="<?php echo $book['category']; ?>"
                                     style="--book-height: <?php echo $h; ?>px; --book-rotation: <?php echo $r; ?>deg;"
                                     onclick="location.href='reading.php?id=<?php echo $book['id']; ?>&title=<?php echo urlencode($book['title']); ?>&author=<?php echo urlencode($book['author']); ?>&cover=<?php echo urlencode($book['cover']); ?>'"
                                     title="<?php echo htmlspecialchars($book['title']); ?>">
                                    <div class="book-premium-3d">
                                        <div class="book-premium-cover">
                                            <?php if (!empty($book['cover'])): ?>
                                                <img src="<?php echo htmlspecialchars($book['cover']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-premium-img" loading="lazy">
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

        <!-- Empty State (hidden by default) -->
        <div class="empty-premium-state" id="emptyState">
            <div class="empty-premium-icon"><i class="fas fa-book-open"></i></div>
            <h3 class="empty-premium-title">لم يتم العثور على كتب</h3>
            <p class="empty-premium-text">حاول تغيير كلمات البحث أو التصنيف</p>
        </div>

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
                <div class="footer-premium-col"><h4>روابط سريعة</h4><a href="HomePage.php">الرئيسية</a><a href="BrowseBooks.php">المكتبة</a><a href="#">الكتّاب</a><a href="#">من نحن</a></div>
                <div class="footer-premium-col"><h4>حسابك</h4><a href="signup.php">تسجيل الدخول</a><a href="signup.php">إنشاء حساب</a></div>
                <div class="footer-premium-col"><h4>تواصل معنا</h4><a href="#">الدعم الفني</a><a href="#">الأسئلة الشائعة</a><a href="#">سياسة الخصوصية</a></div>
            </div>
        </div>
        <div class="footer-premium-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <script src="../Script/BrowseBooks.js"></script>

</body>
</html>
<?php
// BrowseBooks.php
// Full catalog page with sidebar filters, sorting, and a grid/list view toggle.
// Search + filter + sort all run client-side against the cards rendered here.

require_once "../app/core/init.php";

$books = [
    ["id" => 1, "title" => "ظل النخيل", "author_name" => "سلمى عادل", "category" => "رومانسي", "excerpt" => "رواية عن الشوق والعودة للجذور", "cover" => "assets/images/covers/book-1.jpg"],
    ["id" => 2, "title" => "حكاية قلب", "author_name" => "نورا أحمد", "category" => "رومانسي", "excerpt" => "رحلة بحث عن الحب في زمن المتغيرات", "cover" => "assets/images/covers/book-2.jpg"],
    ["id" => 3, "title" => "نسمات الحب", "author_name" => "ليلى سعيد", "category" => "رومانسي", "excerpt" => "قصة حب تتحدى الزمن والمكان", "cover" => "assets/images/covers/book-3.jpg"],

    ["id" => 4, "title" => "العابرون", "author_name" => "هالة نبيل", "category" => "خيال علمي", "excerpt" => "رحلة عبر الأبعاد المختلفة", "cover" => "assets/images/covers/book-4.jpg"],
    ["id" => 5, "title" => "كوكب آخر", "author_name" => "سامي رضا", "category" => "خيال علمي", "excerpt" => "استكشاف حياة جديدة في الفضاء", "cover" => "assets/images/covers/book-5.jpg"],
    ["id" => 6, "title" => "آلة الزمن", "author_name" => "كريم أشرف", "category" => "خيال علمي", "excerpt" => "مغامرات عبر الزمن", "cover" => "assets/images/covers/book-6.jpg"],

    ["id" => 7, "title" => "مرايا الصمت", "author_name" => "يوسف كامل", "category" => "غموض", "excerpt" => "لغز ينتظر من يحله", "cover" => "assets/images/covers/book-7.jpg"],
    ["id" => 8, "title" => "اللغز", "author_name" => "مازن رضا", "category" => "غموض", "excerpt" => "قصة غامضة تأخذك إلى عالم آخر", "cover" => "assets/images/covers/book-8.jpg"],
    ["id" => 9, "title" => "الظل", "author_name" => "طارق منير", "category" => "غموض", "excerpt" => "في الظل تكمن الحقيقة", "cover" => "assets/images/covers/book-9.jpg"],
];

// fg colors match the genre colors already used on HomePage for consistency
$genres = [
    ["label" => "رومانسي", "fg" => "#B34141", "bg" => "#FDF2F2"],
    ["label" => "خيال علمي", "fg" => "#1A6EB5", "bg" => "#F0F7FF"],
    ["label" => "غموض", "fg" => "#6B4C3A", "bg" => "#F5F0EB"],
];

$genreCounts = [];
foreach ($books as $b) {
    $genreCounts[$b['category']] = ($genreCounts[$b['category']] ?? 0) + 1;
}

$activeCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تصفح الروايات — ريشة</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT ?>assets/css/BrowseBooks.css">
</head>
<body>

<header class="site-header">
    <div class="logo" onclick="location.href='index'" style="cursor:pointer;">
        <span class="logo-mark">✒</span>
        <span class="logo-text">ريشة</span>
    </div>
    <div class="header-actions">
        <button class="btn btn-outline" onclick="location.href='Login'">دخول</button>
        <button class="btn btn-filled" onclick="location.href='Signup'">حساب جديد</button>
    </div>
</header>

<!-- Colored header band instead of a plain white title -->
<section class="browse-hero">
    <p class="browse-hero-title">تصفح الروايات</p>
    <p class="browse-hero-sub">كل الروايات المنشورة على ريشة في مكان واحد</p>
    <div class="browse-hero-search">
        <input type="text" id="searchInput" class="search-input" placeholder="ابحث بالعنوان أو اسم الكاتب...">
    </div>
</section>

<main class="browse-layout">

    <!-- Sidebar filters -->
    <aside class="filters-sidebar">
        <div class="filter-block">
            <p class="filter-block-title">التصنيف</p>
            <div class="category-list" id="categoryList">
                <button class="category-item <?php echo $activeCategory === 'all' ? 'category-active' : ''; ?>" data-category="all">
                    <span>كل الروايات</span>
                    <span class="category-item-count"><?php echo count($books); ?></span>
                </button>
                <?php foreach ($genres as $g): ?>
                    <button class="category-item <?php echo $activeCategory === $g['label'] ? 'category-active' : ''; ?>"
                            data-category="<?php echo htmlspecialchars($g['label']); ?>"
                            style="--cat-color: <?php echo $g['fg']; ?>;">
                        <span><?php echo htmlspecialchars($g['label']); ?></span>
                        <span class="category-item-count"><?php echo $genreCounts[$g['label']] ?? 0; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-block">
            <p class="filter-block-title">الترتيب</p>
            <select id="sortSelect" class="sort-select">
                <option value="default">الأحدث</option>
                <option value="title-asc">الاسم: أ - ي</option>
                <option value="title-desc">الاسم: ي - أ</option>
            </select>
        </div>
    </aside>

    <!-- Results -->
    <section class="results-area">
        <div class="results-header">
            <p class="results-count" id="resultsCount"></p>
            <div class="view-toggle">
                <button class="view-btn view-active" id="gridViewBtn" aria-label="عرض شبكي">⬛⬛</button>
                <button class="view-btn" id="listViewBtn" aria-label="عرض قائمة">☰</button>
            </div>
        </div>

        <div class="book-grid" id="bookGrid">
            <?php foreach ($books as $book):
                $genreColor = '#1D9E75';
                $genreBg = '#E1F5EE';
                foreach ($genres as $g) {
                    if ($g['label'] === $book['category']) { $genreColor = $g['fg']; $genreBg = $g['bg']; break; }
                }
            ?>
                <article class="book-card" data-category="<?php echo htmlspecialchars($book['category']); ?>"
                          data-title="<?php echo htmlspecialchars(mb_strtolower($book['title'])); ?>"
                          data-title-raw="<?php echo htmlspecialchars($book['title']); ?>"
                          data-author="<?php echo htmlspecialchars(mb_strtolower($book['author_name'])); ?>"
                          onclick="location.href='BookDetails?id=<?php echo (int)$book['id']; ?>'">
                    <div class="book-cover" style="background: <?php echo $genreBg; ?>;">
                        <?php if (!empty($book['cover']) && file_exists($book['cover'])): ?>
                            <img src="<?php echo htmlspecialchars($book['cover']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover-img">
                        <?php else: ?>
                            <span class="cover-placeholder" style="color: <?php echo $genreColor; ?>;">📖</span>
                        <?php endif; ?>
                        <span class="category-badge" style="color: <?php echo $genreColor; ?>;"><?php echo htmlspecialchars($book['category']); ?></span>
                        <div class="cover-overlay">
                            <button class="read-btn">اقرأ الآن</button>
                        </div>
                    </div>
                    <p class="book-title"><?php echo htmlspecialchars($book['title']); ?></p>
                    <p class="book-author"><?php echo htmlspecialchars($book['author_name']); ?></p>
                    <p class="book-excerpt"><?php echo htmlspecialchars($book['excerpt']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="empty-state" id="emptyState" style="display:none;">
            <p class="empty-icon">🔍</p>
            <p>مفيش روايات مطابقة لبحثك</p>
        </div>
    </section>

</main>

<footer class="site-footer">
    <div class="footer-links">
        <a href="#">عن ريشة</a>
        <a href="WriteWithUs">اكتب معنا</a>
        <a href="#">سياسة الخصوصية</a>
        <a href="#">شروط الاستخدام</a>
        <a href="#">اتصل بنا</a>
    </div>
    <p>© <?php echo date("Y"); ?> ريشة — منصة كتابة وقراءة الروايات العربية</p>
</footer>

<script>
    const initialCategory = <?php echo json_encode($activeCategory); ?>;
</script>
<script src="assets/js/BrowseBooks.js"></script>
</body>
</html>



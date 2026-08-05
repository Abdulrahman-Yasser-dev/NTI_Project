<?php
// BookDetails.php — Premium Book Details Page
// سرد (Sard) — Arabic Reading Platform

require_once __DIR__ . "/../core/init.php";

// Get book ID from URL
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// ============================================================
// HANDLE RATING SUBMISSION
// ============================================================
$ratingSuccess = '';
$ratingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    $bookId = (int)($_POST['book_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($userId <= 0) {
        $ratingError = 'يجب تسجيل الدخول أولاً.';
    } elseif ($rating < 1 || $rating > 5) {
        $ratingError = 'التقييم يجب أن يكون بين 1 و 5.';
    } elseif ($bookId <= 0) {
        $ratingError = 'معرف الكتاب غير صحيح.';
    } else {
        try {
            // Check if user already rated this book
            $checkQuery = "SELECT id FROM book_ratings WHERE user_id = :user_id AND book_id = :book_id";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update existing rating
                $updateQuery = "UPDATE book_ratings SET rating = :rating, updated_at = NOW() WHERE id = :id";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([':rating' => $rating, ':id' => $existing['id']]);
            } else {
                // Insert new rating
                $insertQuery = "INSERT INTO book_ratings (book_id, user_id, rating) VALUES (:book_id, :user_id, :rating)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([
                    ':book_id' => $bookId,
                    ':user_id' => $userId,
                    ':rating' => $rating
                ]);
            }
            
            // Update average rating in novels table
            $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM book_ratings WHERE book_id = :book_id";
            $avgStmt = $conn->prepare($avgQuery);
            $avgStmt->execute([':book_id' => $bookId]);
            $avgData = $avgStmt->fetch(PDO::FETCH_ASSOC);
            
            $newAvg = round($avgData['avg_rating'] ?? 0, 1);
            $totalRatings = $avgData['total_ratings'] ?? 0;
            
            // Update novels table
            $updateNovelQuery = "UPDATE novels SET rating = :rating WHERE id = :book_id";
            $updateNovelStmt = $conn->prepare($updateNovelQuery);
            $updateNovelStmt->execute([':rating' => $newAvg, ':book_id' => $bookId]);
            
            // Redirect to refresh the page and show the new rating
            header('Location: ' . ROOT . 'BookDetails?id=' . $bookId . '&rating=success');
            exit;
            
        } catch (PDOException $e) {
            $ratingError = 'حدث خطأ في قاعدة البيانات. حاول مرة أخرى.';
            error_log("Rating Error: " . $e->getMessage());
        }
    }
}

// Show success message if rating was added
if (isset($_GET['rating']) && $_GET['rating'] === 'success') {
    $ratingSuccess = '✓ تم تحديث تقييمك بنجاح!';
}

// ============================================================
// HANDLE COMMENT SUBMISSION
// ============================================================
$commentSuccess = '';
$commentError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $commentText = trim($_POST['comment_text'] ?? '');
    $commentRating = (int)($_POST['comment_rating'] ?? 0);
    $userId = $_SESSION['user_id'] ?? 0;
    $bookId = (int)($_POST['book_id'] ?? $bookId);
    
    if ($userId <= 0) {
        $commentError = 'يجب تسجيل الدخول أولاً.';
    } elseif (empty($commentText)) {
        $commentError = 'الرجاء كتابة تعليقك.';
    } elseif ($bookId <= 0) {
        $commentError = 'معرف الكتاب غير صحيح.';
    } else {
        try {
            $insertCommentQuery = "
                INSERT INTO comments (book_id, user_id, comment, rating, created_at) 
                VALUES (:book_id, :user_id, :comment, :rating, NOW())
            ";
            $insertCommentStmt = $conn->prepare($insertCommentQuery);
            $insertCommentStmt->execute([
                ':book_id' => $bookId,
                ':user_id' => $userId,
                ':comment' => $commentText,
                ':rating' => $commentRating
            ]);
            
            // Redirect to refresh the page and show the new comment
            header('Location: ' . ROOT . 'BookDetails?id=' . $bookId . '&comment=success');
            exit;
            
        } catch (PDOException $e) {
            $commentError = 'حدث خطأ في إضافة التعليق. حاول مرة أخرى.';
            error_log("Comment Error: " . $e->getMessage());
        }
    }
}

// Show success message if comment was added
if (isset($_GET['comment']) && $_GET['comment'] === 'success') {
    $commentSuccess = '✓ تم إضافة تعليقك بنجاح!';
}

// ============================================================
// FETCH BOOK FROM DATABASE
// ============================================================
try {
    $query = "
        SELECT 
            n.id,
            n.title,
            n.slug,
            n.description,
            n.cover_image,
            n.spine_image,
            n.banner_image,
            n.publish_year,
            n.pages,
            n.language,
            n.country,
            n.rating,
            n.keywords,
            n.is_featured,
            n.status,
            n.views,
            a.id AS author_id,
            a.name AS author_name,
            a.bio AS author_bio,
            a.photo AS author_photo,
            c.id AS category_id,
            c.name_ar AS category_name,
            c.name_en AS category_name_en,
            p.id AS publisher_id,
            p.name AS publisher_name,
            p.country AS publisher_country
        FROM novels n
        JOIN authors a ON n.author_id = a.id
        JOIN categories c ON n.category_id = c.id
        LEFT JOIN publishers p ON n.publisher_id = p.id
        WHERE n.id = :book_id AND n.status = 'published'
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':book_id' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $book = null;
    error_log("Database Error in BookDetails.php: " . $e->getMessage());
}

// If book not found, redirect to BrowseBooks
if (!$book) {
    header('Location: ' . ROOT . 'Browsebooks');
    exit;
}

// ============================================================
// GET USER'S EXISTING RATING
// ============================================================
$userRating = 0;
$isLoggedIn = isset($_SESSION['user_id']);
$currentUserId = $_SESSION['user_id'] ?? 0;

if ($isLoggedIn) {
    try {
        $userRatingQuery = "SELECT rating FROM book_ratings WHERE user_id = :user_id AND book_id = :book_id";
        $userRatingStmt = $conn->prepare($userRatingQuery);
        $userRatingStmt->execute([':user_id' => $currentUserId, ':book_id' => $bookId]);
        $userRatingData = $userRatingStmt->fetch(PDO::FETCH_ASSOC);
        $userRating = $userRatingData['rating'] ?? 0;
    } catch (PDOException $e) {
        $userRating = 0;
    }
}

// ============================================================
// GET TOTAL RATINGS COUNT
// ============================================================
try {
    $countQuery = "SELECT COUNT(*) as total FROM book_ratings WHERE book_id = :book_id";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute([':book_id' => $bookId]);
    $totalRatings = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} catch (PDOException $e) {
    $totalRatings = 0;
}

// ============================================================
// FETCH CHAPTERS
// ============================================================
try {
    $chapterQuery = "
        SELECT 
            id,
            chapter_number,
            title,
            content,
            word_count,
            reading_time
        FROM chapters
        WHERE novel_id = :book_id
        ORDER BY chapter_number ASC
    ";
    
    $chapterStmt = $conn->prepare($chapterQuery);
    $chapterStmt->execute([':book_id' => $bookId]);
    $chapters = $chapterStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $chapters = [];
}

// ============================================================
// FETCH COMMENTS
// ============================================================
try {
    $commentQuery = "
        SELECT 
            c.id,
            c.comment,
            c.created_at,
            c.rating,
            u.name AS user_name,
            u.photo AS user_photo
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.book_id = :book_id
        AND c.status = 'approved'
        ORDER BY c.created_at DESC
        LIMIT 10
    ";
    
    $commentStmt = $conn->prepare($commentQuery);
    $commentStmt->execute([':book_id' => $bookId]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $comments = [];
    error_log("Database Error fetching comments: " . $e->getMessage());
}

// ============================================================
// FETCH RELATED BOOKS (Same category)
// ============================================================
try {
    $relatedQuery = "
        SELECT 
            n.id,
            n.title,
            n.slug,
            n.cover_image,
            n.rating,
            a.name AS author_name
        FROM novels n
        JOIN authors a ON n.author_id = a.id
        WHERE n.category_id = :category_id 
        AND n.id != :book_id 
        AND n.status = 'published'
        LIMIT 8
    ";
    
    $relatedStmt = $conn->prepare($relatedQuery);
    $relatedStmt->execute([
        ':category_id' => $book['category_id'],
        ':book_id' => $bookId
    ]);
    $relatedBooks = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $relatedBooks = [];
}

// ============================================================
// FETCH CATEGORIES FOR SIDEBAR
// ============================================================
try {
    $catQuery = "SELECT id, name_ar, name_en FROM categories ORDER BY name_ar";
    $catStmt = $conn->prepare($catQuery);
    $catStmt->execute();
    $sidebarCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sidebarCategories = [];
}

// ============================================================
// FETCH AUTHORS FOR SIDEBAR
// ============================================================
try {
    $authorQuery = "
        SELECT DISTINCT a.id, a.name, a.photo 
        FROM authors a
        JOIN novels n ON a.id = n.author_id
        WHERE n.status = 'published'
        ORDER BY a.name
        LIMIT 8
    ";
    $authorStmt = $conn->prepare($authorQuery);
    $authorStmt->execute();
    $sidebarAuthors = $authorStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sidebarAuthors = [];
}

// ============================================================
// BUILD COVER IMAGE PATH
// ============================================================
$coverPath = !empty($book['cover_image']) 
    ? ROOT . 'assets/images/' . $book['cover_image'] 
    : ROOT . 'assets/images/placeholder.jpg';

// ============================================================
// CALCULATE RATING
// ============================================================
$rating = round($book['rating'] ?? 0, 1);
$fullStars = floor($rating);
$hasHalfStar = ($rating - $fullStars) >= 0.5;

// ============================================================
// CHECK IF USER HAS STARTED READING
// ============================================================
$hasStarted = false; // Placeholder - will come from user data later

// ============================================================
// AUTHOR BOOK COUNT
// ============================================================
try {
    $countQuery = "SELECT COUNT(*) as total FROM novels WHERE author_id = :author_id AND status = 'published'";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute([':author_id' => $book['author_id']]);
    $authorBookCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} catch (PDOException $e) {
    $authorBookCount = 0;
}

// ============================================================
// PASS ROOT TO JAVASCRIPT
// ============================================================
echo '<script>const ROOT_URL = "' . ROOT . '";</script>';
echo '<script>const BOOK_ID = ' . $bookId . ';</script>';
echo '<script>const USER_RATING = ' . $userRating . ';</script>';
echo '<script>const IS_LOGGED_IN = ' . ($isLoggedIn ? 'true' : 'false') . ';</script>';

// ============================================================
// BUILD READING URL
// ============================================================
$readingUrl = ROOT . 'reading.php?book_id=' . $book['id'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($book['title']); ?> — سرد</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Noto+Naskh+Arabic:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- CSS -->
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/BookDetails.css" />
</head>
<body>

    <!-- ===== NAVBAR — Premium Floating (Same as BrowseBooks) ===== -->
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
                <li><a href="#">من نحن</a></li>
            </ul>
            <div class="nav-premium-actions">
                <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-outline">تسجيل الدخول</a>
                <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-filled">إنشاء حساب</a>
            </div>
        </div>
    </nav>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="container">

        <!-- ===== BREADCRUMB ===== -->
        <div class="breadcrumb-wrap">
            <a href="<?= ROOT ?>index">الرئيسية</a> <i class="fas fa-chevron-left"></i>
            <a href="<?= ROOT ?>Browsebooks">المكتبة</a> <i class="fas fa-chevron-left"></i>
            <a href="<?= ROOT ?>Browsebooks?category=<?= urlencode($book['category_name']) ?>"><?= htmlspecialchars($book['category_name']) ?></a> <i class="fas fa-chevron-left"></i>
            <span><?php echo htmlspecialchars($book['title']); ?></span>
        </div>

        <!-- ===== PAGE GRID: SIDEBAR + MAIN ===== -->
        <div class="page-grid">

            <!-- ===== SIDEBAR ===== -->
            <aside class="sidebar" id="sidebar">
                <!-- Categories -->
                <div class="card">
                    <h3><i class="fas fa-folder-open"></i> التصنيفات</h3>
                    <ul>
                        <?php foreach ($sidebarCategories as $cat): ?>
                            <li class="category-item <?php echo $cat['name_ar'] === $book['category_name'] ? 'active' : ''; ?>" 
                                onclick="location.href='<?= ROOT ?>Browsebooks?category=<?= urlencode($cat['name_ar']) ?>'">
                                <i class="fas fa-folder"></i> <?= htmlspecialchars($cat['name_ar']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="more-btn">المزيد من الأقسام</button>
                </div>

                <!-- Authors -->
                <div class="card">
                    <h3><i class="fas fa-user-edit"></i> المؤلفون</h3>
                    <div class="author-list">
                        <?php foreach ($sidebarAuthors as $author): ?>
                            <div class="author-item" onclick="location.href='<?= ROOT ?>Browsebooks?author=<?= urlencode($author['name']) ?>'">
                                <img src="<?= ROOT ?>assets/images/authors/<?= $author['photo'] ?? 'default-author.png' ?>" 
                                     alt="<?= htmlspecialchars($author['name']) ?>" 
                                     onerror="this.src='<?= ROOT ?>assets/images/avatar-placeholder.png'" />
                                <?= htmlspecialchars($author['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="more-btn">المزيد من المؤلفين</button>
                </div>
            </aside>

            <!-- ===== MAIN CONTENT ===== -->
            <div class="main-content">

                <!-- ===== BOOK DETAILS CARD ===== -->
                <div class="book-details">
                    <div class="book-cover">
                        <img src="<?php echo htmlspecialchars($coverPath); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>" 
                             onerror="this.src='<?= ROOT ?>assets/images/placeholder.jpg'" />
                    </div>
                    <div class="book-info">
                        <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                        <div class="meta-row">
                            <span><i class="fas fa-user"></i> المؤلف: <?php echo htmlspecialchars($book['author_name']); ?></span>
                            <span><i class="fas fa-book"></i> القسم: <?php echo htmlspecialchars($book['category_name']); ?></span>
                            <span><i class="fas fa-file-alt"></i> عدد الصفحات: <?php echo $book['pages'] ?? '—'; ?></span>
                            <span><i class="fas fa-language"></i> اللغة: <?php echo $book['language'] ?? 'العربية'; ?></span>
                        </div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $fullStars): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="rating-number"><?php echo number_format($rating, 1); ?></span>
                            <?php if ($totalRatings > 0): ?>
                                <span class="rating-readers">(<?php echo number_format($totalRatings); ?> تقييم)</span>
                            <?php endif; ?>
                        </div>

                        <!-- ===== ACTIONS BUTTONS ===== -->
                        <div class="actions">
                            <a href="<?= ROOT ?>reading?book_id=<?php echo $book['id']; ?>" class="btn-primary">
                                <i class="fas fa-book-open"></i> <?php echo $hasStarted ? 'متابعة القراءة' : 'قراءة'; ?>
                            </a>
                            
                            <button class="btn-secondary"><i class="fas fa-download"></i> تحميل</button>
                            <button class="fav-btn" id="favBtn"><i class="far fa-heart"></i></button>
                        </div>
                    </div>
                </div>

                <!-- ===== STATISTICS ROW ===== -->
                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fas fa-file-alt"></i>
                        <span>عدد الصفحات</span>
                        <strong><?php echo $book['pages'] ?? '—'; ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-language"></i>
                        <span>اللغة</span>
                        <strong><?php echo $book['language'] ?? 'العربية'; ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-building"></i>
                        <span>الناشر</span>
                        <strong><?php echo $book['publisher_name'] ?? '—'; ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>سنة النشر</span>
                        <strong><?php echo $book['publish_year'] ?? '—'; ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-user"></i>
                        <span>المؤلف</span>
                        <strong><?php echo htmlspecialchars($book['author_name']); ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-tag"></i>
                        <span>القسم</span>
                        <strong><?php echo htmlspecialchars($book['category_name']); ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-star"></i>
                        <span>التقييم</span>
                        <strong><?php echo number_format($rating, 1); ?></strong>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <span>المشاهدات</span>
                        <strong><?php echo number_format($book['views'] ?? 0); ?></strong>
                    </div>
                </div>

                <!-- ===== DESCRIPTION ===== -->
                <div class="card description-card">
                    <h3><i class="fas fa-align-right"></i> وصف الكتاب</h3>
                    <p><?php echo nl2br(htmlspecialchars($book['description'] ?? 'لا يوجد وصف متاح')); ?></p>
                </div>

                <!-- ===== TABLE OF CONTENTS ===== -->
                <div class="card chapters-card">
                    <h3><i class="fas fa-list-ul"></i> فهرس الفصول</h3>
                    <div class="chapters-container" id="chaptersContainer">
                        <?php if (!empty($chapters)): ?>
                            <div class="chapters-list" id="chaptersList">
                                <?php 
                                $totalChapters = count($chapters);
                                $showCount = min(5, $totalChapters);
                                $hiddenCount = $totalChapters - $showCount;
                                
                                foreach ($chapters as $index => $chapter): 
                                    $isHidden = $index >= 5;
                                ?>
                                    <div class="chapter-row <?php echo $isHidden ? 'chapter-hidden' : ''; ?>" 
                                        onclick="location.href='<?= ROOT ?>reading?book_id=<?php echo $book['id']; ?>&chapter=<?php echo $chapter['id']; ?>'"
                                        <div class="chapter-info">
                                            <span class="chapter-number">الفصل <?php echo sprintf('%02d', $chapter['chapter_number']); ?></span>
                                            <span class="chapter-title"><?php echo htmlspecialchars($chapter['title']); ?></span>
                                        </div>
                                        <div class="chapter-meta">
                                            <?php if ($chapter['reading_time'] > 0): ?>
                                                <span class="chapter-time"><i class="far fa-clock"></i> <?php echo $chapter['reading_time']; ?> دقيقة</span>
                                            <?php endif; ?>
                                            <i class="fas fa-chevron-left chapter-arrow"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if ($totalChapters > 5): ?>
                                <div class="chapters-expand-wrapper">
                                    <button class="chapters-expand-btn" id="chaptersExpandBtn">
                                        <span id="expandBtnText">عرض جميع الفصول (<?php echo $totalChapters; ?>)</span>
                                        <i class="fas fa-chevron-down" id="expandBtnIcon"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="chapters-empty">
                                <i class="fas fa-book-open"></i>
                                <p>لا توجد فصول متاحة لهذا الكتاب حالياً.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ===== AUTHOR SECTION ===== -->
                <div class="card author-section">
                    <h3><i class="fas fa-user-circle"></i> عن المؤلف</h3>
                    <div class="author-info">
                        <img src="<?= ROOT ?>assets/images/authors/<?= $book['author_photo'] ?? 'default-author.png' ?>" 
                             alt="<?= htmlspecialchars($book['author_name']) ?>" 
                             onerror="this.src='<?= ROOT ?>assets/images/avatar-placeholder.png'" />
                        <div>
                            <h4><?php echo htmlspecialchars($book['author_name']); ?></h4>
                            <p><?php echo htmlspecialchars($book['author_bio'] ?? 'كاتب وروائي، له العديد من الأعمال الأدبية المميزة.'); ?></p>
                            <div class="author-meta-info">
                                <span><i class="fas fa-book"></i> <?php echo $authorBookCount; ?> كتاب</span>
                            </div>
                            <button class="btn-author" onclick="location.href='<?= ROOT ?>Browsebooks?author=<?= urlencode($book['author_name']) ?>'">
                                <i class="fas fa-book-open"></i> عرض جميع الكتب
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ===== RATING CARD ===== -->
                <div class="card rating-card">
                    <?php if (!empty($ratingSuccess)): ?>
                        <div style="background: #e8f5e9; color: #2e7d32; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border-right: 4px solid #2e7d32; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> <?php echo $ratingSuccess; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($ratingError)): ?>
                        <div style="background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border-right: 4px solid #c62828; font-weight: 600;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $ratingError; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="rating-large">
                        <span class="big-rating"><?php echo number_format($rating, 2); ?></span>
                        <span class="rating-label">/ 5</span>
                        <span class="rating-total" style="font-size: 0.9rem; color: var(--text-secondary); margin-right: 8px;">
                            (<?php echo $totalRatings; ?> تقييم)
                        </span>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <form method="POST" action="" style="width: 100%;">
                            <div class="rating-stars" id="ratingStars">
                                <input type="hidden" name="rating" id="ratingInput" value="<?php echo $userRating; ?>" />
                                <input type="hidden" name="book_id" value="<?php echo $bookId; ?>" />
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo ($i <= $userRating) ? 'fas' : 'far'; ?> fa-star" data-value="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div style="margin-top: 12px;">
                                <button type="submit" name="submit_rating" class="btn-primary" style="padding: 8px 24px; font-size: 0.9rem;">
                                    <i class="fas fa-star"></i> <?php echo $userRating > 0 ? 'تحديث التقييم' : 'تقييم'; ?>
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div style="padding: 10px 0; color: var(--text-secondary);">
                            <a href="<?= ROOT ?>signup" style="color: var(--gold); font-weight: 600;">سجل الدخول</a> لتقييم هذا الكتاب
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ===== COMMENTS SECTION ===== -->
                <div class="card comments-card">
                    <h3><i class="fas fa-comments"></i> آراء القراء</h3>
                    
                    <?php if (!empty($commentSuccess)): ?>
                        <div style="background: #e8f5e9; color: #2e7d32; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border-right: 4px solid #2e7d32; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> <?php echo $commentSuccess; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($commentError)): ?>
                        <div style="background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border-right: 4px solid #c62828; font-weight: 600;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $commentError; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add Comment Form -->
                    <?php if ($isLoggedIn): ?>
                    <div class="add-comment">
                        <form method="POST" action="" class="comment-form">
                            <input type="hidden" name="book_id" value="<?php echo $bookId; ?>" />
                            <div class="comment-rating">
                                <label>تقييمك للكتاب:</label>
                                <div class="comment-stars" id="commentStars">
                                    <input type="hidden" name="comment_rating" id="commentRatingInput" value="0" />
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="far fa-star" data-value="<?php echo $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="comment-input-group">
                                <textarea name="comment_text" placeholder="شارك رأيك في الكتاب..." required></textarea>
                                <button type="submit" name="submit_comment" class="btn-primary">
                                    <i class="fas fa-paper-plane"></i> نشر
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="login-to-comment">
                        <p><a href="<?= ROOT ?>signup" style="color: var(--gold); font-weight: 600;">سجل الدخول</a> لتشارك رأيك</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Comments List -->
                    <?php if (!empty($comments)): ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-user">
                                            <img src="<?= ROOT ?>assets/images/users/<?= $comment['user_photo'] ?? 'avatar-placeholder.png' ?>" 
                                                 alt="<?= htmlspecialchars($comment['user_name']) ?>"
                                                 onerror="this.src='<?= ROOT ?>assets/images/avatar-placeholder.png'" />
                                            <span class="comment-username"><?= htmlspecialchars($comment['user_name']) ?></span>
                                        </div>
                                        <div class="comment-meta">
                                            <?php if ($comment['rating'] > 0): ?>
                                                <span class="comment-rating-display">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $comment['rating'] ? 'active' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="comment-date"><?= date('d/m/Y', strtotime($comment['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="comment-body">
                                        <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-comments">
                            <p>لا توجد تعليقات حتى الآن. كن أول من يشارك رأيه!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ===== RELATED BOOKS - GALLERY ===== -->
                <div class="related-section">
                    <h3><i class="fas fa-book"></i> كتب مشابهة</h3>
                    <div class="gallery-wrapper">
                        <button class="gallery-arrow left-arrow" id="prevArrow" aria-label="السابق">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="gallery-container" id="galleryContainer">
                            <?php foreach ($relatedBooks as $related): ?>
                                <div class="gallery-item">
                                    <a href="<?= ROOT ?>BookDetails?id=<?= $related['id'] ?>" class="book-cover-link" data-book-id="<?= $related['id'] ?>">
                                        <img src="<?= ROOT ?>assets/images/<?= $related['cover_image'] ?? 'placeholder.jpg' ?>" 
                                             alt="<?= htmlspecialchars($related['title']) ?>" 
                                             loading="lazy"
                                             onerror="this.src='<?= ROOT ?>assets/images/placeholder.jpg'" />
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($relatedBooks) < 4): ?>
                                <?php for ($i = count($relatedBooks); $i < 4; $i++): ?>
                                    <div class="gallery-item gallery-empty" style="visibility:hidden;"></div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                        <button class="gallery-arrow right-arrow" id="nextArrow" aria-label="التالي">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                </div>

            </div><!-- /main-content -->
        </div><!-- /page-grid -->

    </main>

    <!-- ===== FOOTER — Premium (Same as Homepage with Dark Theme) ===== -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="footer-brand">
                <span class="brand-name">سرد</span>
                <p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p>
            </div>

            <div class="footer-col">
                <h4>روابط سريعة</h4>
                <ul>
                    <li><a href="<?= ROOT ?>index">الرئيسية</a></li>
                    <li><a href="<?= ROOT ?>Browsebooks">تصفح الكتب</a></li>
                    <li><a href="#">الكتّاب</a></li>
                    <li><a href="#">من نحن</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>حسابك</h4>
                <ul>
                    <li><a href="<?= ROOT ?>signup">تسجيل الدخول</a></li>
                    <li><a href="<?= ROOT ?>signup">إنشاء حساب</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>تواصل معنا</h4>
                <ul>
                    <li><a href="#">الدعم الفني</a></li>
                    <li><a href="#">الأسئلة الشائعة</a></li>
                    <li><a href="#">سياسة الخصوصية</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <!-- ===== SCROLL TO TOP ===== -->
    <button class="scroll-top" id="scrollTop"><i class="fas fa-arrow-up"></i></button>

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        // Function to go to reading page
        function goToReading(bookId) {
            var url = '<?= ROOT ?>reading.php?book_id=' + bookId;
            console.log('Going to reading page: ' + url);
            window.location.href = url;
        }

        // Also fix the chapter links to use book_id instead of id
        document.addEventListener('DOMContentLoaded', function() {
            // Fix chapter links if they exist
            var chapterRows = document.querySelectorAll('.chapter-row');
            chapterRows.forEach(function(row) {
                var onclickAttr = row.getAttribute('onclick');
                if (onclickAttr && onclickAttr.indexOf('reading.php?id=') !== -1) {
                    var newOnclick = onclickAttr.replace('reading.php?id=', 'reading.php?book_id=');
                    row.setAttribute('onclick', newOnclick);
                }
            });

            // ===== COMMENT STARS INTERACTION =====
            const commentStars = document.querySelectorAll('#commentStars i');
            const commentRatingInput = document.getElementById('commentRatingInput');

            if (commentStars.length > 0 && commentRatingInput) {
                let currentCommentRating = 0;

                commentStars.forEach((star, index) => {
                    const value = index + 1;
                    
                    star.addEventListener('click', function() {
                        currentCommentRating = value;
                        updateCommentStars(value);
                        commentRatingInput.value = value;
                    });

                    star.addEventListener('mouseenter', function() {
                        updateCommentStars(value);
                    });

                    star.addEventListener('mouseleave', function() {
                        updateCommentStars(currentCommentRating);
                    });
                });

                function updateCommentStars(value) {
                    commentStars.forEach((s, i) => {
                        if (i < value) {
                            s.className = 'fas fa-star active';
                        } else {
                            s.className = 'far fa-star';
                        }
                    });
                }
            }
        });
    </script>
    <script src="<?= ROOT ?>assets/js/BookDetails.js"></script>
</body>
</html>
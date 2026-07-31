<?php
// BookDetails.php — Premium Book Details Page
// سرد (Sard) — Arabic Reading Platform

require_once __DIR__ . "/../core/init.php";

// Get book ID from URL
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

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
// FETCH CHAPTERS FROM DATABASE
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
    error_log("Database Error fetching chapters: " . $e->getMessage());
}

// ============================================================
// FETCH SIMILAR BOOKS (Same category, different ID)
// ============================================================
try {
    $similarQuery = "
        SELECT 
            n.id,
            n.title,
            n.slug,
            n.cover_image,
            a.name AS author_name
        FROM novels n
        JOIN authors a ON n.author_id = a.id
        WHERE n.category_id = :category_id 
        AND n.id != :book_id 
        AND n.status = 'published'
        LIMIT 4
    ";
    
    $similarStmt = $conn->prepare($similarQuery);
    $similarStmt->execute([
        ':category_id' => $book['category_id'],
        ':book_id' => $bookId
    ]);
    $similarBooks = $similarStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $similarBooks = [];
    error_log("Database Error fetching similar books: " . $e->getMessage());
}

// ============================================================
// BUILD COVER IMAGE PATH
// ============================================================
$coverPath = !empty($book['cover_image']) 
    ? ROOT . 'assets/images/' . $book['cover_image'] 
    : ROOT . 'assets/images/placeholder.jpg';

// ============================================================
// CALCULATE READING PROGRESS (Placeholder for now)
// ============================================================
$progress = 0;
$progressText = 'لم تبدأ بعد';
$isStarted = false;

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Noto+Naskh+Arabic:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/BookDetails.css">
</head>
<body>

    <!-- ============================================================
    NAVBAR (Reused from existing design)
    ============================================================ -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="<?= ROOT ?>index" class="brand-link">
                    <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد logo" class="brand-logo">
                    <span class="brand-name">سرد</span>
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="<?= ROOT ?>index">الرئيسية</a></li>
                <li><a href="<?= ROOT ?>Browsebooks" class="active">المكتبة</a></li>
                <li><a href="#">الكتّاب</a></li>
                <li><a href="#">من نحن</a></li>
            </ul>
            <div class="nav-actions">
                <a href="<?= ROOT ?>signup" class="nav-btn nav-btn-outline">تسجيل الدخول</a>
                <a href="<?= ROOT ?>signup" class="nav-btn nav-btn-filled">إنشاء حساب</a>
            </div>
        </div>
    </nav>

    <!-- ============================================================
    HERO SECTION — Two Columns
    ============================================================ -->
    <section class="book-hero">
        <div class="book-hero-container">
            <!-- Left Column: Cover -->
            <div class="book-hero-cover">
                <div class="book-cover-floating">
                    <img src="<?php echo htmlspecialchars($coverPath); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover-large">
                    <div class="book-cover-actions">
                        <button class="cover-action" aria-label="إضافة للمفضلة">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="cover-action" aria-label="مشاركة">
                            <i class="fas fa-share-alt"></i>
                        </button>
                        <button class="cover-action" aria-label="حفظ للإشارة">
                            <i class="far fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Info -->
            <div class="book-hero-info">
                <h1 class="book-title-large"><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="book-author-large"><?php echo htmlspecialchars($book['author_name']); ?></p>

                <!-- Rating -->
                <div class="book-rating-large">
                    <div class="stars">
                        <?php 
                        $rating = round($book['rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $rating): ?>
                                <i class="fas fa-star"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-number"><?php echo number_format($book['rating'] ?? 0, 1); ?></span>
                </div>

                <!-- Metadata Chips -->
                <div class="book-metadata-chips">
                    <div class="chip">
                        <span class="chip-label">عدد الصفحات</span>
                        <span class="chip-value"><?php echo $book['pages'] ?? '—'; ?></span>
                    </div>
                    <div class="chip">
                        <span class="chip-label">اللغة</span>
                        <span class="chip-value"><?php echo $book['language'] ?? 'العربية'; ?></span>
                    </div>
                    <div class="chip">
                        <span class="chip-label">الناشر</span>
                        <span class="chip-value"><?php echo $book['publisher_name'] ?? '—'; ?></span>
                    </div>
                    <div class="chip">
                        <span class="chip-label">التصنيف</span>
                        <span class="chip-value"><?php echo $book['category_name']; ?></span>
                    </div>
                    <div class="chip">
                        <span class="chip-label">سنة النشر</span>
                        <span class="chip-value"><?php echo $book['publish_year'] ?? '—'; ?></span>
                    </div>
                    <div class="chip">
                        <span class="chip-label">الدولة</span>
                        <span class="chip-value"><?php echo $book['country'] ?? '—'; ?></span>
                    </div>
                </div>

                <!-- Progress (Placeholder for now) -->
                <div class="book-progress">
                    <div class="progress-header">
                        <span class="progress-label">تقدم القراءة</span>
                        <span class="progress-value"><?php echo $progressText; ?></span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="book-actions-large">
                    <button class="btn-primary-large" onclick="location.href='reading?id=<?php echo $book['id']; ?>'">
                        <i class="fas fa-book-open"></i>
                        <?php echo $isStarted ? 'متابعة القراءة' : 'ابدأ القراءة'; ?>
                    </button>
                    <button class="btn-secondary-large">
                        <i class="far fa-heart"></i>
                        إضافة للمفضلة
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    DESCRIPTION SECTION
    ============================================================ -->
    <section class="book-description">
        <div class="description-container">
            <h2 class="section-title">نبذة عن الكتاب</h2>
            <div class="section-divider"></div>
            <div class="description-content">
                <p><?php echo htmlspecialchars($book['description'] ?? 'لا يوجد وصف متاح'); ?></p>
            </div>
        </div>
    </section>

    <!-- ============================================================
    BOOK INFORMATION GRID
    ============================================================ -->
    <section class="book-info-grid">
        <div class="info-grid-container">
            <h2 class="section-title">معلومات الكتاب</h2>
            <div class="section-divider"></div>
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-book"></i></div>
                    <span class="info-card-label">عدد الصفحات</span>
                    <span class="info-card-value"><?php echo $book['pages'] ?? '—'; ?></span>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-calendar-alt"></i></div>
                    <span class="info-card-label">سنة النشر</span>
                    <span class="info-card-value"><?php echo $book['publish_year'] ?? '—'; ?></span>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-building"></i></div>
                    <span class="info-card-label">دار النشر</span>
                    <span class="info-card-value"><?php echo $book['publisher_name'] ?? '—'; ?></span>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-tag"></i></div>
                    <span class="info-card-label">التصنيف</span>
                    <span class="info-card-value"><?php echo $book['category_name']; ?></span>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-globe"></i></div>
                    <span class="info-card-label">اللغة</span>
                    <span class="info-card-value"><?php echo $book['language'] ?? 'العربية'; ?></span>
                </div>
                <div class="info-card">
                    <div class="info-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <span class="info-card-label">الدولة</span>
                    <span class="info-card-value"><?php echo $book['country'] ?? '—'; ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    CHAPTERS & COMMENTS SECTION
    ============================================================ -->
    <section class="book-chapters">
        <div class="chapters-container">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="chapters">
                    <i class="fas fa-list-ul"></i> الفصول
                </button>
                <button class="tab-btn" data-tab="comments">
                    <i class="fas fa-comments"></i> التعليقات
                </button>
            </div>

            <!-- Chapters Tab -->
            <div class="tab-content active" id="tab-chapters">
                <div class="chapters-list">
                    <?php if (!empty($chapters)): ?>
                        <?php foreach ($chapters as $chapter): ?>
                            <div class="chapter-item" onclick="location.href='reading?id=<?php echo $book['id']; ?>&chapter=<?php echo $chapter['chapter_number']; ?>'">
                                <span class="chapter-number"><?php echo sprintf('%02d', $chapter['chapter_number']); ?></span>
                                <span class="chapter-name"><?php echo htmlspecialchars($chapter['title']); ?></span>
                                <?php if ($chapter['word_count'] > 0): ?>
                                    <span class="chapter-info">(<?php echo number_format($chapter['word_count']); ?> كلمة)</span>
                                <?php endif; ?>
                                <button class="chapter-read-btn" aria-label="اقرأ هذا الفصل">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="chapter-item" style="justify-content: center; opacity: 0.7;">
                            <span>لم يتم إضافة فصول بعد</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments Tab -->
            <div class="tab-content" id="tab-comments">
                <div class="comments-list">
                    <div class="comment-item">
                        <div class="comment-avatar">ن</div>
                        <div class="comment-body">
                            <div class="comment-header">
                                <span class="comment-author">نورا أحمد</span>
                                <span class="comment-rating">★★★★★</span>
                                <span class="comment-date">منذ ٣ أيام</span>
                            </div>
                            <p class="comment-text">رواية رائعة جداً! أسلوب الكاتب ممتع والأحداث مشوقة. أنتظر الفصل القادم بفارغ الصبر.</p>
                        </div>
                    </div>
                    <div class="comment-item">
                        <div class="comment-avatar">س</div>
                        <div class="comment-body">
                            <div class="comment-header">
                                <span class="comment-author">سامي رضا</span>
                                <span class="comment-rating">★★★★☆</span>
                                <span class="comment-date">منذ أسبوع</span>
                            </div>
                            <p class="comment-text">قصة عميقة ومؤثرة. أوصي بها بشدة لكل محبي الأدب التاريخي.</p>
                        </div>
                    </div>
                    <div class="comment-item">
                        <div class="comment-avatar">ل</div>
                        <div class="comment-body">
                            <div class="comment-header">
                                <span class="comment-author">ليلى سعيد</span>
                                <span class="comment-rating">★★★★★</span>
                                <span class="comment-date">منذ أسبوعين</span>
                            </div>
                            <p class="comment-text">من أجمل ما قرأت هذا العام. اللغة جميلة والسرد مشوق. أنصح به بشدة.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    RELATED BOOKS
    ============================================================ -->
    <section class="related-books">
        <div class="related-container">
            <h2 class="section-title">كتب قد تعجبك</h2>
            <div class="section-divider"></div>
            <div class="related-carousel">
                <?php foreach ($similarBooks as $similar): ?>
                    <div class="related-book" onclick="location.href='BookDetails.php?id=<?php echo $similar['id']; ?>'">
                        <img src="<?php echo ROOT . 'assets/images/' . ($similar['cover_image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($similar['title']); ?>" class="related-cover">
                        <p class="related-title"><?php echo htmlspecialchars($similar['title']); ?></p>
                        <p class="related-author"><?php echo htmlspecialchars($similar['author_name']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============================================================
    FOOTER CTA
    ============================================================ -->
    <section class="footer-cta">
        <div class="cta-container">
            <h2 class="cta-title">هل أنت مستعد لبدء القراءة؟</h2>
            <button class="cta-btn" onclick="location.href='reading?id=<?php echo $book['id']; ?>'">
                <i class="fas fa-book-open"></i> ابدأ القراءة الآن
            </button>
        </div>
    </section>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <span class="footer-logo">سرد</span>
                <p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p>
            </div>
            <div class="footer-links">
                <div class="footer-col">
                    <h4>روابط سريعة</h4>
                    <a href="<?= ROOT ?>index">الرئيسية</a>
                    <a href="<?= ROOT ?>Browsebooks">المكتبة</a>
                    <a href="#">الكتّاب</a>
                    <a href="#">من نحن</a>
                </div>
                <div class="footer-col">
                    <h4>حسابك</h4>
                    <a href="<?= ROOT ?>signup">تسجيل الدخول</a>
                    <a href="<?= ROOT ?>signup">إنشاء حساب</a>
                </div>
                <div class="footer-col">
                    <h4>تواصل معنا</h4>
                    <a href="#">الدعم الفني</a>
                    <a href="#">الأسئلة الشائعة</a>
                    <a href="#">سياسة الخصوصية</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <script src="<?= ROOT ?>assets/js/BookDetails.js"></script>

</body>
</html>
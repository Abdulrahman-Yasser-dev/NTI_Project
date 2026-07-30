<?php
// HomePage.php
// ============================================================
// TEMP MODE: DB connection disabled for now — using dummy data
// ============================================================

// ============================================================
// ALL BOOKS DATA (for featured books only)
// ============================================================
require_once "../app/core/init.php";


$allBooks = [
    // Romance
    [
        "id" => 1,
        "title" => "ظل النخيل",
        "author_name" => "سلمى عادل",
        "category" => "رومانسي",
        "excerpt" => "رواية عن الشوق والعودة للجذور",
        "cover" => "assets/images/covers/book-1.jpg",
        "rating" => 4.7,
        "readers" => 1243,
        "chapters" => 24
    ],
    [
        "id" => 2,
        "title" => "حكاية قلب",
        "author_name" => "نورا أحمد",
        "category" => "رومانسي",
        "excerpt" => "رحلة بحث عن الحب في زمن المتغيرات",
        "cover" => "assets/images/covers/book-2.jpg",
        "rating" => 4.5,
        "readers" => 876,
        "chapters" => 18
    ],
    // Sci-Fi
    [
        "id" => 4,
        "title" => "العابرون",
        "author_name" => "هالة نبيل",
        "category" => "خيال علمي",
        "excerpt" => "رحلة عبر الأبعاد المختلفة",
        "cover" => "assets/images/covers/book-4.jpg",
        "rating" => 4.6,
        "readers" => 987,
        "chapters" => 28
    ],
    [
        "id" => 6,
        "title" => "آلة الزمن",
        "author_name" => "كريم أشرف",
        "category" => "خيال علمي",
        "excerpt" => "مغامرات عبر الزمن",
        "cover" => "assets/images/covers/book-6.jpg",
        "rating" => 4.9,
        "readers" => 3156,
        "chapters" => 42
    ],
    // Mystery
    [
        "id" => 7,
        "title" => "مرايا الصمت",
        "author_name" => "يوسف كامل",
        "category" => "غموض",
        "excerpt" => "لغز ينتظر من يحله",
        "cover" => "assets/images/covers/book-7.jpg",
        "rating" => 4.3,
        "readers" => 543,
        "chapters" => 14
    ],
    [
        "id" => 8,
        "title" => "اللغز",
        "author_name" => "مازن رضا",
        "category" => "غموض",
        "excerpt" => "قصة غامضة تأخذك إلى عالم آخر",
        "cover" => "assets/images/covers/book-8.jpg",
        "rating" => 4.7,
        "readers" => 1234,
        "chapters" => 22
    ],
];

// ============================================================
// FEATURED BOOKS (only 6 books for homepage)
// ============================================================
$featuredBooks = array_slice($allBooks, 0, 6);

// ============================================================
// STORY OF THE WEEK (featured novel)
// ============================================================
$storyOfTheWeek = [
    "id" => 1,
    "title" => "ظل النخيل",
    "author_name" => "سلمى عادل",
    "excerpt" => "رواية عن الشوق والعودة للجذور",
    "cover" => "assets/images/spotlight/spotlight-cover.jpg",
    "rating" => 4.7,
    "readers" => 1243,
    "chapters" => 24
];

// ============================================================
// CATEGORIES WITH BACKGROUND IMAGES
// ============================================================
$categories = [
    [
        "name" => "رومانسي",
        "description" => "قصص الحب والمشاعر",
        "bg" => "assets/images/categories/romance-bg.avif",
        "color" => "#B34141",
        "count" => 12
    ],
    [
        "name" => "خيال علمي",
        "description" => "عوالم مستقبلية وخيالية",
        "bg" => "assets/images/categories/Sci-Fi-bg.avif",
        "color" => "#1A6EB5",
        "count" => 8
    ],
    [
        "name" => "غموض",
        "description" => "ألغاز وتشويق وإثارة",
        "bg" => "assets/images/categories/Mystery-bg.avif",
        "color" => "#6B4C3A",
        "count" => 15
    ],
    [
        "name" => "دراما",
        "description" => "قصص إنسانية مؤثرة",
        "bg" => "assets/images/categories/drama-bg.avif",
        "color" => "#993556",
        "count" => 10
    ],
];

// ============================================================
// STATISTICS
// ============================================================
$stats = [
    ["number" => "500+", "label" => "رواية"],
    ["number" => "120+", "label" => "كاتب"],
    ["number" => "3K+", "label" => "قارئ"],
    ["number" => "1K+", "label" => "تقييم"],
];

// ============================================================
// FEATURES
// ============================================================
$features = [
    [
        "title" => "قراءة مجانية",
        "desc" => "تصفحي آلاف الروايات دون أي تكلفة",
        "icon" => "📖"
    ],
    [
        "title" => "كتابة ونشر",
        "desc" => "انشري روايتك ووصليها لآلاف القراء",
        "icon" => "✍️"
    ],
    [
        "title" => "مجتمع قراء",
        "desc" => "تفاعلي مع قراء وكتّاب من كل مكان",
        "icon" => "👥"
    ],
];

// ============================================================
// TESTIMONIALS
// ============================================================
$testimonials = [
    [
        "name" => "نورا أحمد",
        "role" => "قارئة",
        "text" => "سرد غيرت طريقة قراءتي للروايات. اكتشفت كتاب جدد وأعمال رائعة!"
    ],
    [
        "name" => "سامي رضا",
        "role" => "كاتب",
        "text" => "أنشر رواياتي بسهولة وأوصل لجمهور واسع. سرد حلم كل كاتب!"
    ],
    [
        "name" => "ليلى سعيد",
        "role" => "قارئة",
        "text" => "أفضل منصة للروايات العربية. التصميم جميل والتجربة سلسة."
    ],
    [
        "name" => "محمد الحسن",
        "role" => "كاتب",
        "text" => "منصة رائعة تجمع بين الكتاب والقراء في مكان واحد. أنصح بها بشدة."
    ],
    [
        "name" => "ريما خالد",
        "role" => "قارئة",
        "text" => "أحببت التصميم البسيط وسهولة الاستخدام. أصبحت أقرأ يومياً بفضل سرد."
    ],
];
?>
<?php
include "header.php";
?>
<main>
    <!-- ============================================================
    SECTION 1: HERO
    ============================================================ -->
    <section class="hero-section" style="background-image: url('<?= ROOT ?>assets/images/hero/hero-bg.jpg');">
        <div class="hero-overlay">
            <p class="eyebrow">منصة الروايات العربية</p>
            <h1>حيث تبدأ<br>كل حكاية</h1>
            <p class="hero-sub">روايات من كل نوع، من كتّاب حقيقيين، تتقرأ مجاناً من أول لحظة</p>
            <div class="hero-actions">
                <button class="btn btn-hero-primary" onclick="location.href='Browsebooks'">
                    استكشف الروايات
                </button>
                <button class="btn btn-hero-secondary" onclick="location.href='Signup?role=writer'">
                    انضم كـ كاتب
                </button>
            </div>
        </div>
    </section>

    <!-- ============================================================
    SECTION 2: STATISTICS
    ============================================================ -->
    <section class="stats-section">
        <div class="stats-container">
            <?php foreach ($stats as $stat): ?>
                <div class="stat-item">
                    <p class="stat-number"><?php echo $stat['number']; ?></p>
                    <p class="stat-label"><?php echo $stat['label']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================
    SECTION 3: STORY OF THE WEEK
    ============================================================ -->
    <section class="story-week-section">
        <div class="story-week-container">
            <div class="story-week-badge">⭐ قصة الأسبوع</div>
            <div class="story-week-content">
                <div class="story-week-cover">
                    <img src="<?php echo $storyOfTheWeek['cover']; ?>"
                        alt="<?php echo htmlspecialchars($storyOfTheWeek['title']); ?>"
                        class="story-week-img">
                </div>
                <div class="story-week-info">
                    <h2 class="story-week-title"><?php echo htmlspecialchars($storyOfTheWeek['title']); ?></h2>
                    <p class="story-week-author"><?php echo htmlspecialchars($storyOfTheWeek['author_name']); ?></p>
                    <p class="story-week-excerpt"><?php echo htmlspecialchars($storyOfTheWeek['excerpt']); ?></p>
                    <div class="story-week-meta">
                        <span class="meta-item">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <?php echo number_format($storyOfTheWeek['rating'], 1); ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-users" aria-hidden="true"></i>
                            <?php echo number_format($storyOfTheWeek['readers']); ?> قارئ
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-book" aria-hidden="true"></i>
                            <?php echo $storyOfTheWeek['chapters']; ?> فصل
                        </span>
                    </div>
                    <button class="btn btn-story-week" onclick="location.href='BookDetails?id=<?php echo (int)$storyOfTheWeek['id']; ?>'">
                        اقرأ الآن
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
    SECTION 4: POPULAR CATEGORIES
    ============================================================ -->
    <section class="categories-section" id="categories">
        <p class="section-kicker">استكشف حسب النوع</p>
        <p class="section-title">اختر عالمك المفضل</p>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-card-modern"
                    style="background-image: linear-gradient(135deg, rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('<?php echo $cat['bg']; ?>');">
                    <div class="category-content">
                        <h3 class="category-name-modern"><?php echo $cat['name']; ?></h3>
                        <p class="category-desc"><?php echo $cat['description']; ?></p>
                        <p class="category-count-modern"><?php echo $cat['count']; ?> رواية</p>
                        <button class="btn btn-category-browse" onclick="location.href='Browsebooks?category=<?php echo urlencode($cat['name']); ?>'">
                            استكشف
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================
    SECTION 5: FEATURED BOOKS
    ============================================================ -->
    <section class="featured-books-section">
        <div class="featured-books-header">
            <div>
                <p class="section-kicker">أشهر الروايات</p>
                <p class="section-title">اقرأ الأكثر رواجاً</p>
            </div>
            <button class="btn btn-view-all" onclick="location.href='Browsebooks'">
                عرض الكل ←
            </button>
        </div>
        <div class="featured-books-grid">
            <?php foreach ($featuredBooks as $book): ?>
                <article class="featured-book-card" onclick="location.href='BookDetails?id=<?php echo (int)$book['id']; ?>'">
                    <div class="featured-book-cover">
                        <?php if (!empty($book['cover']) && file_exists($book['cover'])): ?>
                            <img src="<?php echo htmlspecialchars($book['cover']); ?>"
                                alt="<?php echo htmlspecialchars($book['title']); ?>"
                                class="featured-book-img">
                        <?php else: ?>
                            <span class="cover-placeholder">📖</span>
                        <?php endif; ?>
                    </div>
                    <div class="featured-book-info">
                        <p class="featured-book-title"><?php echo htmlspecialchars($book['title']); ?></p>
                        <p class="featured-book-author"><?php echo htmlspecialchars($book['author_name']); ?></p>
                        <div class="featured-book-rating">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <?php echo number_format($book['rating'] ?? 4.5, 1); ?>
                        </div>
                        <button class="btn btn-book-read" onclick="event.stopPropagation(); location.href='BookDetails?id=<?php echo (int)$book['id']; ?>'">
                            اقرأ
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============================================================
SECTION 6: WHY SARD? (Redesigned)
============================================================ -->
    <section class="features-section" id="features">
        <div class="features-header">
            <p class="section-kicker">لماذا سرد؟</p>
            <h2 class="features-headline">كل ما يحتاجه الكاتب والقارئ في مكان واحد</h2>
            <p class="features-subheadline">منصة متكاملة تجمع بين القراءة والكتابة في تجربة سلسة</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-book-open feature-icon"></i>
                </div>
                <h3 class="feature-title">قراءة مجانية</h3>
                <p class="feature-desc">تصفحي آلاف الروايات دون أي تكلفة واستمتعي بأفضل القصص العربية</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-pen-fancy feature-icon"></i>
                </div>
                <h3 class="feature-title">كتابة ونشر</h3>
                <p class="feature-desc">انشري روايتك ووصليها لآلاف القراء بسهولة واحترافية</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-users feature-icon"></i>
                </div>
                <h3 class="feature-title">مجتمع قراء</h3>
                <p class="feature-desc">تفاعلي مع قراء وكتّاب من كل مكان وشاركي آرائك بحرية</p>
            </div>
        </div>
    </section>

    <!-- ============================================================
SECTION 7: TESTIMONIALS (Redesigned with Carousel)
============================================================ -->
    <section class="testimonials-section" id="testimonials">
        <div class="testimonials-header">
            <p class="section-kicker">آراء قرّائنا وكتّابنا</p>
            <h2 class="testimonials-headline">ماذا يقولون عن سرد؟</h2>
        </div>

        <div class="testimonials-carousel-container">
            <div class="testimonials-track" id="testimonialsTrack">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <div class="testimonial-card" data-index="<?php echo $index; ?>">
                        <div class="testimonial-quote-icon">
                            <i class="fas fa-quote-right"></i>
                        </div>
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"<?php echo $testimonial['text']; ?>"</p>
                        <div class="testimonial-author">
                            <p class="testimonial-name"><?php echo $testimonial['name']; ?></p>
                            <p class="testimonial-role"><?php echo $testimonial['role']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Navigation Dots -->
            <div class="testimonials-dots" id="testimonialsDots">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <button class="dot <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-index="<?php echo $index; ?>"
                        aria-label="شاهد testimonial <?php echo $index + 1; ?>">
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Navigation Arrows -->
            <button class="testimonial-arrow testimonial-arrow-prev" id="prevTestimonial" aria-label="السابق">
                <i class="fas fa-chevron-right"></i>
            </button>
            <button class="testimonial-arrow testimonial-arrow-next" id="nextTestimonial" aria-label="التالي">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
    </section>

    <!-- ============================================================
    SECTION 8: FINAL CTA
    ============================================================ -->
    <section class="final-cta-section" style="background-image: url('assets/images/hero/hero-bg.jpg');">
        <div class="final-cta-overlay">
            <div class="final-cta-content">
                <h2 class="final-cta-title">ابدأ رحلتك مع سرد</h2>
                <p class="final-cta-sub">انضم إلى آلاف القرّاء والكتّاب وابدأ في اكتشاف وكتابة الروايات</p>
                <div class="final-cta-actions">
                    <button class="btn btn-final-cta-primary" onclick="location.href='Signup'">
                        إنشاء حساب مجاني
                    </button>
                    <button class="btn btn-final-cta-secondary" onclick="location.href='Browsebooks'">
                        استكشف الروايات
                    </button>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
include "footer.php";
?>

<!-- ============================================================
JAVASCRIPT
============================================================ -->
<script src="assets/js/index.js"></script>
</body>

</html>
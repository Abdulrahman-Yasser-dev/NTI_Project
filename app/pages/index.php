<?php
// index.php (Homepage)
// سرد (Sard) — Arabic Reading Platform

require_once __DIR__ . "/../core/init.php";

// ============================================================
// FETCH BOOKS FROM DATABASE
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
            a.name AS author_name,
            a.bio AS author_bio,
            a.photo AS author_photo,
            c.name_ar AS category_name,
            c.name_en AS category_name_en,
            p.name AS publisher_name
        FROM novels n
        JOIN authors a ON n.author_id = a.id
        JOIN categories c ON n.category_id = c.id
        LEFT JOIN publishers p ON n.publisher_id = p.id
        WHERE n.status = 'published'
        ORDER BY n.is_featured DESC, n.id ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // If there's an error, show empty array
    $books = [];
    error_log("Database Error in index.php: " . $e->getMessage());
}

// ============================================================
// SPINE IMAGE POOL (Existing spine images from the old homepage)
// ============================================================
$spinePool = [
    "نجيب-محفوظ-اللص و الكلاب(2).png",
    "اولاد الناس.png",
    "ثرثرة فوق النيل .png",
    "طبيب ارياف.png",
    "ماجدولين.png",
    "ايكادولي.png",
    "شجرتي.png",
];

// Build the spine images array
$bookSpines = [];
foreach ($books as $index => $book) {
    // If spine_image exists in database, use it
    if (!empty($book['spine_image'])) {
        $bookSpines[] = ROOT . "assets/images/" . $book['spine_image'];
    } else {
        // Use fallback based on index
        $fallbackIndex = $index % count($spinePool);
        $bookSpines[] = ROOT . "assets/images/" . $spinePool[$fallbackIndex];
    }
}

// ============================================================
// MODAL DATA (for the book modal popup)
// ============================================================
$modalData = [];
foreach ($books as $book) {
    $modalData[$book['id']] = [
        "title" => $book['title'],
        "author" => $book['author_name'],
        "category" => $book['category_name'],
        "year" => $book['publish_year'],
        "pages" => $book['pages'],
        "desc" => $book['description']
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>سرد</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= ROOT ?>assets/css/index.css">
</head>
<body>

  <!-- ─────────────────────────  SECTION 1: NAVBAR  ───────────────────────── -->
  <nav class="navbar" id="navbar">
    <a href="<?= ROOT ?>index" class="nav-brand">
      <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد logo">
      <span>سرد</span>
    </a>

    <ul class="nav-links">
      <li><a href="<?= ROOT ?>index">الرئيسية</a></li>
      <li><a href="<?= ROOT ?>Browsebooks">تصفح الكتب</a></li>
      <li><a href="<?= ROOT ?>Writewithus">الكتّاب</a></li>
      <li><a href="#">من نحن</a></li>
    </ul>

    <div class="nav-actions">
      <a href="<?= ROOT ?>signup" class="nav-btn glass">تسجيل الدخول</a>
      <a href="<?= ROOT ?>signup" class="nav-btn filled">إنشاء حساب</a>
      <button class="nav-toggle" aria-label="القائمة">☰</button>
    </div>
  </nav>

  <!-- ─────────────────────────  SECTION 2: HERO IMAGE  ───────────────────────── -->
  <section class="hero-section">
    <div class="hero-bg-image"></div>
  </section>

  <!-- ─────────────────────────  SECTION 3: SHELF / LIBRARY  ───────────────────────── -->
  <section class="shelf-wrapper">
    <div class="shelf-hero">

      <!-- Row 1: First 9 books -->
      <div class="shelf-row row-1" aria-label="الرف الأول">
        <?php for ($i = 0; $i < 9 && $i < count($books); $i++): ?>
          <div class="book-wrapper">
            <button class="book-btn" data-book="<?= $books[$i]['id'] ?>" onclick="openBookModal(<?= $books[$i]['id'] ?>)">
              <img src="<?= htmlspecialchars($bookSpines[$i]) ?>" alt="<?= htmlspecialchars($books[$i]['title']) ?>">
            </button>
          </div>
        <?php endfor; ?>
      </div>

      <!-- Row 2: Last 9 books -->
      <div class="shelf-row row-2" aria-label="الرف الثاني">
        <?php for ($i = 9; $i < 18 && $i < count($books); $i++): ?>
          <div class="book-wrapper">
            <button class="book-btn" data-book="<?= $books[$i]['id'] ?>" onclick="openBookModal(<?= $books[$i]['id'] ?>)">
              <img src="<?= htmlspecialchars($bookSpines[$i]) ?>" alt="<?= htmlspecialchars($books[$i]['title']) ?>">
            </button>
          </div>
        <?php endfor; ?>
      </div>

    </div>
  </section>

  <!-- ─────────────────────────  SECTION 4: READER / WRITER  ───────────────────────── -->
  <section class="info-section">
    <div class="info-col">
      <div class="info-image">
        <img src="<?= ROOT ?>assets/images/reader.png" alt="قارئ">
      </div>
      <div class="info-content">
        <h2>إن كنت قارئًا</h2>
        <p>
          ستجد في سرد مكتبة عربية غنية تضم روايات وأعمالًا خالدة من أبرز الكتّاب، مرتبة كأنها رفوف مكتبتك الخاصة.
          تصفّح، اقرأ، واحفظ ما يعجبك لتكمله لاحقًا، في تجربة قراءة هادئة وممتعة مصممة خصيصًا لعشّاق الأدب العربي.
        </p>
        <a href="<?= ROOT ?>Browsebooks" class="info-cta">ابدأ رحلة القراءة</a>
      </div>
    </div>

    <div class="info-col">
      <div class="info-image">
        <img src="<?= ROOT ?>assets/images/writer.png" alt="كاتب">
      </div>
      <div class="info-content">
        <h2>إن كنت كاتبًا</h2>
        <p>
          سرد يفتح لك بابًا للوصول إلى قرّاء حقيقيين يبحثون عن أعمال جديدة تستحق القراءة.
          شارك رواياتك وأعمالك مع مجتمع من القرّاء، واحصل على انتشار وتقدير أوسع لأسلوبك ككاتب عربي.
        </p>
        <a href="<?= ROOT ?>signup?role=writer" class="info-cta">انشر أعمالك</a>
      </div>
    </div>
  </section>

  <!-- ─────────────────────────  SECTION 5: FOOTER  ───────────────────────── -->
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
          <li><a href="<?= ROOT ?>Writewithus">الكتّاب</a></li>
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

  <!-- ─────────────────────────  BOOK MODAL  ───────────────────────── -->
  <div class="modal-overlay" id="bookModal">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <button class="modal-close" id="modalClose" aria-label="إغلاق">✕</button>

        <div class="modal-cover">
            <img id="modalCoverImg" src="<?= ROOT ?>assets/images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="غلاف الكتاب">
        </div>

        <div class="modal-body">
            <h2 class="modal-title" id="modalTitle">اللص والكلاب</h2>
            <div class="modal-author" id="modalAuthor">نجيب محفوظ</div>

            <div class="modal-meta" id="modalMeta">
                <span id="modalCategory">رواية</span>
                <span id="modalYear">١٩٦١</span>
                <span id="modalPages">١٤٤ صفحة</span>
            </div>

            <p class="modal-desc" id="modalDesc">
                تدور أحداث الرواية حول سعيد مهران بعد خروجه من السجن، وسعيه للانتقام ممن خانوه، في عمل يعكس صراع الإنسان مع المجتمع ومع ذاته. من أبرز أعمال نجيب محفوظ الفلسفية.
            </p>

            <div class="modal-actions">
                <button class="read-now" id="readNowBtn">اقرأ الآن</button>
                <button class="save-btn">حفظ للاحقًا</button>
            </div>
        </div>
    </div>
  </div>

  <!-- ─────────────────────────  JAVASCRIPT  ───────────────────────── -->
  <script>
    // ============================================================
    // MODAL DATA (from PHP)
    // ============================================================
    const modalData = <?= json_encode($modalData) ?>;

    // ============================================================
    // OPEN MODAL FUNCTION
    // ============================================================
    function openBookModal(bookId) {
        const data = modalData[bookId];
        if (!data) return;

        document.getElementById('modalTitle').textContent = data.title;
        document.getElementById('modalAuthor').textContent = data.author;
        document.getElementById('modalCategory').textContent = data.category;
        document.getElementById('modalYear').textContent = data.year;
        document.getElementById('modalPages').textContent = data.pages + ' صفحة';
        document.getElementById('modalDesc').textContent = data.desc;

        // Update read button to go to BookDetails
        document.getElementById('readNowBtn').onclick = function() {
            window.location.href = 'BookDetails?id=' + bookId;
        };

        document.getElementById('bookModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // ============================================================
    // CLOSE MODAL
    // ============================================================
    document.getElementById('modalClose').addEventListener('click', function() {
        document.getElementById('bookModal').classList.remove('active');
        document.body.style.overflow = '';
    });

    document.getElementById('bookModal').addEventListener('click', function(e) {
        if (e.target === this) {
            document.getElementById('bookModal').classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('bookModal').classList.remove('active');
            document.body.style.overflow = '';
        }
    });
  </script>

</body>
</html>
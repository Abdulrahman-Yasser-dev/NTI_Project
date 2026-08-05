<?php
// index.php (Homepage)
// سرد (Sard) — Arabic Reading Platform

require_once __DIR__ . "/../core/init.php";

// ============================================================
// FETCH HOMEPAGE BOOKS FROM DATABASE (Using homepage_books table)
// ============================================================
try {
    $query = "
        SELECT 
            n.id,
            n.title,
            n.slug,
            n.description,
            n.cover_image,
            n.publish_year,
            n.pages,
            n.rating,
            n.is_featured,
            n.status,
            a.name AS author_name,
            c.name_ar AS category_name,
            hb.shelf_row,
            hb.display_order
        FROM homepage_books hb
        JOIN novels n ON hb.book_id = n.id
        JOIN authors a ON n.author_id = a.id
        JOIN categories c ON n.category_id = c.id
        WHERE n.status = 'published'
        AND n.cover_image IS NOT NULL
        AND n.cover_image != ''
        ORDER BY hb.shelf_row ASC, hb.display_order ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $books = [];
    error_log("Database Error in index.php: " . $e->getMessage());
}

// ============================================================
// SPLIT BOOKS INTO ROWS
// ============================================================
$row1Books = [];
$row2Books = [];

foreach ($books as $book) {
    if ($book['shelf_row'] == 1) {
        $row1Books[] = $book;
    } elseif ($book['shelf_row'] == 2) {
        $row2Books[] = $book;
    }
}

// ============================================================
// MODAL DATA
// ============================================================
$modalData = [];
foreach ($books as $book) {
    $modalData[$book['id']] = [
        "title" => $book['title'],
        "author" => $book['author_name'],
        "category" => $book['category_name'],
        "year" => $book['publish_year'],
        "pages" => $book['pages'],
        "desc" => $book['description'],
        "cover_image" => $book['cover_image'],
        "rating" => $book['rating']
    ];
}

// ============================================================
// PASS DATA TO JAVASCRIPT
// ============================================================
echo '<script>const ROOT_URL = "' . ROOT . '";</script>';
echo '<script>const MODAL_DATA = ' . json_encode($modalData) . ';</script>';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>سرد — المكتبة العربية الرقمية</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= ROOT ?>assets/css/index.css" />
</head>
<body>
  <!-- ─── NAVBAR ─── -->
  <nav class="navbar" id="navbar">
    <a href="<?= ROOT ?>index" class="nav-brand">
      <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد">
      <span>سرد</span>
    </a>
    <ul class="nav-links">
      <li><a href="<?= ROOT ?>index" class="active">الرئيسية</a></li>
      <li><a href="<?= ROOT ?>Browsebooks">تصفح الكتب</a></li>
      <li><a href="#">من نحن</a></li>
    </ul>
    <div class="nav-actions">
      <a href="<?= ROOT ?>signup" class="nav-btn glass">تسجيل الدخول</a>
      <a href="<?= ROOT ?>signup" class="nav-btn filled">إنشاء حساب</a>
      <button class="nav-toggle" aria-label="القائمة">☰</button>
    </div>
  </nav>

  <!-- ─── HERO ─── -->
  <section class="hero-section"><div class="hero-bg-image"></div></section>

  <!-- ─── SHELF ─── -->
  <section class="shelf-wrapper">
    <div class="shelf-hero">
      <!-- Row 1: First books -->
      <div class="shelf-row row-1" aria-label="الرف الأول">
        <?php foreach ($row1Books as $book): 
          $coverPath = !empty($book['cover_image']) 
              ? ROOT . 'assets/images/' . $book['cover_image'] 
              : ROOT . 'assets/images/placeholder.jpg';
        ?>
          <div class="book-wrapper">
            <button class="book-btn" data-book="<?= $book['id'] ?>">
              <img src="<?= htmlspecialchars($coverPath) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
            </button>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Row 2: Next books -->
      <div class="shelf-row row-2" aria-label="الرف الثاني">
        <?php foreach ($row2Books as $book): 
          $coverPath = !empty($book['cover_image']) 
              ? ROOT . 'assets/images/' . $book['cover_image'] 
              : ROOT . 'assets/images/placeholder.jpg';
        ?>
          <div class="book-wrapper">
            <button class="book-btn" data-book="<?= $book['id'] ?>">
              <img src="<?= htmlspecialchars($coverPath) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ─── INFO SECTION: READER / WRITER ─── -->
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

  <!-- ─── FOOTER ─── -->
  <footer class="site-footer">
    <div class="footer-top">
      <div class="footer-brand"><span class="brand-name">سرد</span><p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p></div>
      <div class="footer-col"><h4>روابط سريعة</h4><ul><li><a href="<?= ROOT ?>index">الرئيسية</a></li><li><a href="<?= ROOT ?>Browsebooks">تصفح الكتب</a></li><li><a href="#">الكتّاب</a></li><li><a href="#">من نحن</a></li></ul></div>
      <div class="footer-col"><h4>حسابك</h4><ul><li><a href="<?= ROOT ?>signup">تسجيل الدخول</a></li><li><a href="<?= ROOT ?>signup">إنشاء حساب</a></li></ul></div>
      <div class="footer-col"><h4>تواصل معنا</h4><ul><li><a href="#">الدعم الفني</a></li><li><a href="#">الأسئلة الشائعة</a></li><li><a href="#">سياسة الخصوصية</a></li></ul></div>
    </div>
    <div class="footer-bottom"><span>© 2026 سرد. جميع الحقوق محفوظة.</span><span>صُنع بحب لمحبي القراءة والكتابة العربية</span></div>
  </footer>

  <!-- ─── VIEWER ─── -->
  <div class="book-viewer-overlay" id="bookViewer">
    <div class="overlay-bg"></div>
    <div class="book-modal" id="bookModal">
      <div class="book-panel">
        <img class="book-cover" id="bookCover" src="" alt="book cover" />
      </div>
      <div class="paper-panel" id="paperPanel">
        <div class="paper-content" id="paperContent">
          <h2 class="book-title content-hidden" id="pTitle"></h2>
          <div class="book-author content-hidden" id="pAuthor"></div>
          <div class="book-meta-grid content-hidden" id="pMeta"></div>
          <div class="book-description content-hidden" id="pDesc"></div>
          <div class="book-actions content-hidden" id="pActions">
            <button class="action-btn primary" onclick="window.location.href='<?= ROOT ?>BookDetails?id=' + (typeof selectedBookId !== 'undefined' ? selectedBookId : '')">
              <span class="icon"><i class="fa-solid fa-book-open"></i></span> اقرأ الآن
            </button>
            <button class="action-btn outline"><span class="icon"><i class="fa-regular fa-heart"></i></span> أضف للمفضلة</button>
          </div>
        </div>
      </div>
    </div>
    <button class="book-close-btn" id="bookCloseBtn"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <script src="<?= ROOT ?>assets/js/index.js"></script>
</body>
</html>
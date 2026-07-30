<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>سرد</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
  
  <!-- Custom CSS — CORRECT PATH -->
  <link rel="stylesheet" href="../Style/HomePage.css">
</head>
<body>

  <!-- ─────────────────────────  SECTION 1: NAVBAR  ───────────────────────── -->
  <nav class="navbar" id="navbar">
    <a href="HomePage.php" class="nav-brand">
      <img src="../images/sarrdd Logo.png" alt="سرد logo">
      <span>سرد</span>
    </a>

    <ul class="nav-links">
      <li><a href="HomePage.php">الرئيسية</a></li>
      <li><a href="Browsebooks.php">تصفح الكتب</a></li>
      <li><a href="Writewithus.php">الكتّاب</a></li>
      <li><a href="#">من نحن</a></li>
    </ul>

    <div class="nav-actions">
      <a href="signup.php" class="nav-btn glass">تسجيل الدخول</a>
      <a href="signup.php" class="nav-btn filled">إنشاء حساب</a>
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

      <!-- Row 1: 16 books -->
      <div class="shelf-row row-1" aria-label="الرف الأول">
        <div class="book-wrapper"><button class="book-btn" data-book="1"><img src="../images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="2"><img src="../images/اولاد الناس.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="3"><img src="../images/ثرثرة فوق النيل .png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="4"><img src="../images/طبيب ارياف.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="5"><img src="../images/ماجدولين.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="6"><img src="../images/ايكادولي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="7"><img src="../images/شجرتي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="8"><img src="../images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="9"><img src="../images/اولاد الناس.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="10"><img src="../images/ثرثرة فوق النيل .png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="11"><img src="../images/طبيب ارياف.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="12"><img src="../images/ماجدولين.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="13"><img src="../images/ايكادولي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="14"><img src="../images/شجرتي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="15"><img src="../images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="16"><img src="../images/اولاد الناس.png" alt="book"></button></div>
      </div>

      <!-- Row 2: 16 books -->
      <div class="shelf-row row-2" aria-label="الرف الثاني">
        <div class="book-wrapper"><button class="book-btn" data-book="17"><img src="../images/اولاد الناس.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="18"><img src="../images/ايكادولي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="19"><img src="../images/شجرتي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="20"><img src="../images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="21"><img src="../images/ثرثرة فوق النيل .png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="22"><img src="../images/طبيب ارياف.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="23"><img src="../images/ماجدولين.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="24"><img src="../images/ايكادولي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="25"><img src="../images/اولاد الناس.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="26"><img src="../images/شجرتي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="27"><img src="../images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="28"><img src="../images/ثرثرة فوق النيل .png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="29"><img src="../images/طبيب ارياف.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="30"><img src="../images/ماجدولين.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="31"><img src="../images/ايكادولي.png" alt="book"></button></div>
        <div class="book-wrapper"><button class="book-btn" data-book="32"><img src="../images/شجرتي.png" alt="book"></button></div>
      </div>

    </div>
  </section>

  <!-- ─────────────────────────  SECTION 4: READER / WRITER  ───────────────────────── -->
  <section class="info-section">
    <div class="info-col">
      <div class="info-image">
        <img src="../images/reader.png" alt="قارئ">
      </div>
      <div class="info-content">
        <h2>إن كنت قارئًا</h2>
        <p>
          ستجد في سرد مكتبة عربية غنية تضم روايات وأعمالًا خالدة من أبرز الكتّاب، مرتبة كأنها رفوف مكتبتك الخاصة.
          تصفّح، اقرأ، واحفظ ما يعجبك لتكمله لاحقًا، في تجربة قراءة هادئة وممتعة مصممة خصيصًا لعشّاق الأدب العربي.
        </p>
        <a href="Browsebooks.php" class="info-cta">ابدأ رحلة القراءة</a>
      </div>
    </div>

    <div class="info-col">
      <div class="info-image">
        <img src="../images/writer.png" alt="كاتب">
      </div>
      <div class="info-content">
        <h2>إن كنت كاتبًا</h2>
        <p>
          سرد يفتح لك بابًا للوصول إلى قرّاء حقيقيين يبحثون عن أعمال جديدة تستحق القراءة.
          شارك رواياتك وأعمالك مع مجتمع من القرّاء، واحصل على انتشار وتقدير أوسع لأسلوبك ككاتب عربي.
        </p>
        <a href="signup.php?role=writer" class="info-cta">انشر أعمالك</a>
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
          <li><a href="HomePage.php">الرئيسية</a></li>
          <li><a href="Browsebooks.php">تصفح الكتب</a></li>
          <li><a href="Writewithus.php">الكتّاب</a></li>
          <li><a href="#">من نحن</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>حسابك</h4>
        <ul>
          <li><a href="signup.php">تسجيل الدخول</a></li>
          <li><a href="signup.php">إنشاء حساب</a></li>
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
            <img id="modalCoverImg" src="../images/نجيب-محفوظ-اللص و الكلاب(2).png" alt="غلاف الكتاب">
        </div>

        <div class="modal-body">
            <h2 class="modal-title" id="modalTitle">اللص والكلاب</h2>
            <div class="modal-author" id="modalAuthor">نجيب محفوظ</div>

            <div class="modal-meta" id="modalMeta">
                <span>رواية</span>
                <span>أدب عربي</span>
                <span>١٩٦١</span>
                <span>١٤٤ صفحة</span>
            </div>

            <p class="modal-desc" id="modalDesc">
                تدور أحداث الرواية حول سعيد مهران بعد خروجه من السجن، وسعيه للانتقام ممن خانوه، في عمل يعكس صراع الإنسان مع المجتمع ومع ذاته. من أبرز أعمال نجيب محفوظ الفلسفية.
            </p>

            <div class="modal-actions">
                <!-- ============================================================
                CHANGED: Added id="readNowBtn" for JavaScript to target
                ============================================================ -->
                <button class="read-now" id="readNowBtn">اقرأ الآن</button>
                <button class="save-btn">حفظ للاحقًا</button>
            </div>
        </div>
    </div>
</div>

  <!-- ─────────────────────────  JAVASCRIPT — CORRECT PATH  ───────────────────────── -->
  <script src="../Script/HomePage.js"></script>

</body>
</html>
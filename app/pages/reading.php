<?php
/* ============================================================
   سرد — Reading Page (reading.php)
   PHP is used here only to receive/prepare book data.
   Replace the block below with a real query against nti_sard,
   e.g. SELECT * FROM books WHERE id = ?  (use PDO + prepared
   statements — the array below is a stand-in for that result).
   ============================================================ */

require_once __DIR__ . "/../core/init.php";

$bookId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

$book = [
    'id'          => $bookId,
    'title'       => 'أولاد حارتنا',
    'author'      => 'نجيب محفوظ',
    'genre'       => 'رواية رمزية',
    'language'    => 'العربية',
    'pages_count' => 312,
    'rating'      => 4.7,
    'description' => 'حكاية حارة مصرية تمتد عبر أجيال، يرويها نجيب محفوظ في نسيج رمزي يمزج بين التاريخ والفلسفة والإنسان.',
    // TODO: point this to the real cover filename inside assets/images
    'cover'       => ROOT . 'assets/images/Background-desk.png',
    'quote'       => 'الحارة لا تنسى، والزمن وحده من يملك أن يُنصف أو يظلم.'
];

$chapters = [
    ['id' => 1, 'title' => 'الفصل الأول: عدلي',      'time' => '9 دقائق',  'status' => 'completed'],
    ['id' => 2, 'title' => 'الفصل الثاني: جبل',       'time' => '11 دقيقة', 'status' => 'completed'],
    ['id' => 3, 'title' => 'الفصل الثالث: رفاعة',     'time' => '14 دقيقة', 'status' => 'current'],
    ['id' => 4, 'title' => 'الفصل الرابع: قاسم',      'time' => '13 دقيقة', 'status' => 'unread'],
    ['id' => 5, 'title' => 'الفصل الخامس: عرفة',      'time' => '16 دقيقة', 'status' => 'unread'],
    ['id' => 6, 'title' => 'الفصل السادس: الخاتمة',   'time' => '8 دقائق',  'status' => 'unread'],
];

// Empty on purpose — comments are loaded/posted from the client.
// A real backend would fetch these with something like:
// SELECT * FROM comments WHERE book_id = ? ORDER BY created_at DESC
$comments = [];

// Each string is one "page" of the open book. A real integration
// would page a chapter's stored HTML/text into chunks like this.
$pages = [
    "في تلك الحارة التي تمتد بين الجبل والصحراء، وُلدت الحكاية الأولى. كان أهلها لا يعرفون عن الفوتوة إلا أنه القانون الذي يحكم كل شيء، وأن الرضا بالقسمة هو السبيل الوحيد للنجاة من غضب الحارة.",
    "قال الجبلاوي في وصيته: لن أترك لكم إلا هذا الوقف، فاحفظوه بينكم، ولا تجعلوا الطمع يفرقكم كما فرّق أولاد آدم من قبلكم. غير أن الوصية سرعان ما نُسيت، وحلّ مكانها صوت الأقوى.",
    "مرّت الأجيال، وتغيّر الفتوات، وبقيت الحارة على حالها؛ تحلم بالعدل وتكتفي بالحكايات. حتى جاء عرفة يحمل قنينة سحرية، لا يعرف أحد ما بداخلها على وجه اليقين.",
];

$related = [
    ['title' => 'ثرثرة فوق النيل', 'cover' => ROOT . 'assets/images/غلاف ثرثرة فوق النيل.jpg'],
    ['title' => 'اللص والكلاب',    'cover' => ROOT . 'assets/images/غلاف اللص والكلاب.jpg'],
    ['title' => 'ميرامار',         'cover' => ROOT . 'assets/images/غلاف ميرامار.jpg'],
];

$today = ['books_read' => 1, 'time_spent' => '45 دقيقة'];
$progress = ['current_page' => 128, 'total_pages' => $book['pages_count']];
$progress['percentage'] = round(($progress['current_page'] / $progress['total_pages']) * 100);

$achievements = [
    ['name' => 'الفصل الأول', 'earned' => true],
    ['name' => '50 صفحة',     'earned' => true],
    ['name' => '100 صفحة',    'earned' => false],
    ['name' => 'إنهاء الكتاب', 'earned' => false],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($book['title']) ?> — سرد</title>
<link rel="icon" href="<?= ROOT ?>assets/images/sarrdd Logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;500;700&family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ROOT ?>assets/css/reading.css">
</head>
<body>

<!-- ============================================================
     NAVBAR — placeholder matching the homepage navbar. Swap this
     markup for the shared header.php include used elsewhere.
     ============================================================ -->
<header class="navbar" role="banner">
  <div class="navbar__inner">
    <a href="<?= ROOT ?>index" class="navbar__logo">
      <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد">
      <span>سرد</span>
    </a>
    <nav class="navbar__links" aria-label="التنقل الرئيسي">
      <a href="<?= ROOT ?>index">الرئيسية</a>
      <a href="<?= ROOT ?>Browsebooks">تصفح الكتب</a>
      <a href="<?= ROOT ?>Writewithus">اكتب معنا</a>
    </nav>
    <div class="navbar__actions">
      <button class="navbar__icon-btn" aria-label="بحث">
        <svg viewBox="0 0 24 24" width="20" height="20"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.6" y2="16.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
      <a href="<?= ROOT ?>signup" class="navbar__cta">حسابي</a>
    </div>
  </div>
</header>

<main class="reading-page">

  <div class="reading-grid">

    <!-- ==========================================================
         RIGHT COLUMN — Book Information
         ========================================================== -->
    <aside class="panel book-info" id="bookInfoPanel" aria-label="معلومات الكتاب">
      <button class="book-info__collapse-toggle" id="bookInfoToggle" aria-expanded="true" aria-controls="bookInfoBody">
        <span>معلومات الكتاب</span>
        <svg class="chev" viewBox="0 0 24 24" width="16" height="16"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>

      <div class="book-info__body" id="bookInfoBody">

        <div class="glass-card book-info__cover-card">
          <img src="<?= htmlspecialchars($book['cover']) ?>" alt="غلاف كتاب <?= htmlspecialchars($book['title']) ?>" class="book-info__cover" onerror="this.classList.add('img-fallback')">
          <h1 class="book-info__title"><?= htmlspecialchars($book['title']) ?></h1>
          <p class="book-info__author"><?= htmlspecialchars($book['author']) ?></p>

          <div class="book-info__meta">
            <span class="meta-chip"><?= htmlspecialchars($book['genre']) ?></span>
            <span class="meta-chip"><?= htmlspecialchars($book['language']) ?></span>
            <span class="meta-chip"><?= (int) $book['pages_count'] ?> صفحة</span>
          </div>

          <div class="book-info__rating" aria-label="التقييم <?= htmlspecialchars($book['rating']) ?> من 5">
            <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.7 7-6.3-3.9L5.7 21l1.7-7-5.4-4.7 7.1-.6z"/></svg>
            <span><?= htmlspecialchars($book['rating']) ?></span>
          </div>

          <p class="book-info__desc"><?= htmlspecialchars($book['description']) ?></p>

          <div class="book-info__buttons">
            <button class="btn btn--gold" id="continueReadingBtn">متابعة القراءة</button>
            <div class="btn-row">
              <button class="btn btn--ghost btn--icon" id="bookmarkToggleBtn" aria-pressed="false">
                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M6 3h12v18l-6-4-6 4V3z"/></svg>
                <span>حفظ العلامة</span>
              </button>
              <button class="btn btn--ghost btn--icon" id="favoriteToggleBtn" aria-pressed="false">
                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M12 21s-7.5-4.9-10-9.3C.4 8.2 2 4.5 5.6 4c2-.3 3.8.7 4.9 2.3C11.6 4.7 13.4 3.7 15.4 4c3.6.5 5.2 4.2 3.6 7.7C19.5 16.1 12 21 12 21z"/></svg>
                <span>المفضلة</span>
              </button>
            </div>
          </div>
        </div>

        <div class="glass-card book-info__progress-card">
          <h2 class="card-title">التقدم اليومي</h2>
          <div class="mini-progress">
            <div class="mini-progress__track"><div class="mini-progress__fill" style="width:0%" data-target="<?= (int) $progress['percentage'] ?>"></div></div>
            <span class="mini-progress__label"><?= (int) $progress['percentage'] ?>٪ مكتمل</span>
          </div>
        </div>

        <div class="glass-card book-info__quote-card">
          <h2 class="card-title">اقتباس من الكتاب</h2>
          <p class="book-quote">"<?= htmlspecialchars($book['quote']) ?>"</p>
        </div>

        <div class="glass-card book-info__related-card">
          <h2 class="card-title">كتب قد تعجبك</h2>
          <ul class="related-list">
            <?php foreach ($related as $r): ?>
            <li class="related-item">
              <img src="<?= htmlspecialchars($r['cover']) ?>" alt="غلاف <?= htmlspecialchars($r['title']) ?>" onerror="this.classList.add('img-fallback')">
              <span><?= htmlspecialchars($r['title']) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

      </div>
    </aside>

    <!-- ==========================================================
         CENTER COLUMN — Open Book
         ========================================================== -->
    <section class="panel open-book-wrap" aria-label="عرض الكتاب">
      <div class="book-container" id="bookContainer">

        <div class="book-atmosphere" aria-hidden="true">
          <span class="light-ray light-ray--1"></span>
          <span class="light-ray light-ray--2"></span>
          <span class="dust dust--1"></span>
          <span class="dust dust--2"></span>
          <span class="dust dust--3"></span>
          <span class="dust dust--4"></span>
          <span class="dust dust--5"></span>
        </div>

        <!-- Signature opening animation: plays once on load, then removes itself -->
        <div class="book-opening-cover" id="bookOpeningCover" aria-hidden="true">
          <span class="book-opening-cover__emblem">سرد</span>
        </div>

        <div class="book" id="book">
          <div class="book__spine" aria-hidden="true"></div>

          <div class="page page--right" id="pageRight">
            <div class="page__inner">
              <p class="page__text" id="pageTextRight" style="font-family:'Amiri', serif;"><?= nl2br(htmlspecialchars($pages[0])) ?></p>
              <span class="page__number">1</span>
            </div>
          </div>

          <div class="page page--left" id="pageLeft">
            <div class="page__inner">
              <p class="page__text" id="pageTextLeft" style="font-family:'Amiri', serif;"><?= nl2br(htmlspecialchars($pages[1] ?? '')) ?></p>
              <span class="page__number">2</span>
            </div>
          </div>

          <div class="page page--flip" id="pageFlip" aria-hidden="true">
            <div class="page__inner">
              <p class="page__text" id="pageTextFlip" style="font-family:'Amiri', serif;"></p>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================
           FLOATING READING TOOLBAR
           ====================================================== -->
      <div class="toolbar glass-card" role="toolbar" aria-label="أدوات القراءة" id="readingToolbar"
           data-total-pages="<?= (int) $progress['total_pages'] ?>" data-start-page="<?= (int) $progress['current_page'] ?>">

        <button class="toolbar__btn toolbar__btn--nav" id="prevPageBtn" aria-label="الصفحة السابقة">
          <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
        </button>

        <div class="toolbar__center">
          <span class="toolbar__page-indicator" id="toolbarPageIndicator">— / —</span>
          <div class="toolbar__dots" id="toolbarDots" aria-hidden="true">
            <span class="toolbar__dot"></span><span class="toolbar__dot"></span><span class="toolbar__dot"></span><span class="toolbar__dot"></span><span class="toolbar__dot"></span>
          </div>
        </div>

        <button class="toolbar__btn" id="zoomBtn" aria-label="تكبير">
          <svg viewBox="0 0 24 24" width="22" height="22"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.6" y2="16.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>

        <button class="toolbar__btn toolbar__btn--text" id="fontSettingsBtn" aria-haspopup="true" aria-expanded="false" aria-label="حجم ونوع الخط">Aa</button>

        <button class="toolbar__btn" id="darkModeBtn" aria-pressed="false" aria-label="الوضع الليلي">
          <svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
        </button>

        <button class="toolbar__btn" id="toolbarBookmarkBtn" aria-pressed="false" aria-label="علامة مرجعية">
          <svg viewBox="0 0 24 24" width="22" height="22"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M6 3h12v18l-6-4-6 4V3z"/></svg>
        </button>

        <button class="toolbar__btn toolbar__btn--nav" id="nextPageBtn" aria-label="الصفحة التالية">
          <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
        </button>

      </div>

      <!-- Settings popover (opened from the "Aa" button) -->
      <div class="settings-popover glass-card" id="settingsPopover" hidden>
        <h3 class="card-title">إعدادات القراءة</h3>
        <label class="settings-row">
          <span>حجم الخط</span>
          <input type="range" min="14" max="28" step="1" value="18" id="fontSizeSlider">
        </label>
        <label class="settings-row">
          <span>نوع الخط</span>
          <select id="fontFamilySelect">
            <option value="'Amiri', serif">Amiri</option>
            <option value="'Noto Naskh Arabic', serif">Noto Naskh Arabic</option>
          </select>
        </label>
        <label class="settings-row">
          <span>تباعد الأسطر</span>
          <input type="range" min="1.4" max="2.4" step="0.1" value="1.9" id="lineHeightSlider">
        </label>
      </div>
    </section>

    <!-- ==========================================================
         LEFT COLUMN — Chapters / Comments
         ========================================================== -->
    <aside class="panel side-panel" id="sidePanel" aria-label="الفصول والتعليقات">

      <button class="side-panel__drawer-toggle" id="drawerToggle" aria-label="فتح لوحة الفصول والتعليقات" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="22" height="22"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>

      <div class="side-panel__body">
        <div class="tabs" role="tablist" aria-label="تبويبات الكتاب">
          <button class="tab tab--active" id="tabChapters" role="tab" aria-selected="true" aria-controls="chaptersPanel">الفصول</button>
          <button class="tab" id="tabComments" role="tab" aria-selected="false" aria-controls="commentsPanel">التعليقات</button>
        </div>

        <!-- CHAPTERS -->
        <div class="tab-panel" id="chaptersPanel" role="tabpanel" aria-labelledby="tabChapters">

          <div class="chapter-search">
            <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.6" y2="16.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input type="search" id="chapterSearchInput" placeholder="ابحث عن فصل...">
          </div>

          <div class="chapters-progress">
            <div class="chapters-progress__track"><div class="chapters-progress__fill" style="width:0%" data-target="<?= (int) $progress['percentage'] ?>"></div></div>
            <span><?= (int) $progress['percentage'] ?>٪ من الكتاب مكتمل</span>
          </div>

          <ul class="chapter-list" id="chapterList">
            <?php foreach ($chapters as $ch): ?>
            <li class="chapter-row chapter-row--<?= htmlspecialchars($ch['status']) ?>" data-title="<?= htmlspecialchars($ch['title']) ?>" tabindex="0">
              <span class="chapter-row__status" aria-hidden="true"></span>
              <span class="chapter-row__title"><?= htmlspecialchars($ch['title']) ?></span>
              <span class="chapter-row__time"><?= htmlspecialchars($ch['time']) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- COMMENTS -->
        <div class="tab-panel" id="commentsPanel" role="tabpanel" aria-labelledby="tabComments" hidden>

          <form class="comment-form" id="commentForm">
            <div class="comment-form__avatar" aria-hidden="true">ز</div>
            <textarea id="commentInput" placeholder="شاركنا رأيك في هذا الفصل..." rows="2" aria-label="اكتب تعليقًا"></textarea>
            <button type="submit" class="btn btn--gold btn--sm">إرسال</button>
          </form>

          <ul class="comment-list" id="commentList">
            <?php if (empty($comments)): ?>
              <li class="comment-empty" id="commentEmptyState">لا توجد تعليقات حتى الآن</li>
            <?php else: foreach ($comments as $c): ?>
              <li class="comment-card">
                <div class="comment-card__avatar" aria-hidden="true"><?= htmlspecialchars(mb_substr($c['name'], 0, 1)) ?></div>
                <div class="comment-card__body">
                  <div class="comment-card__head">
                    <span class="comment-card__name"><?= htmlspecialchars($c['name']) ?></span>
                    <span class="comment-card__date"><?= htmlspecialchars($c['date']) ?></span>
                  </div>
                  <p class="comment-card__text"><?= htmlspecialchars($c['text']) ?></p>
                  <div class="comment-card__actions">
                    <button class="comment-action">إعجاب</button>
                    <button class="comment-action">رد</button>
                  </div>
                </div>
              </li>
            <?php endforeach; endif; ?>
          </ul>
        </div>
      </div>
    </aside>

  </div>

  <!-- ==========================================================
       BOTTOM DASHBOARD
       ========================================================== -->
  <section class="dashboard" aria-label="لوحة القراءة">

    <div class="glass-card dashboard__card">
      <div class="dashboard__icon">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M4 5.5A2.5 2.5 0 016.5 3H19v16H6.5A2.5 2.5 0 004 16.5v-11z"/><path fill="none" stroke="currentColor" stroke-width="1.6" d="M4 16.5A2.5 2.5 0 006.5 19H19"/></svg>
      </div>
      <h3 class="card-title">قراءة اليوم</h3>
      <p class="dashboard__stat"><?= (int) $today['books_read'] ?> كتاب</p>
      <p class="dashboard__substat"><?= htmlspecialchars($today['time_spent']) ?></p>
    </div>

    <div class="glass-card dashboard__card">
      <div class="dashboard__icon">
        <svg viewBox="0 0 24 24" width="24" height="24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 12V6M12 12l4.5 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
      </div>
      <h3 class="card-title">تقدم القراءة</h3>
      <div class="mini-progress">
        <div class="mini-progress__track"><div class="mini-progress__fill" style="width:0%" data-target="<?= (int) $progress['percentage'] ?>"></div></div>
      </div>
      <p class="dashboard__substat">صفحة <?= (int) $progress['current_page'] ?> من <?= (int) $progress['total_pages'] ?> — <?= (int) $progress['percentage'] ?>٪</p>
    </div>

    <div class="glass-card dashboard__card">
      <div class="dashboard__icon">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="1.6" d="M12 2l2.6 5.8L21 8.6l-4.5 4.2L17.6 19 12 15.8 6.4 19l1.1-6.2L3 8.6l6.4-.8z"/></svg>
      </div>
      <h3 class="card-title">الإنجازات</h3>
      <ul class="achievement-list">
        <?php foreach ($achievements as $a): ?>
          <li class="badge <?= $a['earned'] ? 'badge--earned' : 'badge--locked' ?>"><?= htmlspecialchars($a['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </section>

</main>

<!-- Page content passed from PHP to JS for the flip/turn logic -->
<script id="bookPagesData" type="application/json"><?= json_encode($pages, JSON_UNESCAPED_UNICODE) ?></script>

<script src="<?= ROOT ?>assets/js/reading.js"></script>
</body>
</html>
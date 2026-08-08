<?php
/* ============================================================
   سرد — Reading Page (reading.php)
   ============================================================ */

require_once __DIR__ . "/../core/init.php";

// Accept both 'book_id' and 'id' parameters
$bookId = isset($_GET['book_id']) ? (int) $_GET['book_id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 1);

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
            a.name AS author_name,
            c.name_ar AS category_name
        FROM novels n
        JOIN authors a ON n.author_id = a.id
        JOIN categories c ON n.category_id = c.id
        WHERE n.id = :book_id AND n.status = 'published'
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':book_id' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $book = null;
    error_log("Database Error in reading.php: " . $e->getMessage());
}

// If book not found, redirect to BrowseBooks
if (!$book) {
    header('Location: ' . ROOT . 'Browsebooks');
    exit;
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
// CURRENT CHAPTER — resolve ID and fetch content
// ============================================================
$currentChapterId = null;
$currentChapterContent = '';
$currentChapterNumber = 1;

if (isset($_GET['chapter'])) {
    $requestedChapterNumber = (int) $_GET['chapter'];
    foreach ($chapters as $ch) {
        if ((int) $ch['chapter_number'] === $requestedChapterNumber) {
            $currentChapterId = (int) $ch['id'];
            $currentChapterNumber = (int) $ch['chapter_number'];
            break;
        }
    }
}

// If no chapter requested, default to first chapter
if (!$currentChapterId && !empty($chapters)) {
    $currentChapterId = (int) $chapters[0]['id'];
    $currentChapterNumber = (int) $chapters[0]['chapter_number'];
}

// Fetch the actual chapter content from DB
if ($currentChapterId) {
    try {
        $contentStmt = $conn->prepare("SELECT content FROM chapters WHERE id = :id LIMIT 1");
        $contentStmt->execute([':id' => $currentChapterId]);
        $chRow = $contentStmt->fetch(PDO::FETCH_ASSOC);
        if ($chRow && !empty($chRow['content'])) {
            $currentChapterContent = $chRow['content'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching chapter content: " . $e->getMessage());
    }
}

// ============================================================
// READING PROGRESS
// ============================================================
$totalChaptersCount = count($chapters);
$progress = [
    'percentage' => $totalChaptersCount > 0 ? round(($currentChapterNumber / $totalChaptersCount) * 100) : 0,
];

// ============================================================
// SPLIT CONTENT INTO PAGES (preserve HTML formatting)
// ============================================================
$rawContent = !empty($currentChapterContent) ? $currentChapterContent : '<p>لا يوجد محتوى لهذا الفصل بعد.</p>';

// Split by <\/p> tags while keeping the tag itself
$parts = preg_split('/(<\/p>)/i', $rawContent, -1, PREG_SPLIT_DELIM_CAPTURE);
$pages = [];
$currentPage = '';
foreach ($parts as $part) {
    $currentPage .= $part;
    if (strtolower(trim($part)) === '</p>') {
        // Push page when it has enough text (>400 chars)
        if (mb_strlen(strip_tags($currentPage), 'UTF-8') > 400) {
            $pages[] = $currentPage;
            $currentPage = '';
        }
    }
}
// Push any leftover content as final page (even if short)
if (!empty(trim(strip_tags($currentPage)))) {
    $pages[] = $currentPage;
}
// Ensure at least one page — if nothing split, treat entire content as one page
if (empty($pages)) {
    $pages = [$rawContent];
}
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
     NAVBAR
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
      <a href="#" class="navbar__link--active">القراءة</a>
    </nav>
    <div class="navbar__actions">
      <a href="<?= ROOT ?>BookDetails?id=<?= $bookId ?>" class="navbar__back-link" title="العودة لصفحة الكتاب">
        <svg viewBox="0 0 24 24" width="20" height="20"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/></svg>
        <span>العودة للكتاب</span>
      </a>
      <?php if (!isset($_SESSION['user'])): ?>
          <a href="<?= ROOT ?>signup" class="navbar__cta">حساب جديد</a>
      <?php else: ?>
          <a href="<?= ROOT ?>profile" class="navbar__cta" style="background:var(--gold-primary); color:#140b06;"><?= htmlspecialchars($_SESSION['user']['username']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="reading-page">

  <div class="reading-grid">

    <!-- ==========================================================
         LEFT COLUMN — Chapters Panel
         ========================================================== -->
    <aside class="panel side-panel" id="sidePanel" aria-label="الفصول">
      <button class="side-panel__drawer-toggle" id="drawerToggle" aria-label="فتح لوحة الفصول" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="22" height="22"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>

      <div class="side-panel__body">
        <div class="panel-header">
          <h2 class="panel-title">الفصول</h2>
          <span class="panel-badge"><?= count($chapters) ?></span>
        </div>

        <!-- Search -->
        <div class="chapter-search">
          <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.6" y2="16.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input type="search" id="chapterSearchInput" placeholder="ابحث عن فصل...">
        </div>

        <!-- Reading Progress -->
        <div class="chapters-progress">
          <div class="chapters-progress__track">
            <div class="chapters-progress__fill" style="width: <?= $progress['percentage'] ?>%;"></div>
          </div>
          <span><?= $progress['percentage'] ?>% مكتمل</span>
        </div>

        <!-- Chapter List -->
        <ul class="chapter-list" id="chapterList">
          <?php if (!empty($chapters)): ?>
            <?php foreach ($chapters as $ch): ?>
            <li class="chapter-row <?= $ch['chapter_number'] <= 2 ? 'chapter-row--completed' : ($ch['chapter_number'] == 3 ? 'chapter-row--current' : 'chapter-row--unread') ?>" 
                data-title="<?= htmlspecialchars($ch['title']) ?>" 
                tabindex="0"
                onclick="location.href='<?= ROOT ?>reading?book_id=<?= $bookId ?>&chapter=<?= $ch['chapter_number'] ?>'">
              <span class="chapter-row__status" aria-hidden="true"></span>
              <span class="chapter-row__number"><?= sprintf('%02d', $ch['chapter_number']) ?></span>
              <span class="chapter-row__title"><?= htmlspecialchars($ch['title']) ?></span>
              <?php if ($ch['reading_time'] > 0): ?>
                <span class="chapter-row__time"><?= $ch['reading_time'] ?> د</span>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="chapter-row" style="justify-content:center; opacity:0.6; cursor:default;">
              <span>لم يتم إضافة فصول بعد</span>
            </li>
          <?php endif; ?>
        </ul>

        <!-- ==================================================
             NOTES PANEL — "ملاحظاتي"
             (Same visual language as the Chapters panel above.
             Frontend-only, no backend/storage. Designed so the
             `text`/`page` fields on a note can later carry a
             highlight reference without changing this markup.)
             ================================================== -->
        <div class="notes-panel" id="notesPanel"
             data-novel-id="<?= (int) $bookId ?>"
             data-chapter-id="<?= $currentChapterId !== null ? (int) $currentChapterId : '' ?>"
             data-notes-endpoint="<?= ROOT ?>notes"
             data-logged-in="<?= empty($_SESSION['user']['id']) ? '0' : '1' ?>">

          <div class="panel-header notes-panel__header">
            <h2 class="panel-title">ملاحظاتي</h2>
            <span class="panel-badge" id="notesBadge">0</span>
          </div>

          <button type="button" class="notes-add-btn" id="addNoteBtn">
            <span class="notes-add-btn__icon" aria-hidden="true">➕</span>
            <span>إضافة ملاحظة</span>
          </button>

          <!-- Notes Search -->
          <div class="chapter-search notes-search">
            <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.6" y2="16.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input type="search" id="noteSearchInput" placeholder="ابحث في ملاحظاتك...">
          </div>

          <!-- Notes List (rendered by notes.js) -->
          <ul class="note-list" id="noteList"></ul>

          <!-- Empty State (toggled by notes.js) -->
          <div class="notes-empty" id="notesEmptyState">
            <div class="notes-empty__icon" aria-hidden="true">🗒️</div>
            <p class="notes-empty__title">لا توجد ملاحظات بعد</p>
            <p class="notes-empty__hint">وتستطيع إضافة أول ملاحظة باستخدام الزر بالأعلى.</p>
          </div>

        </div>
        <!-- ===== End Notes Panel ===== -->

      </div>
    </aside>

    <!-- ==========================================================
         CENTER COLUMN — 3D FLIP BOOK
         ========================================================== -->
    <section class="panel open-book-wrap" aria-label="عرض الكتاب">
      <div class="book-container" id="bookContainer">

        <!-- Book Atmosphere -->
        <div class="book-atmosphere" aria-hidden="true">
          <span class="light-ray light-ray--1"></span>
          <span class="light-ray light-ray--2"></span>
          <span class="dust dust--1"></span>
          <span class="dust dust--2"></span>
          <span class="dust dust--3"></span>
          <span class="dust dust--4"></span>
          <span class="dust dust--5"></span>
        </div>

        <!-- Opening Animation -->
        <div class="book-opening-cover" id="bookOpeningCover" aria-hidden="true">
          <span class="book-opening-cover__emblem">سرد</span>
        </div>

        <!-- ======================================================
             3D REALISTIC BOOK WRAPPER
             ====================================================== -->
        <div class="book" id="book">
          
          <!-- 3D Spine with depth -->
          <div class="book__spine">
            <div class="book__spine-inner"></div>
            <div class="book__spine-shadow"></div>
          </div>

          <!-- Right Page (First Page) - physically on the right in RTL -->
          <div class="page page--left" id="pageLeft">
            <div class="page__inner">
              <div class="page__paper-texture"></div>
              <div class="page__text" id="pageTextLeft" dir="rtl" style="text-align:right;"><?= $pages[0] ?? '' ?></div>
              <div class="page__number">1</div>
            </div>
          </div>

          <!-- Left Page (Second Page) - physically on the left in RTL -->
          <div class="page page--right" id="pageRight">
            <div class="page__inner">
              <div class="page__paper-texture"></div>
              <div class="page__text" id="pageTextRight" dir="rtl" style="text-align:right;"><?= $pages[1] ?? '' ?></div>
              <div class="page__number">2</div>
            </div>
          </div>

          <!-- ======================================================
               REAL BOOK PAGE TURN MECHANISM (The Core)
               ====================================================== -->
          <div class="page-turn-wrapper" id="pageTurnWrapper">
              
            <!-- Front face (The page being lifted) -->
            <div class="page page-turn page-turn--front" id="pageTurnFront">
              <div class="page__inner page-turn-inner">
                <div class="page__paper-texture"></div>
                <div class="page__text" id="pageTextFront" dir="rtl" style="text-align:right;"></div>
                <div class="page__number" id="pageNumFront"></div>
              </div>
              <div class="page__thickness-edge"></div>
            </div>

            <!-- Back face (The reverse side of the paper) -->
            <div class="page page-turn page-turn--back" id="pageTurnBack">
              <div class="page__inner page-turn-inner">
                <div class="page__paper-texture"></div>
                <div class="page__text" id="pageTextBack" dir="rtl" style="text-align:right;"></div>
                <div class="page__number" id="pageNumBack"></div>
              </div>
              <div class="page__thickness-edge"></div>
            </div>

            <!-- Dynamic shadow under the flipping paper -->
            <div class="page-turn-shadow" id="pageTurnShadow"></div>
          </div>
          <!-- ====================================================== -->

          <!-- Book shadow overlay for depth -->
          <div class="book__shadow-overlay"></div>

        </div>
        <!-- ===== End of 3D Book ===== -->

      </div>

      <!-- ======================================================
           FLOATING READING TOOLBAR WITH SETTINGS POPOVER
           ====================================================== -->
      <div class="toolbar glass-card" role="toolbar" aria-label="أدوات القراءة" id="readingToolbar"
           data-total-pages="<?= count($pages) ?>" data-start-page="1">

        <button class="toolbar__btn toolbar__btn--nav" id="prevPageBtn" aria-label="الصفحة السابقة">
          <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/></svg>
        </button>

        <div class="toolbar__center">
          <span class="toolbar__page-indicator" id="toolbarPageIndicator">1 / <?= count($pages) ?></span>
          <div class="toolbar__dots" id="toolbarDots" aria-hidden="true">
            <span class="toolbar__dot"></span><span class="toolbar__dot"></span><span class="toolbar__dot"></span><span class="toolbar__dot"></span><span class="toolbar__dot"></span>
          </div>
        </div>

        <button class="toolbar__btn" id="zoomBtn" aria-label="تكبير">
          <svg viewBox="0 0 24 24" width="22" height="22"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.6" y2="16.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>

        <!-- Aa Button - Anchor for settings popover -->
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

        <!-- ======================================================
             SETTINGS POPOVER
             ====================================================== -->
        <div class="settings-popover" id="settingsPopover" role="dialog" aria-label="إعدادات القراءة">
          <h3 class="card-title">إعدادات القراءة</h3>

          <!-- Font Size -->
          <div class="settings-row">
            <label>
              <span>حجم الخط</span>
              <span id="fontSizeDisplay">19</span>
            </label>
            <input type="range" min="14" max="28" step="1" value="19" id="fontSizeSlider">
          </div>

          <!-- Font Family -->
          <div class="settings-row">
            <label>نوع الخط</label>
            <select id="fontFamilySelect">
              <option value="'Amiri', serif">Amiri</option>
              <option value="'Noto Naskh Arabic', serif">Noto Naskh Arabic</option>
              <option value="'Tajawal', sans-serif">Tajawal</option>
            </select>
          </div>

          <!-- Line Height -->
          <div class="settings-row">
            <label>
              <span>تباعد الأسطر</span>
              <span id="lineHeightDisplay">1.9</span>
            </label>
            <input type="range" min="1.4" max="2.4" step="0.1" value="1.9" id="lineHeightSlider">
          </div>

          <!-- Theme -->
          <div class="settings-row">
            <label>المظهر</label>
            <div class="settings-themes">
              <button data-theme="light" class="active">
                <span class="theme-icon">☀️</span>
                فاتح
              </button>
              <button data-theme="sepia">
                <span class="theme-icon">📜</span>
                Sepia
              </button>
              <button data-theme="dark">
                <span class="theme-icon">🌙</span>
                داكن
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- ======================================================
           READING PROGRESS BAR
           ====================================================== -->
      <div class="reading-progress-bar">
        <div class="reading-progress__track">
          <div class="reading-progress__fill" style="width: <?= $progress['percentage'] ?>%;"></div>
        </div>
        <div class="reading-progress__info">
          <span class="reading-progress__percent"><?= $progress['percentage'] ?>% مكتمل</span>
          <span class="reading-progress__pages">الفصل <?= $currentChapterNumber ?> من <?= $totalChaptersCount ?></span>
        </div>
        <div class="reading-progress__save">
          <span class="reading-progress__save-indicator">✓ تم حفظ تقدم القراءة تلقائياً</span>
        </div>
      </div>

    </section>

  </div>

</main>

<!-- ============================================================
     NOTE MODAL — "إضافة / تعديل ملاحظة"
     Shared by both Add and Edit flows (notes.js swaps the mode).
     ============================================================ -->
<div class="note-modal-overlay" id="noteModalOverlay">
  <div class="note-modal glass-card" role="dialog" aria-modal="true" aria-labelledby="noteModalTitle" tabindex="-1">

    <button type="button" class="note-modal__close" id="noteModalCloseBtn" aria-label="إغلاق">
      <svg viewBox="0 0 24 24" width="18" height="18"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <h3 class="note-modal__title" id="noteModalTitle">إضافة ملاحظة</h3>
    <p class="note-modal__hint">اكتب أي فكرة، اقتباس، أو ملحوظة تريد الرجوع إليها لاحقاً.</p>

    <textarea
      class="note-modal__textarea"
      id="noteModalTextarea"
      placeholder="اكتب ملاحظتك هنا..."
      rows="6"
      maxlength="500"
    ></textarea>

    <div class="note-modal__footer-row">
      <span class="note-modal__error" id="noteModalError">يرجى كتابة ملاحظة أولاً.</span>
      <span class="note-modal__counter" id="noteModalCounter">0 / 500</span>
    </div>

    <div class="note-modal__actions">
      <button type="button" class="note-modal__btn note-modal__btn--cancel" id="noteModalCancelBtn">إلغاء</button>
      <button type="button" class="note-modal__btn note-modal__btn--save" id="noteModalSaveBtn">
        <span class="note-modal__btn-label">حفظ</span>
        <span class="note-modal__spinner" aria-hidden="true"></span>
      </button>
    </div>
  </div>
</div>

<!-- Toast container for success/feedback messages -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<!-- Page content passed from PHP to JS -->
<script id="bookPagesData" type="application/json"><?= json_encode($pages, JSON_UNESCAPED_UNICODE) ?></script>

<script src="<?= ROOT ?>assets/js/reading.js"></script>
<script src="<?= ROOT ?>assets/js/notes.js"></script>
</body>
</html>
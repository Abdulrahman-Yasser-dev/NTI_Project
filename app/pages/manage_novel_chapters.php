<?php
require_once "../app/core/init.php";

// Get novel_id from URL segment (/manage_novel_chapters/2) or query string
$url_parts = isset($_GET['url']) ? explode('/', trim($_GET['url'], '/')) : [];
$novel_id = isset($url_parts[1]) && (int)$url_parts[1] > 0
    ? (int)$url_parts[1]
    : (isset($_GET['id']) && (int)$_GET['id'] > 0 ? (int)$_GET['id'] : 1);

// Handle Settings Modal POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_novel') {
    $new_title = trim($_POST['novel_title'] ?? '');
    $new_category = (int)($_POST['novel_category'] ?? 0);

    if ($new_title) {
        execute($conn, "UPDATE novels SET title = :title, category_id = :category_id WHERE id = :id", [
            'title'       => $new_title,
            'category_id' => $new_category,
            'id'          => $novel_id,
        ]);
    }

    // Handle cover image upload
    if (!empty($_FILES['cover_image']['name'])) {
        $upload_dir = '../public/assets/images/';
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $filename = 'cover_' . $novel_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $filename)) {
            execute($conn, "UPDATE novels SET cover_image = :cover_image WHERE id = :id", [
                'cover_image' => $filename,
                'id'          => $novel_id,
            ]);
        }
    }

    header("Location: " . ROOT . "manage_novel_chapters/" . $novel_id);
    die;
}

// Fetch Novel details (categories column is name_ar)
$novel_data = query($conn, "
    SELECT n.*, c.name_ar as category_name 
    FROM novels n 
    LEFT JOIN categories c ON n.category_id = c.id 
    WHERE n.id = :id
", ['id' => $novel_id]);

$novel = $novel_data ? $novel_data[0] : null;

if (!$novel) {
    die("الرواية غير موجودة.");
}

// Fetch all categories for the settings dropdown
$categories = query($conn, "SELECT id, name_ar FROM categories ORDER BY name_ar ASC", []);

// Fetch Chapters
$chapters = query($conn, "
    SELECT * 
    FROM chapters 
    WHERE novel_id = :novel_id 
    ORDER BY chapter_number ASC
", ['novel_id' => $novel_id]);

// Calculate published count
$published_count = count(array_filter($chapters, fn($ch) => $ch['isPublished'] == 1));

// Cover image path with fallback
$cover_image = !empty($novel['cover_image'])
    ? ROOT . "assets/images/" . $novel['cover_image']
    : "https://lh3.googleusercontent.com/aida-public/AB6AXuCoYgpJ4ZHMmFIVzlsmUEm6pssKLVOyV9HAzJ8PVnzWS9oogDRHYfMSui41dEUXizaQ41dJlEm9O3yVOkHVTI5PLRFEU6t5ylY2nDFdIWXapLc2cHf5JTylKmo6N4534F7gDIybFhMPRvbZtwmRV2VmAPnzGLq7FBBN5EGHAO5QULTzF6G2PVUNNWbcmuu6AgJw20ssHx1MTHgbPA0_x54Wp7yDHyCbRx6ulpQcFtbgvc9bIJBza08atcz8G_FSSMJWH5IRbMsLmsj0";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفصول — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Amiri:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/Browsebooks.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/manage_novel_chapters.css">
</head>

<body>

    <!-- ============================================================
    NAVBAR WRAPPER
    ============================================================ -->
    <div class="navbar-wrapper">
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
                    <li><a href="<?= ROOT ?>Browsebooks">المكتبة</a></li>
                    <?php if (isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><li><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a></li><?php else: ?><li><a href="<?= ROOT ?>writer_application">كن كاتبا</a></li><?php endif; ?>
                </ul>
                <div class="nav-premium-actions">
                    <?php if (!isset($_SESSION["user"])): ?>
                        <a href="<?= ROOT ?>login" class="nav-btn glass">تسجيل الدخول</a>
                        <a href="<?= ROOT ?>signup" class="nav-btn filled">إنشاء حساب</a>
                    <?php else: ?>
                        <?php if ($_SESSION["user"]["role"] == "admin"): ?>
                            <a href="<?= ROOT ?>admin" class="nav-btn glass">لوحة التحكم</a>
                        <?php endif; ?>
                        <div class="profile-dropdown">
                            <button class="profile-toggle" onclick="toggleProfileMenu()">
                                <?php if (!empty($_SESSION['user']['image'])): ?>
                                    <img src="<?= ROOT ?>assets/images/users/<?= htmlspecialchars($_SESSION['user']['image']) ?>" alt="avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-user-circle"></i>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($_SESSION["user"]["username"]) ?></span>
                                <i class="fa-solid fa-chevron-down text-sm"></i>
                            </button>
                            <div class="profile-menu" id="profileMenu">
                                <a href="<?= ROOT ?>profile"><i class="fa-solid fa-user"></i> حسابي</a>
                                <a href="<?= ROOT ?>logout"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>

    <!-- ============================================================
    MAIN CONTENT
    ============================================================ -->
    <main class="chapters-premium-hall">
        
        <!-- Sidebar -->
        <aside class="premium-sidebar">
            <div class="cover-container">
                <img alt="غلاف رواية <?= htmlspecialchars($novel['title'] ?? '') ?>"
                    src="<?= htmlspecialchars($cover_image) ?>">
            </div>
            <h2><?= htmlspecialchars($novel['title'] ?? 'بدون عنوان') ?></h2>
            <div class="badges">
                <?php if (!empty($novel['category_name'])): ?>
                    <span class="badge-premium"><?= htmlspecialchars($novel['category_name']) ?></span>
                <?php endif; ?>
                <span class="badge-premium-dark"><?= $published_count ?> فصول منشورة</span>
            </div>

            <button class="premium-btn-block btn-outline-gold" id="openSettingsBtn">
                <i class="fas fa-cog"></i> إعدادات الرواية
            </button>
        </aside>

        <!-- Main Content -->
        <section class="premium-main">
            <div class="list-header">
                <h3>إدارة الفصول</h3>
                <div class="sort-wrapper">
                    <button class="premium-btn-block btn-outline-gold" id="sortToggleBtn"
                        style="width: auto; padding: 6px 16px; margin: 0;">
                        <i class="fas fa-sort"></i> <span id="sortLabel">ترتيب</span>
                    </button>
                    <div class="sort-dropdown" id="sortDropdown">
                        <button class="sort-option active" data-sort="manual">
                            <i class="fas fa-grip-vertical"></i>
                            ترتيب يدوي (Drag)
                            <span class="sort-dir"></span>
                        </button>
                        <div class="sort-divider"></div>
                        <button class="sort-option" data-sort="title-asc">
                            <i class="fas fa-sort-alpha-down"></i>
                            ترتيب أبجدي (أ ← ي)
                            <span class="sort-dir">أ→ي</span>
                        </button>
                        <button class="sort-option" data-sort="title-desc">
                            <i class="fas fa-sort-alpha-up"></i>
                            ترتيب أبجدي عكسي
                            <span class="sort-dir">ي→أ</span>
                        </button>
                        <div class="sort-divider"></div>
                        <button class="sort-option" data-sort="date-desc">
                            <i class="fas fa-calendar-alt"></i>
                            الأحدث أولاً
                            <span class="sort-dir">↓</span>
                        </button>
                        <button class="sort-option" data-sort="date-asc">
                            <i class="fas fa-calendar-alt"></i>
                            الأقدم أولاً
                            <span class="sort-dir">↑</span>
                        </button>
                        <div class="sort-divider"></div>
                        <button class="sort-option" data-sort="words-desc">
                            <i class="fas fa-pen-nib"></i>
                            أكثر كلمات أولاً
                            <span class="sort-dir">↓</span>
                        </button>
                        <button class="sort-option" data-sort="words-asc">
                            <i class="fas fa-pen-nib"></i>
                            أقل كلمات أولاً
                            <span class="sort-dir">↑</span>
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($novel['status'] === 'published'): ?>
                <a href="<?= ROOT ?>write_new_chapter_existing_novel/<?= $novel_id ?>" class="add-chapter-premium"
                    style="text-decoration: none;">
                    <i class="fas fa-plus-circle"></i>
                    <span>كتابة فصل جديد</span>
                </a>
            <?php else: ?>
                <div class="status-message">
                    <i class="fas fa-clock text-warning" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <h4>قيد المراجعة</h4>
                    <p>هذه الرواية قيد المراجعة من قبل الإدارة. لا يمكنك إضافة فصول جديدة حتى يتم الموافقة عليها ونشرها.</p>
                </div>
            <?php endif; ?>

            <!-- Dynamic Chapters List -->
            <?php if (!empty($chapters)): ?>
                <?php foreach ($chapters as $chapter):
                    $isDraft = $chapter['isPublished'] == 0;
                    $updatedDate = date('d F', strtotime($chapter['updated_at']));
                    $createdDate = date('d F', strtotime($chapter['created_at']));
                    $wordCount = number_format((int)$chapter['word_count']);

                    // Draft styling
                    $itemStyle = $isDraft ? 'style="border: 2px dashed rgba(212, 166, 74, 0.4); background: rgba(255,255,255,0.5);"' : '';
                    $handleStyle = $isDraft ? 'style="background: transparent;"' : '';
                ?>
                    <div class="chapter-premium-item" draggable="true"
                        data-title="<?= htmlspecialchars($chapter['title']) ?>"
                        data-date="<?= $chapter['created_at'] ?>"
                        data-words="<?= $chapter['word_count'] ?>"
                        <?= $itemStyle ?>>
                        <div class="chapter-info-wrapper">
                            <div class="drag-handle" <?= $handleStyle ?>><i class="fas fa-grip-vertical"></i></div>
                            <div class="chapter-details">
                                <h4>
                                    <?= htmlspecialchars($chapter['title']) ?>
                                    <?php if ($isDraft): ?>
                                        <span class="status-badge-draft">مسودة</span>
                                    <?php endif; ?>
                                </h4>
                                <div class="chapter-meta">
                                    <span><i class="fas fa-history"></i> آخر تعديل: <?= $updatedDate ?></span>
                                    <span><i class="far fa-calendar-alt"></i> <?= $isDraft ? 'جاري الكتابة' : $createdDate ?></span>
                                    <span><i class="fas fa-pen-nib"></i> <?= $wordCount ?> كلمة</span>
                                </div>
                            </div>
                        </div>
                        <div class="chapter-actions">
                            <a href="<?= ROOT ?>write_new_chapter_existing_novel/<?= $novel_id ?>/<?= $chapter['id'] ?>" class="btn" title="تعديل الفصل">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-chapters-message">
                    لا يوجد فصول لهذه الرواية حتى الآن. ابدأ بكتابة الفصل الأول!
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- ============================================================
    FOOTER WRAPPER
    ============================================================ -->
    <footer class="footer-premium">
        <div class="footer-premium-curve"></div>
        <div class="footer-premium-content">
            <div class="footer-premium-brand">
                <span class="footer-premium-logo">سرد</span>
                <p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p>
            </div>
            <div class="footer-premium-links">
                <div class="footer-premium-col">
                    <h4>روابط سريعة</h4><a href="<?= ROOT ?>index">الرئيسية</a><a
                        href="<?= ROOT ?>Browsebooks">المكتبة</a><a href="#">الكتّاب</a><?php if (isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a><?php else: ?><a href="<?= ROOT ?>writer_application">كن كاتبا</a><?php endif; ?>
                </div>
                <div class="footer-premium-col">
                    <h4>حسابك</h4><a href="<?= ROOT ?>signup">تسجيل الدخول</a><a href="<?= ROOT ?>signup">إنشاء حساب</a>
                </div>
                <div class="footer-premium-col">
                    <h4>تواصل معنا</h4><a href="#">الدعم الفني</a><a href="#">الأسئلة الشائعة</a><a href="#">سياسة
                        الخصوصية</a>
                </div>
            </div>
        </div>
        <div class="footer-premium-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <!-- ============================================================
    SETTINGS MODAL
    ============================================================ -->
    <div class="modal-overlay" id="settingsModal">
        <div class="modal-content">
            <button class="modal-close" id="closeSettingsBtn"><i class="fas fa-times"></i></button>
            <h2 class="modal-title">إعدادات الرواية</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_novel">

                <div class="form-group">
                    <label>اسم الرواية</label>
                    <input type="text" name="novel_title" class="form-input"
                        value="<?= htmlspecialchars($novel['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>التصنيف</label>
                    <select name="novel_category" class="form-input">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= $cat['id'] == $novel['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name_ar']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>غلاف الرواية</label>
                    <div class="file-upload-wrapper">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p style="color: var(--text-soft); font-size: 0.9rem;">اسحب وأفلت الصورة هنا أو اضغط لاختيار ملف</p>
                        <input type="file" name="cover_image" accept="image/*">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="premium-btn-block btn-walnut" style="margin: 0; padding: 10px;">
                        <i class="fas fa-save"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        // Navbar Scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });

        // Modal Logic
        const modal = document.getElementById('settingsModal');
        const openBtn = document.getElementById('openSettingsBtn');
        const closeBtn = document.getElementById('closeSettingsBtn');
        openBtn.addEventListener('click', () => {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Drag & Drop Reorder
        const chapterList = document.querySelector('.premium-main');
        let dragSrc = null;

        function getDraggableItems() {
            return [...chapterList.querySelectorAll('.chapter-premium-item[draggable="true"]')];
        }

        chapterList.addEventListener('dragstart', (e) => {
            const item = e.target.closest('.chapter-premium-item[draggable]');
            if (!item) return;
            dragSrc = item;
            requestAnimationFrame(() => item.classList.add('dragging'));
        });

        chapterList.addEventListener('dragend', (e) => {
            const item = e.target.closest('.chapter-premium-item[draggable]');
            if (!item) return;
            item.classList.remove('dragging');
            getDraggableItems().forEach(el => el.classList.remove('drag-over'));
            dragSrc = null;
        });

        chapterList.addEventListener('dragover', (e) => {
            e.preventDefault();
            const target = e.target.closest('.chapter-premium-item[draggable]');
            if (!target || target === dragSrc) return;
            getDraggableItems().forEach(el => el.classList.remove('drag-over'));
            target.classList.add('drag-over');
        });

        chapterList.addEventListener('dragleave', (e) => {
            const target = e.target.closest('.chapter-premium-item[draggable]');
            if (target) target.classList.remove('drag-over');
        });

        chapterList.addEventListener('drop', (e) => {
            e.preventDefault();
            const target = e.target.closest('.chapter-premium-item[draggable]');
            if (!target || !dragSrc || target === dragSrc) return;

            const items = getDraggableItems();
            const srcIdx = items.indexOf(dragSrc);
            const tgtIdx = items.indexOf(target);

            if (srcIdx < tgtIdx) {
                target.after(dragSrc);
            } else {
                target.before(dragSrc);
            }

            target.classList.remove('drag-over');

            dragSrc.style.transition = 'box-shadow 0.3s ease';
            dragSrc.style.boxShadow = '0 0 0 3px rgba(212, 166, 74, 0.4)';
            setTimeout(() => {
                dragSrc.style.boxShadow = '';
            }, 600);
        });

        // Sort Dropdown & Logic
        const sortToggleBtn = document.getElementById('sortToggleBtn');
        const sortDropdown = document.getElementById('sortDropdown');
        const sortLabel = document.getElementById('sortLabel');
        const sortOptions = document.querySelectorAll('.sort-option');

        sortToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sortDropdown.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!sortToggleBtn.contains(e.target) && !sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('open');
            }
        });

        sortOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const sortType = option.getAttribute('data-sort');

                sortOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                sortDropdown.classList.remove('open');

                if (sortType === 'manual') sortLabel.textContent = 'ترتيب يدوي';
                else if (sortType.startsWith('title')) sortLabel.textContent = 'ترتيب أبجدي';
                else if (sortType.startsWith('date')) sortLabel.textContent = 'ترتيب بالزمن';
                else if (sortType.startsWith('words')) sortLabel.textContent = 'ترتيب بالكلمات';

                sortChapters(sortType);
            });
        });

        function sortChapters(sortType) {
            const container = document.querySelector('.premium-main');
            const items = Array.from(container.querySelectorAll('.chapter-premium-item'));

            const isManual = sortType === 'manual';
            items.forEach(item => {
                item.setAttribute('draggable', isManual);
                const handle = item.querySelector('.drag-handle');
                if (handle) {
                    handle.style.opacity = isManual ? '1' : '0.3';
                    handle.style.cursor = isManual ? 'grab' : 'not-allowed';
                }
            });

            if (isManual) return;

            items.sort((a, b) => {
                if (sortType === 'title-asc') {
                    return a.dataset.title.localeCompare(b.dataset.title, 'ar');
                } else if (sortType === 'title-desc') {
                    return b.dataset.title.localeCompare(a.dataset.title, 'ar');
                } else if (sortType === 'date-asc') {
                    return new Date(a.dataset.date) - new Date(b.dataset.date);
                } else if (sortType === 'date-desc') {
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
                } else if (sortType === 'words-asc') {
                    return parseInt(a.dataset.words) - parseInt(b.dataset.words);
                } else if (sortType === 'words-desc') {
                    return parseInt(b.dataset.words) - parseInt(a.dataset.words);
                }
                return 0;
            });

            items.forEach(item => container.appendChild(item));
        }
    </script>
</body>

</html>
<?php
// author_dashboard.php
require_once __DIR__ . "/../core/init.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'writer') {
    header('Location: ' . ROOT . 'index');
    exit;
}

$user = $_SESSION['user'];
$authorId = $user['id']; 

// Fetch novels for this author (lookup via authors table by username)
$novels = [];
try {
    $authorName = $user['username'];
    $authorStmt = $conn->prepare("SELECT id FROM authors WHERE name = :name LIMIT 1");
    $authorStmt->execute([':name' => $authorName]);
    $authorRow = $authorStmt->fetch(PDO::FETCH_ASSOC);
    $authorId = $authorRow ? $authorRow['id'] : 0;

    $stmt = $conn->prepare("SELECT * FROM novels WHERE author_id = :author_id ORDER BY created_at DESC");
    $stmt->execute([':author_id' => $authorId]);
    $novels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    //
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الكاتب — سرد</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/Browsebooks.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/author_dashboard.css">
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
                        <span class="brand-premium-name"></span>
                    </a>
                </div>
                <ul class="nav-premium-links">
                    <li><a href="<?= ROOT ?>index">الرئيسية</a></li>
                    <li><a href="<?= ROOT ?>Browsebooks">المكتبة</a></li>
                    <?php if (isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?><li><a href="<?= ROOT ?>author_dashboard" class="active">لوحة الكاتب</a></li><?php else: ?><li><a href="<?= ROOT ?>writer_application">كن كاتبا</a></li><?php endif; ?>
                </ul>
                <button class="nav-mobile-toggle" id="navMobileToggle" aria-label="القائمة">☰</button>
                <div class="nav-premium-actions">
                    <?php if (!isset($_SESSION["user"])): ?>
                        <a href="<?= ROOT ?>login" class="nav-premium-btn nav-premium-btn-outline">تسجيل الدخول</a>
                        <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-filled">إنشاء حساب</a>
                    <?php else: ?>
                        <?php if ($_SESSION["user"]["role"] == "admin"): ?>
                            <a href="<?= ROOT ?>admin" class="nav-premium-btn nav-premium-btn-outline">لوحة التحكم</a>
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
    MAIN DASHBOARD CARD
    ============================================================ -->
    <div class="dashboard-wrapper">
        
        <div class="dashboard-header">
            <div class="header-left">
                <h1 class="dashboard-title">رواياتي</h1>
                <p class="dashboard-subtitle">إدارة أعمالك المنشورة والمسودات من مكان واحد</p>
            </div>
            <a href="<?= ROOT ?>add_novel" class="add-novel-btn"><i class="fas fa-plus"></i> إضافة رواية جديدة</a>
        </div>

        <?php if (empty($novels)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>لم تقم بإضافة أي روايات بعد</h3>
                <p>ابدأ رحلتك ككاتب وأضف روايتك الأولى الآن.</p>
                <a href="<?= ROOT ?>add_novel" class="add-novel-btn"><i class="fas fa-plus"></i> إضافة رواية جديدة</a>
            </div>
        <?php else: ?>
            <div class="novels-list">
                <?php foreach ($novels as $novel): ?>
                    <div class="novel-card">
                        <img class="novel-cover" src="<?= !empty($novel['cover_image']) ? ROOT . 'assets/images/' . $novel['cover_image'] : ROOT . 'assets/images/sarrdd Logo.png' ?>" alt="<?= htmlspecialchars($novel['title']) ?>">

                        <div class="novel-info">
                            <div class="novel-title"><?= htmlspecialchars($novel['title']) ?></div>
                            <div class="novel-meta">
                                <?php
                                $statusLabel = '';
                                $statusClass = '';
                                switch ($novel['status']) {
                                    case 'published':
                                        $statusLabel = 'منشورة';
                                        $statusClass = 'status-published';
                                        break;
                                    case 'archived':
                                        $statusLabel = 'قيد المراجعة';
                                        $statusClass = 'status-archived';
                                        break;
                                    default:
                                        $statusLabel = 'مسودة';
                                        $statusClass = 'status-draft';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                <span>• مشاهدات: <?= $novel['views'] ?></span>
                            </div>
                        </div>

                        <div class="novel-actions">
                            <a href="<?= ROOT ?>manage_novel_chapters?id=<?= $novel['id'] ?>" class="action-btn btn-outline-dark"><i class="fas fa-list"></i> إدارة الفصول</a>
                            <a href="<?= ROOT ?>write_new_chapter_existing_novel?novel_id=<?= $novel['id'] ?>" class="action-btn primary"><i class="fas fa-pen"></i> كتابة فصل جديد</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- ============================================================
    FOOTER (Same structure as index, matched to Author Dashboard colors)
    ============================================================ -->
    <footer class="site-footer author-footer">
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
                    <?php if(isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?>
                        <li><a href="<?= ROOT ?>author_dashboard">لوحة الكاتب</a></li>
                    <?php else: ?>
                        <li><a href="<?= ROOT ?>writer_application">كن كاتبا</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>حسابك</h4>
                <ul>
                    <li><a href="<?= ROOT ?>login">تسجيل الدخول</a></li>
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

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        function toggleProfileMenu(e) {
            if (e) e.stopPropagation();
            var menu = document.getElementById('profileMenu');
            if (menu) menu.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            var toggle = document.querySelector('.profile-toggle');
            var menu = document.getElementById('profileMenu');
            if (toggle && menu && !toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        var navToggle = document.getElementById('navMobileToggle');
        var navLinks = document.querySelector('.nav-premium-links');
        if (navToggle && navLinks) {
            navToggle.addEventListener('click', function() {
                navLinks.classList.toggle('is-open');
                navToggle.textContent = navLinks.classList.contains('is-open') ? '✕' : '☰';
            });
        }
    </script>
</body>
</html>
<?php
require_once "../app/core/init.php";


$url_parts = isset($_GET['url']) ? explode('/', trim($_GET['url'], '/')) : [];
$novel_id   = isset($url_parts[1]) && (int)$url_parts[1] > 0
    ? (int)$url_parts[1]
    : (isset($_GET['novel_id']) && (int)$_GET['novel_id'] > 0 ? (int)$_GET['novel_id'] : 1);
$chapter_id = isset($url_parts[2]) && (int)$url_parts[2] > 0
    ? (int)$url_parts[2]
    : (isset($_GET['id']) && (int)$_GET['id'] > 0 ? (int)$_GET['id'] : null);
$chapter = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch novel status
    $novelCheck = query($conn, "SELECT status FROM novels WHERE id = :id", ['id' => $novel_id]);
    if (!$novelCheck || $novelCheck[0]['status'] !== 'published') {
        die("لا يمكن إضافة أو تعديل فصول لرواية غير منشورة.");
    }

    $title = $_POST['title'] ?? 'بدون عنوان';
    $content = $_POST['content'] ?? '';
    $word_count = (int)($_POST['word_count'] ?? 0);
    $isPublished = (int)($_POST['isPublished'] ?? 0);
    $ch_id = isset($_POST['chapter_id']) && !empty($_POST['chapter_id']) ? (int)$_POST['chapter_id'] : null;

    if ($ch_id) {
        // Update existing
        $query = "UPDATE chapters SET title = :title, content = :content, word_count = :word_count, isPublished = :isPublished WHERE id = :id AND novel_id = :novel_id";
        execute($conn, $query, [
            'title' => $title,
            'content' => $content,
            'word_count' => $word_count,
            'isPublished' => $isPublished,
            'id' => $ch_id,
            'novel_id' => $novel_id
        ]);
        $chapter_id = $ch_id; // keep it set
    } else {
        // Insert new
        // Auto increment chapter_number
        $max = query($conn, "SELECT MAX(chapter_number) as max_num FROM chapters WHERE novel_id = :novel_id", ['novel_id' => $novel_id]);
        $next_num = ($max && $max[0]['max_num']) ? (int)$max[0]['max_num'] + 1 : 1;

        $query = "INSERT INTO chapters (novel_id, chapter_number, title, content, word_count, isPublished) VALUES (:novel_id, :chapter_number, :title, :content, :word_count, :isPublished)";
        execute($conn, $query, [
            'novel_id' => $novel_id,
            'chapter_number' => $next_num,
            'title' => $title,
            'content' => $content,
            'word_count' => $word_count,
            'isPublished' => $isPublished
        ]);
        $chapter_id = $conn->lastInsertId();
    }

    // Redirect to manage page
    header("Location: " . ROOT . "manage_novel_chapters/" . $novel_id);
    die;
}

// Fetch novel status for page load
$novelCheckLoad = query($conn, "SELECT status FROM novels WHERE id = :id", ['id' => $novel_id]);
if (!$novelCheckLoad || $novelCheckLoad[0]['status'] !== 'published') {
    die("لا يمكن إضافة أو تعديل فصول لرواية غير منشورة.");
}

// Fetch existing chapter if editing
if ($chapter_id) {
    $res = query($conn, "SELECT * FROM chapters WHERE id = :id AND novel_id = :novel_id", [
        'id' => $chapter_id,
        'novel_id' => $novel_id
    ]);
    if ($res) {
        $chapter = $res[0];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كتابة فصل — سرد</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Amiri:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/Browsebooks.css">
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/write_new_chapter.css">
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

    <main class="editor-premium-hall">

        <!-- Sidebar Metadata -->
        <aside class="editor-premium-sidebar">
            <h3>تفاصيل الفصل</h3>

            <div class="form-premium-group">
                <label>عنوان الفصل</label>
                <input type="text" class="form-premium-input" id="titleInput" value="<?= htmlspecialchars($chapter['title'] ?? '') ?>">
            </div>

            <div class="meta-stats" style="margin-top: auto;">
                <div class="stat-row">
                    <span>عدد الكلمات</span>
                    <span id="wordCount"><?= number_format((int)($chapter['word_count'] ?? 0)) ?></span>
                </div>
                <div class="stat-row">
                    <span>حالة الحفظ</span>
                    <span class="status-saved"><i class="fas fa-check-circle"></i>
                        <?= $chapter ? ($chapter['isPublished'] ? 'منشور' : 'مسودة') : 'جديد' ?>
                    </span>
                </div>
            </div>
        </aside>

        <!-- Editor Core -->
        <section class="editor-premium-core">
            <!-- Toolbar -->
            <div class="toolbar-premium">
                <a href="<?= ROOT ?>manage_novel_chapters/<?= $novel_id ?>" class="back-btn">
                    <i class="fas fa-arrow-right"></i> إدارة الفصول
                </a>
                <div class="toolbar-divider"></div>
                <div class="toolbar-group">
                    <button class="toolbar-btn" id="btn-bold" title="غامق (Ctrl+B)"><i class="fas fa-bold"></i></button>
                    <button class="toolbar-btn" id="btn-italic" title="مائل (Ctrl+I)"><i class="fas fa-italic"></i></button>
                    <button class="toolbar-btn" id="btn-underline" title="تسطير (Ctrl+U)"><i class="fas fa-underline"></i></button>
                </div>
                <div class="toolbar-divider"></div>
                <div class="toolbar-group">
                    <button class="toolbar-btn active" id="btn-justifyRight" title="محاذاة لليمين"><i class="fas fa-align-right"></i></button>
                    <button class="toolbar-btn" id="btn-justifyCenter" title="توسيط"><i class="fas fa-align-center"></i></button>
                    <button class="toolbar-btn" id="btn-justifyLeft" title="محاذاة لليسار"><i class="fas fa-align-left"></i></button>
                </div>
            </div>

            <!-- Canvas -->
            <div class="canvas-premium">
                <h1 class="editor-title" id="editorTitle" contenteditable="true"><?= htmlspecialchars($chapter['title'] ?? '') ?></h1>
                <div class="editor-content" id="editorContent" contenteditable="true" data-placeholder="اكتب حكايتك هنا..."><?= $chapter['content'] ?? '' ?></div>
            </div>
        </section>

    </main>

    <!-- Floating Save Bar -->
    <div class="editor-action-bar">
        <a href="<?= ROOT ?>manage_novel_chapters/<?= $novel_id ?>" class="action-btn btn-ghost" style="text-decoration:none;">تجاهل التغييرات</a>
        <div class="toolbar-divider" style="height: 20px;"></div>
        <button class="action-btn btn-draft" id="saveDraftBtn">حفظ مسودة</button>
        <button class="action-btn btn-publish" id="publishBtn">
            <i class="fas fa-paper-plane"></i> نشر الفصل
        </button>
    </div>

    <!-- Hidden form for submission -->
    <form id="saveForm" method="POST" style="display:none;">
        <input type="hidden" name="chapter_id" value="<?= $chapter_id ?? '' ?>">
        <input type="hidden" name="title" id="formTitle">
        <input type="hidden" name="content" id="formContent">
        <input type="hidden" name="word_count" id="formWordCount">
        <input type="hidden" name="isPublished" id="formIsPublished">
    </form>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script>
        // ─── Navbar scroll ───────────────────────────────────────────────────────
        window.addEventListener('scroll', () => {
            document.getElementById('navbar')
                .classList.toggle('scrolled', window.scrollY > 50);
        });

        // ─── Element refs ─────────────────────────────────────────────────────────
        const editorContent = document.getElementById('editorContent');
        const editorTitle = document.getElementById('editorTitle');
        const titleInput = document.getElementById('titleInput');
        const wordCountEl = document.getElementById('wordCount');

        // ─── Toolbar formatting ───────────────────────────────────────────────────
        // Using execCommand — well-supported for contenteditable rich text.
        // preventDefault on mousedown is the key trick: it prevents the editor
        // from losing focus (and thus losing the user's selection) when the
        // toolbar button is clicked.
        const formatCmds = {
            'btn-bold': 'bold',
            'btn-italic': 'italic',
            'btn-underline': 'underline',
            'btn-justifyRight': 'justifyRight',
            'btn-justifyCenter': 'justifyCenter',
            'btn-justifyLeft': 'justifyLeft',
        };

        Object.entries(formatCmds).forEach(([id, cmd]) => {
            const btn = document.getElementById(id);
            if (!btn) return;

            // Prevent focus loss on click — critical for keeping selection alive
            btn.addEventListener('mousedown', (e) => e.preventDefault());

            btn.addEventListener('click', () => {
                // Make sure the editor is the active contenteditable
                const sel = window.getSelection();
                const inEditor = editorContent.contains(sel.anchorNode) ||
                    editorTitle.contains(sel.anchorNode);

                if (!inEditor) editorContent.focus();

                document.execCommand(cmd, false, null);
                updateToolbarState();
                editorContent.dispatchEvent(new Event('input')); // trigger word count
            });
        });

        // ─── Toolbar active state ─────────────────────────────────────────────────
        // Listen to selectionchange on the document so we know exactly what
        // formatting is applied at the cursor/selection at all times.
        const formattingBtns = ['bold', 'italic', 'underline'];
        const alignBtns = {
            'justifyRight': 'btn-justifyRight',
            'justifyCenter': 'btn-justifyCenter',
            'justifyLeft': 'btn-justifyLeft',
        };

        function updateToolbarState() {
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;
            const inEditor = editorContent.contains(sel.anchorNode) ||
                editorTitle.contains(sel.anchorNode);
            if (!inEditor) return;

            // Formatting buttons
            formattingBtns.forEach(cmd => {
                const btn = document.getElementById('btn-' + cmd);
                if (btn) btn.classList.toggle('active', document.queryCommandState(cmd));
            });

            // Alignment buttons — only one active at a time
            Object.entries(alignBtns).forEach(([cmd, btnId]) => {
                const btn = document.getElementById(btnId);
                if (btn) btn.classList.toggle('active', document.queryCommandState(cmd));
            });
        }

        document.addEventListener('selectionchange', updateToolbarState);

        // ─── Bidirectional title sync ─────────────────────────────────────────────
        let syncingTitle = false;

        // Sidebar input → editor h1
        titleInput.addEventListener('input', () => {
            if (syncingTitle) return;
            syncingTitle = true;
            editorTitle.textContent = titleInput.value;
            syncingTitle = false;
        });

        // Editor h1 → sidebar input
        editorTitle.addEventListener('input', () => {
            if (syncingTitle) return;
            syncingTitle = true;
            titleInput.value = editorTitle.textContent;
            syncingTitle = false;
        });

        // ─── Live word count ──────────────────────────────────────────────────────
        function updateWordCount() {
            const text = editorContent.innerText.trim();
            const count = text === '' ? 0 : text.split(/\s+/).length;
            wordCountEl.textContent = count.toLocaleString('ar-EG');
        }

        editorContent.addEventListener('input', updateWordCount);
        // Run once on load
        updateWordCount();

        // ─── Keyboard shortcuts (Ctrl/Cmd) ────────────────────────────────────────
        // The browser already handles Ctrl+B/I/U for execCommand natively inside
        // contenteditable, but we update the toolbar active state after each one.
        editorContent.addEventListener('keyup', updateToolbarState);
        editorTitle.addEventListener('keyup', updateToolbarState);

        // ─── Ensure new lines create <p> tags cleanly ─────────────────────────────
        editorContent.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                // Let the browser handle it, but ensure we get a proper <p>
                // execCommand insertParagraph gives cleaner output than default <div>
                e.preventDefault();
                document.execCommand('insertParagraph', false, null);
            }
        });

        // ─── Save & Publish Logic ────────────────────────────────────────────────
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        const publishBtn = document.getElementById('publishBtn');
        const saveForm = document.getElementById('saveForm');

        function submitForm(isPublished) {
            document.getElementById('formTitle').value = titleInput.value.trim();
            document.getElementById('formContent').value = editorContent.innerHTML;

            // Calculate word count
            const text = editorContent.innerText.trim();
            const count = text === '' ? 0 : text.split(/\s+/).length;
            document.getElementById('formWordCount').value = count;

            document.getElementById('formIsPublished').value = isPublished;

            saveForm.submit();
        }

        saveDraftBtn.addEventListener('click', () => submitForm(0));
        publishBtn.addEventListener('click', () => submitForm(1));
    </script>
</body>

</html>
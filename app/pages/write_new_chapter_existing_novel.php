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
    <style>
        .editor-premium-hall {
            max-width: 1300px;
            margin: 0 auto;
            padding: 100px 2rem 40px;
            display: flex;
            gap: 30px;
            min-height: calc(100vh - 200px);
        }
        
        @media (max-width: 900px) {
            .editor-premium-hall {
                flex-direction: column;
            }
        }

        /* Sidebar Meta */
        .editor-premium-sidebar {
            flex: 0 0 300px;
            background: #FFFFFF;
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow-premium);
            border: 1px solid rgba(44, 26, 14, 0.03);
            display: flex;
            flex-direction: column;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        .editor-premium-sidebar h3 {
            font-family: 'Aref Ruqaa', serif;
            font-size: 1.6rem;
            color: var(--walnut);
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(44, 26, 14, 0.05);
            padding-bottom: 12px;
        }

        .form-premium-group {
            margin-bottom: 24px;
        }
        .form-premium-group label {
            display: block;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .form-premium-input {
            width: 100%;
            background: var(--cream);
            border: 1px solid rgba(44, 26, 14, 0.08);
            border-radius: 12px;
            padding: 12px 16px;
            font-family: 'Tajawal', sans-serif;
            color: var(--text-dark);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-premium-input:focus {
            outline: none;
            border-color: var(--gold);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px var(--gold-glow);
        }
        textarea.form-premium-input {
            resize: vertical;
            min-height: 120px;
        }

        .meta-stats {
            margin-top: auto;
            background: var(--cream-warm);
            border-radius: 12px;
            padding: 16px;
        }
        .meta-stats .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-family: 'Tajawal', sans-serif;
        }
        .meta-stats .stat-row:last-child { margin-bottom: 0; }
        .meta-stats .stat-row span:first-child { color: var(--text-soft); font-family: 'Cairo', sans-serif; font-size: 0.8rem; }
        .meta-stats .stat-row span:last-child { color: var(--walnut); font-weight: 700; }
        .status-saved {
            color: #388E3C !important;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Editor Area */
        .editor-premium-core {
            flex: 1;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: var(--shadow-premium);
            border: 1px solid rgba(44, 26, 14, 0.03);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .toolbar-premium {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(44, 26, 14, 0.06);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .toolbar-group {
            display: flex;
            background: var(--cream);
            border-radius: 8px;
            padding: 4px;
            border: 1px solid rgba(44, 26, 14, 0.04);
        }
        .toolbar-btn {
            background: transparent;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            color: var(--text-soft);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toolbar-btn:hover, .toolbar-btn.active {
            background: #FFFFFF;
            color: var(--gold);
            box-shadow: var(--shadow-soft);
        }
        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--cream);
            border: 1px solid rgba(44, 26, 14, 0.08);
            color: var(--walnut);
            border-radius: 10px;
            padding: 8px 16px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: auto;
        }
        .back-btn:hover {
            background: var(--gold-glow);
            border-color: rgba(212, 166, 74, 0.4);
            transform: translateX(3px);
        }
        .back-btn i { font-size: 0.8rem; }
        .toolbar-divider {
            width: 1px;
            height: 24px;
            background: rgba(44, 26, 14, 0.08);
        }

        .canvas-premium {
            flex: 1;
            padding: 40px 60px 80px;
            background: url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><filter id="noise"><feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="4" stitchTiles="stitch"/></filter><rect width="100" height="100" filter="url(%23noise)" opacity="0.015"/></svg>'), #FFFFFF;
            overflow-y: auto;
        }
        .canvas-premium:focus { outline: none; }
        
        .editor-title {
            font-family: 'Aref Ruqaa', serif;
            font-size: 2.4rem;
            color: var(--walnut);
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed rgba(212, 166, 74, 0.3);
            outline: none;
        }
        .editor-content {
            font-family: 'Amiri', serif;
            font-size: 1.3rem;
            line-height: 2.2;
            color: var(--text-dark);
            text-align: justify;
            outline: none;
            min-height: 400px;
        }
        .editor-content p {
            margin-bottom: 24px;
        }

        /* Action Bar */
        .editor-action-bar {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 40px;
            padding: 12px 24px;
            box-shadow: 0 10px 40px rgba(44, 26, 14, 0.15);
            border: 1px solid rgba(44, 26, 14, 0.05);
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 100;
        }
        .action-btn {
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 10px 24px;
            border-radius: 30px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-ghost {
            background: transparent;
            color: var(--text-soft);
        }
        .btn-ghost:hover { color: #D32F2F; background: rgba(211, 47, 47, 0.05); }
        .btn-draft {
            background: transparent;
            border: 1.5px solid var(--gold);
            color: var(--walnut);
        }
        .btn-draft:hover {
            background: var(--gold-glow);
        }
        .btn-publish {
            background: linear-gradient(145deg, var(--gold-light), var(--gold));
            color: var(--walnut);
            box-shadow: 0 4px 15px rgba(212, 166, 74, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-publish:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 166, 74, 0.3);
        }
    </style>
</head>
<body>

    <!-- NAVBAR PREMIUM (Copied from Browsebooks.php) -->
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
                <li><a href="#">الكتّاب</a></li>
                <li><a href="#">من نحن</a></li>
            </ul>
            <div class="nav-premium-actions">
                <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-outline">تسجيل الدخول</a>
                <a href="<?= ROOT ?>signup" class="nav-premium-btn nav-premium-btn-filled">إنشاء حساب</a>
            </div>
        </div>
    </nav>

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
                    <button class="toolbar-btn" id="btn-bold"    title="غامق (Ctrl+B)"><i class="fas fa-bold"></i></button>
                    <button class="toolbar-btn" id="btn-italic"  title="مائل (Ctrl+I)"><i class="fas fa-italic"></i></button>
                    <button class="toolbar-btn" id="btn-underline" title="تسطير (Ctrl+U)"><i class="fas fa-underline"></i></button>
                </div>
                <div class="toolbar-divider"></div>
                <div class="toolbar-group">
                    <button class="toolbar-btn active" id="btn-justifyRight"  title="محاذاة لليمين"><i class="fas fa-align-right"></i></button>
                    <button class="toolbar-btn"        id="btn-justifyCenter" title="توسيط"><i class="fas fa-align-center"></i></button>
                    <button class="toolbar-btn"        id="btn-justifyLeft"   title="محاذاة لليسار"><i class="fas fa-align-left"></i></button>
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

    <!-- FOOTER PREMIUM (Copied from Browsebooks.php) -->
    <footer class="footer-premium">
        <div class="footer-premium-curve"></div>
        <div class="footer-premium-content">
            <div class="footer-premium-brand">
                <span class="footer-premium-logo">سرد</span>
                <p>مكتبة عربية رقمية تجمع القرّاء والكتّاب في مكان واحد، احتفاءً بالأدب العربي بكل تنوعه.</p>
            </div>
            <div class="footer-premium-links">
                <div class="footer-premium-col"><h4>روابط سريعة</h4><a href="<?= ROOT ?>index">الرئيسية</a><a href="<?= ROOT ?>Browsebooks">المكتبة</a><a href="#">الكتّاب</a><a href="#">من نحن</a></div>
                <div class="footer-premium-col"><h4>حسابك</h4><a href="<?= ROOT ?>signup">تسجيل الدخول</a><a href="<?= ROOT ?>signup">إنشاء حساب</a></div>
                <div class="footer-premium-col"><h4>تواصل معنا</h4><a href="#">الدعم الفني</a><a href="#">الأسئلة الشائعة</a><a href="#">سياسة الخصوصية</a></div>
            </div>
        </div>
        <div class="footer-premium-bottom">
            <span>© 2026 سرد. جميع الحقوق محفوظة.</span>
            <span>صُنع بحب لمحبي القراءة والكتابة العربية</span>
        </div>
    </footer>

    <script>
        // ─── Navbar scroll ───────────────────────────────────────────────────────
        window.addEventListener('scroll', () => {
            document.getElementById('navbar')
                .classList.toggle('scrolled', window.scrollY > 50);
        });

        // ─── Element refs ─────────────────────────────────────────────────────────
        const editorContent = document.getElementById('editorContent');
        const editorTitle   = document.getElementById('editorTitle');
        const titleInput    = document.getElementById('titleInput');
        const wordCountEl   = document.getElementById('wordCount');

        // ─── Toolbar formatting ───────────────────────────────────────────────────
        // Using execCommand — well-supported for contenteditable rich text.
        // preventDefault on mousedown is the key trick: it prevents the editor
        // from losing focus (and thus losing the user's selection) when the
        // toolbar button is clicked.
        const formatCmds = {
            'btn-bold':      'bold',
            'btn-italic':    'italic',
            'btn-underline': 'underline',
            'btn-justifyRight':  'justifyRight',
            'btn-justifyCenter': 'justifyCenter',
            'btn-justifyLeft':   'justifyLeft',
        };

        Object.entries(formatCmds).forEach(([id, cmd]) => {
            const btn = document.getElementById(id);
            if (!btn) return;

            // Prevent focus loss on click — critical for keeping selection alive
            btn.addEventListener('mousedown', (e) => e.preventDefault());

            btn.addEventListener('click', () => {
                // Make sure the editor is the active contenteditable
                const sel = window.getSelection();
                const inEditor = editorContent.contains(sel.anchorNode)
                              || editorTitle.contains(sel.anchorNode);

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
            'justifyRight':  'btn-justifyRight',
            'justifyCenter': 'btn-justifyCenter',
            'justifyLeft':   'btn-justifyLeft',
        };

        function updateToolbarState() {
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0) return;
            const inEditor = editorContent.contains(sel.anchorNode)
                          || editorTitle.contains(sel.anchorNode);
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
        editorTitle.addEventListener('keyup',   updateToolbarState);

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
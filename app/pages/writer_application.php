<?php
// writer_application.php
require_once __DIR__ . "/../core/init.php";

if (!isset($_SESSION['user'])) {
    header('Location: ' . ROOT . 'login');
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];
$successMessage = '';
$errorMessage = '';

// Check if user is already an author or has a pending application
try {
    $stmt = $conn->prepare("SELECT role, writer_request_status FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData['role'] === 'writer') {
        header('Location: ' . ROOT . 'author_dashboard');
        exit;
    }
    
    if ($userData['writer_request_status'] === 'pending') {
        $successMessage = 'لديك طلب قيد المراجعة بالفعل. سنتواصل معك قريباً.';
    } elseif ($userData['writer_request_status'] === 'approved') {
        header('Location: ' . ROOT . 'author_dashboard');
        exit;
    }
} catch (PDOException $e) {
    $errorMessage = 'حدث خطأ في قاعدة البيانات.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($successMessage)) {
    $fullName = trim($_POST['full_name'] ?? '');
    $requestDetails = trim($_POST['request_details'] ?? '');
    $novelName = trim($_POST['novel_name'] ?? '');
    
    if (empty($fullName) || empty($requestDetails) || empty($novelName)) {
        $errorMessage = 'الرجاء تعبئة جميع الحقول المطلوبة.';
    } else {
        try {
            $conn->beginTransaction();
            
            // Insert into writer_requests
            $stmt = $conn->prepare("INSERT INTO writer_requests (user_id, full_name, request_details, novel_name, status) VALUES (:user_id, :full_name, :request_details, :novel_name, 'pending')");
            $stmt->execute([
                ':user_id' => $userId,
                ':full_name' => $fullName,
                ':request_details' => $requestDetails,
                ':novel_name' => $novelName
            ]);
            
            // Update user status
            $stmt = $conn->prepare("UPDATE users SET writer_request_status = 'pending' WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            
            $conn->commit();
            $successMessage = 'تم إرسال طلبك بنجاح. سيتم مراجعته من قبل الإدارة.';
            
            // Update session if needed
            $_SESSION['user']['writer_request_status'] = 'pending';
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $errorMessage = 'حدث خطأ أثناء تقديم الطلب: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كن كاتباً — سرد</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <!-- Shared Browsebooks CSS -->
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/Browsebooks.css" />
    
    <style>
        /* ─── RESET & PAGE BACKGROUND ─── */
        body {
            background-color: #F7F1E8; /* SARD warm cream */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 0; 
            margin: 0;
            font-family: 'Tajawal', sans-serif;
        }

        /* ============================================================
           ISOLATED NAVBAR (OVERRIDE FOR FIXED)
           ============================================================ */
        .navbar-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 0 10px;
            box-sizing: border-box;
            margin-top: 20px;
        }

        .navbar-premium {
            width: min(92%, 1450px);
            background: rgba(44, 26, 14, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 0 2rem;
            box-shadow: 0 10px 35px rgba(44,26,14,.15);
            transition: .3s ease;
            z-index: 1000;
            box-sizing: border-box;
            /* CRITICAL: Overrides the 'fixed' property from Browsebooks.css */
            position: relative !important; 
            left: 0 !important;
            right: 0 !important;
            top: 0 !important;
            transform: none !important;
            align-self: center; 
            margin: 0 auto;
        }
        .navbar-premium-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            min-height: 68px;
        }

        /* ============================================================
           HERO GRID - IMAGE LEFT, FORM RIGHT
           ============================================================ */
        .writer-hero {
            width: min(92%, 1300px);
            /* EXACT FIX: Forces the hero down 65px to create the visible cream gap */
            margin: 65px auto 70px;
            display: grid;
            grid-template-columns: 1fr 0.85fr;
            gap: 65px;
            align-items: center;
            /* Forces visual grid to LTR so Column 1 = Left, Column 2 = Right */
            direction: ltr;
        }

        /* ─── LEFT COLUMN: WRITER ILLUSTRATION ─── */
        .writer-image-col {
            grid-column: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            direction: rtl;
            /* Fine tuning: 15px downward shift to align visual center with the form */
            padding-top: 15px;
        }

        .writer-illustration {
            width: 100%;
            max-width: 700px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        /* ─── RIGHT COLUMN: FORM ─── */
        .writer-form-col {
            grid-column: 2;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding-top: 0;
            direction: rtl;
            /* Ensure no negative margins are shifting this element up */
            margin-top: 0;
        }

        .application-card {
            width: 100%;
            max-width: 500px;
            background: #FFFFFF;
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: 0 20px 60px rgba(44, 26, 14, 0.08);
            border: 1px solid rgba(59, 36, 23, 0.04);
            box-sizing: border-box;
            /* Ensure no hidden transformations are shifting this element */
            transform: none !important;
        }

        /* ─── FORM TYPOGRAPHY ─── */
        .application-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .application-header h1 {
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: 1.85rem;
            color: #3A2418;
            margin: 0 0 10px;
            line-height: 1.5;
        }
        .application-header h1 i {
            color: #D8A43A;
            margin-left: 8px;
        }
        .application-header p {
            color: #7A695D;
            font-size: 0.92rem;
            line-height: 1.8;
            margin: 0;
        }

        /* ─── FORM INPUTS ─── */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2C1A0E;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 15px 17px;
            border: 1px solid #E5D7C7;
            border-radius: 13px;
            background: #FFFEFC;
            color: #2C1A0E;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.98rem;
            transition: all 0.25s ease;
            box-sizing: border-box;
        }
        .form-control::placeholder {
            color: #A8988A;
        }
        .form-control:hover {
            border-color: #D6C3AE;
        }
        .form-control:focus {
            border-color: #D8A43A;
            background: #FFFFFF;
            outline: none;
            box-shadow: 0 0 0 4px rgba(216, 164, 58, 0.10);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* ─── SUBMIT BUTTON ─── */
        .submit-btn {
            background: #3A2418;
            color: #FFFFFF;
            border: none;
            padding: 15px 30px;
            border-radius: 13px;
            font-size: 1rem;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            width: 100%;
            font-weight: 700;
            transition: all 0.25s ease;
            box-shadow: 0 6px 18px rgba(58, 36, 24, 0.14);
            margin-top: 8px;
        }
        .submit-btn:hover {
            background: #4D3021;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(58, 36, 24, 0.20);
        }

        /* ─── ALERTS ─── */
        .alert {
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            border: 1px solid transparent;
            font-family: 'Tajawal', sans-serif;
        }
        .alert-success {
            background: #F0F4EB;
            color: #3A5A2F;
            border-color: #D7E3CC;
        }
        .alert-error {
            background: #FBECEB;
            color: #962C2C;
            border-color: #F3D8D7;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1100px) {
            .writer-hero { gap: 50px; }
            .writer-illustration { max-width: 580px; }
        }

        @media (max-width: 820px) {
            .writer-hero {
                width: 92%;
                grid-template-columns: 1fr;
                gap: 35px;
                margin-top: 45px; /* Slightly less gap on tablet */
                margin-bottom: 45px;
                direction: ltr;
            }
            .writer-form-col {
                grid-column: 1;
                justify-content: center;
                direction: rtl;
                padding-top: 0;
            }
            .application-card { max-width: 520px; }
            
            .writer-image-col {
                grid-column: 1;
                justify-content: center;
                direction: rtl;
                max-width: 500px;
                margin: 0 auto;
            }
            .writer-illustration { max-width: 500px; }
        }

        @media (max-width: 550px) {
            .application-card { padding: 28px 20px; border-radius: 18px; }
            .application-header h1 { font-size: 1.5rem; }
            .application-header p { font-size: 0.88rem; }
            .writer-illustration { max-width: 100%; }
            .navbar-premium { padding: 0 1rem; border-radius: 16px; width: 96%; }
            .navbar-wrapper { padding: 0 5px; }
        }
    </style>
</head>
<body>

    <!-- ============================================================
    ISOLATED NAVBAR CONTAINER
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
                    <li><a href="<?= ROOT ?>writer_application" class="active">كن كاتبا</a></li>
                </ul>

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
    HERO SECTION - IMAGE LEFT, FORM RIGHT
    ============================================================ -->
    <div class="writer-hero">
        
        <!-- LEFT: WRITER ILLUSTRATION -->
        <div class="writer-image-col">
            <img src="<?= ROOT ?>assets/images/writer2.png" alt="كاتب يكتب في مكتبه" class="writer-illustration" />
        </div>

        <!-- RIGHT: REGISTRATION FORM -->
        <div class="writer-form-col">
            <div class="application-card">
                <div class="application-header">
                    <h1><i class="fa-regular fa-pen-to-square"></i> انضم إلى كتّاب سرد</h1>
                    <p>شارك إبداعاتك مع مجتمع كبير من القرّاء الشغوفين بالأدب.</p>
                </div>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
                    </div>
                    <div style="text-align:center; margin-top:20px;">
                        <a href="<?= ROOT ?>index" style="color: #3A2418; text-decoration: underline; font-weight:600;">العودة للرئيسية</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="full_name">الاسم الثلاثي</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required placeholder="أدخل اسمك الحقيقي">
                        </div>
                        
                        <div class="form-group">
                            <label for="novel_name">اسم الرواية المقترحة أو الحالية</label>
                            <input type="text" id="novel_name" name="novel_name" class="form-control" required placeholder="ما هو العمل الذي تود نشره؟">
                        </div>
                        
                        <div class="form-group">
                            <label for="request_details">نبذة عن الرواية وعنك</label>
                            <textarea id="request_details" name="request_details" class="form-control" required placeholder="تحدث عن فكرة الرواية، خبراتك السابقة في الكتابة، وأي تفاصيل أخرى مفيدة..."></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">إرسال الطلب</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ============================================================
    JAVASCRIPT - Profile Dropdown
    ============================================================ -->
    <script>
        function toggleProfileMenu() {
            var menu = document.getElementById('profileMenu');
            if(menu) {
                menu.classList.toggle('show');
            }
        }

        window.addEventListener('click', function(e) {
            var toggle = document.querySelector('.profile-toggle');
            var menu = document.getElementById('profileMenu');
            if (toggle && menu) {
                if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                }
            }
        });
    </script>

</body>
</html>
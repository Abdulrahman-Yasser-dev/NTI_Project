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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- Global CSS for consistency -->
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/index.css" />
    <style>
        .application-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .application-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .application-header h1 {
            color: #d4af37; /* Gold */
            margin-bottom: 10px;
        }
        .application-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #d4af37;
            outline: none;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        .submit-btn {
            background: #d4af37;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            background: #b8962c;
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    
    <!-- Navbar (simplified for this page) -->
                <nav class="navbar" id="navbar" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 15px 5%;">
        <a href="<?= ROOT ?>index" class="nav-brand" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="<?= ROOT ?>assets/images/sarrdd Logo.png" alt="سرد" style="height: 40px;">
            <span style="font-family: 'Aref Ruqaa', serif; font-size: 1.5rem; color: #8b5a2b; font-weight: bold;">سرد</span>
        </a>
        <ul class="nav-links" style="display: flex; gap: 20px; list-style: none; margin: 0; padding: 0;">
            <li><a href="<?= ROOT ?>index" style="text-decoration: none; color: #333; font-weight: 500;">الرئيسية</a></li>
            <li><a href="<?= ROOT ?>Browsebooks" style="text-decoration: none; color: #333; font-weight: 500;">تصفح الكتب</a></li>
            <?php if(isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "writer"): ?>
                <li><a href="<?= ROOT ?>author_dashboard" style="text-decoration: none; color: #8b5a2b; font-weight: 600;">لوحة الكاتب</a></li>
            <?php else: ?>
                <li><a href="<?= ROOT ?>writer_application" style="text-decoration: none; color: #8b5a2b; font-weight: 600;">كن كاتبا</a></li>
            <?php endif; ?>
        </ul>
        <div class="nav-actions" style="display: flex; gap: 15px; align-items: center;">
            <?php if(!isset($_SESSION["user"])):?>
                <a href="<?= ROOT ?>login" class="nav-btn glass" style="text-decoration: none; padding: 8px 20px; border-radius: 20px; border: 1px solid #8b5a2b; color: #8b5a2b;">تسجيل الدخول</a>
                <a href="<?= ROOT ?>signup" class="nav-btn filled" style="text-decoration: none; padding: 8px 20px; border-radius: 20px; background: #8b5a2b; color: white;">إنشاء حساب</a>
            <?php else: ?>
                <?php if($_SESSION["user"]["role"]== "admin"):?>
                    <a href="<?= ROOT ?>admin" class="nav-btn glass" style="text-decoration: none; padding: 8px 20px; border-radius: 20px; border: 1px solid #8b5a2b; color: #8b5a2b;">لوحة التحكم</a>
                <?php endif; ?>
                <div class="profile-dropdown" style="position: relative;">
                    <button class="profile-toggle" onclick="this.nextElementSibling.classList.toggle('show')" style="background: none; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: inherit;">
                        <?php if(!empty($_SESSION['user']['image'])): ?>
                            <img src="<?= ROOT ?>assets/images/users/<?= htmlspecialchars($_SESSION['user']['image']) ?>" alt="avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fa-solid fa-user-circle" style="font-size: 1.5rem; color: #8b5a2b;"></i>
                        <?php endif; ?>
                        <span style="font-weight: 600; color: #333;"><?= htmlspecialchars($_SESSION["user"]["username"]) ?></span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: #666;"></i>
                    </button>
                    <div class="profile-menu" style="display: none; position: absolute; top: 100%; left: 0; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px; padding: 10px 0; min-width: 150px; flex-direction: column; z-index: 1001;">
                        <a href="<?= ROOT ?>profile" style="padding: 10px 20px; text-decoration: none; color: #333; display: block;"><i class="fa-solid fa-user" style="width: 20px;"></i> حسابي</a>
                        <a href="<?= ROOT ?>logout" style="padding: 10px 20px; text-decoration: none; color: #d32f2f; display: block;"><i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> تسجيل الخروج</a>
                    </div>
                </div>
                <style>
                    .profile-menu.show { display: flex !important; }
                    .profile-menu a:hover { background: #f5f5f5; }
                </style>
            <?php endif; ?>
        </div>
    </nav>

    <div class="application-container">
        <div class="application-header">
            <h1>انضم إلى كتّاب سرد</h1>
            <p>شارك إبداعاتك مع مجتمع كبير من القرّاء الشغوفين بالأدب.</p>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
            </div>
            <div style="text-align:center; margin-top:20px;">
                <a href="<?= ROOT ?>index" style="color: #d4af37; text-decoration: underline;">العودة للرئيسية</a>
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

</body>
</html>

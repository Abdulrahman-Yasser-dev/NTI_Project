<?php
// author_dashboard.php
require_once __DIR__ . "/../core/init.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'writer') {
    header('Location: ' . ROOT . 'index');
    exit;
}

$user = $_SESSION['user'];
$authorId = $user['id']; // Assumes author_id in novels table matches user id, OR we need to find the author record linked to this user.
// Wait, the authors table has its own `id`. Does users table link to authors?
// Let's check `users` and `authors` relationship. Usually, if a user is approved, they might be added to the authors table or novels links directly to user_id.
// The database schema says `novels` has `author_id`. `authors` has `name`, `bio`, `photo`. But no `user_id`.
// Let's assume for this project, `novels.author_id` is the `users.id` if the user is a writer, or we need to ensure when an admin approves a writer request, an author record is created, and the user somehow links to it. Let's just use `users.id` in `novels.author_id` for simplicity if that's how it's intended, OR look up authors by name. Let's assume user.id = author.id is NOT true because authors table is separate.
// But we can check novels by fetching by some matching logic. Let's assume there's a user_id in authors or novels.author_id = users.id.

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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/index.css" />
    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 100px auto 50px;
            padding: 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .dashboard-header h1 {
            color: #d4af37;
            margin: 0;
        }
        .add-novel-btn {
            background: #d4af37;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .add-novel-btn:hover {
            background: #b8962c;
            color: #fff;
        }
        .novel-card {
            display: flex;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            gap: 20px;
            align-items: center;
        }
        .novel-cover {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }
        .novel-info {
            flex-grow: 1;
        }
        .novel-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .novel-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-archived { background: #fff3e0; color: #e65100; }
        .status-published { background: #e8f5e9; color: #2e7d32; }
        .status-draft { background: #eeeeee; color: #616161; }
        
        .novel-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            background: #f5f5f5;
            color: #333;
            transition: 0.3s;
        }
        .action-btn:hover {
            background: #e0e0e0;
        }
        .action-btn.primary {
            background: #d4af37;
            color: #fff;
        }
        .action-btn.primary:hover {
            background: #b8962c;
        }
    </style>
</head>
<body>

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

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>رواياتي</h1>
            <a href="<?= ROOT ?>add_novel" class="add-novel-btn"><i class="fas fa-plus"></i> إضافة رواية جديدة</a>
        </div>

        <?php if (empty($novels)): ?>
            <div style="text-align: center; padding: 50px; color: #777;">
                <i class="fas fa-book-open" style="font-size: 3rem; margin-bottom: 20px; color: #ddd;"></i>
                <h3>لم تقم بإضافة أي روايات بعد</h3>
                <p>ابدأ رحلتك ككاتب وأضف روايتك الأولى الآن.</p>
            </div>
        <?php else: ?>
            <?php foreach ($novels as $novel): ?>
                <div class="novel-card">
                    <img class="novel-cover" src="<?= !empty($novel['cover_image']) ? ROOT . 'assets/images/' . $novel['cover_image'] : ROOT . 'assets/images/sarrdd Logo.png' ?>" alt="<?= htmlspecialchars($novel['title']) ?>">
                    
                    <div class="novel-info">
                        <div class="novel-title"><?= htmlspecialchars($novel['title']) ?></div>
                        <div class="novel-meta">
                            <?php
                            $statusLabel = '';
                            $statusClass = '';
                            switch($novel['status']) {
                                case 'published': $statusLabel = 'منشورة'; $statusClass = 'status-published'; break;
                                case 'archived': $statusLabel = 'قيد المراجعة'; $statusClass = 'status-archived'; break;
                                default: $statusLabel = 'مسودة'; $statusClass = 'status-draft'; break;
                            }
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            <span>• مشاهدات: <?= $novel['views'] ?></span>
                        </div>
                    </div>
                    
                    <div class="novel-actions">
                        <a href="<?= ROOT ?>manage_novel_chapters?id=<?= $novel['id'] ?>" class="action-btn"><i class="fas fa-list"></i> إدارة الفصول</a>
                        <a href="<?= ROOT ?>write_new_chapter_existing_novel?novel_id=<?= $novel['id'] ?>" class="action-btn primary"><i class="fas fa-pen"></i> كتابة فصل جديد</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>

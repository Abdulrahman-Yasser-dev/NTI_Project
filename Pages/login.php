<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: ../index.php");
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة";
        }
    } else {
        $error = "البريد الإلكتروني غير مسجل";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="../Style/style.css">
</head>
<body>
    <div class="auth-form">
        <div class="brand">سرد</div>
        <p class="brand-sub">حيث يبدأ كل حكاية</p>

        <h1>تسجيل الدخول</h1>
        <p class="subtitle">عالم من الروايات في انتظارك</p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@email.com" required>

            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="كلمة المرور" required>

            <button type="submit">دخول</button>
        </form>

        <p class="switch-link">مفيش حساب؟ <a href="signup.php">إنشاء حساب جديد</a></p>
    </div>
</body>
</html>
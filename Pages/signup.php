<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "كلمة المرور غير متطابقة";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "البريد الإلكتروني مستخدم بالفعل";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "حدث خطأ، حاول مرة أخرى";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب</title>
    <link rel="stylesheet" href="../Style/style.css">
</head>
<body>
    <div class="auth-form">
        <div class="brand">سرد</div>
        <p class="brand-sub">حيث يبدأ كل حكاية</p>

        <h1>إنشاء حساب</h1>
        <p class="subtitle">انضم إلى مجتمع القراء والكتّاب</p>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="signup.php">
            <label>الاسم الكامل</label>
            <input type="text" name="username" placeholder="اسم المستخدم" required>

            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@email.com" required>

            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <p class="hint">٨ أحرف على الأقل • حرف كبير وحرف صغير • رقم واحد على الأقل</p>

            <label>تأكيد كلمة المرور</label>
            <input type="password" name="confirm_password" placeholder="إعادة إدخال كلمة المرور" required>

            <button type="submit">إنشاء حساب</button>
        </form>

        <p class="switch-link">عندك حساب بالفعل؟ <a href="login.php">سجل دخول</a></p>
    </div>
</body>
</html>
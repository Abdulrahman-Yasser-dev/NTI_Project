<?php
require_once "../app/core/init.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT id, username, password, role FROM users WHERE email = :email";
    $result = query($conn, $query, ["email" => $email]);

    if ($result) {
        $row = $result[0];
        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = [
                "id"       => $row['id'],
                "username" => $row['username'],
                "role"     => $row['role']
            ];
            header("Location: index");
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
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/style.css">
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

        <form method="POST" action="login">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@email.com" required>
            <p class="field-rule" id="rule-email">صيغة بريد إلكتروني صحيحة</p>

            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <p class="field-rule" id="rule-password">أدخل كلمة المرور</p>

            <button type="submit">دخول</button>
        </form>

        <p class="switch-link">مفيش حساب؟ <a href="signup">إنشاء حساب جديد</a></p>
    </div>

    <script src="<?= ROOT ?>assets/js/login.js"></script>
</body>

</html>
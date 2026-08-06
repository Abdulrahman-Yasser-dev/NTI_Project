<?php
require_once "../app/core/init.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "كلمة المرور غير متطابقة";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "SELECT id FROM users WHERE email = :email";
        $result = query($conn, $query, ["email" => $email]);

        if (!empty($result)) {
            $error = "البريد الإلكتروني مستخدم بالفعل";
        } else {
            $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";

            $success = execute($conn, $query, ["username" => $username, "email" => $email, "password" => $hashed_password]);
            if ($success) {
                header("Location: login");
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
    <link rel="stylesheet" href="<?= ROOT ?>assets/css/style.css">
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

        <form method="POST" action="signup">
            <label>الاسم الكامل</label>
            <input type="text" name="username" placeholder="اسم المستخدم" required>
            <p class="field-rule" id="rule-username">٣ أحرف على الأقل</p>

            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@email.com" required>
            <p class="field-rule" id="rule-email">صيغة بريد إلكتروني صحيحة</p>

            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <ul class="password-rules">
                <li id="rule-length">٨ أحرف منهم حرف واحد كبير على الاقل</li>
                <li id="rule-case">يجب ان يحتوى على @او #</li>
                <li id="rule-number">رقم واحد على الأقل</li>
            </ul>

            <label>تأكيد كلمة المرور</label>
            <input type="password" name="confirm_password" placeholder="إعادة إدخال كلمة المرور" required>
            <p class="field-rule" id="rule-confirm">يجب أن تطابق كلمة المرور</p>

            <button type="submit">إنشاء حساب</button>
        </form>

        <p class="switch-link">عندك حساب بالفعل؟ <a href="login">سجل دخول</a></p>
    </div>

    <script src="<?= ROOT ?>assets/js/signup.js"></script>
</body>

</html>
<?php
session_start();
?>

<?php if (isset($_SESSION['username'])): ?>
    <p>أهلاً <?php echo $_SESSION['username']; ?></p>
    <a href="Pages/logout.php">تسجيل الخروج</a>
<?php else: ?>
    <a href="Pages/login.php">تسجيل الدخول</a>
    <a href="Pages/signup.php">إنشاء حساب</a>
<?php endif; ?>
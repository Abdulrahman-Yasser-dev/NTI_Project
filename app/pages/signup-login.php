<?php
require_once "../app/core/init.php";

/*
 * ============================================================
 * BACKEND LOGIC BELOW IS UNCHANGED FROM THE OLD login.php AND
 * signup.php (same queries, same fields, same session shape,
 * same redirects, same Arabic error strings).
 *
 * The only additions are:
 *  - a $_POST['form_type'] switch so one file can handle both
 *    the login form and the signup form that now live on the
 *    same page.
 *  - combining first_name + last_name into the single
 *    "username" value the old backend expects.
 *  - redirecting signup success to this combined page instead
 *    of the old standalone "login" page (which no longer
 *    exists on its own).
 *
 * NOT persisted (old backend has no field/column for them):
 * "phone", "role", "remember me" — see notes further down.
 * ============================================================
 */

$login_error   = null;
$signup_error  = null;
$active_tab    = 'login'; // default tab shown on first load

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type'])) {

    // ================= LOGIN (identical to old login.php) =================
    if ($_POST['form_type'] === 'login') {
        $active_tab = 'login';

        $email = $_POST['email'];
        $password = $_POST['password'];

        $query = "SELECT id, username, password, role, image FROM users WHERE email = :email";
        $result = query($conn, $query, ["email" => $email]);

        if ($result) {
            $row = $result[0];
            if (password_verify($password, $row['password'])) {
                $_SESSION['user'] = [
                    "id"       => $row['id'],
                    "username" => $row['username'],
                    "role"     => $row['role'],
                    "image"    => $row['image']
                ];
                header("Location: index");
                exit();
            } else {
                $login_error = "كلمة المرور غير صحيحة";
            }
        } else {
            $login_error = "البريد الإلكتروني غير مسجل";
        }
    }

    // ================= SIGNUP (identical to old signup.php) =================
    elseif ($_POST['form_type'] === 'signup') {
        $active_tab = 'signup';

        // Old backend expects one "username" field. New design collects
        // first + last name separately, so we combine them here only —
        // the query/insert below is untouched.
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $username   = trim($first_name . ' ' . $last_name);

        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // NOTE: $_POST['phone'] and $_POST['role'] are intentionally not
        // read here. The old backend's INSERT only ever wrote
        // username/email/password. Wiring phone/role in would require
        // adding columns and editing the INSERT below — a backend change
        // I have not made without your confirmation.

        if ($password !== $confirm_password) {
            $signup_error = "كلمة المرور غير متطابقة";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "SELECT id FROM users WHERE email = :email";
            $result = query($conn, $query, ["email" => $email]);

            if (!empty($result)) {
                $signup_error = "البريد الإلكتروني مستخدم بالفعل";
            } else {
                $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";

                $success = execute($conn, $query, ["username" => $username, "email" => $email, "password" => $hashed_password]);
                if ($success) {
                    // Old code redirected to the standalone "login" page.
                    // That page is now merged into this file, so we redirect
                    // back here — it defaults to the login tab.
                    header("Location: signup-login");
                    exit();
                } else {
                    $signup_error = "حدث خطأ، حاول مرة أخرى";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>سرد — منصة القراء والكتّاب</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Merged stylesheet. Adjust this path to match where you place the
       file in the project (old pages used <?= ROOT ?>assets/css/...). -->
  <link rel="stylesheet" href="<?= ROOT ?>assets/css/signup-login.css">
</head>
<body>

<div class="stage">
  <div class="card" role="region" aria-label="نموذج الدخول وإنشاء الحساب">

    <span class="corner corner-tl"></span>
    <span class="corner corner-tr"></span>
    <span class="corner corner-bl"></span>
    <span class="corner corner-br"></span>

    <div class="brand">
      <h1>سرد</h1>
      <p>منصّة تجمع القرّاء والكتّاب تحت غلاف واحد</p>
    </div>

    <div class="gold-ornament">
      <span class="line"></span>
      <span class="diamond"></span>
      <span class="line"></span>
    </div>

    <div class="tabs">
      <button class="tab <?= $active_tab === 'login' ? 'active' : '' ?>" id="tabLogin" type="button">تسجيل الدخول</button>
      <button class="tab <?= $active_tab === 'signup' ? 'active' : '' ?>" id="tabSignup" type="button">إنشاء حساب</button>
      <span class="tab-indicator <?= $active_tab === 'signup' ? 'signup' : '' ?>" id="tabIndicator"></span>
    </div>

    <div class="panels">

      <!-- LOGIN -->
      <form class="panel <?= $active_tab === 'login' ? 'active' : '' ?>" id="loginPanel" method="POST" action="signup-login" novalidate>
        <input type="hidden" name="form_type" value="login">

        <div class="field" id="loginEmailField">
          <label for="loginEmail">البريد الإلكتروني</label>
          <input type="email" id="loginEmail" name="email" placeholder="name@example.com" autocomplete="email" required>
          <div class="hint"></div>
        </div>

        <div class="field" id="loginPasswordField">
          <label for="loginPassword">كلمة المرور</label>
          <input type="password" id="loginPassword" name="password" placeholder="••••••••" autocomplete="current-password" required>
          <div class="hint"></div>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" id="remember" name="remember">
            تذكرني
          </label>
          <a href="#">نسيت كلمة المرور؟</a>
        </div>

        <button class="submit" type="submit">دخول</button>

        <div class="form-msg <?= $login_error ? 'error' : '' ?>" id="loginMsg"><?= $login_error ? htmlspecialchars($login_error) : '' ?></div>
      </form>

      <!-- SIGNUP -->
      <form class="panel <?= $active_tab === 'signup' ? 'active' : '' ?>" id="signupPanel" method="POST" action="signup-login" novalidate>
        <input type="hidden" name="form_type" value="signup">

        <div class="grid-2col">
          <div class="field" id="signupFirstNameField">
            <label for="signupFirstName">الاسم الأول</label>
            <input type="text" id="signupFirstName" name="first_name" placeholder="أحمد" autocomplete="given-name" required>
            <div class="hint"></div>
          </div>

          <div class="field" id="signupLastNameField">
            <label for="signupLastName">الاسم الأخير</label>
            <input type="text" id="signupLastName" name="last_name" placeholder="المصري" autocomplete="family-name" required>
            <div class="hint"></div>
          </div>
        </div>

        <div class="grid-2col">
          <div class="field" id="signupEmailField">
            <label for="signupEmail">البريد الإلكتروني</label>
            <input type="email" id="signupEmail" name="email" placeholder="name@example.com" autocomplete="email" required>
            <div class="hint"></div>
          </div>

          <div class="field" id="signupPhoneField">
            <label for="signupPhone">رقم الهاتف</label>
            <input type="tel" id="signupPhone" name="phone" placeholder="05xxxxxxxx" autocomplete="tel" required>
            <div class="hint"></div>
          </div>
        </div>

        <!-- Not persisted by the old backend (no column in the old INSERT).
             Kept in the UI per the new design; see PHP notes above. -->
        <div class="field field-full">
          <label>أنا هنا بصفتي</label>
          <div class="role-choice">
            <input type="radio" name="role" id="roleReader" value="reader" checked>
            <label for="roleReader">قارئ</label>
            <input type="radio" name="role" id="roleWriter" value="writer">
            <label for="roleWriter">كاتب</label>
          </div>
        </div>

        <div class="grid-2col">
          <div class="field" id="signupPasswordField">
            <label for="signupPassword">كلمة المرور</label>
            <input type="password" id="signupPassword" name="password" placeholder="8 أحرف على الأقل" autocomplete="new-password" required>
            <div class="hint"></div>
          </div>

          <div class="field" id="signupConfirmField">
            <label for="signupConfirm">تأكيد كلمة المرور</label>
            <input type="password" id="signupConfirm" name="confirm_password" placeholder="أعد كتابة كلمة المرور" autocomplete="new-password" required>
            <div class="hint"></div>
          </div>
        </div>

        <button class="submit" type="submit">إنشاء الحساب</button>

        <div class="form-msg <?= $signup_error ? 'error' : '' ?>" id="signupMsg"><?= $signup_error ? htmlspecialchars($signup_error) : '' ?></div>
      </form>

    </div>

    <div class="switch-line" id="switchLine">
      <?php if ($active_tab === 'signup'): ?>
        لديك حساب بالفعل؟ <button type="button" id="goLogin">سجّل الدخول</button>
      <?php else: ?>
        ليس لديك حساب؟ <button type="button" id="goSignup">أنشئ حسابًا الآن</button>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Merged script. Adjust this path to match your project layout. -->
<script src="<?= ROOT ?>assets/js/signup-login.js"></script>

</body>
</html>
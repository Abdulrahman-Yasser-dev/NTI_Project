<?php
require_once __DIR__ . "/../app/core/init.php";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>سرد — منصة القراء والكتّاب</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@400;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome for quill icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="stylesheet" href="signup.css">
</head>
<body>

<div class="stage">
  <div class="card" role="region" aria-label="نموذج الدخول وإنشاء الحساب">

    <!-- decorative corners -->
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
      <button class="tab active" id="tabLogin" type="button">تسجيل الدخول</button>
      <button class="tab" id="tabSignup" type="button">إنشاء حساب</button>
      <span class="tab-indicator" id="tabIndicator"></span>
    </div>

    <div class="panels">

      <!-- LOGIN -->
      <form class="panel active" id="loginPanel" novalidate>
        <div class="field" id="loginEmailField">
          <label for="loginEmail">البريد الإلكتروني</label>
          <input type="email" id="loginEmail" placeholder="name@example.com" autocomplete="email" required>
          <div class="hint"></div>
        </div>

        <div class="field" id="loginPasswordField">
          <label for="loginPassword">كلمة المرور</label>
          <input type="password" id="loginPassword" placeholder="••••••••" autocomplete="current-password" required>
          <div class="hint"></div>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" id="remember">
            تذكرني
          </label>
          <a href="#">نسيت كلمة المرور؟</a>
        </div>

        <button class="submit" type="submit">
          دخول
        </button>

        <div class="form-msg" id="loginMsg"></div>
      </form>

      <!-- SIGNUP - 2 COLUMN LAYOUT -->
      <form class="panel" id="signupPanel" novalidate>
        
        <!-- Row 1: First Name + Last Name -->
        <div class="grid-2col">
          <div class="field" id="signupFirstNameField">
            <label for="signupFirstName">الاسم الأول</label>
            <input type="text" id="signupFirstName" placeholder="أحمد" autocomplete="given-name" required>
            <div class="hint"></div>
          </div>

          <div class="field" id="signupLastNameField">
            <label for="signupLastName">الاسم الأخير</label>
            <input type="text" id="signupLastName" placeholder="المصري" autocomplete="family-name" required>
            <div class="hint"></div>
          </div>
        </div>

        <!-- Row 2: Email + Phone -->
        <div class="grid-2col">
          <div class="field" id="signupEmailField">
            <label for="signupEmail">البريد الإلكتروني</label>
            <input type="email" id="signupEmail" placeholder="name@example.com" autocomplete="email" required>
            <div class="hint"></div>
          </div>

          <div class="field" id="signupPhoneField">
            <label for="signupPhone">رقم الهاتف</label>
            <input type="tel" id="signupPhone" placeholder="05xxxxxxxx" autocomplete="tel" required>
            <div class="hint"></div>
          </div>
        </div>

        <!-- Row 3: Role (full width) -->
        <div class="field field-full">
          <label>أنا هنا بصفتي</label>
          <div class="role-choice">
            <input type="radio" name="role" id="roleReader" value="reader" checked>
            <label for="roleReader">قارئ</label>
            <input type="radio" name="role" id="roleWriter" value="writer">
            <label for="roleWriter">كاتب</label>
          </div>
        </div>

        <!-- Row 4: Password + Confirm Password (side by side) -->
        <div class="grid-2col">
          <div class="field" id="signupPasswordField">
            <label for="signupPassword">كلمة المرور</label>
            <input type="password" id="signupPassword" placeholder="8 أحرف على الأقل" autocomplete="new-password" required>
            <div class="hint"></div>
          </div>

          <div class="field" id="signupConfirmField">
            <label for="signupConfirm">تأكيد كلمة المرور</label>
            <input type="password" id="signupConfirm" placeholder="أعد كتابة كلمة المرور" autocomplete="new-password" required>
            <div class="hint"></div>
          </div>
        </div>

        <button class="submit" type="submit">
          إنشاء الحساب
        </button>

        <div class="form-msg" id="signupMsg"></div>
      </form>

    </div>

    <div class="switch-line" id="switchLine">
      ليس لديك حساب؟ <button type="button" id="goSignup">أنشئ حسابًا الآن</button>
    </div>

  </div>
</div>

<script src="signup.js"></script>

</body>
</html>
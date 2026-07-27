<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب — سرد</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Style/signup.css">
</head>
<body>

    <!-- ============================================================
    BACKGROUND SECTION
    ============================================================ -->
    <div class="background-container" role="presentation" aria-hidden="true">
        <div class="background-image"></div>
        <div class="overlay-gradient"></div>
    </div>

    <!-- ============================================================
    MAIN CONTAINER
    ============================================================ -->
    <main class="signup-container" role="main" aria-label="إنشاء حساب جديد">

        <!-- ============================================================
        SIGNUP CARD — 500px Wide
        ============================================================ -->
        <div class="signup-card glass-card" role="dialog" aria-labelledby="signup-title">

            <!-- Brand Header -->
            <div class="brand-header">
                <div class="logo-wrapper">
                    <span class="logo-text">سرد</span>
                </div>
                <p class="brand-tagline">حيث تبدأ كل حكاية</p>
            </div>

            <!-- Form Header -->
            <div class="form-header">
                <h1 id="signup-title" class="form-title">إنشاء حساب</h1>
                <p class="form-subtitle">انضم إلى مجتمع القرّاء والكتّاب</p>
            </div>

            <!-- ============================================================
            SIGNUP FORM
            ============================================================ -->
            <form id="signupForm" class="signup-form" novalidate autocomplete="off">

                <!-- Full Name -->
                <div class="form-group">
                    <label for="fullname" class="input-label">
                        <i class="fas fa-user input-label-icon" aria-hidden="true"></i>
                        الاسم الكامل
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            class="input-field" 
                            placeholder="أدخل اسمك الكامل"
                            required
                            autocomplete="name"
                            aria-required="true"
                        >
                        <i class="fas fa-user input-icon" aria-hidden="true"></i>
                    </div>
                    <div class="input-error" id="fullname-error" role="alert"></div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="input-label">
                        <i class="fas fa-envelope input-label-icon" aria-hidden="true"></i>
                        البريد الإلكتروني
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="input-field" 
                            placeholder="example@email.com"
                            required
                            autocomplete="email"
                            aria-required="true"
                        >
                        <i class="fas fa-envelope input-icon" aria-hidden="true"></i>
                    </div>
                    <div class="input-error" id="email-error" role="alert"></div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="input-label">
                        <i class="fas fa-lock input-label-icon" aria-hidden="true"></i>
                        كلمة المرور
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input-field" 
                            placeholder="أدخل كلمة المرور"
                            required
                            autocomplete="new-password"
                            aria-required="true"
                            minlength="8"
                        >
                        <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="passwordToggle"
                            aria-label="إظهار أو إخفاء كلمة المرور"
                            tabindex="0"
                        >
                            <i class="fas fa-eye" id="passwordIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="input-error" id="password-error" role="alert"></div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">أدخل كلمة مرور قوية</div>
                    </div>

                    <!-- Password Requirements -->
                    <ul class="password-requirements" id="passwordRequirements">
                        <li class="req-item" id="req-length">
                            <i class="fas fa-circle req-icon" aria-hidden="true"></i>
                            <span>8 أحرف على الأقل</span>
                        </li>
                        <li class="req-item" id="req-uppercase">
                            <i class="fas fa-circle req-icon" aria-hidden="true"></i>
                            <span>حرف كبير واحد على الأقل</span>
                        </li>
                        <li class="req-item" id="req-number">
                            <i class="fas fa-circle req-icon" aria-hidden="true"></i>
                            <span>رقم واحد على الأقل</span>
                        </li>
                        <li class="req-item" id="req-special">
                            <i class="fas fa-circle req-icon" aria-hidden="true"></i>
                            <span>رمز خاص واحد على الأقل</span>
                        </li>
                    </ul>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirmPassword" class="input-label">
                        <i class="fas fa-check-circle input-label-icon" aria-hidden="true"></i>
                        تأكيد كلمة المرور
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirmPassword" 
                            name="confirmPassword" 
                            class="input-field" 
                            placeholder="أعد إدخال كلمة المرور"
                            required
                            autocomplete="new-password"
                            aria-required="true"
                        >
                        <i class="fas fa-check-circle input-icon" aria-hidden="true"></i>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="confirmPasswordToggle"
                            aria-label="إظهار أو إخفاء كلمة المرور"
                            tabindex="0"
                        >
                            <i class="fas fa-eye" id="confirmPasswordIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="input-error" id="confirmPassword-error" role="alert"></div>
                </div>

                <!-- Terms & Conditions -->
                <div class="form-group terms-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="termsCheckbox" class="checkbox-input" required>
                        <span class="checkbox-custom" aria-hidden="true">
                            <i class="fas fa-check checkbox-check" aria-hidden="true"></i>
                        </span>
                        <span class="checkbox-text">
                            أوافق على 
                            <a href="#" class="terms-link">شروط الاستخدام</a>
                            و
                            <a href="#" class="terms-link">سياسة الخصوصية</a>
                        </span>
                    </label>
                    <div class="input-error" id="terms-error" role="alert"></div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary" id="submitBtn">
                    <span class="btn-text">إنشاء حساب</span>
                    <span class="btn-loader" id="btnLoader" style="display: none;">
                        <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                    </span>
                    <span class="btn-success" id="btnSuccess" style="display: none;">
                        <i class="fas fa-check" aria-hidden="true"></i>
                    </span>
                </button>

                <!-- Divider -->
                <div class="divider">
                    <span class="divider-line"></span>
                    <span class="divider-text">أو سجل بـ</span>
                    <span class="divider-line"></span>
                </div>

                <!-- Social Signup -->
                <div class="social-signup">
                    <button type="button" class="social-btn social-google" aria-label="سجل باستخدام Google">
                        <i class="fab fa-google" aria-hidden="true"></i>
                        <span>Google</span>
                    </button>
                    <button type="button" class="social-btn social-facebook" aria-label="سجل باستخدام Facebook">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        <span>Facebook</span>
                    </button>
                    <button type="button" class="social-btn social-apple" aria-label="سجل باستخدام Apple">
                        <i class="fab fa-apple" aria-hidden="true"></i>
                        <span>Apple</span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="login-link">
                    <span>عندك حساب؟</span>
                    <a href="login.php" class="login-link-text">سجل دخول</a>
                </div>

            </form>

            <!-- ============================================================
            SUCCESS STATE (Hidden by default)
            ============================================================ -->
            <div class="success-state" id="successState" style="display: none;">
                <div class="success-icon" aria-hidden="true">✅</div>
                <h2 class="success-title">تم إنشاء الحساب!</h2>
                <p class="success-text">مرحباً بك في سرد. يمكنك الآن البدء في القراءة والكتابة.</p>
                <button class="btn-primary btn-success-cta" onclick="location.href='HomePage.php'">
                    ابدأ القراءة
                </button>
            </div>

        </div>

        <!-- ============================================================
        FOOTER
        ============================================================ -->
        <footer class="signup-footer">
            <p>© <?php echo date("Y"); ?> سرد — منصة الروايات العربية</p>
        </footer>

    </main>

    <!-- ============================================================
    JAVASCRIPT
    ============================================================ -->
    <script src="../Script/JS signup.js"></script>

</body>
</html>
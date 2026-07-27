/**
 * signup.js — Premium Signup Page
 * سرد (Sard) — Arabic Reading Platform
 *
 * Features:
 * - Form validation (real-time + on submit)
 * - Password strength indicator
 * - Password requirements checklist
 * - Show/Hide password toggle
 * - Match password confirmation
 * - Terms & conditions validation
 * - Button loading/success states
 * - Accessibility (ARIA, focus management)
 * - Smooth animations
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // DOM REFERENCES
    // ============================================================

    const form = document.getElementById('signupForm');
    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const termsCheckbox = document.getElementById('termsCheckbox');

    const fullnameError = document.getElementById('fullname-error');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirmPassword-error');
    const termsError = document.getElementById('terms-error');

    const passwordToggle = document.getElementById('passwordToggle');
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
    const passwordIcon = document.getElementById('passwordIcon');
    const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');

    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    const submitBtn = document.getElementById('submitBtn');
    const btnLoader = document.getElementById('btnLoader');
    const btnSuccess = document.getElementById('btnSuccess');
    const btnText = submitBtn.querySelector('.btn-text');

    const successState = document.getElementById('successState');
    const signupForm = document.querySelector('.signup-form');

    // ============================================================
    // PASSWORD VALIDATION HELPERS
    // ============================================================

    const passwordValidators = {
        length: (pwd) => pwd.length >= 8,
        uppercase: (pwd) => /[A-Z]/.test(pwd),
        number: (pwd) => /\d/.test(pwd),
        special: (pwd) => /[!@#$%^&*(),.?":{}|<>]/.test(pwd)
    };

    function validatePassword(pwd) {
        return {
            length: passwordValidators.length(pwd),
            uppercase: passwordValidators.uppercase(pwd),
            number: passwordValidators.number(pwd),
            special: passwordValidators.special(pwd)
        };
    }

    function getPasswordStrength(pwd) {
        if (!pwd) return { level: 'none', score: 0 };

        const validations = validatePassword(pwd);
        const score = Object.values(validations).filter(Boolean).length;

        if (score === 4) return { level: 'strong', score: 4 };
        if (score >= 3) return { level: 'medium', score: 3 };
        if (score >= 2) return { level: 'weak', score: 2 };
        if (score >= 1) return { level: 'weak', score: 1 };
        return { level: 'none', score: 0 };
    }

    // ============================================================
    // PASSWORD REQUIREMENTS UI
    // ============================================================

    function updateRequirements(pwd) {
        const validations = validatePassword(pwd);

        updateRequirementItem(reqLength, validations.length);
        updateRequirementItem(reqUppercase, validations.uppercase);
        updateRequirementItem(reqNumber, validations.number);
        updateRequirementItem(reqSpecial, validations.special);
    }

    function updateRequirementItem(element, isValid) {
        if (isValid) {
            element.classList.add('met');
            element.querySelector('.req-icon').className = 'fas fa-check-circle req-icon';
        } else {
            element.classList.remove('met');
            element.querySelector('.req-icon').className = 'fas fa-circle req-icon';
        }
    }

    // ============================================================
    // PASSWORD STRENGTH UI
    // ============================================================

    function updateStrength(pwd) {
        const strength = getPasswordStrength(pwd);
        const fill = strengthFill;
        const text = strengthText;

        // Reset classes
        fill.classList.remove('weak', 'medium', 'strong');

        if (strength.level === 'none' || !pwd) {
            fill.style.width = '0%';
            text.textContent = 'أدخل كلمة مرور قوية';
            text.style.color = '#5F5E5A';
            return;
        }

        // Set width and class
        if (strength.level === 'weak') {
            fill.classList.add('weak');
            fill.style.width = '33%';
            text.textContent = 'ضعيفة — حاول إضافة أحرف وأرقام';
            text.style.color = '#D9534F';
        } else if (strength.level === 'medium') {
            fill.classList.add('medium');
            fill.style.width = '66%';
            text.textContent = 'متوسطة — أضف المزيد من التنوع';
            text.style.color = '#F0AD4E';
        } else if (strength.level === 'strong') {
            fill.classList.add('strong');
            fill.style.width = '100%';
            text.textContent = 'قوية! كلمة مرور ممتازة';
            text.style.color = '#5CB85C';
        }
    }

    // ============================================================
    // PASSWORD TOGGLE
    // ============================================================

    function togglePasswordVisibility(input, icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
            input.setAttribute('aria-label', 'إخفاء كلمة المرور');
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
            input.setAttribute('aria-label', 'إظهار كلمة المرور');
        }
    }

    passwordToggle.addEventListener('click', function(e) {
        e.preventDefault();
        togglePasswordVisibility(passwordInput, passwordIcon);
    });

    confirmPasswordToggle.addEventListener('click', function(e) {
        e.preventDefault();
        togglePasswordVisibility(confirmPasswordInput, confirmPasswordIcon);
    });

    // Keyboard support for password toggles
    passwordToggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            togglePasswordVisibility(passwordInput, passwordIcon);
        }
    });

    confirmPasswordToggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            togglePasswordVisibility(confirmPasswordInput, confirmPasswordIcon);
        }
    });

    // ============================================================
    // REAL-TIME VALIDATION & PASSWORD CHECK
    // ============================================================

    // Password input events
    passwordInput.addEventListener('input', function() {
        const pwd = this.value;
        updateStrength(pwd);
        updateRequirements(pwd);

        // Check if password matches confirmation
        if (confirmPasswordInput.value) {
            validateConfirmPassword();
        }

        // Validate password field
        validateField(passwordInput, passwordError, function(value) {
            if (!value) return 'كلمة المرور مطلوبة';
            if (value.length < 8) return 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
            if (!/[A-Z]/.test(value)) return 'كلمة المرور تحتاج حرف كبير واحد على الأقل';
            if (!/\d/.test(value)) return 'كلمة المرور تحتاج رقم واحد على الأقل';
            return null;
        });
    });

    // Confirm password events
    confirmPasswordInput.addEventListener('input', function() {
        validateConfirmPassword();
    });

    function validateConfirmPassword() {
        const pwd = passwordInput.value;
        const confirmPwd = confirmPasswordInput.value;

        validateField(confirmPasswordInput, confirmPasswordError, function(value) {
            if (!value) return 'تأكيد كلمة المرور مطلوب';
            if (value !== pwd) return 'كلمة المرور غير متطابقة';
            return null;
        });
    }

    // Fullname validation
    fullnameInput.addEventListener('input', function() {
        validateField(fullnameInput, fullnameError, function(value) {
            if (!value) return 'الاسم الكامل مطلوب';
            if (value.length < 3) return 'الاسم يجب أن يكون 3 أحرف على الأقل';
            return null;
        });
    });

    // Email validation
    emailInput.addEventListener('input', function() {
        validateField(emailInput, emailError, function(value) {
            if (!value) return 'البريد الإلكتروني مطلوب';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) return 'البريد الإلكتروني غير صحيح';
            return null;
        });
    });

    // Terms checkbox
    termsCheckbox.addEventListener('change', function() {
        if (this.checked) {
            termsError.classList.remove('visible');
            termsError.style.display = 'none';
        }
    });

    // ============================================================
    // FIELD VALIDATION HELPER
    // ============================================================

    function validateField(input, errorElement, validator) {
        const value = input.value;
        const error = validator(value);

        if (error) {
            input.classList.remove('success');
            input.classList.add('error');
            errorElement.textContent = error;
            errorElement.style.display = 'block';
            errorElement.classList.add('visible');
            return false;
        } else {
            input.classList.remove('error');
            input.classList.add('success');
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            errorElement.classList.remove('visible');
            return true;
        }
    }

    // ============================================================
    // FORM SUBMISSION
    // ============================================================

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate all fields
        const isFullnameValid = validateField(fullnameInput, fullnameError, function(value) {
            if (!value) return 'الاسم الكامل مطلوب';
            if (value.length < 3) return 'الاسم يجب أن يكون 3 أحرف على الأقل';
            return null;
        });

        const isEmailValid = validateField(emailInput, emailError, function(value) {
            if (!value) return 'البريد الإلكتروني مطلوب';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) return 'البريد الإلكتروني غير صحيح';
            return null;
        });

        const isPasswordValid = validateField(passwordInput, passwordError, function(value) {
            if (!value) return 'كلمة المرور مطلوبة';
            if (value.length < 8) return 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
            if (!/[A-Z]/.test(value)) return 'كلمة المرور تحتاج حرف كبير واحد على الأقل';
            if (!/\d/.test(value)) return 'كلمة المرور تحتاج رقم واحد على الأقل';
            return null;
        });

        const isConfirmValid = validateField(confirmPasswordInput, confirmPasswordError, function(value) {
            if (!value) return 'تأكيد كلمة المرور مطلوب';
            if (value !== passwordInput.value) return 'كلمة المرور غير متطابقة';
            return null;
        });

        // Check terms
        let isTermsValid = true;
        if (!termsCheckbox.checked) {
            termsError.textContent = 'يجب الموافقة على الشروط والأحكام';
            termsError.style.display = 'block';
            termsError.classList.add('visible');
            isTermsValid = false;
        } else {
            termsError.classList.remove('visible');
            termsError.style.display = 'none';
        }

        // If any validation fails, scroll to first error
        if (!isFullnameValid || !isEmailValid || !isPasswordValid || !isConfirmValid || !isTermsValid) {
            const firstError = document.querySelector('.input-field.error');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // ============================================================
        // SUBMIT SUCCESS SIMULATION
        // ============================================================

        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        btnText.textContent = 'جاري الإنشاء...';

        // Simulate API call
        setTimeout(function() {
            // Remove loading, show success
            submitBtn.classList.remove('loading');
            submitBtn.classList.add('success');
            btnText.textContent = 'تم الإنشاء!';

            // After success, show success state
            setTimeout(function() {
                signupForm.style.display = 'none';
                successState.style.display = 'block';

                // Reset button
                submitBtn.classList.remove('success');
                submitBtn.disabled = false;
                btnText.textContent = 'إنشاء حساب';

                // Reset form
                form.reset();
                resetPasswordUI();
                resetFieldStyles();

            }, 800);

        }, 2000);
    });

    // ============================================================
    // RESET HELPERS
    // ============================================================

    function resetPasswordUI() {
        strengthFill.style.width = '0%';
        strengthFill.className = 'strength-fill';
        strengthText.textContent = 'أدخل كلمة مرور قوية';
        strengthText.style.color = '#5F5E5A';

        const reqItems = document.querySelectorAll('.req-item');
        reqItems.forEach(function(item) {
            item.classList.remove('met');
            item.querySelector('.req-icon').className = 'fas fa-circle req-icon';
        });

        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-eye';
        confirmPasswordInput.type = 'password';
        confirmPasswordIcon.className = 'fas fa-eye';
    }

    function resetFieldStyles() {
        document.querySelectorAll('.input-field').forEach(function(field) {
            field.classList.remove('error', 'success');
        });
        document.querySelectorAll('.input-error').forEach(function(err) {
            err.classList.remove('visible');
            err.style.display = 'none';
            err.textContent = '';
        });
    }

    // ============================================================
    // ACCESSIBILITY: Focus management
    // ============================================================

    // Auto-focus first field on load
    setTimeout(function() {
        fullnameInput.focus();
    }, 800);

    // ============================================================
    // KEYBOARD SUPPORT: Enter key on terms triggers form submit
    // ============================================================

    termsCheckbox.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.checked = !this.checked;
            this.dispatchEvent(new Event('change'));
        }
    });

    // ============================================================
    // CONSOLE HELPER (for debugging)
    // ============================================================

    console.log('%c📖 سرد — Premium Signup Page', 'font-size:20px; font-weight:bold; color:#C9A96E;');
    console.log('%cتم تحميل الصفحة بنجاح!', 'color:#1D9E75;');

}); // end DOMContentLoaded
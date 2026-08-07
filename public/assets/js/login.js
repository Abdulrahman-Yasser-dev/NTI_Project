document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const form = document.querySelector('form');

    const ruleEmail = document.getElementById('rule-email');
    const rulePassword = document.getElementById('rule-password');

    if (!emailInput || !passwordInput || !form || !ruleEmail || !rulePassword) {
        console.error('Login form elements not found');
        return;
    }

    emailInput.addEventListener('input', () => {
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);
        ruleEmail.className = 'field-rule ' + (valid ? 'valid' : 'invalid');
    });

    passwordInput.addEventListener('input', () => {
        const valid = passwordInput.value.length > 0;
        rulePassword.className = 'field-rule ' + (valid ? 'valid' : 'invalid');
    });

    form.addEventListener('submit', (e) => {
        const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);
        const passwordValid = passwordInput.value.length > 0;

        if (!emailValid || !passwordValid) {
            e.preventDefault();
            alert('من فضلك أدخل بريد إلكتروني وكلمة مرور صحيحين');
        }
    });
});
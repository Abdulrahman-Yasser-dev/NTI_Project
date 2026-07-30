const usernameInput = document.querySelector('input[name="username"]');
const emailInput = document.querySelector('input[name="email"]');
const passwordInput = document.querySelector('input[name="password"]');
const confirmInput = document.querySelector('input[name="confirm_password"]');
const form = document.querySelector('form');

const ruleUsername = document.getElementById('rule-username');
const ruleEmail = document.getElementById('rule-email');
const ruleLength = document.getElementById('rule-length');
const ruleCase = document.getElementById('rule-case');
const ruleNumber = document.getElementById('rule-number');
const ruleConfirm = document.getElementById('rule-confirm');

usernameInput.addEventListener('input', () => {
    const valid = usernameInput.value.trim().length >= 3;
    ruleUsername.className = 'field-rule ' + (valid ? 'valid' : 'invalid');
});

emailInput.addEventListener('input', () => {
    const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);
    ruleEmail.className = 'field-rule ' + (valid ? 'valid' : 'invalid');
});

passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    ruleLength.className = val.length >= 8 ? 'valid' : 'invalid';
    ruleCase.className = (/[a-z]/.test(val) && /[A-Z]/.test(val)) ? 'valid' : 'invalid';
    ruleNumber.className = /[0-9]/.test(val) ? 'valid' : 'invalid';
    checkConfirm();
});

confirmInput.addEventListener('input', checkConfirm);

function checkConfirm() {
    if (confirmInput.value.length === 0) {
        ruleConfirm.className = 'field-rule';
        return;
    }
    const valid = confirmInput.value === passwordInput.value;
    ruleConfirm.className = 'field-rule ' + (valid ? 'valid' : 'invalid');
}

form.addEventListener('submit', (e) => {
    const usernameValid = usernameInput.value.trim().length >= 3;
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);
    const val = passwordInput.value;
    const passwordValid = val.length >= 8 && /[a-z]/.test(val) && /[A-Z]/.test(val) && /[0-9]/.test(val);
    const confirmValid = confirmInput.value === passwordInput.value && confirmInput.value.length > 0;

    if (!usernameValid || !emailValid || !passwordValid || !confirmValid) {
        e.preventDefault();
        alert('من فضلك تأكد من صحة جميع الحقول قبل إنشاء الحساب');
    }
});
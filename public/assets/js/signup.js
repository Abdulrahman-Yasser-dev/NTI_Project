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

    const lengthValid = val.length >= 8 && /[A-Z]/.test(val);
    ruleLength.className = lengthValid ? 'valid' : 'invalid';

    const caseValid = /[@#]/.test(val);
    ruleCase.className = caseValid ? 'valid' : 'invalid';

    const numberValid = /[0-9]/.test(val);
    ruleNumber.className = numberValid ? 'valid' : 'invalid';

    checkConfirm();
});

confirmInput.addEventListener('input', checkConfirm);

function checkConfirm() {
    if (confirmInput.value.length === 0) {
        ruleConfirm.className = 'field-rule';
        ruleConfirm.textContent = 'يجب أن تطابق كلمة المرور';
        return;
    }
    const valid = confirmInput.value === passwordInput.value;
    ruleConfirm.className = 'field-rule ' + (valid ? 'valid' : 'invalid');
    ruleConfirm.textContent = valid ? 'كلمة المرور متطابقة ✓' : 'كلمة المرور غير متطابقة';
}

form.addEventListener('submit', (e) => {
    const usernameValid = usernameInput.value.trim().length >= 3;
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);

    const val = passwordInput.value;
    const passwordValid = val.length >= 8 && /[A-Z]/.test(val) && /[@#]/.test(val) && /[0-9]/.test(val);
    const confirmValid = confirmInput.value === passwordInput.value && confirmInput.value.length > 0;

    if (!usernameValid || !emailValid || !passwordValid || !confirmValid) {
        e.preventDefault();
        alert('من فضلك تأكد من صحة جميع الحقول قبل إنشاء الحساب');
    }
});
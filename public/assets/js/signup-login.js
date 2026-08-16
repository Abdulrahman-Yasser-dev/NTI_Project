(function() {
  const tabLogin = document.getElementById('tabLogin');
  const tabSignup = document.getElementById('tabSignup');
  const tabIndicator = document.getElementById('tabIndicator');
  const loginPanel = document.getElementById('loginPanel');
  const signupPanel = document.getElementById('signupPanel');
  const switchLine = document.getElementById('switchLine');

  function showLogin() {
    tabLogin.classList.add('active');
    tabSignup.classList.remove('active');
    tabIndicator.classList.remove('signup');
    loginPanel.classList.add('active');
    signupPanel.classList.remove('active');
    switchLine.innerHTML = 'ليس لديك حساب؟ <button type="button" id="goSignup">أنشئ حسابًا الآن</button>';
    document.getElementById('goSignup').addEventListener('click', showSignup);
  }

  function showSignup() {
    tabSignup.classList.add('active');
    tabLogin.classList.remove('active');
    tabIndicator.classList.add('signup');
    signupPanel.classList.add('active');
    loginPanel.classList.remove('active');
    switchLine.innerHTML = 'لديك حساب بالفعل؟ <button type="button" id="goLogin">سجّل الدخول</button>';
    document.getElementById('goLogin').addEventListener('click', showLogin);
  }

  tabLogin.addEventListener('click', showLogin);
  tabSignup.addEventListener('click', showSignup);

  // Bind whichever switch-line button the server rendered on load
  // (login page reload after a signup error keeps the signup tab open,
  // and vice versa).
  const initialGoSignup = document.getElementById('goSignup');
  const initialGoLogin = document.getElementById('goLogin');
  if (initialGoSignup) initialGoSignup.addEventListener('click', showSignup);
  if (initialGoLogin) initialGoLogin.addEventListener('click', showLogin);

  function setError(fieldEl, message) {
    fieldEl.classList.add('error');
    fieldEl.querySelector('.hint').textContent = message;
  }
  function clearError(fieldEl) {
    fieldEl.classList.remove('error');
    fieldEl.querySelector('.hint').textContent = '';
  }

  // ================= LOGIN =================
  const loginMsg = document.getElementById('loginMsg');
  document.getElementById('loginPanel').addEventListener('submit', function(e) {
    let ok = true;
    const emailField = document.getElementById('loginEmailField');
    const passField = document.getElementById('loginPasswordField');
    const email = document.getElementById('loginEmail').value.trim();
    const pass = document.getElementById('loginPassword').value;

    clearError(emailField); clearError(passField);

    if (!/^\S+@\S+\.\S+$/.test(email)) {
      setError(emailField, 'أدخل بريدًا إلكترونيًا صحيحًا'); ok = false;
    }
    if (pass.length < 6) {
      setError(passField, 'كلمة المرور قصيرة جدًا'); ok = false;
    }

    if (!ok) {
      e.preventDefault();
      loginMsg.textContent = 'يرجى تصحيح الحقول أعلاه.';
      loginMsg.classList.add('error');
    }
    // If ok, let the form submit normally to signup&login.php (form_type=login),
    // which runs the old login.php logic unchanged.
  });

  // ================= SIGNUP =================
  const signupMsg = document.getElementById('signupMsg');
  document.getElementById('signupPanel').addEventListener('submit', function(e) {
    let ok = true;

    const firstNameField = document.getElementById('signupFirstNameField');
    const lastNameField = document.getElementById('signupLastNameField');
    const emailField = document.getElementById('signupEmailField');
    const phoneField = document.getElementById('signupPhoneField');
    const passField = document.getElementById('signupPasswordField');
    const confirmField = document.getElementById('signupConfirmField');

    const firstName = document.getElementById('signupFirstName').value.trim();
    const lastName = document.getElementById('signupLastName').value.trim();
    const email = document.getElementById('signupEmail').value.trim();
    const phone = document.getElementById('signupPhone').value.trim();
    const pass = document.getElementById('signupPassword').value;
    const confirm = document.getElementById('signupConfirm').value;

    [firstNameField, lastNameField, emailField, phoneField, passField, confirmField].forEach(clearError);

    if (firstName.length < 2) {
      setError(firstNameField, 'أدخل الاسم الأول'); ok = false;
    }
    if (lastName.length < 2) {
      setError(lastNameField, 'أدخل الاسم الأخير'); ok = false;
    }
    if (!/^\S+@\S+\.\S+$/.test(email)) {
      setError(emailField, 'أدخل بريدًا إلكترونيًا صحيحًا'); ok = false;
    }
    if (phone.length < 9) {
      setError(phoneField, 'أدخل رقم هاتف صحيح'); ok = false;
    }

    // Password rule preserved from the old backend's signup.js:
    // at least 8 characters, one uppercase letter, one digit, and
    // one of @ or #.
    const passLengthValid = pass.length >= 8 && /[A-Z]/.test(pass);
    const passSymbolValid = /[@#]/.test(pass);
    const passNumberValid = /[0-9]/.test(pass);
    if (!passLengthValid || !passSymbolValid || !passNumberValid) {
      let msg = 'يجب أن تكون ٨ أحرف على الأقل مع حرف كبير، رمز (@ أو #) ورقم';
      setError(passField, msg); ok = false;
    }

    if (confirm !== pass || confirm === '') {
      setError(confirmField, 'كلمتا المرور غير متطابقتين'); ok = false;
    }

    if (!ok) {
      e.preventDefault();
      signupMsg.textContent = 'يرجى تصحيح الحقول أعلاه.';
      signupMsg.classList.add('error');
    }
    // If ok, let the form submit normally to signup&login.php (form_type=signup),
    // which runs the old signup.php logic unchanged.
  });
})();
(function() {
  const tabLogin = document.getElementById('tabLogin');
  const tabSignup = document.getElementById('tabSignup');
  const tabIndicator = document.getElementById('tabIndicator');
  const loginPanel = document.getElementById('loginPanel');
  const signupPanel = document.getElementById('signupPanel');
  const switchLine = document.getElementById('switchLine');
  const goSignup = document.getElementById('goSignup');

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
  if (goSignup) goSignup.addEventListener('click', showSignup);

  function setError(fieldEl, message) {
    fieldEl.classList.add('error');
    fieldEl.querySelector('.hint').textContent = message;
  }
  function clearError(fieldEl) {
    fieldEl.classList.remove('error');
    fieldEl.querySelector('.hint').textContent = '';
  }

  const loginMsg = document.getElementById('loginMsg');
  document.getElementById('loginPanel').addEventListener('submit', function(e) {
    e.preventDefault();
    let ok = true;
    const emailField = document.getElementById('loginEmailField');
    const passField = document.getElementById('loginPasswordField');
    const email = document.getElementById('loginEmail').value.trim();
    const pass = document.getElementById('loginPassword').value;

    clearError(emailField); clearError(passField);
    loginMsg.textContent = ''; loginMsg.classList.remove('error');

    if (!/^\S+@\S+\.\S+$/.test(email)) {
      setError(emailField, 'أدخل بريدًا إلكترونيًا صحيحًا'); ok = false;
    }
    if (pass.length < 6) {
      setError(passField, 'كلمة المرور قصيرة جدًا'); ok = false;
    }

    if (ok) {
      loginMsg.textContent = 'تم تسجيل الدخول بنجاح.';
    } else {
      loginMsg.textContent = 'يرجى تصحيح الحقول أعلاه.';
      loginMsg.classList.add('error');
    }
  });

  const signupMsg = document.getElementById('signupMsg');
  document.getElementById('signupPanel').addEventListener('submit', function(e) {
    e.preventDefault();
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
    signupMsg.textContent = ''; signupMsg.classList.remove('error');

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
    if (pass.length < 8) {
      setError(passField, 'يجب ألا تقل عن 8 أحرف'); ok = false;
    }
    if (confirm !== pass || confirm === '') {
      setError(confirmField, 'كلمتا المرور غير متطابقتين'); ok = false;
    }

    if (ok) {
      signupMsg.textContent = 'تم إنشاء الحساب بنجاح، مرحبًا بك في سرد.';
    } else {
      signupMsg.textContent = 'يرجى تصحيح الحقول أعلاه.';
      signupMsg.classList.add('error');
    }
  });
})();
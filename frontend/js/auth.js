function hideMessage(element) {
  if (!element) return;
  element.hidden = true;
  element.replaceChildren();
}

function showMessage(element, messages) {
  const items = Array.isArray(messages) ? messages : [messages];
  const cleanItems = items.filter(Boolean);

  element.replaceChildren();
  if (cleanItems.length > 1) {
    const list = document.createElement('ul');
    cleanItems.forEach(message => {
      const item = document.createElement('li');
      item.textContent = message;
      list.appendChild(item);
    });
    element.appendChild(list);
  } else {
    element.textContent = cleanItems[0] || 'Something went wrong. Please try again.';
  }
  element.hidden = false;
}

function clearRegistrationPasswords() {
  const passwordInput = document.getElementById('reg-password');
  const confirmPasswordInput = document.getElementById('reg-confirm-password');

  if (passwordInput) passwordInput.value = '';
  if (confirmPasswordInput) confirmPasswordInput.value = '';
}

async function register(event) {
  event.preventDefault();

  const form = document.getElementById('register-form');
  const otpForm = document.getElementById('otp-form');
  const fname = document.getElementById('reg-fname').value.trim();
  const lname = document.getElementById('reg-lname').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const contact = document.getElementById('reg-contact').value.trim();
  const passwordInput = document.getElementById('reg-password');
  const confirmPasswordInput = document.getElementById('reg-confirm-password');
  const password = passwordInput.value;
  const confirmPassword = confirmPasswordInput.value;
  const errEl = document.getElementById('register-error');
  const sucEl = document.getElementById('register-success');

  hideMessage(errEl);
  hideMessage(sucEl);

  if (password !== confirmPassword) {
    clearRegistrationPasswords();
    passwordInput.focus();
    showMessage(errEl, 'Passwords do not match.');
    return;
  }

  try {
    const result = await apiRequest('register', { fname, lname, email, contact, password });
    window.pendingRegistrationEmail = result.email || email;
    document.getElementById('otp-email-label').textContent = window.pendingRegistrationEmail;
    form.hidden = true;
    otpForm.hidden = false;
    document.getElementById('reg-otp').value = '';
    document.getElementById('reg-otp').focus();
    startResendCountdown(document.getElementById('resend-otp-btn'));

    const debugNote = result.debugOtp ? ' Local test OTP: ' + result.debugOtp : '';
    showMessage(sucEl, (result.message || 'Verification code sent. Please check your email.') + debugNote);
  } catch (err) {
    clearRegistrationPasswords();
    passwordInput.focus();
    showMessage(errEl, err.errors || err.message || 'Registration failed. Please try again.');
    return;
  }

  clearRegistrationPasswords();
}

async function verifyRegistrationOtp(event) {
  event.preventDefault();

  const otpInput = document.getElementById('reg-otp');
  const errEl = document.getElementById('register-error');
  const sucEl = document.getElementById('register-success');
  const email = window.pendingRegistrationEmail || document.getElementById('reg-email').value.trim();
  const otp = otpInput.value.trim();

  hideMessage(errEl);
  hideMessage(sucEl);

  try {
    const result = await apiRequest('verify_registration_otp', { email, otp });
    const user = result.user;

    Cookie.remove('currentUser');
    Cookie.remove('currentAdmin');
    localStorage.removeItem('aqCart');
    sessionStorage.removeItem('aqGuestCart');
    if (user) {
      Cookie.set('currentUser', user, 60 / 1440);
    }
  } catch (err) {
    otpInput.value = '';
    otpInput.focus();
    showMessage(errEl, err.errors || err.message || 'OTP verification failed. Please try again.');
    return;
  }

  showMessage(sucEl, 'Account created successfully. Redirecting...');
  setTimeout(() => {
    window.location.href = 'index.php';
  }, 800);
}

async function resendRegistrationOtp() {
  const errEl = document.getElementById('register-error');
  const sucEl = document.getElementById('register-success');
  const resendBtn = document.getElementById('resend-otp-btn');
  const email = window.pendingRegistrationEmail || document.getElementById('reg-email').value.trim();

  hideMessage(errEl);
  hideMessage(sucEl);

  try {
    const result = await apiRequest('resend_registration_otp', { email });
    const debugNote = result.debugOtp ? ' Local test OTP: ' + result.debugOtp : '';
    showMessage(sucEl, (result.message || 'A new verification code has been sent.') + debugNote);
    startResendCountdown(resendBtn);
  } catch (err) {
    if (err.waitSeconds !== undefined) {
      startResendCountdown(document.getElementById('resend-otp-btn'), err.waitSeconds);
    }
    showMessage(errEl, err.errors || err.message || 'Unable to resend OTP. Please try again.');
  }
}

function startResendCountdown(button, initialSeconds = 60) {
  let seconds = initialSeconds;
  button.disabled = true;
  button.style.opacity = '0.5';
  button.style.cursor = 'not-allowed';

  const originalText = button.textContent;
  const updateButton = () => {
    button.textContent = `Resend OTP (${seconds}s)`;
    if (seconds <= 0) {
      button.disabled = false;
      button.style.opacity = '1';
      button.style.cursor = 'pointer';
      button.textContent = originalText;
    } else {
      seconds--;
      setTimeout(updateButton, 1000);
    }
  };

  updateButton();
}

function editRegistrationDetails() {
  const registerForm = document.getElementById('register-form');
  const otpForm = document.getElementById('otp-form');
  const errEl = document.getElementById('register-error');
  const sucEl = document.getElementById('register-success');

  hideMessage(errEl);
  hideMessage(sucEl);
  otpForm.hidden = true;
  registerForm.hidden = false;
  clearRegistrationPasswords();
  document.getElementById('reg-password').focus();
}

async function login(event) {
  event.preventDefault();

  const email = document.getElementById('login-email').value.trim();
  const passwordInput = document.getElementById('login-password');
  const password = passwordInput.value;
  const errEl = document.getElementById('login-error');

  hideMessage(errEl);

  try {
    const result = await apiRequest('login', { email, password });
    const user = result.user;

    Cookie.remove('currentUser');
    Cookie.remove('currentAdmin');
    localStorage.removeItem('aqCart');
    sessionStorage.removeItem('aqGuestCart');
    if (user.role === 'admin') {
      Cookie.set('currentAdmin', user, 60 / 1440);
    } else {
      Cookie.set('currentUser', user, 60 / 1440);
    }

    sessionStorage.removeItem('aqsmile_cart');
    passwordInput.value = '';
    showToast('Welcome back, ' + user.name.split(' ')[0] + '.');
    setTimeout(() => {
      window.location.href = result.redirect || (user.role === 'admin' ? 'admin.php' : 'index.php');
    }, 600);
  } catch (err) {
    passwordInput.value = '';
    showMessage(errEl, err.errors || err.message || 'Invalid email or password.');
  }
}

const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', register);
}

const contactInput = document.getElementById('reg-contact');
if (contactInput) {
  contactInput.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
  });
}

const otpForm = document.getElementById('otp-form');
if (otpForm) {
  otpForm.addEventListener('submit', verifyRegistrationOtp);
}

const resendOtpBtn = document.getElementById('resend-otp-btn');
if (resendOtpBtn) {
  resendOtpBtn.addEventListener('click', resendRegistrationOtp);
}

const editRegistrationBtn = document.getElementById('edit-registration-btn');
if (editRegistrationBtn) {
  editRegistrationBtn.addEventListener('click', editRegistrationDetails);
}

const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', login);
}

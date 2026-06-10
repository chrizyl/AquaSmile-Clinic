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

async function register(event) {
  event.preventDefault();

  const fname = document.getElementById('reg-fname').value.trim();
  const lname = document.getElementById('reg-lname').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const contact = document.getElementById('reg-contact').value.trim();
  const passwordInput = document.getElementById('reg-password');
  const password = passwordInput.value;
  const errEl = document.getElementById('register-error');
  const sucEl = document.getElementById('register-success');

  hideMessage(errEl);
  hideMessage(sucEl);

  try {
    await apiRequest('register', { fname, lname, email, contact, password });
  } catch (err) {
    passwordInput.value = '';
    showMessage(errEl, err.errors || err.message || 'Registration failed. Please try again.');
    return;
  }

  passwordInput.value = '';
  showMessage(sucEl, 'Account created successfully. Redirecting...');
  setTimeout(() => {
    window.location.href = 'login.php';
  }, 1800);
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

const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', login);
}

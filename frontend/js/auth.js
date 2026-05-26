
async function register() {
  const fname    = document.getElementById('reg-fname').value.trim();
  const lname    = document.getElementById('reg-lname').value.trim();
  const email    = document.getElementById('reg-email').value.trim();
  const contact  = document.getElementById('reg-contact').value.trim();
  const password = document.getElementById('reg-password').value;
  const errEl    = document.getElementById('register-error');
  const sucEl    = document.getElementById('register-success');

  errEl.style.display = 'none';
  sucEl.style.display = 'none';

  // Basic Validation[cite: 2]
  if (!fname || !lname || !email || !contact || !password) {
    errEl.textContent = 'All fields are required.';
    errEl.style.display = 'block';
    return;
  }

  if (password.length < 6) {
    errEl.textContent = 'Password must be at least 6 characters.';
    errEl.style.display = 'block';
    return;
  }

  try {
    const result = await apiRequest('register', {
      fname,
      lname,
      name: fname + ' ' + lname,
      email,
      contact,
      password,
    });

    const users = DB.get('users') || [];
    if (!users.find(u => u.email === result.user.email)) {
      users.push({ ...result.user, password });
      DB.set('users', users);
    }
  } catch (err) {
    errEl.textContent = err.message || 'Registration failed. Please try again.';
    errEl.style.display = 'block';
    return;
  }

  sucEl.textContent = 'Account created successfully. Redirecting...';
  sucEl.style.display = 'block';

  setTimeout(() => { window.location.href = 'login.php'; }, 1800);
}

// ── LOGIN ──
async function login() {
  const email    = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;
  const errEl    = document.getElementById('login-error');

  errEl.style.display = 'none';

  if (!email || !password) {
    errEl.textContent = 'Please enter your email and password.';
    errEl.style.display = 'block';
    return;
  }

  const admin = ADMIN_ACCOUNTS.find(a => a.email === email && a.password === password);
  if (admin) {
    DB.set('currentAdmin', null);
    Cookie.set('currentAdmin', admin, 1/1440); // 30/86400 kapag seconds
    DB.set('currentUser', null);
    sessionStorage.removeItem('aqsmile_cart'); 
    showToast('Welcome, ' + admin.name + '.');
    setTimeout(() => { window.location.href = 'admin.php'; }, 600);
    return;
  }

  let user = null;

  try {
    const result = await apiRequest('login', { email, password });
    user = result.user;
  } catch (err) {
    errEl.textContent = err.message || 'Login failed. Please try again.';
    errEl.style.display = 'block';
    return;
  }

  if (user) {
    DB.set('currentUser', null);
    Cookie.set('currentUser', user, 60/1440); // naka-set sa minute
    DB.set('currentAdmin', null);
    sessionStorage.removeItem('aqsmile_cart'); // Clear previous user's cart
    showToast('Welcome back, ' + user.name.split(' ')[0] + '.');

    // Role-based redirection
    const redirectUrl = user.role === 'admin' ? 'admin.php' : 'index.php';
    setTimeout(() => { window.location.href = redirectUrl; }, 600);
    return;
  }

  errEl.textContent = 'Invalid email or password.';
  errEl.style.display = 'block';
}

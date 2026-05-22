
function register() {
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

  // Duplicate Check[cite: 2]
  const users = DB.get('users') || [];
  if (users.find(u => u.email === email)) {
    errEl.textContent = 'An account with this email already exists.';
    errEl.style.display = 'block';
    return;
  }

  // Save User[cite: 2, 3]
  const newUser = {
    id:       'U' + Date.now(),
    name:     fname + ' ' + lname,
    email:    email,
    contact:  contact,
    password: password,
  };

  users.push(newUser);
  DB.set('users', users);

  sucEl.textContent = 'Account created successfully. Redirecting...';
  sucEl.style.display = 'block';

  setTimeout(() => { window.location.href = 'login.php'; }, 1800);
}

// ── LOGIN ──
function login() {
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
    setTimeout(() => { window.location.href = 'admin-dashboard.php'; }, 600);
    return;
  }

  const users = DB.get('users') || [];
  const user  = users.find(u => u.email === email && u.password === password);

  if (user) {
    DB.set('currentUser', null);
    Cookie.set('currentUser', user, 1/1440); // naka-set sa minute
    DB.set('currentAdmin', null);
    sessionStorage.removeItem('aqsmile_cart'); // Clear previous user's cart
    showToast('Welcome back, ' + user.name.split(' ')[0] + '.');
    setTimeout(() => { window.location.href = 'index.php'; }, 600);
    return;
  }

  errEl.textContent = 'Invalid email or password.';
  errEl.style.display = 'block';
}
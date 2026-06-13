

// ── LOCAL STORAGE HELPER ──
const DB = {
  get(k) {
    try { return JSON.parse(localStorage.getItem('aqsmile_' + k)) || null; }
    catch { return null; }
  },
  set(k, v) { localStorage.setItem('aqsmile_' + k, JSON.stringify(v)); },
};

// ── SESSION STORAGE HELPER (cart only) ──
const Session = {
  get(k) {
    try { return JSON.parse(sessionStorage.getItem('aqsmile_' + k)) || null; }
    catch { return null; }
  },
  set(k, v) { sessionStorage.setItem('aqsmile_' + k, JSON.stringify(v)); },
  remove(k)  { sessionStorage.removeItem('aqsmile_' + k); },
};

// ── COOKIE HELPER (login persistence) ──
const Cookie = {
  set(name, value, days = 7) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `aqsmile_${name}=${encodeURIComponent(JSON.stringify(value))}; expires=${expires}; path=/; SameSite=Lax`;
  },
  get(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )aqsmile_' + name + '=([^;]*)'));
    try { return match ? JSON.parse(decodeURIComponent(match[1])) : null; }
    catch { return null; }
  },
  remove(name) {
    document.cookie = `aqsmile_${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
  },
};

function isAdmin() {
  const user = Cookie.get('currentUser');
  const admin = Cookie.get('currentAdmin');
  return (user && user.role === 'admin') || (admin && admin.role === 'admin');
}

function getCurrentUser() {
  return Cookie.get('currentUser');
}

const API_BASE = new URL('../backend/api/index.php', window.location.href).pathname;

async function apiRequest(action, data = null, method = 'POST') {
  const options = {
    method,
    headers: { 'Content-Type': 'application/json' },
  };

  if (data !== null) {
    options.body = JSON.stringify(data);
  }

  const url = API_BASE + '?action=' + encodeURIComponent(action);
  const response = await fetch(url, { ...options, cache: 'no-store' });
  const payload = await parseApiResponse(response);

  if (!response.ok || !payload.ok) {
    const error = new Error(payload.message || 'Database request failed.');
    error.errors = payload.errors || null;
    throw error;
  }

  return payload;
}

async function apiGet(action, params = {}) {
  const query = new URLSearchParams({ action, ...params });
  const response = await fetch(API_BASE + '?' + query.toString(), { cache: 'no-store' });
  const payload = await parseApiResponse(response);

  if (!response.ok || !payload.ok) {
    throw new Error(payload.message || 'Database request failed.');
  }

  return payload;
}

async function parseApiResponse(response) {
  const text = await response.text();

  try {
    return text ? JSON.parse(text) : {};
  } catch (err) {
    console.error('Non-JSON API response:', text);
    return {
      ok: false,
      message: 'Server returned an invalid response. Please try again.',
    };
  }
}

async function syncCatalogFromDatabase() {
  try {
    const data = await apiGet('catalog');
    if (data.services?.length) {
      SERVICES.splice(0, SERVICES.length, ...data.services);
    }
    if (data.products?.length) {
      PRODUCTS.splice(0, PRODUCTS.length, ...data.products);
    }
    if (data.dentists?.length) {
      DENTISTS.splice(0, DENTISTS.length, ...data.dentists);
    }
    return true;
  } catch (err) {
    console.warn('Using local catalog fallback:', err.message);
    return false;
  }
}

// ── SHARED DATA ──
const DENTISTS = [
  {
    id: 'D1',
    name: 'Dr. Sophia Reyes',
    photo: 'images/dentist_doctorg12.jpg',   
    cred: 'DMD · 12 years experience',
    spec: 'General & Cosmetic Dentistry',
    desc: 'Dr. Reyes is passionate about smile transformations. She specializes in cosmetic procedures and comprehensive preventive care, making every patient feel at ease from the first consult.',
  },
  {
    id: 'D2',
    name: 'Dr. Marcus Tan',
    photo: 'images/dentist_doctorm.jpg',   
    cred: 'DMD, MScD · 9 years experience',
    spec: 'Orthodontics & Oral Surgery',
    desc: 'With a dual degree in dentistry and dental surgery, Dr. Tan handles complex cases with precision and care. His calm demeanor and expertise make even difficult procedures stress-free.',
  },
  {
    id: 'D3',
    name: 'Dr. Leila Varon',
    photo: 'images/dentist_doctorg2.jpg',   
    cred: 'DMD, PedDent · 7 years experience',
    spec: 'Pediatric & Family Dentistry',
    desc: 'Dr. Varon brings warmth and patience to every appointment. She is the go-to specialist for families and younger patients, ensuring a positive dental experience from an early age.',
  },
];

const SERVICES = [
  { id: 'S1', photo: 'images/dental cleaning.jpeg',   name: 'Dental Cleaning',        desc: 'Professional prophylaxis to remove plaque and tartar for a fresher, healthier smile.', price: '₱800' },
  { id: 'S2', photo: 'images/xray.webp',       name: 'Dental X-Ray',           desc: 'Digital X-rays for accurate diagnosis of hidden dental issues.', price: '₱450' },
  { id: 'S3', photo: 'images/tooth extraction.jpg', name: 'Tooth Extraction',       desc: 'Safe and comfortable removal of damaged or problematic teeth.', price: '₱1,200' },
  { id: 'S4', photo: 'images/teeth whitening.jpg',  name: 'Teeth Whitening',        desc: 'Professional-grade whitening treatment for a noticeably brighter smile.', price: '₱3,500' },
  { id: 'S5', photo: 'images/dental braces.webp',     name: 'Dental Braces Consult',  desc: 'Comprehensive orthodontic evaluation and treatment planning for a straighter smile.', price: '₱500' },
  { id: 'S6', photo: 'images/root canal.jpeg',  name: 'Root Canal Treatment',   desc: 'Precision endodontic therapy to save severely infected or damaged teeth.', price: '₱6,000' },
  { id: 'S7', photo: 'images/dental crown.png',      name: 'Dental Crown',           desc: 'Custom-fitted porcelain crowns to restore strength and appearance of damaged teeth.', price: '₱8,000' },
  { id: 'S8', photo: 'images/veneers.jpg',    name: 'Porcelain Veneers',      desc: 'Ultra-thin custom shells bonded to the front of teeth for a flawless aesthetic result.', price: '₱12,000' },
  { id: 'S9', photo: 'images/pediatric check up.jpg',  name: 'Pediatric Check-Up',     desc: 'Gentle and fun dental visits designed especially for children aged 2–12.', price: '₱600' },
];

const PRODUCTS = [
  { id: 'P1', photo: 'images/toothbrush.avif', name: 'Sonic Pro Toothbrush',    desc: 'Rechargeable electric toothbrush with 3 modes and UV sanitizer.', price: 1299 },
  { id: 'P2', photo: 'images/toothpaste.jpg', name: 'WhiteGlow Toothpaste',    desc: 'Enamel-strengthening whitening paste with fluoride and mint.', price: 299 },
  { id: 'P3', photo: 'images/floss.jpg',      name: 'Silk Dental Floss',       desc: 'Natural silk floss with wax coating for smooth, effortless cleaning.', price: 189 },
  { id: 'P4', photo: 'images/mouthwash.jpg',  name: 'AquaFresh Mouthwash',     desc: 'Antibacterial rinse with fresh mint and zero alcohol formula.', price: 349 },
  { id: 'P5', photo: 'images/whitening strips.jpg',     name: 'Teeth Whitening Strips',  desc: '14-day whitening kit, clinically proven to whiten up to 7 shades.', price: 899 },
  { id: 'P6', photo: 'images/scraper set.jpg',    name: 'Tongue Scraper Set',      desc: 'Stainless steel scrapers for fresher breath and improved oral hygiene.', price: 249 },
  { id: 'P7', photo: 'images/gum gel.png',        name: 'Sensitive Gum Gel',       desc: 'Soothing gel formula for gum sensitivity and irritation relief.', price: 399 },
  { id: 'P8', photo: 'images/bamboo toothbrush.webp',     name: 'Natural Bamboo Brush Set', desc: '4-pack biodegradable bamboo toothbrushes with charcoal bristles.', price: 549 },
];

// ── SESSION STATE ──
let currentUser  = Cookie.get('currentUser');
let currentAdmin = Cookie.get('currentAdmin');

// ── TOAST ──
let toastTimer = null;
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ── NAV UPDATE ──
function updateNav() {
  const loggedIn = currentUser || currentAdmin;
  const loginBtn  = document.getElementById('nav-login-btn');
  const logoutBtn = document.getElementById('nav-logout-btn');
  const userInfo  = document.getElementById('nav-user-info');
  const bookBtn   = document.getElementById('nav-book-btn');
  const apptsBtn  = document.getElementById('nav-appts-btn');
  const cartBtn   = document.getElementById('nav-cart-btn');
  const adminBtn  = document.getElementById('nav-admin-btn');

  if (loginBtn)  loginBtn.style.display  = loggedIn ? 'none' : '';
  if (logoutBtn) logoutBtn.style.display = loggedIn ? '' : 'none';
  if (userInfo)  userInfo.style.display  = loggedIn ? '' : 'none';
  if (bookBtn) {
    bookBtn.style.display = (currentUser && !currentAdmin) ? '' : 'none';
    if (currentUser && !currentAdmin) {
      bookBtn.classList.remove('admin-disabled');
      bookBtn.disabled = false;
      bookBtn.onclick = function() { window.location.href = 'booking.php'; };
    } else if (currentAdmin) {
      bookBtn.classList.add('admin-disabled');
      bookBtn.disabled = true;
      bookBtn.onclick = function() { return false; };
    }
  }
  if (apptsBtn)  apptsBtn.style.display  = currentUser ? '' : 'none';
  if (cartBtn)   cartBtn.style.display   = currentUser ? '' : 'none';
  if (adminBtn)  adminBtn.style.display  = currentAdmin ? '' : 'none';

  if (currentAdmin && userInfo) userInfo.textContent = currentAdmin.name;
  else if (currentUser && userInfo) userInfo.textContent = currentUser.name;

  updateCartBadge();
  renderNotificationCenter();
}

// ── LOGOUT ──
function logout() {
  currentUser = null; currentAdmin = null;
  Cookie.remove('currentUser');
  Cookie.remove('currentAdmin');
  Session.remove('cart');              
  localStorage.removeItem('aqsmile_cart');
  window.location.href = 'logout.php';
}

// ── AUTH GUARD ──
function requireAuth(redirect) {
  if (!currentUser && !currentAdmin) {
    window.location.href = 'login.php';
    return;
  }
  if (currentAdmin && redirect === 'booking.php') {
    showToast('Admins cannot book appointments.');
    return;
  }
  window.location.href = redirect;
}

// ── CART BADGE ──
function updateCartBadge() {
  const cartItems = Session.get('cart') || []; // was DB.get
  const total = cartItems.reduce((s, c) => s + c.qty, 0);
  const badge = document.getElementById('cart-badge');
  if (badge) badge.textContent = total;
}

function isForCurrentUser(item) {
  if (!currentUser || !item) return false;
  return item.userId === currentUser.id ||
    item.userEmail === currentUser.email ||
    item.userName === currentUser.name;
}

function getUserNotifications() {
  return (DB.get('notifications') || []).filter(item => (item.audience || 'user') === 'user' && isForCurrentUser(item));
}

function getUserAppointments() {
  return (DB.get('appointments') || []).filter(isForCurrentUser);
}

function formatStatusLabel(status) {
  return (status || 'pending').replace('_', ' ');
}

function renderNotificationCenter() {
  if (window.AquaNotify) return;

  const navLinks = document.getElementById('nav-links');
  if (document.body.classList.contains('admin-body')) return;
  if (!navLinks || !currentUser || currentAdmin) return;

  let wrap = document.getElementById('notify-wrap');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.className = 'notify-wrap';
    wrap.id = 'notify-wrap';
    wrap.innerHTML = `
      <button class="notify-btn" type="button" onclick="toggleNotifications()" aria-label="Notifications">
        <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        <span class="notify-count" id="notify-count">0</span>
      </button>
      <div class="notify-panel" id="notify-panel"></div>`;

    const userInfo = document.getElementById('nav-user-info');
    navLinks.insertBefore(wrap, userInfo || navLinks.firstChild);
  }

  renderNotificationPanel();
}

function toggleNotifications() {
  const panel = document.getElementById('notify-panel');
  if (!panel) return;
  panel.classList.toggle('open');
  renderNotificationPanel();
}

function renderNotificationPanel() {
  const panel = document.getElementById('notify-panel');
  const badge = document.getElementById('notify-count');
  if (!panel || !badge || !currentUser) return;

  const notifications = getUserNotifications();
  const appointments = getUserAppointments();
  const unread = notifications.filter(n => !n.read).length;

  badge.textContent = unread;
  badge.classList.toggle('show', unread > 0);

  const notificationHtml = notifications.length
    ? notifications.map(n => `
      <div class="notify-item ${n.read ? '' : 'unread'}">
        <div class="notify-message">${n.message}</div>
        <div class="notify-meta">${n.createdAt || ''}</div>
      </div>`).join('')
    : '<div class="notify-empty">No appointment updates yet.</div>';

  const appointmentHtml = appointments.length
    ? appointments.map(a => `
      <div class="notify-item">
        <div class="notify-booking-title">${a.serviceName || 'Dental Service'} - ${formatStatusLabel(a.status)}</div>
        <div class="notify-booking-body">${a.date || '-'} at ${a.time || '-'}<br>${a.dentistName || 'Dentist pending'}</div>
        ${a.status === 'pending'
          ? `<button class="notify-cancel-btn" type="button" onclick="cancelUserAppointment('${a.id}')">Cancel Booking</button>`
          : ''}
      </div>`).join('')
    : '<div class="notify-empty">No bookings yet.</div>';

  panel.innerHTML = `
    <div class="notify-panel-head">
      <div class="notify-panel-title">Notifications</div>
      <button class="notify-mark-btn" type="button" onclick="markNotificationsRead()">Mark read</button>
    </div>
    <div class="notify-section-label">Updates</div>
    ${notificationHtml}
    <div class="notify-section-label">My Bookings</div>
    ${appointmentHtml}`;
}

function markNotificationsRead() {
  const all = DB.get('notifications') || [];
  all.forEach(n => {
    if ((n.audience || 'user') === 'user' && isForCurrentUser(n)) n.read = true;
  });
  DB.set('notifications', all);
  apiRequest('mark_notifications_read', { userId: currentUser?.id }).catch(err => {
    console.warn('Notification read sync failed:', err.message);
  });
  renderNotificationPanel();
}

function cancelUserAppointment(id) {
  const appts = DB.get('appointments') || [];
  const appt = appts.find(a => a.id === id && isForCurrentUser(a));
  if (!appt) return;

  if (appt.status !== 'pending') {
    showToast('Only pending appointments can be cancelled.');
    return;
  }

  if (!confirm('Cancel this pending appointment?')) return;

  apiRequest('cancel_appointment', { id, userId: currentUser.id }).then(() => {
    appt.status = 'cancelled';
    appt.cancelledBy = 'user';
    appt.cancellationReason = 'Cancelled by patient before admin approval.';
    DB.set('appointments', appts);

    const notifications = DB.get('notifications') || [];
    notifications.unshift({
      id: 'N' + Date.now(),
      audience: 'admin',
      userId: appt.userId || '',
      userEmail: appt.userEmail || '',
      userName: appt.userName || 'Patient',
      appointmentId: appt.id,
      message: `${appt.userName || 'Patient'} cancelled the appointment for ${appt.serviceName || 'the dental service'} on ${appt.date} at ${appt.time}.`,
      createdAt: new Date().toLocaleString('en-PH'),
      read: false,
    });
    DB.set('notifications', notifications.slice(0, 30));

    renderNotificationPanel();
    showToast('Your pending appointment has been cancelled.');
  }).catch(err => {
    console.warn('Appointment cancellation failed:', err.message);
    showToast(err.message || 'Appointment cancellation failed.');
  });
}

function notifyCurrentUser() {
  if (!currentUser) return;

  const note = getUserNotifications().find(n => !n.read);
  if (note) showToast(note.message);
  renderNotificationPanel();
}

// ══════════════════════════════════════
//  HOMEPAGE RENDERING (index.html only)
// ══════════════════════════════════════

function renderHomeDentists() {
  const grid = document.getElementById('home-dentist-grid');
  if (!grid) return;
  grid.innerHTML = DENTISTS.map(d => `
    <div class="card dentist-card" onclick="window.location.href='dentists.php'">
      <div class="dentist-avatar">
        <img
          src="${d.photo}"
          alt="Photo of ${d.name}"
          onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e8f4f4%22/%3E%3Ccircle cx=%2250%22 cy=%2238%22 r=%2218%22 fill=%22%2390c4c4%22/%3E%3Cellipse cx=%2250%22 cy=%2280%22 rx=%2228%22 ry=%2218%22 fill=%22%2390c4c4%22/%3E%3C/svg%3E'"
        />
      </div>
      <div class="dentist-card-body">
        <div class="dentist-name">${d.name}</div>
        <div class="dentist-cred">${d.spec}</div>
        <div class="dentist-desc">${d.desc.substring(0, 100)}…</div>
      </div>
    </div>`).join('');
}

function renderHomeServices() {
  const grid = document.getElementById('home-services-grid');
  if (!grid) return;
  grid.innerHTML = SERVICES.slice(0, 4).map(s => `
    <div class="card">
      <div class="service-img">
        <img src="${s.photo}" alt="${s.name}" onerror="this.style.display='none'" />
      </div>
      <div class="service-name">${s.name}</div>
      <div class="service-desc">${s.desc}</div>
      <div class="service-price">${s.price}</div>
    </div>`).join('');
}

// ── SERVICE CATEGORY FILTER (Lesson 2: Switch Statement) ──
// Each service is tagged; filterServices uses a switch to map categories
const SERVICE_CATEGORIES = {
  S1: 'preventive', S2: 'preventive', S9: 'preventive',
  S3: 'restorative', S6: 'restorative', S7: 'restorative',
  S4: 'cosmetic',   S5: 'cosmetic',   S8: 'cosmetic',
};

function getServiceCategory(id) {
  // Switch statement — Lesson 2
  switch (SERVICE_CATEGORIES[id]) {
    case 'preventive':   return 'preventive';
    case 'cosmetic':     return 'cosmetic';
    case 'restorative':  return 'restorative';
    default:             return 'all';
  }
}

function filterServices(category) {
  // Update active tab
  document.querySelectorAll('#service-filter-tabs .tab-btn').forEach(btn => {
    btn.classList.toggle('active', btn.textContent.toLowerCase() === category || (category === 'all' && btn.textContent === 'All'));
  });

  const grid = document.getElementById('home-services-grid');
  if (!grid) return;

  // Boolean: true if showing all, false if filtered
  const showAll = (category === 'all');

  // Filter array (forEach / array concepts — Lesson 2)
  const filtered = showAll
    ? SERVICES.slice(0, 4)
    : SERVICES.filter(s => getServiceCategory(s.id) === category).slice(0, 4);

  if (filtered.length === 0) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><div class="icon">🦷</div><p>No services in this category shown on home. <a href="services.php" style="color:var(--aqua)">View all services →</a></p></div>`;
    return;
  }

  grid.innerHTML = filtered.map(s => `
    <div class="card">
      <div class="service-img">
        <img src="${s.photo}" alt="${s.name}" onerror="this.style.display='none'" />
      </div>
      <div class="service-name">${s.name}</div>
      <div class="service-desc">${s.desc}</div>
      <div class="service-price">${s.price}</div>
    </div>`).join('');
}

// ── DEALS DATA (Lesson 2: Arrays, Data Types, String Manipulation) ──
const DEALS = [
  {
    id: 'DEAL1',
    service: 'S1',
    name: 'Cleaning + X-Ray Bundle',
    desc: 'Complete preventive care package — professional cleaning paired with digital X-rays for a full oral health check.',
    photo: 'images/dental cleaning.jpeg',
    originalPrice: 1250,
    discountPct: 30,
    badge: 'Best Value',
    badgeClass: '',
    code: 'CLEAN30',
    validUntil: null, // computed from end-of-month
  },
  {
    id: 'DEAL2',
    service: 'S4',
    name: 'Teeth Whitening Special',
    desc: 'Professional-grade whitening treatment for a noticeably brighter smile — results you\'ll love from the first session.',
    photo: 'images/teeth whitening.jpg',
    originalPrice: 3500,
    discountPct: 20,
    badge: 'Hot Deal',
    badgeClass: 'hot',
    code: 'WHITE20',
    validUntil: null,
  },
  {
    id: 'DEAL3',
    service: 'S9',
    name: 'Kids Check-Up Promo',
    desc: 'Gentle pediatric check-up for children 2–12, including fluoride treatment — making dental visits a happy experience.',
    photo: 'images/pediatric check up.jpg',
    originalPrice: 900,
    discountPct: 25,
    badge: 'New',
    badgeClass: 'new-badge',
    code: 'KIDS25',
    validUntil: null,
  },
];

// Lesson 2: Numeric operations — compute discounted price, savings amount
function computeDiscount(originalPrice, discountPct) {
  const discount = originalPrice * (discountPct / 100);        // numeric arithmetic
  const discounted = originalPrice - discount;                  // subtraction
  const savings = discount;
  return { discounted: Math.round(discounted), savings: Math.round(savings) };
}

// Lesson 3: String manipulation — format price as Philippine peso string
function formatPeso(amount) {
  // String concatenation + toLocaleString
  return '₱' + amount.toLocaleString('en-PH');
}

// Lesson 2: String operations — truncate description
function truncateStr(str, maxLen) {
  if (str.length <= maxLen) return str;           // boolean check
  return str.substring(0, maxLen) + '…';          // substring — string manipulation
}

function renderDeals() {
  const grid = document.getElementById('deals-grid');
  if (!grid) return;
  const adminViewing = isAdmin();

  // forEach loop — Lesson 2 (JS forEach equivalent)
  let html = '';
  DEALS.forEach(deal => {
    const { discounted, savings } = computeDiscount(deal.originalPrice, deal.discountPct);
    const badgeClass = deal.badgeClass ? ' ' + deal.badgeClass : '';
    html += `
      <div class="deal-card">
        <div class="deal-badge${badgeClass}">${deal.badge} · ${deal.discountPct}% OFF</div>
        <div class="deal-img">
          <img src="${deal.photo}" alt="${deal.name}" onerror="this.style.display='none'" />
        </div>
        <div class="deal-body">
          <div class="deal-name">${deal.name}</div>
          <div class="deal-desc">${truncateStr(deal.desc, 110)}</div>
          <div class="deal-pricing">
            <span class="deal-original">${formatPeso(deal.originalPrice)}</span>
            <span class="deal-discounted">${formatPeso(discounted)}</span>
            <span class="deal-savings">Save ${formatPeso(savings)}</span>
          </div>
          <button
            class="deal-book-btn ${adminViewing ? 'admin-disabled' : ''}"
            onclick="${adminViewing ? 'return false;' : `bookDeal('${deal.code}')`}"
            ${adminViewing ? 'disabled' : ''}
          >${adminViewing ? 'View Only' : 'Book This Deal'}</button>
        </div>
      </div>`;
  });
  grid.innerHTML = html;
}

function bookDeal(code) {
  if (isAdmin()) {
    showToast('Admin accounts cannot book appointments.');
    return;
  }

  if (!currentUser) {
    showToast('Please log in to book a deal.');
    setTimeout(() => window.location.href = 'login.php', 1200);
    return;
  }
  // Store promo code for booking page
  DB.set('activePromo', code);
  showToast('Promo code ' + code + ' applied! Redirecting to booking…');
  setTimeout(() => window.location.href = 'booking.php', 1400);
}

// ── PROMO CODE VALIDATOR (Lesson 2: Associative Array / Object, String comparison) ──
const PROMO_CODES = {
  'SMILE20':  { discount: 20, desc: '20% off your next appointment!' },
  'CLEAN30':  { discount: 30, desc: '30% off Dental Cleaning + X-Ray bundle.' },
  'WHITE20':  { discount: 20, desc: '20% off Teeth Whitening.' },
  'KIDS25':   { discount: 25, desc: '25% off Pediatric Check-Up.' },
  'NEWSMILE': { discount: 15, desc: '15% off for new patients.' },
};

function applyPromoCode() {
  const input = document.getElementById('promo-code-input');
  const result = document.getElementById('promo-code-result');
  if (!input || !result) return;

  // String manipulation: toUpperCase, trim — Lesson 3
  const code = input.value.trim().toUpperCase();

  // Boolean logic + associative array lookup — Lesson 2
  const isValid = (code.length > 0) && (PROMO_CODES[code] !== undefined);

  if (!isValid) {
    result.innerHTML = `<div class="error-msg" style="margin-top:12px;margin-bottom:0">
      Code "<strong>${code}</strong>" is not valid or has expired.
    </div>`;
    return;
  }

  const promo = PROMO_CODES[code];
  DB.set('activePromo', code);
  result.innerHTML = `<div class="success-msg" style="margin-top:12px;margin-bottom:0">
    <strong>${code}</strong> applied — ${promo.desc} Discount: <strong>${promo.discount}%</strong>
  </div>`;
  showToast('Promo code ' + code + ' saved! Use it when you book.');
}

// ── COUNTDOWN TIMER (Lesson 2: Numeric operations, conditionals) ──
// Promo ends at end of current month
function getPromoEndDate() {
  const now = new Date();
  // End of current month at midnight
  return new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
}

function padNum(n) {
  // String padding — Lesson 3 string manipulation
  return n < 10 ? '0' + n : String(n);
}

function updateCountdown() {
  const end = getPromoEndDate();
  const now = new Date();
  let diff = Math.max(0, Math.floor((end - now) / 1000)); // numeric arithmetic

  // Numeric decomposition using division and modulo — Lesson 2 arithmetic
  const days  = Math.floor(diff / 86400); diff -= days  * 86400;
  const hours = Math.floor(diff / 3600);  diff -= hours * 3600;
  const mins  = Math.floor(diff / 60);    diff -= mins  * 60;
  const secs  = diff;

  const elDays  = document.getElementById('cd-days');
  const elHours = document.getElementById('cd-hours');
  const elMins  = document.getElementById('cd-mins');
  const elSecs  = document.getElementById('cd-secs');

  if (elDays)  elDays.textContent  = padNum(days);
  if (elHours) elHours.textContent = padNum(hours);
  if (elMins)  elMins.textContent  = padNum(mins);
  if (elSecs)  elSecs.textContent  = padNum(secs);
}

// ── CLINIC STATS STRIP (Lesson 2: Data Types — int, string, bool) ──
// Demonstrates different JS data types rendered into a UI trust bar
const CLINIC_STATS = [
  { num: 2400,  suffix: '+', label: 'Happy Patients',    type: 'integer'  },
  { num: 12,    suffix: ' yrs', label: 'Years of Service', type: 'integer'  },
  { num: 98.6,  suffix: '%', label: 'Satisfaction Rate', type: 'float'    },
  { num: 3,     suffix: '',   label: 'Expert Dentists',   type: 'integer'  },
];

function renderClinicStats() {
  const strip = document.getElementById('clinic-stats-strip');
  if (!strip) return;
  // forEach — Lesson 2
  strip.innerHTML = CLINIC_STATS.map(stat => `
    <div class="stat-item">
      <div class="stat-num">${stat.num}${stat.suffix}</div>
      <div class="stat-label">${stat.label}</div>
    </div>`).join('');
}

// SVG icons
const TIP_ICONS = {
  urgent: `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
  general: `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
  care:    `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>`,
};

const REFRESH_ICON = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>`;

const DENTAL_TIPS = [
  { type: 'urgent',  text: 'Bleeding gums when you brush is not normal — it may indicate early-stage gum disease. Schedule a check-up at your earliest convenience.', source: 'Philippine Dental Association' },
  { type: 'general', text: 'Brush your teeth for at least two minutes, twice daily — once in the morning and once before bed.', source: 'ADA Oral Health Guidelines' },
  { type: 'care',    text: 'Drinking water after meals helps neutralize acid and rinse away food debris, reducing the risk of decay.', source: 'AquaSmile Clinical Advisory' },
  { type: 'general', text: 'Replace your toothbrush every three months, or earlier if the bristles show visible wear.', source: 'WHO Oral Health Recommendations' },
  { type: 'urgent',  text: 'Prolonged sensitivity to hot or cold temperatures lasting beyond 30 seconds may indicate nerve involvement — consult your dentist promptly.', source: 'AquaSmile Clinical Advisory' },
  { type: 'care',    text: 'Flossing removes bacteria from areas between teeth that brushing cannot reach — up to 40% of tooth surfaces.', source: 'Journal of Periodontology' },
  { type: 'general', text: 'Firm, fibrous foods such as carrots and celery stimulate saliva production and naturally assist in cleaning tooth surfaces.', source: 'AquaSmile Clinical Advisory' },
  { type: 'urgent',  text: 'Acidic beverages — including coffee, tea, and soft drinks — gradually erode enamel. Rinsing with water immediately after consumption helps reduce damage.', source: 'AquaSmile Clinical Advisory' },
  { type: 'care',    text: 'Saliva production decreases significantly during sleep, leaving teeth more vulnerable to bacterial activity. Evening brushing is especially important.', source: 'ADA Oral Health Guidelines' },
  { type: 'general', text: 'Mouthguards are recommended for contact sports at any age. A custom-fitted guard from your dentist provides significantly better protection.', source: 'Philippine Dental Association' },
];

// Returns today's date as a readable string — Lesson 3 date handling
function getTodayLabel() {
  const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const now = new Date();
  return days[now.getDay()] + ', ' + months[now.getMonth()] + ' ' + now.getDate();
}

function getRandomTip() {
  // Math.random() — Lesson 2: random number condition
  const randomIndex = Math.floor(Math.random() * DENTAL_TIPS.length);
  return DENTAL_TIPS[randomIndex];
}

function renderDailyTip(tip) {
  const widget = document.getElementById('daily-tip-widget');
  if (!widget) return;

  // if/else condition based on random tip type — Lesson 2
  let typeLabel;
  if (tip.type === 'urgent') {
    typeLabel = 'Clinical Advisory';
  } else if (tip.type === 'care') {
    typeLabel = 'Oral Care';
  } else {
    typeLabel = 'Daily Tip';
  }

  widget.innerHTML = `
    <div class="tip-widget tip-${tip.type}">
      <div class="tip-icon-wrap">${TIP_ICONS[tip.type]}</div>
      <div class="tip-body">
        <div class="tip-meta">
          <span class="tip-label">${typeLabel}</span>
          <span class="tip-day">${getTodayLabel()}</span>
        </div>
        <div class="tip-text">${tip.text}</div>
        <div class="tip-source">${tip.source}</div>
      </div>
      <button class="tip-refresh-btn" onclick="refreshTip()">${REFRESH_ICON} Refresh</button>
    </div>`;
}

function refreshTip() {
  const tip = getRandomTip();
  renderDailyTip(tip);
}

// ── INIT ──
async function init() {
  if (localStorage.getItem('aqsmile_cart') !== null) {
    localStorage.removeItem('aqsmile_cart');
  }
  
  await syncCatalogFromDatabase();
  updateNav();
  notifyCurrentUser();
  renderHomeDentists();
  renderHomeServices();
  renderDeals();
  renderClinicStats();
  updateCountdown();
  setInterval(updateCountdown, 1000);
  renderDailyTip(getRandomTip());
}

init();

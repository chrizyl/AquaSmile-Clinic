const adminState = {
  year: new Date().getFullYear(),
  month: new Date().getMonth(),
};

const ADMIN_SAMPLE_ORDERS = [
  {
    id: 'O1001',
    customer: 'Maria Santos',
    total: 2198,
    status: 'pending',
    items: [
      { name: 'Sonic Pro Toothbrush', qty: 1, unit_price: 1299 },
      { name: 'Teeth Whitening Strips', qty: 1, unit_price: 899 },
    ],
  },
  {
    id: 'O1002',
    customer: 'Juan Dela Cruz',
    total: 548,
    status: 'completed',
    items: [
      { name: 'WhiteGlow Toothpaste', qty: 1, unit_price: 299 },
      { name: 'Tongue Scraper Set', qty: 1, unit_price: 249 },
    ],
  },
];

const ADMIN_DEFAULT_STOCK = {
  P1: 12, P2: 30, P3: 24, P4: 18, P5: 16, P6: 20, P7: 15, P8: 10,
  S1: 8, S2: 10, S3: 5, S4: 6, S5: 7, S6: 4, S7: 4, S8: 3, S9: 9,
};

function adminPeso(value) {
  return 'PHP ' + Number(value || 0).toLocaleString('en-PH');
}

function adminStatus(status) {
  const clean = status || 'pending';
  const label = clean.replace('_', ' ');
  return `<span class="status-pill status-${clean}">${label}</span>`;
}

function isActiveAppointmentStatus(status) {
  return !['cancelled', 'user_cancelled'].includes(status);
}

function adminTableEmpty(colspan, label) {
  return `<tr><td colspan="${colspan}"><div class="empty-admin">${label}</div></td></tr>`;
}

function adminReadCart() {
  try {
    return JSON.parse(localStorage.getItem('aqCart') || '[]');
  } catch {
    return [];
  }
}

function adminReadOrders() {
  return DB.get('orders') || ADMIN_SAMPLE_ORDERS;
}

function adminReadStock() {
  return DB.get('adminStock') || { ...ADMIN_DEFAULT_STOCK };
}

function adminWriteStock(stock) {
  DB.set('adminStock', stock);
}

function adminReadCustomProducts() {
  return DB.get('adminProducts') || [];
}

function adminReadCustomServices() {
  return DB.get('adminServices') || [];
}

function adminAllProducts() {
  return DB.get('dbProducts') || PRODUCTS;
}

function adminAllServices() {
  return DB.get('dbServices') || SERVICES;
}

function adminSeedAppointments(appts) {
  if (appts.length) return appts;

  const today = new Date();
  const pad = n => String(n).padStart(2, '0');
  const date = offset => {
    const d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + offset);
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  };

  const sample = [
    {
      id: 'A1001',
      userId: 'U1001',
      userName: 'Maria Santos',
      userEmail: 'maria@example.com',
      serviceName: 'Dental Cleaning',
      dentistId: 'D1',
      dentistName: 'Dr. Sophia Reyes',
      date: date(1),
      time: '9:00 AM',
      status: 'pending',
    },
    {
      id: 'A1002',
      userId: 'U1002',
      userName: 'Juan Dela Cruz',
      userEmail: 'juan@example.com',
      serviceName: 'Teeth Whitening',
      dentistId: 'D2',
      dentistName: 'Dr. Marcus Tan',
      date: date(2),
      time: '2:00 PM',
      status: 'confirmed',
    },
    {
      id: 'A1003',
      userId: 'U1003',
      userName: 'Ana Reyes',
      userEmail: 'ana@example.com',
      serviceName: 'Pediatric Check-Up',
      dentistId: 'D3',
      dentistName: 'Dr. Leila Varon',
      date: date(4),
      time: '10:00 AM',
      status: 'completed',
    },
  ];

  DB.set('appointments', sample);
  return sample;
}

function adminGetAppointments() {
  return DB.get('appointments') || [];
}

function showAdminView(view) {
  document.querySelectorAll('.admin-view').forEach(panel => {
    panel.classList.toggle('active', panel.dataset.adminView === view);
  });

  document.querySelectorAll('.admin-side-link').forEach(link => {
    link.classList.toggle('active', link.dataset.view === view);
  });

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function renderAdminStats(appts, users, cart, orders) {
  const pendingCount = appts.filter(a => a.status === 'pending').length;
  const cartCount = cart.reduce((sum, item) => sum + Number(item.qty || item.quantity || 0), 0);
  const revenue = orders.reduce((sum, order) => sum + Number(order.total || 0), 0);

  document.getElementById('stat-appointments').textContent = appts.length;
  document.getElementById('stat-pending').textContent = `${pendingCount} pending appointments`;
  document.getElementById('stat-users').textContent = users.length;
  document.getElementById('stat-cart').textContent = cartCount;
  document.getElementById('stat-revenue').textContent = adminPeso(revenue);
}

function renderAppointments(appts) {
  const tbody = document.getElementById('appointments-table');
  if (!tbody) return;

  tbody.innerHTML = appts.length
    ? appts.slice(0, 6).map(a => `
      <tr>
        <td>${a.id}</td>
        <td><strong>${a.userName || 'Walk-in Patient'}</strong><br><small>${a.userEmail || ''}</small></td>
        <td>${a.serviceName || a.serviceId || 'Service'}</td>
        <td>${a.dentistName || a.dentistId || 'Unassigned'}</td>
        <td>${a.date || '-'}<br><small>${a.time || ''}</small></td>
        <td>${adminStatus(a.status)}</td>
      </tr>`).join('')
    : adminTableEmpty(6, 'No appointments yet.');
}

function renderManageAppointments(appts) {
  const tbody = document.getElementById('appointments-manage-table');
  if (!tbody) return;

  tbody.innerHTML = appts.length
    ? appts.map(a => `
      <tr>
        <td>${a.id}</td>
        <td><strong>${a.userName || 'Walk-in Patient'}</strong><br><small>${a.userEmail || ''}</small></td>
        <td>${a.serviceName || a.serviceId || 'Service'}</td>
        <td>${a.dentistName || a.dentistId || 'Unassigned'}</td>
        <td>${a.date || '-'}<br><small>${a.time || ''}</small></td>
        <td>${adminStatus(a.status)}</td>
        <td>
          <div class="action-row">
            ${a.status === 'pending'
              ? `<button class="action-btn" type="button" onclick="updateAppointmentStatus('${a.id}', 'confirmed')">Confirm</button>
                 <button class="action-btn danger" type="button" onclick="updateAppointmentStatus('${a.id}', 'cancelled')">Cancel</button>`
              : ''}
            ${a.status === 'confirmed'
              ? `<button class="action-btn" type="button" onclick="updateAppointmentStatus('${a.id}', 'completed')">Complete</button>`
              : ''}
          </div>
        </td>
      </tr>`).join('')
    : adminTableEmpty(7, 'No appointments to manage.');
}

async function updateAppointmentStatus(id, status) {
  const appts = adminGetAppointments();
  const target = appts.find(a => a.id === id);
  if (!target) return;

  let reason = '';
  if (status === 'cancelled') {
    reason = prompt('Reason for cancellation to send to the patient:');
    if (reason === null) return;
    reason = reason.trim();
    if (!reason) {
      showToast('Please provide a cancellation reason.');
      return;
    }
  }

  try {
    const result = await apiRequest('update_appointment', { id, status, reason });
    Object.assign(target, result.appointment);
  } catch (err) {
    target.status = status;
    target.cancelledBy = status === 'cancelled' ? 'admin' : target.cancelledBy || '';
    target.cancellationReason = reason || target.cancellationReason || '';
    addAdminNotification(target, status, reason);
  }

  DB.set('appointments', appts);
  adminRefresh(false);
  showToast(`Appointment ${id} marked as ${status}.`);
}

function addAdminNotification(appt, status, reason = '') {
  const notifications = DB.get('notifications') || [];
  const statusText = {
    confirmed: 'confirmed',
    completed: 'completed',
    cancelled: 'cancelled',
    pending: 'set to pending',
  }[status] || status;

  notifications.unshift({
    id: 'N' + Date.now(),
    userId: appt.userId || '',
    userEmail: appt.userEmail || '',
    userName: appt.userName || 'Patient',
    appointmentId: appt.id,
    message: `Your appointment for ${appt.serviceName || 'your dental service'} on ${appt.date} at ${appt.time} has been ${statusText}.${reason ? ' Reason: ' + reason : ''}`,
    reason: reason,
    createdAt: new Date().toLocaleString('en-PH'),
    read: false,
  });

  DB.set('notifications', notifications.slice(0, 30));
}

function renderNotifications() {
  const tbody = document.getElementById('notifications-table');
  if (!tbody) return;

  const notifications = DB.get('notifications') || [];
  tbody.innerHTML = notifications.length
    ? notifications.map(n => `
      <tr>
        <td>${n.userName}</td>
        <td>${n.appointmentId}</td>
        <td>${n.message}${n.reason ? `<br><small>Reason: ${n.reason}</small>` : ''}</td>
        <td>${n.createdAt}</td>
      </tr>`).join('')
    : adminTableEmpty(4, 'No notifications sent yet. Confirm or cancel an appointment to create one.');
}

function renderDentistPatients(appts) {
  const tbody = document.getElementById('dentist-patient-table');
  if (!tbody) return;

  const rows = [...appts].sort((a, b) => `${a.dentistName}${a.date}${a.time}`.localeCompare(`${b.dentistName}${b.date}${b.time}`));
  tbody.innerHTML = rows.length
    ? rows.map(a => `
      <tr>
        <td>${a.dentistName || a.dentistId || 'Unassigned'}</td>
        <td><strong>${a.userName || 'Patient'}</strong><br><small>${a.userEmail || ''}</small></td>
        <td>${a.serviceName || a.serviceId || 'Service'}</td>
        <td>${a.date || '-'}</td>
        <td>${a.time || '-'}</td>
        <td>${adminStatus(a.status)}</td>
      </tr>`).join('')
    : adminTableEmpty(6, 'No dentist schedules yet.');
}

function renderUsers(users) {
  const tbody = document.getElementById('users-table');
  if (!tbody) return;

  tbody.innerHTML = users.length
    ? users.map(u => `
      <tr>
        <td>${u.id}</td>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.contact || '-'}</td>
      </tr>`).join('')
    : adminTableEmpty(4, 'No patient accounts yet.');
}

function renderOrders(orders) {
  const ordersTbody = document.getElementById('orders-table');
  const itemsTbody = document.getElementById('order-items-table');
  if (!ordersTbody || !itemsTbody) return;

  ordersTbody.innerHTML = orders.length
    ? orders.map(o => `
      <tr>
        <td>${o.id}</td>
        <td>${o.customer || o.customer_name || 'Customer'}</td>
        <td>${adminPeso(o.total)}</td>
        <td>${adminStatus(o.status)}</td>
      </tr>`).join('')
    : adminTableEmpty(4, 'No orders yet.');

  const items = orders.flatMap(o => (o.items || []).map(item => ({ ...item, orderId: o.id })));
  itemsTbody.innerHTML = items.length
    ? items.map(item => `
      <tr>
        <td>${item.name}</td>
        <td>${item.orderId}</td>
        <td>${item.qty || item.quantity}</td>
        <td>${adminPeso(item.unit_price || item.price)}</td>
      </tr>`).join('')
    : adminTableEmpty(4, 'No order items yet.');
}

function renderCart(cart) {
  const tbody = document.getElementById('cart-table');
  if (!tbody) return;

  tbody.innerHTML = cart.length
    ? cart.map(item => {
      const product = adminAllProducts().find(p => p.id === item.id) || item;
      const qty = Number(item.qty || item.quantity || 0);
      const price = Number(product.price || item.price || 0);
      return `
        <tr>
          <td>${product.name || item.name || item.id}</td>
          <td>${qty}</td>
          <td>${adminPeso(price)}</td>
          <td>${adminPeso(price * qty)}</td>
        </tr>`;
    }).join('')
    : adminTableEmpty(4, 'No active cart items.');
}

function renderCatalog() {
  const productsGrid = document.getElementById('products-grid-admin');
  const servicesGrid = document.getElementById('services-grid-admin');
  const stock = adminReadStock();

  if (productsGrid) {
    productsGrid.innerHTML = adminAllProducts().map(p => `
      <div class="catalog-item">
        <div class="catalog-main">
          <img src="${p.photo || p.img || ''}" alt="${p.name}" onerror="this.style.opacity='0'">
          <div>
            <strong>${p.name}</strong>
            <span>${adminPeso(p.price)} - ${p.category || 'Dental Product'}</span>
          </div>
        </div>
        <div class="stock-control" aria-label="Product quantity">
          <button class="stock-btn" type="button" onclick="changeCatalogStock('${p.id}', -1)">-</button>
          <span class="stock-value">${p.stock ?? stock[p.id] ?? 0}</span>
          <button class="stock-btn" type="button" onclick="changeCatalogStock('${p.id}', 1)">+</button>
        </div>
      </div>`).join('');
  }

  if (servicesGrid) {
    servicesGrid.innerHTML = adminAllServices().map(s => `
      <div class="catalog-item">
        <div class="catalog-main">
          <img src="${s.photo || ''}" alt="${s.name}" onerror="this.style.opacity='0'">
          <div>
            <strong>${s.name}</strong>
            <span>${s.price} - ${s.category || 'Service'} slots</span>
          </div>
        </div>
        <div class="stock-control" aria-label="Service quantity">
          <button class="stock-btn" type="button" onclick="changeCatalogStock('${s.id}', -1)">-</button>
          <span class="stock-value">${s.dailySlots ?? stock[s.id] ?? 0}</span>
          <button class="stock-btn" type="button" onclick="changeCatalogStock('${s.id}', 1)">+</button>
        </div>
      </div>`).join('');
  }
}

async function changeCatalogStock(id, delta) {
  const stock = adminReadStock();
  const item = adminAllProducts().find(p => p.id === id) || adminAllServices().find(s => s.id === id);
  const current = Number(item?.stock ?? item?.dailySlots ?? stock[id] ?? 0);
  const quantity = Math.max(0, current + delta);
  stock[id] = quantity;
  adminWriteStock(stock);

  try {
    await apiRequest('update_stock', {
      id,
      quantity,
      type: adminAllServices().find(s => s.id === id) ? 'service' : 'product',
    });
    if (item) {
      if (item.stock !== undefined) item.stock = quantity;
      if (item.dailySlots !== undefined) item.dailySlots = quantity;
    }
  } catch (err) {
    console.warn('Stock saved locally because API failed:', err.message);
  }

  renderCatalog();
}

function renderDentists() {
  const list = document.getElementById('dentist-list');
  if (!list) return;

  list.innerHTML = DENTISTS.map(d => `
    <div class="dentist-row">
      <img src="${d.photo}" alt="${d.name}" onerror="this.style.opacity='0'">
      <div>
        <strong>${d.name}</strong>
        <span>${d.spec}<br>${d.cred}</span>
      </div>
    </div>`).join('');
}

function adminChangeMonth(delta) {
  adminState.month += delta;
  if (adminState.month < 0) {
    adminState.month = 11;
    adminState.year -= 1;
  }
  if (adminState.month > 11) {
    adminState.month = 0;
    adminState.year += 1;
  }
  renderDentistCalendars(adminGetAppointments());
}

function renderDentistCalendars(appts) {
  const grid = document.getElementById('dentist-calendar-grid');
  const title = document.getElementById('admin-calendar-title');
  if (!grid) return;

  const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const dayNames = ['Su','Mo','Tu','We','Th','Fr','Sa'];
  const daysInMonth = new Date(adminState.year, adminState.month + 1, 0).getDate();
  const firstDay = new Date(adminState.year, adminState.month, 1).getDay();

  if (title) title.textContent = `${monthNames[adminState.month]} ${adminState.year}`;

  grid.innerHTML = DENTISTS.map(dentist => {
    const days = [];
    dayNames.forEach(day => days.push(`<div class="cal-mini-name">${day}</div>`));
    for (let i = 0; i < firstDay; i += 1) days.push('<div class="cal-mini-day"></div>');

    for (let day = 1; day <= daysInMonth; day += 1) {
      const dateStr = `${adminState.year}-${String(adminState.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const count = appts.filter(a => a.dentistId === dentist.id && a.date === dateStr && isActiveAppointmentStatus(a.status)).length;
      days.push(`<div class="cal-mini-day ${count ? 'has-booking' : ''}" data-count="${count || ''}" title="${count} booking(s)">${day}</div>`);
    }

    return `
      <div class="dentist-calendar">
        <div class="dentist-calendar-head">
          <img src="${dentist.photo}" alt="${dentist.name}">
          <div>
            <div class="dentist-calendar-name">${dentist.name}</div>
            <div class="dentist-calendar-spec">${dentist.spec}</div>
          </div>
        </div>
        <div class="calendar-mini">${days.join('')}</div>
      </div>`;
  }).join('');
}

async function loadAdminDatabase() {
  try {
    const data = await apiGet('dashboard');
    const orders = data.orders || [];
    const orderItems = data.orderItems || [];
    orders.forEach(order => {
      order.items = orderItems
        .filter(item => item.order_id === order.id)
        .map(item => ({
          name: item.product_name,
          qty: item.quantity,
          unit_price: item.unit_price,
        }));
    });

    DB.set('appointments', data.appointments || []);
    DB.set('users', data.users || []);
    DB.set('orders', orders);
    DB.set('dbProducts', data.products || []);
    DB.set('dbServices', data.services || []);
    DB.set('notifications', (data.notifications || []).map(n => ({
      id: n.id,
      userId: n.user_id,
      userName: n.user_name,
      appointmentId: n.appointment_id,
      message: n.message,
      createdAt: n.created_at,
      read: Number(n.is_read) === 1,
    })));
    return true;
  } catch (err) {
    console.warn('Using admin local fallback:', err.message);
    if (!(DB.get('appointments') || []).length) {
      DB.set('appointments', adminSeedAppointments([]));
    }
    return false;
  }
}

async function adminRefresh(showMessage = true) {
  await loadAdminDatabase();
  const appointments = adminGetAppointments();
  const users = DB.get('users') || [];
  const cart = adminReadCart();
  const orders = adminReadOrders();

  renderAdminStats(appointments, users, cart, orders);
  renderAppointments(appointments);
  renderManageAppointments(appointments);
  renderDentistPatients(appointments);
  renderUsers(users);
  renderOrders(orders);
  renderCart(cart);
  renderCatalog();
  renderDentists();
  renderDentistCalendars(appointments);
  renderNotifications();

  if (showMessage) showToast('Admin dashboard refreshed.');
}

function initAdminDashboard() {
  adminRefresh(false);
  showAdminView('overview');
}

initAdminDashboard();

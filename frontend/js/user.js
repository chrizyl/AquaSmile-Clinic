let accountData = { user: null, appointments: [], orders: [], notifications: [] };
let profileSnapshot = null;

function accountMessage(message) {
  if (typeof showToast === 'function') showToast(message);
}

function setProfileMessage(messages = [], type = 'error') {
  const panel = document.getElementById('profile-form-message');
  const items = Array.isArray(messages) ? messages : [messages];
  const filtered = items.filter(Boolean);
  panel.hidden = filtered.length === 0;
  panel.classList.toggle('success', type === 'success');
  panel.innerHTML = filtered.length > 1
    ? `<ul>${filtered.map(message => `<li>${escapeHtml(message)}</li>`).join('')}</ul>`
    : escapeHtml(filtered[0] || '');
}

function validateProfile(payload) {
  const errors = [];
  const mobilePattern = /^09\d{9}$/;
  const addressLimits = {
    house_no: ['House No.', 50],
    street: ['Street', 150],
    barangay: ['Barangay', 100],
    city: ['City / Municipality', 100],
    province: ['Province / Region', 100],
    zip_code: ['ZIP Code', 10],
  };

  if (!payload.first_name) errors.push('First name is required.');
  if (!payload.last_name) errors.push('Last name is required.');
  if (!payload.phone) {
    errors.push('Phone number is required.');
  } else if (!mobilePattern.test(payload.phone)) {
    errors.push('Phone number must start with 09 and contain exactly 11 digits.');
  }
  if (payload.birthdate) {
    const birthdate = new Date(`${payload.birthdate}T00:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (Number.isNaN(birthdate.getTime()) || birthdate >= today) {
      errors.push('Birthdate must be a valid past date.');
    }
  }
  if (!['Female', 'Male', 'Prefer not to say'].includes(payload.gender)) {
    errors.push('Please select a valid gender.');
  }
  Object.entries(addressLimits).forEach(([field, [label, limit]]) => {
    if (payload[field].length > limit) errors.push(`${label} must not exceed ${limit} characters.`);
  });
  if (payload.zip_code && !/^\d+$/.test(payload.zip_code)) {
    errors.push('ZIP Code must contain numbers only.');
  }
  if (payload.emergency_contact_number && !mobilePattern.test(payload.emergency_contact_number)) {
    errors.push('Emergency contact number must start with 09 and contain exactly 11 digits.');
  }

  return errors;
}

function escapeHtml(value) {
  const node = document.createElement('div');
  node.textContent = value ?? '';
  return node.innerHTML;
}

function parseLocalDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(' ', 'T'));
  return Number.isNaN(date.getTime()) ? null : date;
}

function formatAccountDate(value, includeTime = false) {
  const date = parseLocalDate(value);
  if (!date) return value || '-';
  return new Intl.DateTimeFormat('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric',
    ...(includeTime ? { hour: 'numeric', minute: '2-digit' } : {}),
  }).format(date);
}

function formatAppointmentTime(value) {
  if (!value) return '-';
  const [hours, minutes] = String(value).split(':');
  const date = new Date();
  date.setHours(Number(hours), Number(minutes || 0), 0, 0);
  return new Intl.DateTimeFormat('en-PH', { hour: 'numeric', minute: '2-digit' }).format(date);
}

function formatMoney(value) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(value) || 0);
}

function formatDeliveryAddress(order) {
  return [
    order.house_no,
    order.street,
    order.barangay,
    order.city,
    order.province,
    order.zip,
  ].map(part => String(part || '').trim()).filter(Boolean).join(', ') || '-';
}

function statusBadge(status) {
  const allowed = ['pending','confirmed','completed','cancelled','archived','processing','out_for_delivery','delivered'];
  const safe = allowed.includes(status) ? status : 'pending';
  return `<span class="status-badge status-${safe.replaceAll('_','-')}">${safe.replaceAll('_',' ')}</span>`;
}

function emptyState(message) {
  return `<div class="empty-history">${escapeHtml(message)}</div>`;
}

function switchSection(section) {
  document.querySelectorAll('[data-section-panel]').forEach(panel => panel.classList.toggle('active', panel.dataset.sectionPanel === section));
  document.querySelectorAll('[data-section]').forEach(button => button.classList.toggle('active', button.dataset.section === section));
  if (window.innerWidth < 850) document.querySelector(`[data-section="${section}"]`)?.scrollIntoView({ behavior:'smooth', inline:'center', block:'nearest' });
  window.history.replaceState(null, '', `#${section}`);
}

function renderProfile(user) {
  profileSnapshot = { ...user };
  const name = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'AquaSmile Patient';
  const initials = `${(user.first_name || 'A')[0]}${(user.last_name || 'S')[0]}`.toUpperCase();
  const genderKey = String(user.gender || '').trim().toLowerCase().replaceAll('_', ' ');
  const genderValues = {
    female: 'Female',
    male: 'Male',
    'prefer not to say': 'Prefer not to say',
  };
  document.getElementById('profile-avatar').textContent = initials;
  document.getElementById('sidebar-name').textContent = name;
  document.getElementById('hero-greeting').textContent = `Hello, ${user.first_name || 'Patient'}!`;
  document.getElementById('overview-profile-name').textContent = name;
  document.getElementById('overview-profile-email').textContent = user.email || '-';
  document.getElementById('profile-first-name').value = user.first_name || '';
  document.getElementById('profile-last-name').value = user.last_name || '';
  document.getElementById('profile-email').value = user.email || '';
  document.getElementById('profile-phone').value = user.phone || '';
  document.getElementById('profile-birthdate').value = user.birthdate || '';
  document.getElementById('profile-gender').value = genderValues[genderKey] || '';
  document.getElementById('profile-house-no').value = user.house_no || '';
  document.getElementById('profile-street').value = user.street || '';
  document.getElementById('profile-barangay').value = user.barangay || '';
  document.getElementById('profile-city').value = user.city || '';
  document.getElementById('profile-province').value = user.province || '';
  document.getElementById('profile-zip-code').value = user.zip_code || '';
  document.getElementById('profile-emergency-name').value = user.emergency_contact_name || '';
  document.getElementById('profile-emergency-number').value = user.emergency_contact_number || '';
  document.getElementById('profile-member-since').textContent = formatAccountDate(user.created_at);
}

function renderAppointments() {
  document.getElementById('appointment-count').textContent = accountData.appointments.length;
  document.getElementById('appointment-list').innerHTML = accountData.appointments.length
    ? accountData.appointments.map(item => `
        <article class="history-item">
          <div>
            <div class="history-title">${escapeHtml(item.serviceName || 'Dental Service')}</div>
            <div class="history-meta">
              ${escapeHtml(formatAccountDate(item.date))} at ${escapeHtml(formatAppointmentTime(item.time))}<br>
              ${escapeHtml(item.dentistName || 'Dentist to be assigned')}
            </div>
          </div>
          <div class="history-side">
            ${statusBadge(item.status)}
            ${item.status === 'pending'
              ? `<button class="history-action-btn cancel" type="button" data-cancel-appointment="${escapeHtml(item.id)}">Cancel Appointment</button>`
              : ''}
          </div>
        </article>`).join('')
    : emptyState('No appointments found yet.');
}

function renderOrders() {
  document.getElementById('order-count').textContent = accountData.orders.length;
  document.getElementById('order-list').innerHTML = accountData.orders.length
    ? accountData.orders.map(item => `
        <article class="history-item">
          <div>
            <div class="history-title">Order #${escapeHtml(item.id)}</div>
            <div class="history-meta">
              ${escapeHtml(formatAccountDate(item.created_at,true))}<br>
              ${escapeHtml((item.payment_method || 'Not specified').replaceAll('_',' ').toUpperCase())}
            </div>
          </div>
          <div class="history-side">
            <div class="history-amount">${escapeHtml(formatMoney(item.total))}</div>
            ${statusBadge(item.status)}
            <button class="history-action-btn" type="button" data-order-details="${escapeHtml(item.id)}">View Details</button>
          </div>
        </article>`).join('')
    : emptyState('No orders found yet.');
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.hidden = false;
  document.body.style.overflow = 'hidden';
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.hidden = true;
  if (!document.querySelector('.account-modal-overlay:not([hidden])')) {
    document.body.style.overflow = '';
  }
}

function openCancellationModal(appointmentId) {
  const appointment = accountData.appointments.find(item => String(item.id) === String(appointmentId));
  if (!appointment || appointment.status !== 'pending') {
    accountMessage('Only pending appointments can be cancelled.');
    return;
  }

  document.getElementById('cancel-appointment-id').value = appointment.id;
  document.getElementById('cancellation-reason').value = '';
  document.getElementById('cancel-appointment-summary').textContent =
    `${appointment.serviceName || 'Dental Service'} on ${formatAccountDate(appointment.date)} at ${formatAppointmentTime(appointment.time)}.`;
  openModal('cancel-modal');
  setTimeout(() => document.getElementById('cancellation-reason').focus(), 0);
}

function openOrderDetails(orderId) {
  const order = accountData.orders.find(item => String(item.id) === String(orderId));
  if (!order) {
    accountMessage('Order details could not be found.');
    return;
  }

  document.getElementById('order-modal-title').textContent = `Order #${order.id}`;
  const products = order.items?.length
    ? order.items.map(item => `
        <div class="order-product">
          <div>
            <div class="order-product-name">${escapeHtml(item.name || 'Product')}</div>
            <div class="order-product-meta">Qty: ${escapeHtml(item.quantity)} &times; ${escapeHtml(formatMoney(item.unit_price))}</div>
          </div>
          <div class="order-product-price">
            <span>Subtotal</span><br>
            <strong>${escapeHtml(formatMoney(item.subtotal))}</strong>
          </div>
        </div>`).join('')
    : emptyState('No product details are available for this order.');

  document.getElementById('order-detail-content').innerHTML = `
    <div class="order-info-grid">
      <div class="order-info"><span>Order Date</span><strong>${escapeHtml(formatAccountDate(order.created_at,true))}</strong></div>
      <div class="order-info"><span>Payment Method</span><strong>${escapeHtml((order.payment_method || 'Not specified').replaceAll('_',' ').toUpperCase())}</strong></div>
      <div class="order-info"><span>Status</span><strong>${statusBadge(order.status)}</strong></div>
      <div class="order-info"><span>Total Amount</span><strong>${escapeHtml(formatMoney(order.total))}</strong></div>
      <div class="order-info"><span>Delivery Address</span><strong>${escapeHtml(formatDeliveryAddress(order))}</strong></div>
      <div class="order-info"><span>Notes</span><strong>${escapeHtml(order.notes || 'None')}</strong></div>
    </div>
    <h3 class="order-products-title">Ordered Products</h3>
    ${products}
    <div class="order-total-row"><span>Total</span><strong>${escapeHtml(formatMoney(order.total))}</strong></div>`;
  openModal('order-modal');
}

function updateNotificationCounts() {
  const unread = accountData.notifications.filter(item => !item.is_read).length;
  const badge = document.getElementById('sidebar-unread');
  badge.textContent = unread;
  badge.hidden = unread === 0;
  document.getElementById('mark-all-read-btn').hidden = unread === 0;
}

function syncNotificationCache(notificationId = null) {
  try {
    const key = 'aqsmile_notifications';
    const cached = JSON.parse(localStorage.getItem(key) || '[]');
    cached.forEach(item => {
      if (notificationId === null || String(item.id) === String(notificationId)) item.read = true;
    });
    localStorage.setItem(key, JSON.stringify(cached));
  } catch (error) {
    console.warn('Notification cache could not be updated:', error.message);
  }
}

function renderNotifications() {
  const list = document.getElementById('notification-list');
  list.innerHTML = accountData.notifications.length
    ? accountData.notifications.map(item => `<article class="notification-item ${item.is_read ? '' : 'unread'}"><div><div class="notification-message">${escapeHtml(item.message)}</div><div class="notification-meta">${escapeHtml(formatAccountDate(item.created_at,true))}</div></div><div class="notification-actions"><span class="read-badge ${item.is_read ? 'read' : 'unread'}">${item.is_read ? 'Read' : 'Unread'}</span>${item.is_read ? '' : `<button class="mark-read-btn" type="button" data-notification-id="${escapeHtml(item.id)}">Mark as Read</button>`}</div></article>`).join('')
    : emptyState('No notifications yet.');
  updateNotificationCounts();
}

function renderOverview() {
  const now = new Date();
  const upcoming = accountData.appointments
    .filter(item => ['pending','confirmed'].includes(item.status) && parseLocalDate(`${item.date} ${item.time || '00:00'}`) >= now)
    .sort((a,b) => parseLocalDate(`${a.date} ${a.time}`) - parseLocalDate(`${b.date} ${b.time}`));
  const activeOrders = accountData.orders.filter(item => ['pending','processing','out_for_delivery'].includes(item.status));
  const completed = accountData.appointments.filter(item => item.status === 'completed');
  const completedOrders = accountData.orders.filter(item => item.status === 'completed');
  const next = upcoming[0];
  const latestOrder = accountData.orders[0];
  const latestNote = accountData.notifications[0];

  document.getElementById('summary-upcoming').textContent = upcoming.length;
  document.getElementById('summary-orders').textContent = activeOrders.length;
  document.getElementById('summary-completed').textContent = completed.length;
  document.getElementById('summary-completed-orders').textContent = completedOrders.length;
  updateNotificationCounts();

  document.getElementById('next-appointment').innerHTML = next
    ? `<div class="overview-detail"><strong>${escapeHtml(next.serviceName || 'Dental Service')}</strong>${escapeHtml(formatAccountDate(next.date))} at ${escapeHtml(formatAppointmentTime(next.time))}<br>${escapeHtml(next.dentistName || 'Dentist to be assigned')}<br><br>${statusBadge(next.status)}</div>`
    : emptyState('No upcoming appointments.');
  document.getElementById('latest-order').innerHTML = latestOrder
    ? `<div class="overview-detail"><strong>Order #${escapeHtml(latestOrder.id)} - ${escapeHtml(formatMoney(latestOrder.total))}</strong>${escapeHtml(formatAccountDate(latestOrder.created_at,true))}<br>${escapeHtml((latestOrder.payment_method || '').replaceAll('_',' ').toUpperCase())}<br><br>${statusBadge(latestOrder.status)}</div>`
    : emptyState('No orders found yet.');
  document.getElementById('recent-notification').innerHTML = latestNote
    ? `<div class="overview-detail"><strong>${latestNote.is_read ? 'Read' : 'New notification'}</strong>${escapeHtml(latestNote.message)}<br><br>${escapeHtml(formatAccountDate(latestNote.created_at,true))}</div>`
    : emptyState('No notifications yet.');
}

function setProfileEditing(editing) {
  [
    'profile-first-name',
    'profile-last-name',
    'profile-phone',
    'profile-birthdate',
    'profile-house-no',
    'profile-street',
    'profile-barangay',
    'profile-city',
    'profile-province',
    'profile-zip-code',
    'profile-emergency-name',
    'profile-emergency-number',
  ].forEach(id => document.getElementById(id).readOnly = !editing);
  document.getElementById('profile-gender').disabled = !editing;
  document.getElementById('edit-profile-btn').hidden = editing;
  document.getElementById('save-profile-btn').hidden = !editing;
  document.getElementById('cancel-profile-btn').hidden = !editing;
  if (editing) document.getElementById('profile-first-name').focus();
}

async function loadAccount() {
  try {
    accountData = await apiGet('user_account');
    renderProfile(accountData.user);
    renderAppointments();
    renderOrders();
    renderNotifications();
    renderOverview();
    document.getElementById('account-loading').hidden = true;
    document.getElementById('account-content').hidden = false;
    const requested = location.hash.slice(1);
    switchSection(document.querySelector(`[data-section="${requested}"]`) ? requested : 'overview');
  } catch (error) {
    document.getElementById('account-loading').hidden = true;
    const alert = document.getElementById('account-alert');
    alert.textContent = error.message || 'Unable to load your account.';
    alert.hidden = false;
  }
}

document.querySelectorAll('[data-section]').forEach(button => button.addEventListener('click', () => switchSection(button.dataset.section)));
document.querySelectorAll('[data-go-section]').forEach(button => button.addEventListener('click', () => switchSection(button.dataset.goSection)));
document.getElementById('edit-profile-btn').addEventListener('click', () => {
  setProfileMessage();
  setProfileEditing(true);
});
document.getElementById('cancel-profile-btn').addEventListener('click', () => {
  renderProfile(profileSnapshot);
  setProfileMessage();
  setProfileEditing(false);
});

document.getElementById('appointment-list').addEventListener('click', event => {
  const button = event.target.closest('[data-cancel-appointment]');
  if (button) openCancellationModal(button.dataset.cancelAppointment);
});

document.getElementById('order-list').addEventListener('click', event => {
  const button = event.target.closest('[data-order-details]');
  if (button) openOrderDetails(button.dataset.orderDetails);
});

document.querySelectorAll('[data-close-modal]').forEach(button => {
  button.addEventListener('click', () => closeModal(button.dataset.closeModal));
});

document.querySelectorAll('.account-modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', event => {
    if (event.target === overlay) closeModal(overlay.id);
  });
});

document.addEventListener('keydown', event => {
  if (event.key !== 'Escape') return;
  document.querySelectorAll('.account-modal-overlay:not([hidden])').forEach(modal => closeModal(modal.id));
});

document.getElementById('cancel-appointment-form').addEventListener('submit', async event => {
  event.preventDefault();
  const appointmentId = document.getElementById('cancel-appointment-id').value;
  const reason = document.getElementById('cancellation-reason').value.trim();
  const button = event.currentTarget.querySelector('button[type="submit"]');

  if (!reason) {
    accountMessage('Please enter a cancellation reason.');
    document.getElementById('cancellation-reason').focus();
    return;
  }

  button.disabled = true;
  try {
    const result = await apiRequest('cancel_appointment', { id: appointmentId, reason });
    const index = accountData.appointments.findIndex(item => String(item.id) === String(appointmentId));
    if (index !== -1) accountData.appointments[index] = result.appointment;
    renderAppointments();
    renderOverview();
    closeModal('cancel-modal');

    try {
      const cached = JSON.parse(localStorage.getItem('aqsmile_appointments') || '[]');
      const cachedAppointment = cached.find(item => String(item.id) === String(appointmentId));
      if (cachedAppointment) {
        cachedAppointment.status = 'cancelled';
        cachedAppointment.cancellationReason = reason;
        cachedAppointment.cancelledBy = 'user';
        localStorage.setItem('aqsmile_appointments', JSON.stringify(cached));
      }
    } catch (error) {
      console.warn('Appointment cache could not be updated:', error.message);
    }

    accountMessage(result.message || 'Appointment cancelled successfully.');
  } catch (error) {
    accountMessage(error.message || 'Unable to cancel the appointment.');
  } finally {
    button.disabled = false;
  }
});

document.getElementById('profile-form').addEventListener('submit', async event => {
  event.preventDefault();
  const button = document.getElementById('save-profile-btn');
  const payload = {
    first_name: document.getElementById('profile-first-name').value.trim(),
    last_name: document.getElementById('profile-last-name').value.trim(),
    phone: document.getElementById('profile-phone').value.trim(),
    birthdate: document.getElementById('profile-birthdate').value,
    gender: document.getElementById('profile-gender').value,
    house_no: document.getElementById('profile-house-no').value.trim(),
    street: document.getElementById('profile-street').value.trim(),
    barangay: document.getElementById('profile-barangay').value.trim(),
    city: document.getElementById('profile-city').value.trim(),
    province: document.getElementById('profile-province').value.trim(),
    zip_code: document.getElementById('profile-zip-code').value.trim(),
    emergency_contact_name: document.getElementById('profile-emergency-name').value.trim(),
    emergency_contact_number: document.getElementById('profile-emergency-number').value.trim(),
  };
  const validationErrors = validateProfile(payload);
  if (validationErrors.length) {
    setProfileMessage(validationErrors);
    return;
  }

  setProfileMessage();
  button.disabled = true;
  try {
    const result = await apiRequest('update_profile', payload);
    accountData.user = {
      ...accountData.user,
      ...result.user,
      phone: result.user.phone || result.user.contact || '',
      created_at: accountData.user.created_at || result.user.createdAt || '',
    };
    renderProfile(accountData.user);
    setProfileEditing(false);
    const cookieUser = Cookie.get('currentUser') || {};
    Cookie.set('currentUser', { ...cookieUser, ...result.user }, 60 / 1440);
    const navName = document.querySelector('.account-nav-name');
    const navAvatar = document.querySelector('.account-nav-avatar');
    if (navName) navName.textContent = result.user.name;
    if (navAvatar) {
      navAvatar.textContent = `${(result.user.first_name || 'A')[0]}${(result.user.last_name || 'S')[0]}`.toUpperCase();
    }
    setProfileMessage(result.message, 'success');
    accountMessage(result.message);
  } catch (error) {
    setProfileMessage(error.errors || error.message || 'Unable to update your profile.');
    accountMessage(error.errors?.join(' ') || error.message);
  } finally { button.disabled = false; }
});

document.getElementById('notification-list').addEventListener('click', async event => {
  const button = event.target.closest('[data-notification-id]');
  if (!button) return;
  button.disabled = true;
  try {
    await apiRequest('mark_notification_read', { id: button.dataset.notificationId });
    const notification = accountData.notifications.find(item => String(item.id) === button.dataset.notificationId);
    if (notification) notification.is_read = true;
    syncNotificationCache(button.dataset.notificationId);
    renderNotifications();
    renderOverview();
    window.AquaNotify?.render();
  } catch (error) { accountMessage(error.message); button.disabled = false; }
});

document.getElementById('mark-all-read-btn').addEventListener('click', async () => {
  try {
    await apiRequest('mark_notifications_read', { userId: accountData.user.id });
    accountData.notifications.forEach(item => { item.is_read = true; });
    syncNotificationCache();
    renderNotifications();
    renderOverview();
    window.AquaNotify?.render();
    accountMessage('All notifications marked as read.');
  } catch (error) { accountMessage(error.message); }
});

document.getElementById('password-form').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('button[type="submit"]');
  const newPassword = document.getElementById('new-password').value;
  const confirmPassword = document.getElementById('confirm-password').value;
  if (newPassword !== confirmPassword) { accountMessage('New password and confirmation do not match.'); return; }
  button.disabled = true;
  try {
    const result = await apiRequest('change_password', {
      current_password: document.getElementById('current-password').value,
      new_password: newPassword, confirm_password: confirmPassword,
    });
    form.reset();
    accountMessage(result.message);
  } catch (error) { accountMessage(error.errors?.join(' ') || error.message); }
  finally { button.disabled = false; }
});

loadAccount();

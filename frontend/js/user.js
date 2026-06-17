let accountData = { user: null, appointments: [], orders: [], notifications: [] };
let profileSnapshot = null;
let selectedAppointmentId = null;
let selectedOrderId = null;

const LETTERS_ONLY_PATTERN = /^[A-Za-z' -]+$/;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function sanitizeLettersOnly(value) {
  return String(value || '').replace(/[^A-Za-z' -]/g, '');
}

function sanitizeDigitsOnly(value) {
  return String(value || '').replace(/[^0-9]/g, '');
}

function sanitizeHouseNumber(value) {
  return String(value || '').replace(/[^0-9/]/g, '');
}

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
  const mobilePattern = /^\d{11}$/;
  const addressLimits = {
    house_no: ['House No.', 50],
    street: ['Street', 150],
    barangay: ['Barangay', 100],
    city: ['City / Municipality', 100],
    province: ['Province / Region', 100],
    zip_code: ['ZIP Code', 10],
  };

  if (!payload.first_name) errors.push('First name is required.');
  else if (!LETTERS_ONLY_PATTERN.test(payload.first_name)) errors.push('Only letters are allowed.');
  if (!payload.last_name) errors.push('Last name is required.');
  else if (!LETTERS_ONLY_PATTERN.test(payload.last_name)) errors.push('Only letters are allowed.');
  if (!payload.phone) {
    errors.push('Phone number is required.');
  } else if (!mobilePattern.test(payload.phone)) {
    errors.push('Please enter a valid 11-digit phone number.');
  }
  if (payload.email && !EMAIL_PATTERN.test(payload.email)) {
    errors.push('Please enter a valid email address.');
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
  if (payload.house_no && !/^[0-9/]+$/.test(payload.house_no)) {
    errors.push('House number may contain numbers and slash only.');
  }
  if (payload.emergency_contact_number && !mobilePattern.test(payload.emergency_contact_number)) {
    errors.push('Please enter a valid 11-digit phone number.');
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

function formatPaymentMethod(value) {
  return (value || 'Not specified').replaceAll('_',' ').toUpperCase();
}

function statusBadge(status) {
  const allowed = ['pending','confirmed','completed','cancelled','archived','processing','out_for_delivery','delivered'];
  const safe = allowed.includes(status) ? status : 'pending';
  return `<span class="status-badge status-${safe.replaceAll('_','-')}">${safe.replaceAll('_',' ')}</span>`;
}

function emptyState(message) {
  return `<div class="empty-history">${escapeHtml(message)}</div>`;
}

function detailRow(label, value, allowHtml = false) {
  const content = allowHtml ? value : escapeHtml(value || '-');
  return `<div class="detail-row"><span>${escapeHtml(label)}</span><strong>${content}</strong></div>`;
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
  if (!selectedAppointmentId || !accountData.appointments.some(item => String(item.id) === String(selectedAppointmentId))) {
    selectedAppointmentId = accountData.appointments[0]?.id || null;
  }

  const selected = accountData.appointments.find(item => String(item.id) === String(selectedAppointmentId));
  document.getElementById('appointment-list').innerHTML = accountData.appointments.length
    ? `
      <div class="history-master-detail">
        <div class="history-master-list" aria-label="Appointment list">
          ${accountData.appointments.map(item => `
            <article class="history-list-card ${String(item.id) === String(selectedAppointmentId) ? 'selected' : ''}" role="button" tabindex="0" data-select-appointment="${escapeHtml(item.id)}">
              <div class="history-list-top">
                <div class="history-title">${escapeHtml(item.serviceName || 'Dental Service')}</div>
                ${statusBadge(item.status)}
              </div>
              <div class="history-meta">${escapeHtml(formatAccountDate(item.date))} at ${escapeHtml(formatAppointmentTime(item.time))}</div>
              <div class="history-meta">${escapeHtml(item.dentistName || 'Dentist to be assigned')}</div>
            </article>`).join('')}
        </div>
        ${renderAppointmentPreview(selected)}
      </div>`
    : emptyState('No appointments found yet.');
}

function renderAppointmentPreview(appointment) {
  if (!appointment) return '';
  const cancelledDetails = appointment.status === 'cancelled'
    ? `${appointment.cancellationReason ? detailRow('Cancellation Reason', appointment.cancellationReason) : ''}${appointment.cancelledBy ? detailRow('Cancelled By', appointment.cancelledBy) : ''}`
    : '';
  const createdDate = appointment.createdAt || appointment.created_at
    ? detailRow('Created Date', formatAccountDate(appointment.createdAt || appointment.created_at, true))
    : '';

  return `
    <aside class="history-detail-panel" aria-live="polite">
      <div class="history-detail-head">
        <span class="card-kicker">Appointment #${escapeHtml(appointment.id)}</span>
        ${statusBadge(appointment.status)}
      </div>
      <h3>${escapeHtml(appointment.serviceName || 'Dental Service')}</h3>
      <div class="history-detail-grid">
        ${detailRow('Dentist', appointment.dentistName || 'Dentist to be assigned')}
        ${detailRow('Date', formatAccountDate(appointment.date))}
        ${detailRow('Time', formatAppointmentTime(appointment.time))}
        ${detailRow('Status', statusBadge(appointment.status), true)}
        ${detailRow('Notes', appointment.notes || 'None')}
        ${cancelledDetails}
        ${createdDate}
      </div>
      <div class="history-detail-actions">
        ${appointment.status === 'pending'
          ? `<button class="history-action-btn cancel" type="button" data-cancel-appointment="${escapeHtml(appointment.id)}">Cancel Appointment</button>`
          : ''}
      </div>
    </aside>`;
}

function renderOrders() {
  document.getElementById('order-count').textContent = accountData.orders.length;
  if (!selectedOrderId || !accountData.orders.some(item => String(item.id) === String(selectedOrderId))) {
    selectedOrderId = accountData.orders[0]?.id || null;
  }

  const selected = accountData.orders.find(item => String(item.id) === String(selectedOrderId));
  document.getElementById('order-list').innerHTML = accountData.orders.length
    ? `
      <div class="history-master-detail">
        <div class="history-master-list" aria-label="Order list">
          ${accountData.orders.map(item => `
            <article class="history-list-card ${String(item.id) === String(selectedOrderId) ? 'selected' : ''}" role="button" tabindex="0" data-select-order="${escapeHtml(item.id)}">
              <div class="history-list-top">
                <div class="history-title">Order #${escapeHtml(item.id)}</div>
                ${statusBadge(item.status)}
              </div>
              <div class="history-meta">${escapeHtml(formatAccountDate(item.created_at,true))}</div>
              <div class="history-list-bottom">
                <span>${escapeHtml(formatPaymentMethod(item.payment_method))}</span>
                <strong>${escapeHtml(formatMoney(item.total))}</strong>
              </div>
            </article>`).join('')}
        </div>
        ${renderOrderPreview(selected)}
      </div>`
    : emptyState('No orders found yet.');
}

function renderOrderPreview(order) {
  if (!order) return '';
  const discount = Number(order.discount_amount ?? order.discountAmount ?? 0);
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

  return `
    <aside class="history-detail-panel order-preview-panel" aria-live="polite">
      <div class="history-detail-head">
        <span class="card-kicker">Order #${escapeHtml(order.id)}</span>
        ${statusBadge(order.status)}
      </div>
      <h3>${escapeHtml(formatMoney(order.total))}</h3>
      <div class="history-detail-grid">
        ${detailRow('Order Date', formatAccountDate(order.created_at,true))}
        ${detailRow('Payment Method', formatPaymentMethod(order.payment_method))}
        ${detailRow('Status', statusBadge(order.status), true)}
        ${detailRow('Total Amount', `<span class="detail-total">${escapeHtml(formatMoney(order.total))}</span>`, true)}
        ${discount > 0 ? detailRow('Discount', formatMoney(discount)) : ''}
        ${detailRow('Delivery Address', formatDeliveryAddress(order))}
      </div>
      <h4 class="panel-products-title">Ordered Products</h4>
      <div class="panel-products-list">${products}</div>
    </aside>`;
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
      const belongsToUser = String(item.userId || '') === String(accountData.user?.id || '')
        || item.userEmail === accountData.user?.email;
      if (belongsToUser && (notificationId === null || String(item.id) === String(notificationId))) {
        item.read = true;
      }
    });
    localStorage.setItem(key, JSON.stringify(cached));
  } catch (error) {
    console.warn('Notification cache could not be updated:', error.message);
  }
}

function notificationReference(item) {
  if (item.order_id) return `Order #${item.order_id}`;
  if (item.appointment_id) return `Appointment #${item.appointment_id}`;
  return 'General update';
}

function renderNotifications() {
  const list = document.getElementById('notification-list');
  list.innerHTML = accountData.notifications.length
    ? accountData.notifications.map(item => `<article class="notification-item ${item.is_read ? '' : 'unread'}" role="button" tabindex="0" data-notification-id="${escapeHtml(item.id)}"><div><div class="notification-message">${escapeHtml(item.message)}</div><div class="notification-meta">${escapeHtml(notificationReference(item))} &middot; ${escapeHtml(formatAccountDate(item.created_at,true))}</div></div><div class="notification-actions"><span class="read-badge ${item.is_read ? 'read' : 'unread'}">${item.is_read ? 'Read' : 'Unread'}</span>${item.is_read ? '' : `<button class="mark-read-btn" type="button" data-notification-id="${escapeHtml(item.id)}">Mark as Read</button>`}</div></article>`).join('')
    : emptyState('No notifications yet.');
  updateNotificationCounts();
}

async function markSingleNotificationRead(notificationId) {
  const notification = accountData.notifications.find(item => String(item.id) === String(notificationId));
  if (!notification) return null;
  if (!notification.is_read) {
    const wasRead = notification.is_read;
    notification.is_read = true;
    syncNotificationCache(notificationId);
    renderNotifications();
    renderOverview();
    window.AquaNotify?.render();
    try {
      await apiRequest('mark_notification_read', { id: notificationId });
    } catch (error) {
      notification.is_read = wasRead;
      renderNotifications();
      renderOverview();
      window.AquaNotify?.render();
      throw error;
    }
  }
  return notification;
}

function openNotificationTarget(notification) {
  if (!notification) return;
  if (notification.appointment_id) {
    selectedAppointmentId = notification.appointment_id;
    switchSection('appointments');
    renderAppointments();
  } else if (notification.order_id) {
    selectedOrderId = notification.order_id;
    switchSection('orders');
    renderOrders();
  }
}

window.openUserNotificationTarget = async function openUserNotificationTarget(notificationId) {
  const notification = await markSingleNotificationRead(notificationId);
  openNotificationTarget(notification);
};

function handleStoredNotificationTarget() {
  try {
    const raw = sessionStorage.getItem('aqsmile_notification_target');
    if (!raw) return;
    sessionStorage.removeItem('aqsmile_notification_target');
    const target = JSON.parse(raw);
    if (target.notificationId) {
      window.openUserNotificationTarget(target.notificationId);
    } else if (target.appointmentId) {
      selectedAppointmentId = target.appointmentId;
      switchSection('appointments');
      renderAppointments();
    } else if (target.orderId) {
      selectedOrderId = target.orderId;
      switchSection('orders');
      renderOrders();
    }
  } catch (error) {
    console.warn('Notification target could not be opened:', error.message);
  }
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
    ? `<div class="overview-detail"><strong>Order #${escapeHtml(latestOrder.id)} - ${escapeHtml(formatMoney(latestOrder.total))}</strong>${escapeHtml(formatAccountDate(latestOrder.created_at,true))}<br>${escapeHtml(formatPaymentMethod(latestOrder.payment_method))}<br><br>${statusBadge(latestOrder.status)}</div>`
    : emptyState('No orders found yet.');
  document.getElementById('recent-notification').innerHTML = latestNote
    ? `<div class="overview-detail"><strong>${latestNote.is_read ? 'Read' : 'New notification'}</strong>${escapeHtml(latestNote.message)}<br><br>${escapeHtml(notificationReference(latestNote))} &middot; ${escapeHtml(formatAccountDate(latestNote.created_at,true))}</div>`
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
    handleStoredNotificationTarget();
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
  const cancelButton = event.target.closest('[data-cancel-appointment]');
  if (cancelButton) {
    openCancellationModal(cancelButton.dataset.cancelAppointment);
    return;
  }
  const selected = event.target.closest('[data-select-appointment]');
  if (selected) {
    selectedAppointmentId = selected.dataset.selectAppointment;
    renderAppointments();
  }
});

document.getElementById('appointment-list').addEventListener('keydown', event => {
  if (!['Enter', ' '].includes(event.key)) return;
  const selected = event.target.closest('[data-select-appointment]');
  if (!selected) return;
  event.preventDefault();
  selectedAppointmentId = selected.dataset.selectAppointment;
  renderAppointments();
});

document.getElementById('order-list').addEventListener('click', event => {
  const selected = event.target.closest('[data-select-order]');
  if (selected) {
    selectedOrderId = selected.dataset.selectOrder;
    renderOrders();
  }
});

document.getElementById('order-list').addEventListener('keydown', event => {
  if (!['Enter', ' '].includes(event.key)) return;
  const selected = event.target.closest('[data-select-order]');
  if (!selected) return;
  event.preventDefault();
  selectedOrderId = selected.dataset.selectOrder;
  renderOrders();
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
  const target = event.target.closest('[data-notification-id]');
  if (!target) return;
  const notificationId = target.dataset.notificationId;
  if (target.matches('button')) target.disabled = true;
  try {
    const notification = await markSingleNotificationRead(notificationId);
    openNotificationTarget(notification);
  } catch (error) {
    accountMessage(error.message);
    if (target.matches('button')) target.disabled = false;
  }
});

document.getElementById('notification-list').addEventListener('keydown', event => {
  if (!['Enter', ' '].includes(event.key)) return;
  const target = event.target.closest('[data-notification-id]');
  if (!target) return;
  event.preventDefault();
  target.click();
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

['profile-first-name', 'profile-last-name'].forEach(id => {
  const input = document.getElementById(id);
  if (input) input.addEventListener('input', event => {
    event.target.value = sanitizeLettersOnly(event.target.value);
  });
});

['profile-phone', 'profile-emergency-number'].forEach(id => {
  const input = document.getElementById(id);
  if (input) input.addEventListener('input', event => {
    event.target.value = sanitizeDigitsOnly(event.target.value).slice(0, 11);
  });
});

const profileHouseNoInput = document.getElementById('profile-house-no');
if (profileHouseNoInput) {
  profileHouseNoInput.addEventListener('input', event => {
    event.target.value = sanitizeHouseNumber(event.target.value);
  });
}

loadAccount();

(function () {
  const N_PREFIX = 'aqsmile_';

  function readStorage(key) {
    try { return JSON.parse(localStorage.getItem(N_PREFIX + key)) || null; }
    catch { return null; }
  }

  function writeStorage(key, value) {
    localStorage.setItem(N_PREFIX + key, JSON.stringify(value));
  }

  function readCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + N_PREFIX + name + '=([^;]*)'));
    try { return match ? JSON.parse(decodeURIComponent(match[1])) : null; }
    catch { return null; }
  }

  function userMatches(user, item) {
    if (!user || !item) return false;
    return item.userId === user.id ||
      item.userEmail === user.email ||
      item.userName === user.name;
  }

  function statusLabel(status) {
    return (status || 'pending').replace('_', ' ');
  }

  function showToastMessage(message) {
    if (typeof window.showToast === 'function') {
      window.showToast(message);
      return;
    }

    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
  }

  function getCurrentUser() {
    return readCookie('currentUser');
  }

  function getNotifications() {
    const user = getCurrentUser();
    return (readStorage('notifications') || []).filter(item => userMatches(user, item));
  }

  function getAppointments() {
    const user = getCurrentUser();
    return (readStorage('appointments') || []).filter(item => userMatches(user, item));
  }

  function ensureBell() {
    const user = getCurrentUser();
    const admin = readCookie('currentAdmin');
    const navLinks = document.getElementById('nav-links');
    if (!navLinks || !user || admin) return null;

    let wrap = document.getElementById('notify-wrap');
    if (wrap) return wrap;

    wrap = document.createElement('div');
    wrap.className = 'notify-wrap';
    wrap.id = 'notify-wrap';
    wrap.innerHTML = `
      <button class="notify-btn" type="button" onclick="AquaNotify.toggle()" aria-label="Notifications">
        <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        <span class="notify-count" id="notify-count">0</span>
      </button>
      <div class="notify-panel" id="notify-panel"></div>`;

    const userInfo = document.getElementById('nav-user-info');
    const loginBtn = document.getElementById('nav-login-btn');
    navLinks.insertBefore(wrap, userInfo || loginBtn || null);
    return wrap;
  }

  function render() {
    if (!ensureBell()) return;

    const panel = document.getElementById('notify-panel');
    const badge = document.getElementById('notify-count');
    if (!panel || !badge) return;

    const notifications = getNotifications();
    const appointments = getAppointments();
    const unread = notifications.filter(item => !item.read).length;

    badge.textContent = unread;
    badge.classList.toggle('show', unread > 0);

    const updatesHtml = notifications.length
      ? notifications.map(item => `
        <div class="notify-item ${item.read ? '' : 'unread'}">
          <div class="notify-message">${item.message}</div>
          <div class="notify-meta">${item.createdAt || ''}</div>
        </div>`).join('')
      : '<div class="notify-empty">No appointment updates yet.</div>';

    const appointmentsHtml = appointments.length
      ? appointments.map(item => `
        <div class="notify-item">
          <div class="notify-booking-title">${item.serviceName || 'Dental Service'} - ${statusLabel(item.status)}</div>
          <div class="notify-booking-body">${item.date || '-'} at ${item.time || '-'}<br>${item.dentistName || 'Dentist pending'}</div>
          ${item.status === 'pending'
            ? `<button class="notify-cancel-btn" type="button" onclick="AquaNotify.cancelBooking('${item.id}')">Cancel Booking</button>`
            : ''}
        </div>`).join('')
      : '<div class="notify-empty">No bookings yet.</div>';

    panel.innerHTML = `
      <div class="notify-panel-head">
        <div class="notify-panel-title">Notifications</div>
        <button class="notify-mark-btn" type="button" onclick="AquaNotify.markRead()">Mark read</button>
      </div>
      <div class="notify-section-label">Updates</div>
      ${updatesHtml}
      <div class="notify-section-label">My Bookings</div>
      ${appointmentsHtml}`;
  }

  function markRead() {
    const user = getCurrentUser();
    const all = readStorage('notifications') || [];
    all.forEach(item => {
      if (userMatches(user, item)) item.read = true;
    });
    writeStorage('notifications', all);
    render();
  }

  function cancelBooking(id) {
    const user = getCurrentUser();
    const appointments = readStorage('appointments') || [];
    const appointment = appointments.find(item => item.id === id && userMatches(user, item));
    if (!appointment) return;

    if (appointment.status !== 'pending') {
      showToastMessage('Only pending appointments can be cancelled.');
      return;
    }

    if (!confirm('Cancel this pending appointment?')) return;

    appointment.status = 'user_cancelled';
    appointment.cancelledBy = 'user';
    appointment.cancellationReason = 'Cancelled by patient before admin approval.';
    writeStorage('appointments', appointments);

    const notifications = readStorage('notifications') || [];
    notifications.unshift({
      id: 'N' + Date.now(),
      userId: appointment.userId || '',
      userEmail: appointment.userEmail || '',
      userName: appointment.userName || 'Patient',
      appointmentId: appointment.id,
      message: `You cancelled your appointment for ${appointment.serviceName || 'your dental service'} on ${appointment.date} at ${appointment.time}.`,
      createdAt: new Date().toLocaleString('en-PH'),
      read: false,
    });
    writeStorage('notifications', notifications.slice(0, 30));

    render();
    showToastMessage('Your pending appointment has been cancelled.');
  }

  function toggle() {
    const panel = document.getElementById('notify-panel');
    if (!panel) return;
    panel.classList.toggle('open');
    render();
  }

  window.AquaNotify = { render, toggle, markRead, cancelBooking };

  document.addEventListener('DOMContentLoaded', render);
  window.addEventListener('pageshow', render);
})();

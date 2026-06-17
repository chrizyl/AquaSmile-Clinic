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

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;',
    })[character]);
  }

  function notificationReference(item) {
    if (item.orderId) return `Order #${escapeHtml(item.orderId)}`;
    if (item.appointmentId) return `Appointment #${escapeHtml(item.appointmentId)}`;
    return 'General update';
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

  const API_BASE = new URL('../backend/api/index.php', window.location.href).pathname;

  async function apiGet(action, params = {}) {
    const query = new URLSearchParams({ action, ...params });
    const response = await fetch(API_BASE + '?' + query.toString(), { cache: 'no-store' });
    const payload = await response.json();
    if (!response.ok || !payload.ok) throw new Error(payload.message || 'API failed');
    return payload;
  }

  async function apiPost(action, data = {}) {
    const response = await fetch(API_BASE + '?action=' + encodeURIComponent(action), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      cache: 'no-store',
      body: JSON.stringify(data),
    });
    const payload = await response.json();
    if (!response.ok || !payload.ok) throw new Error(payload.message || 'API failed');
    return payload;
  }

  async function syncFromApi() {
    const user = getCurrentUser();
    if (!user) return;

    try {
      const [notes, appts] = await Promise.all([
        apiGet('notifications', { user_id: user.id }),
        apiGet('appointments', { user_id: user.id }),
      ]);

      const allNotifications = readStorage('notifications') || [];
      const others = allNotifications.filter(item => !userMatches(user, item));
      const mappedNotes = (notes.notifications || []).map(item => ({
        id: item.id,
        audience: item.audience || 'user',
        userId: item.user_id,
        userName: user.name,
        userEmail: user.email,
        appointmentId: item.appointment_id,
        orderId: item.order_id,
        message: item.message,
        createdAt: item.created_at,
        read: Number(item.is_read) === 1 || allNotifications.some(local =>
          String(local.id) === String(item.id) &&
          (local.audience || 'user') === 'user' &&
          userMatches(user, local) &&
          local.read
        ),
      }));
      writeStorage('notifications', [...mappedNotes, ...others]);

      const allAppointments = readStorage('appointments') || [];
      const otherAppointments = allAppointments.filter(item => !userMatches(user, item));
      writeStorage('appointments', [...(appts.appointments || []), ...otherAppointments]);
    } catch (err) {
      console.warn('Using local notifications fallback:', err.message);
    }
  }

  function getNotifications() {
    const user = getCurrentUser();
    return (readStorage('notifications') || []).filter(item => (item.audience || 'user') === 'user' && userMatches(user, item));
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
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'notify-wrap';
      wrap.id = 'notify-wrap';
    }

    if (wrap.dataset.owner !== 'aquaNotify') {
      wrap.dataset.owner = 'aquaNotify';
      wrap.innerHTML = `
        <button class="notify-btn" type="button" onclick="AquaNotify.toggle()" aria-label="Notifications">
          <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
          <span class="notify-count" id="notify-count">0</span>
        </button>
        <div class="notify-panel" id="notify-panel"></div>`;
    }

    if (!wrap.parentElement) {
      const userInfo = document.getElementById('nav-user-info');
      const loginBtn = document.getElementById('nav-login-btn');
      navLinks.insertBefore(wrap, userInfo || loginBtn || null);
    }
    return wrap;
  }

  function render() {
    if (!ensureBell()) return;

    const panel = document.getElementById('notify-panel');
    const badge = document.getElementById('notify-count');
    if (!panel || !badge) return;

    const notifications = getNotifications();
    const unread = notifications.filter(item => !item.read).length;

    badge.textContent = unread;
    badge.classList.toggle('show', unread > 0);

    const updatesHtml = notifications.length
      ? notifications.map(item => `
        <div class="notify-item ${item.read ? '' : 'unread'}" role="button" tabindex="0"
          onclick="AquaNotify.openNotification('${escapeHtml(item.id)}')"
          onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();AquaNotify.openNotification('${escapeHtml(item.id)}');}">
          <div class="notify-message">${escapeHtml(item.message)}</div>
          <div class="notify-meta">${notificationReference(item)} &middot; ${escapeHtml(item.createdAt || '')}</div>
        </div>`).join('')
      : '<div class="notify-empty">No notifications yet.</div>';

    panel.innerHTML = `
      <div class="notify-panel-head">
        <div class="notify-panel-title">Notifications</div>
        <button class="notify-mark-btn" type="button" onclick="AquaNotify.markRead()">Mark All as Read</button>
      </div>
      <div class="notify-section-label">Updates</div>
      ${updatesHtml}`;
  }

  function setupMobileNav() {
    const nav = document.getElementById('main-nav') || document.querySelector('body > nav');
    const navLinks = nav?.querySelector('.nav-links');
    if (!nav || !navLinks || nav.querySelector('.nav-menu-toggle')) return;

    const toggle = document.createElement('button');
    toggle.className = 'nav-menu-toggle';
    toggle.type = 'button';
    toggle.setAttribute('aria-label', 'Toggle navigation menu');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.innerHTML = '<span></span><span></span><span></span>';
    nav.insertBefore(toggle, navLinks);

    toggle.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      toggle.setAttribute('aria-expanded', String(open));
    });

    navLinks.addEventListener('click', event => {
      if (event.target.closest('button, a')) {
        navLinks.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  async function markOneRead(notificationId) {
    const user = getCurrentUser();
    const all = readStorage('notifications') || [];
    const item = all.find(note =>
      String(note.id) === String(notificationId) &&
      (note.audience || 'user') === 'user' &&
      userMatches(user, note)
    );

    if (!item) return null;
    item.read = true;
    writeStorage('notifications', all);
    render();

    try {
      await apiPost('mark_notification_read', { id: notificationId });
      await syncFromApi();
      render();
    } catch (err) {
      console.warn('Notification read sync failed:', err.message);
    }

    return item;
  }

  async function openNotification(notificationId) {
    const item = await markOneRead(notificationId);
    if (!item) return;

    const target = {
      notificationId: item.id,
      appointmentId: item.appointmentId || null,
      orderId: item.orderId || null,
    };

    if (typeof window.openUserNotificationTarget === 'function') {
      window.openUserNotificationTarget(item.id);
      return;
    }

    if (target.appointmentId || target.orderId) {
      sessionStorage.setItem('aqsmile_notification_target', JSON.stringify(target));
      window.location.href = `user.php#${target.appointmentId ? 'appointments' : 'orders'}`;
    }
  }

  async function markRead() {
    const user = getCurrentUser();
    const all = readStorage('notifications') || [];
    all.forEach(item => {
      if ((item.audience || 'user') === 'user' && userMatches(user, item)) item.read = true;
    });
    writeStorage('notifications', all);
    render();
    try {
      await apiPost('mark_notifications_read', { userId: user?.id });
      await syncFromApi();
      render();
    } catch (err) {
      console.warn('Notification read sync failed:', err.message);
    }
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

    apiPost('cancel_appointment', { id, userId: user.id }).catch(err => {
      console.warn('Appointment cancellation failed:', err.message);
      showToastMessage(err.message || 'Appointment cancellation failed.');
      return null;
    }).then(result => {
      if (!result) return;

      appointment.status = 'cancelled';
      appointment.cancelledBy = 'user';
      appointment.cancellationReason = 'Cancelled by patient before admin approval.';
      writeStorage('appointments', appointments);

      const notifications = readStorage('notifications') || [];
      notifications.unshift({
        id: 'N' + Date.now(),
        audience: 'admin',
        userId: appointment.userId || '',
        userEmail: appointment.userEmail || '',
        userName: appointment.userName || 'Patient',
        appointmentId: appointment.id,
        message: `${appointment.userName || 'Patient'} cancelled the appointment for ${appointment.serviceName || 'the dental service'} on ${appointment.date} at ${appointment.time}.`,
        createdAt: new Date().toLocaleString('en-PH'),
        read: false,
      });
      writeStorage('notifications', notifications.slice(0, 30));

      render();
      showToastMessage('Your pending appointment has been cancelled.');
    });
  }

  function toggle() {
    const panel = document.getElementById('notify-panel');
    if (!panel) return;
    panel.classList.toggle('open');
    render();
  }

  window.AquaNotify = { render, toggle, markRead, openNotification, cancelBooking };

  document.addEventListener('DOMContentLoaded', async () => {
    setupMobileNav();
    await syncFromApi();
    render();
    if (typeof window.showNextUnreadNotificationToast === 'function') {
      window.showNextUnreadNotificationToast(getNotifications());
    }
  });
  window.addEventListener('pageshow', async () => {
    await syncFromApi();
    render();
    if (typeof window.showNextUnreadNotificationToast === 'function') {
      window.showNextUnreadNotificationToast(getNotifications());
    }
  });
})();

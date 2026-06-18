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

  function setupPatientSessionTimeout() {
    const protectedPages = ['index.php', '', 'dentists.php', 'services.php', 'products.php', 'cart.php', 'checkout.php', 'booking.php', 'user.php'];
    const page = window.location.pathname.split('/').pop().toLowerCase();
    const user = getCurrentUser();
    const admin = readCookie('currentAdmin');
    if (!protectedPages.includes(page) || !user || admin || window.AquaPatientTimeoutStarted) return;

    window.AquaPatientTimeoutStarted = true;

    const timeoutSeconds = 60;
    const warningAfterSeconds = 5;
    let secondsLeft = timeoutSeconds;
    let intervalId = null;

    let toast = document.getElementById('session-timeout-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'session-timeout-toast';
      toast.setAttribute('role', 'status');
      toast.setAttribute('aria-live', 'polite');
      toast.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:3000;display:block;width:360px;max-width:calc(100vw - 32px);padding:16px 17px 14px;border-radius:20px;background:rgba(255,255,255,.98);border:1px solid rgba(120,154,153,.22);box-shadow:0 18px 44px rgba(44,62,62,.16),0 5px 16px rgba(120,154,153,.12);pointer-events:none;opacity:0;transform:translateY(12px);transition:opacity .22s ease,transform .22s ease;';
      toast.innerHTML = `
        <div style="display:flex;align-items:flex-start;gap:12px;">
          <span style="flex:0 0 38px;width:38px;height:38px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(120,154,153,.16),rgba(255,232,223,.68));color:var(--aqua-dark,#5A7978);box-shadow:inset 0 0 0 1px rgba(120,154,153,.12);" aria-hidden="true">
            <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7.5v5l3.2 2"></path></svg>
          </span>
          <div style="min-width:0;flex:1;">
            <strong style="display:block;margin:1px 0 5px;color:var(--text-dark,#2C3E3E);font:800 .95rem 'DM Sans',sans-serif;letter-spacing:.01em;">Session timeout</strong>
            <p id="session-timeout-countdown" style="margin:0;color:var(--text-mid,#4A6363);font:400 .84rem/1.55 'DM Sans',sans-serif;">You will be logged out in 60 seconds due to inactivity.</p>
          </div>
        </div>
        <div style="height:5px;margin-top:14px;overflow:hidden;border-radius:999px;background:rgba(120,154,153,.12);">
          <span id="session-timeout-progress" style="display:block;width:100%;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--aqua,#789A99),var(--aqua-dark,#5A7978));transition:width .35s linear;"></span>
        </div>
        <style>
          @media (max-width: 560px) {
            #session-timeout-toast {
              right: 50% !important;
              bottom: 18px !important;
              transform: translate(50%, 12px) !important;
            }
            #session-timeout-toast.is-visible {
              transform: translate(50%, 0) !important;
            }
          }
        </style>`;
      document.body.appendChild(toast);
    }

    const countdown = document.getElementById('session-timeout-countdown');
    const progress = document.getElementById('session-timeout-progress');

    function renderCountdown() {
      if (countdown) {
        countdown.textContent = `You will be logged out in ${secondsLeft} second${secondsLeft === 1 ? '' : 's'} due to inactivity.`;
      }
      if (progress) {
        progress.style.width = Math.max(0, Math.min(100, (secondsLeft / timeoutSeconds) * 100)) + '%';
      }
    }

    function logoutForTimeout() {
      if (typeof window.logout === 'function') {
        window.logout();
        return;
      }
      window.location.href = 'logout.php';
    }

    function showToast() {
      toast.classList.add('is-visible');
      toast.style.opacity = '1';
      toast.style.transform = 'translateY(0)';
      renderCountdown();
    }

    function hideToast() {
      toast.classList.remove('is-visible');
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(12px)';
    }

    function resetTimer() {
      secondsLeft = timeoutSeconds;
      hideToast();
      renderCountdown();
    }

    function startCountdown() {
      clearInterval(intervalId);
      intervalId = setInterval(() => {
        secondsLeft -= 1;
        if (secondsLeft <= timeoutSeconds - warningAfterSeconds) {
          showToast();
        }
        renderCountdown();
        if (secondsLeft <= 0) {
          clearInterval(intervalId);
          logoutForTimeout();
        }
      }, 1000);
    }

    function resetForActivity() {
      resetTimer();
      startCountdown();
    }

    resetTimer();
    startCountdown();
    ['click', 'keydown', 'mousemove', 'scroll', 'touchstart', 'input'].forEach(eventName => {
      document.addEventListener(eventName, resetForActivity, { passive: true });
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
    setupPatientSessionTimeout();
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

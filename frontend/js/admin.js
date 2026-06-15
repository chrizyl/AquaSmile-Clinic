'use strict';

let _adminData    = {};
let _calMonth     = new Date().getMonth();
let _calYear      = new Date().getFullYear();
let _showArchived = { appointments: false, orders: false, products: false, services: false, dentists: false };
let _appointmentFilter = 'all';
let _selectedAppointmentId = null;
let _selectedOrderId = null;
let _adminRouteHandled = false;
const SERVICE_CATEGORY_OPTIONS = ['Preventive', 'Diagnostic', 'Restorative', 'Cosmetic', 'Orthodontic'];

function showToast(msg, ok = true) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className   = 'toast ' + (ok ? 'toast-ok' : 'toast-err') + ' show';
  clearTimeout(t._tid);
  t._tid = setTimeout(() => t.classList.remove('show'), 3800);
}

function formatOrderAddress(order) {
  return [
    order.house_no,
    order.street,
    order.barangay,
    order.city,
    order.province,
    order.zip,
  ].map(part => String(part || '').trim()).filter(Boolean).join(', ') || '-';
}

async function adminApi(action, body = null) {
  const isFormData = body instanceof FormData;
  const opts = { method: body ? 'POST' : 'GET', headers: {} };
  if (body) {
    opts.body = isFormData ? body : JSON.stringify(body);
    if (!isFormData) opts.headers['Content-Type'] = 'application/json';
  }
  const res  = await fetch('../backend/api/index.php?action=' + action, opts);
  return res.json();
}

async function adminRefresh() {
  try {
    const d = await adminApi('dashboard');
    if (!d.ok) { showToast(d.message || 'Failed to load dashboard.', false); return; }
    _adminData = d;
    renderOverview(d);
    renderAppointmentsManage(d.appointments || []);
    renderUsers(d.users || []);
    renderDentistCalendar();
    renderOrders(d.orders || [], d.orderItems || []);
    renderCatalog(d.products || [], d.services || [], d.dentists || []);
    renderNotifications(d.notifications || []);
    updateNotifyBadge(d.notifications || []);
    applyAdminRouteTarget();
  } catch (e) {
    showToast('Network error. Could not refresh dashboard.', false);
  }
}

async function showAdminView(view) {
  document.querySelectorAll('.admin-view').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.admin-side-link').forEach(el => el.classList.remove('active'));
  const section = document.getElementById('view-' + view);
  if (section) section.classList.add('active');
  const btn = document.querySelector('[data-view="' + view + '"]');
  if (btn) btn.classList.add('active');
}

function toggleCatalogPanel(button) {
  const panel = button.closest('.catalog-collapsible');
  if (!panel) return;

  const willExpand = panel.classList.contains('is-collapsed');
  document.querySelectorAll('.catalog-collapsible').forEach(item => {
    item.classList.add('is-collapsed');
    const itemButton = item.querySelector('.catalog-collapse-toggle');
    if (!itemButton) return;
    itemButton.setAttribute('aria-expanded', 'false');
    const label = itemButton.querySelector('span:first-child');
    if (label) label.textContent = 'Show ' + item.dataset.catalogPanel;
  });

  if (willExpand) {
    panel.classList.remove('is-collapsed');
    button.setAttribute('aria-expanded', 'true');
    const label = button.querySelector('span:first-child');
    if (label) label.textContent = 'Hide ' + panel.dataset.catalogPanel;
  }
}

function renderOverview(d) {
  const appointments = d.appointments || [];
  const orders = d.orders || [];
  const patients = (d.users || []).filter(user => (user.role || 'patient') !== 'admin');
  const today = new Date().toISOString().slice(0, 10);
  const pendingToday = appointments.filter(a => a.date === today && a.status === 'pending').length;
  const pendingOrders = orders.filter(order => order.status === 'pending').length;

  setText('stat-appointments', appointments.length);
  setText('stat-pending', pendingToday + ' pending today');
  setText('stat-users', patients.length);
  setText('stat-orders', orders.length);
  setText('stat-pending-orders', pendingOrders + ' pending orders');
  const revenue = orders.reduce((s, o) => s + (parseFloat(o.total) || 0), 0);
  setText('stat-revenue', 'PHP ' + revenue.toLocaleString('en-PH', { minimumFractionDigits: 2 }));

  const appointmentsPreview = document.getElementById('appointments-preview');
  if (appointmentsPreview) {
    const todayAppointments = appointments.filter(appointment => appointment.date === today);
    const recentAppointments = (todayAppointments.length ? todayAppointments : appointments).slice(0, 5);
    appointmentsPreview.innerHTML = recentAppointments.map(appointment => `
      <article class="overview-list-item">
        <div class="overview-list-icon">#${esc(appointment.id)}</div>
        <div class="overview-list-copy">
          <strong>${esc(appointment.userName || 'Patient')}</strong>
          <span>${esc(appointment.serviceName || 'Dental service')} with ${esc(appointment.dentistName || 'Assigned dentist')}</span>
          <small>${esc(formatSchedule(appointment.date, appointment.time))}</small>
        </div>
        <span class="status-pill pill-${appointment.status}">${statusLabel(appointment.status)}</span>
      </article>`).join('') || '<div class="empty-state-card">No appointments yet.</div>';
  }

  const usersPreview = document.getElementById('users-preview');
  if (usersPreview) {
    usersPreview.innerHTML = patients.slice(0, 5).map(user => `
      <article class="overview-list-item">
        <div class="overview-avatar">${initials(user.name)}</div>
        <div class="overview-list-copy">
          <strong>${esc(user.name || 'Patient')}</strong>
          <span>${esc(user.email || 'No email')}</span>
          <small>${esc(user.contact || user.phone || 'No contact number')}</small>
        </div>
        <span class="role-pill">${esc(capitalize(user.role || 'patient'))}</span>
      </article>`).join('') || '<div class="empty-state-card">No users yet.</div>';
  }
}

function renderUsers(users) {
  const list = document.getElementById('users-manage-list');
  if (!list) return;
  const patients = users.filter(user => (user.role || 'patient') !== 'admin');

  list.innerHTML = patients.map(user => `
    <article class="admin-user-card">
      <div class="admin-user-avatar">${initials(user.name)}</div>
      <div class="admin-user-main">
        <div class="admin-user-heading">
          <div>
            <span class="admin-user-id">User #${esc(user.id)}</span>
            <h3>${esc(user.name || 'Patient')}</h3>
          </div>
          <span class="role-pill">${esc(capitalize(user.role || 'patient'))}</span>
        </div>
        <div class="admin-user-contact">
          <span>${esc(user.email || 'No email provided')}</span>
          <span>${esc(user.contact || user.phone || 'No contact number')}</span>
        </div>
        <div class="admin-user-created">Joined ${esc(formatDateOnly(user.createdAt))}</div>
      </div>
    </article>`).join('') || '<div class="empty-state-card">No users yet.</div>';
}

function renderAppointmentsManage(appointments) {
  const showArchived = _showArchived.appointments;
  const archiveToggle = document.getElementById('appointments-archive-toggle');
  if (archiveToggle) archiveToggle.checked = showArchived;

  document.querySelectorAll('[data-appointment-filter]').forEach(button => {
    button.classList.toggle('active', button.dataset.appointmentFilter === _appointmentFilter);
  });

  const visibleAppointments = appointments.filter(appointment => {
    if (!showArchived && appointment.status === 'archived') return false;
    return _appointmentFilter === 'all' || appointment.status === _appointmentFilter;
  });

  const list = document.getElementById('appointment-master-list');
  if (!list) return;

  if (!visibleAppointments.some(appointment => String(appointment.id) === String(_selectedAppointmentId))) {
    _selectedAppointmentId = visibleAppointments[0]?.id || null;
  }

  list.innerHTML = visibleAppointments.map(appointment => `
    <button class="appointment-master-card ${String(appointment.id) === String(_selectedAppointmentId) ? 'selected' : ''} ${appointment.status === 'archived' ? 'item-archived' : ''}"
      type="button" onclick="selectAppointment('${esc(appointment.id)}')" aria-pressed="${String(appointment.id) === String(_selectedAppointmentId)}">
      <div class="appointment-card-top">
        <span class="appointment-card-id">#${esc(appointment.id)}</span>
        <span class="status-pill pill-${appointment.status}">${statusLabel(appointment.status)}</span>
      </div>
      <strong>${esc(appointment.userName || 'Patient')}</strong>
      <span>${esc(appointment.serviceName || 'Dental service')}</span>
      <small>${esc(appointment.dentistName || 'Assigned dentist')}</small>
      <time>${esc(formatSchedule(appointment.date, appointment.time))}</time>
    </button>`).join('') || '<div class="empty-state-card">No appointments match this filter.</div>';

  renderAppointmentDetails(appointments);
}

function setAppointmentFilter(filter) {
  _appointmentFilter = filter;
  if (filter === 'archived') {
    _showArchived.appointments = true;
  }
  _selectedAppointmentId = null;
  renderAppointmentsManage(_adminData.appointments || []);
}

function selectAppointment(id) {
  _selectedAppointmentId = id;
  renderAppointmentsManage(_adminData.appointments || []);
}

function renderAppointmentDetails(appointments) {
  const panel = document.getElementById('appointment-detail-panel');
  if (!panel) return;

  const appointment = appointments.find(item => String(item.id) === String(_selectedAppointmentId));
  if (!appointment) {
    panel.innerHTML = `
      <div class="appointment-detail-empty">
        <div class="appointment-detail-empty-icon">AS</div>
        <h3>Select an appointment</h3>
        <p>Choose a booking from the list to review patient details and available actions.</p>
      </div>`;
    return;
  }

  const contact = [appointment.userEmail, appointment.userContact].filter(Boolean).join(' / ') || 'Not provided';
  panel.innerHTML = `
    <div class="appointment-detail-head">
      <div>
        <span class="section-label">Appointment #${esc(appointment.id)}</span>
        <h3>${esc(appointment.userName || 'Patient')}</h3>
      </div>
      <span class="status-pill pill-${appointment.status}">${statusLabel(appointment.status)}</span>
    </div>
    <div class="appointment-detail-grid">
      ${appointmentDetailItem('Patient', appointment.userName || 'Patient')}
      ${appointmentDetailItem('Email / Contact', contact)}
      ${appointmentDetailItem('Service', appointment.serviceName || '-')}
      ${appointmentDetailItem('Dentist', appointment.dentistName || '-')}
      ${appointmentDetailItem('Appointment Date', formatDateOnly(appointment.date))}
      ${appointmentDetailItem('Appointment Time', formatAppointmentTime(appointment.time))}
      ${appointmentDetailItem('Notes', appointment.notes || 'No notes provided.', true)}
      ${appointmentDetailItem('Status', statusLabel(appointment.status))}
      ${appointment.status === 'cancelled' ? appointmentDetailItem('Cancellation Reason', appointment.cancellationReason || 'No reason recorded.', true) : ''}
      ${appointment.cancelledBy ? appointmentDetailItem('Cancelled By', statusLabel(appointment.cancelledBy)) : ''}
      ${appointmentDetailItem('Created Date', formatDate(appointment.createdAt))}
    </div>
    <div class="appointment-detail-actions">
      ${appointmentActionButtons(appointment)}
    </div>`;
}

function appointmentDetailItem(label, value, wide = false) {
  return `<div class="appointment-detail-item ${wide ? 'wide' : ''}"><span>${esc(label)}</span><strong>${esc(value)}</strong></div>`;
}

function appointmentActionButtons(appointment) {
  if (appointment.status === 'pending') {
    return `
      <button class="appointment-action confirm" type="button" onclick="updateAppointmentStatus('${esc(appointment.id)}', 'confirmed')">Confirm</button>
      <button class="appointment-action cancel" type="button" onclick="openAppointmentCancelModal('${esc(appointment.id)}')">Cancel</button>`;
  }
  if (appointment.status === 'confirmed') {
    return `
      <button class="appointment-action complete" type="button" onclick="confirmAppointmentStatus('${esc(appointment.id)}', 'completed')">Mark as Completed</button>
      <button class="appointment-action cancel" type="button" onclick="openAppointmentCancelModal('${esc(appointment.id)}')">Cancel</button>`;
  }
  if (['completed', 'cancelled'].includes(appointment.status)) {
    return `<button class="appointment-action archive" type="button" onclick="confirmAppointmentStatus('${esc(appointment.id)}', 'archived')">Archive</button>`;
  }
  return '<span class="appointment-archived-label">This appointment is archived.</span>';
}

function confirmAppointmentStatus(id, status) {
  const appointment = (_adminData.appointments || []).find(item => String(item.id) === String(id));
  const isArchive = status === 'archived';
  confirmAdminAction({
    title: isArchive ? 'Confirm Archive' : 'Confirm Completion',
    message: isArchive
      ? `Are you sure you want to archive Appointment #${id}?`
      : `Mark Appointment #${id} as completed?`,
    confirmText: isArchive ? 'Yes, Archive' : 'Yes, Complete',
    loadingText: isArchive ? 'Archiving...' : 'Completing...',
    confirmClass: isArchive ? 'archive' : 'complete',
    onConfirm: () => updateAppointmentStatus(appointment?.id || id, status),
  });
}

async function updateAppointmentStatus(id, status, reason = '') {
  try {
    const d = await adminApi('admin_update_appointment_status', { id, status, reason });
    if (d.ok) {
      showToast(d.message);
      const index = (_adminData.appointments || []).findIndex(appointment => String(appointment.id) === String(id));
      if (index >= 0) {
        _adminData.appointments[index] = d.appointment || {
          ..._adminData.appointments[index],
          status,
          cancellationReason: reason,
          cancelledBy: status === 'cancelled' ? 'admin' : '',
        };
      }
      if (status === 'archived' && !_showArchived.appointments) {
        _selectedAppointmentId = null;
      }
      renderAppointmentsManage(_adminData.appointments || []);
      renderDentistCalendar();
      renderOverview(_adminData);
      return true;
    } else {
      showToast(d.message || 'Failed to update status.', false);
    }
  } catch {
    showToast('Network error.', false);
  }
  return false;
}

function openAppointmentCancelModal(id) {
  const appointment = (_adminData.appointments || []).find(item => String(item.id) === String(id));
  if (!appointment) return;

  removeModal();
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.id = 'admin-modal';
  overlay.onclick = event => { if (event.target === overlay) removeModal(); };
  overlay.innerHTML = `
    <div class="modal-box cancellation-modal" role="dialog" aria-modal="true" aria-label="Cancel appointment #${esc(id)}">
      <div class="modal-head">
        <div><span class="modal-kicker">Cancellation</span><h3>Cancel Appointment #${esc(id)}</h3></div>
        <button class="modal-close" type="button" onclick="removeModal()" aria-label="Close">&#x2715;</button>
      </div>
      <div class="modal-body">
        <p class="cancellation-intro">Provide a reason for cancelling ${esc(appointment.userName || 'this patient')}'s appointment. The patient will receive this reason in their notification.</p>
        <div class="form-group">
          <label for="appointment-cancellation-reason">Cancellation Reason *</label>
          <textarea id="appointment-cancellation-reason" rows="4" placeholder="Enter the reason for cancellation"></textarea>
        </div>
        <div class="modal-err" id="appointment-cancel-error"></div>
      </div>
      <div class="modal-foot">
        <button class="btn-secondary" type="button" onclick="removeModal()">Keep Appointment</button>
        <button class="appointment-action cancel" type="button" id="confirm-appointment-cancel">Cancel Appointment</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
  document.getElementById('confirm-appointment-cancel').addEventListener('click', async event => {
    const reason = document.getElementById('appointment-cancellation-reason').value.trim();
    const error = document.getElementById('appointment-cancel-error');
    if (!reason) {
      error.textContent = 'Cancellation reason is required.';
      return;
    }
    event.currentTarget.disabled = true;
    event.currentTarget.textContent = 'Cancelling...';
    if (await updateAppointmentStatus(id, 'cancelled', reason)) removeModal();
    else {
      event.currentTarget.disabled = false;
      event.currentTarget.textContent = 'Cancel Appointment';
    }
  });
  requestAnimationFrame(() => {
    overlay.classList.add('modal-visible');
    document.getElementById('appointment-cancellation-reason').focus();
  });
}

function toggleArchived(type, show) {
  _showArchived[type] = show;
  const d = _adminData;
  if (type === 'appointments') {
    if (!show && _appointmentFilter === 'archived') _appointmentFilter = 'all';
    renderAppointmentsManage(d.appointments || []);
  }
  if (type === 'orders')       renderOrders(d.orders || [], d.orderItems || []);
  if (type === 'products')     renderCatalog(d.products || [], d.services || [], d.dentists || []);
  if (type === 'services')     renderCatalog(d.products || [], d.services || [], d.dentists || []);
  if (type === 'dentists')     renderCatalog(d.products || [], d.services || [], d.dentists || []);
}

function renderDentistCalendar() {
  const titleEl = document.getElementById('admin-calendar-title');
  if (titleEl) {
    titleEl.textContent = new Date(_calYear, _calMonth, 1)
      .toLocaleString('default', { month: 'long', year: 'numeric' });
  }

  const dentists = _adminData.dentists || [];
  const appointments = (_adminData.appointments || []).filter(a => !['cancelled','archived'].includes(a.status));
  const grid = document.getElementById('dentist-calendar-grid');
  const lists = document.getElementById('dentist-patient-lists');
  if (!grid) return;

  const year = _calYear, month = _calMonth;
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const firstDay    = new Date(year, month, 1).getDay();

  const byDentistDate = {};
  appointments.forEach(a => {
    const [ay, am] = a.date.split('-').map(Number);
    if (ay === year && (am - 1) === month) {
      const key = a.dentistId + ':' + a.date;
      if (!byDentistDate[key]) byDentistDate[key] = [];
      byDentistDate[key].push(a);
    }
  });

  grid.innerHTML = dentists.filter(d => d.status !== 'archived').map(dentist => `
    <div class="dentist-col">
      <div class="dentist-col-header">${esc(dentist.name)}</div>
      <div class="mini-calendar">
        <div class="cal-grid">
          ${['Su','Mo','Tu','We','Th','Fr','Sa'].map(d => `<div class="cal-day-name">${d}</div>`).join('')}
          ${Array.from({length: firstDay}, () => '<div class="cal-day empty"></div>').join('')}
          ${Array.from({length: daysInMonth}, (_, i) => {
            const day = i + 1;
            const dateStr = year + '-' + String(month + 1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
            const key = dentist.id + ':' + dateStr;
            const count = (byDentistDate[key] || []).length;
            const cls = count > 0 ? 'cal-day has-appt' : 'cal-day';
            return `<div class="${cls}" title="${count} appt(s)" onclick="showDentistDay('${dentist.id}','${dateStr}')">
              ${day}${count > 0 ? `<span class="cal-dot">${count}</span>` : ''}
            </div>`;
          }).join('')}
        </div>
      </div>
    </div>`).join('');

  if (lists) lists.innerHTML = '';
}

function showDentistDay(dentistId, dateStr) {
  const appointments = (_adminData.appointments || []).filter(
    appointment => appointment.dentistId === dentistId
      && appointment.date === dateStr
      && !['cancelled', 'archived'].includes(appointment.status)
  ).sort((a, b) => String(a.time).localeCompare(String(b.time)));
  const dentist = (_adminData.dentists || []).find(item => item.id === dentistId);
  const lists = document.getElementById('dentist-patient-lists');
  if (!lists) return;

  const displayDate = formatDateOnly(dateStr);
  if (!appointments.length) {
    lists.innerHTML = `<p class="no-appt-note">No appointments for ${esc(dentist?.name || 'dentist')} on ${esc(displayDate)}.</p>`;
    return;
  }

  lists.innerHTML = `
    <article class="admin-panel dentist-day-panel">
      <div class="dentist-day-head">
        <div>
          <div class="section-label">${esc(dentist?.name || 'Dentist')} - ${esc(displayDate)}</div>
          <h2>${appointments.length} Appointment${appointments.length === 1 ? '' : 's'}</h2>
        </div>
      </div>
      <div class="dentist-day-appointments">
        ${appointments.map(appointment => `
          <article class="dentist-appointment-card">
            <div class="dentist-appointment-time">${esc(formatAppointmentTime(appointment.time))}</div>
            <div class="dentist-appointment-copy">
              <strong>${esc(appointment.serviceName || 'Dental service')}</strong>
              <span>Patient: ${esc(appointment.userName || 'Patient')}</span>
            </div>
            <span class="status-pill pill-${appointment.status}">${statusLabel(appointment.status)}</span>
          </article>`).join('')}
      </div>
    </article>`;
}

function adminChangeMonth(dir) {
  _calMonth += dir;
  if (_calMonth > 11) { _calMonth = 0;  _calYear++; }
  if (_calMonth < 0)  { _calMonth = 11; _calYear--; }
  renderDentistCalendar();
}

function renderOrders(orders, orderItems) {
  const showArchived = _showArchived.orders;
  const filtered = showArchived ? orders : orders.filter(o => o.status !== 'archived');

  const archiveToggle = document.getElementById('orders-archive-toggle');
  if (archiveToggle) archiveToggle.checked = showArchived;

  const list = document.getElementById('orders-list');
  if (!list) return;

  if (!filtered.some(order => String(order.id) === String(_selectedOrderId))) {
    _selectedOrderId = filtered[0]?.id || null;
  }

  list.innerHTML = filtered.map(order => `
    <button class="order-master-card ${String(order.id) === String(_selectedOrderId) ? 'selected' : ''} ${order.status === 'archived' ? 'item-archived' : ''}"
      type="button" onclick="selectOrder('${esc(order.id)}')" aria-pressed="${String(order.id) === String(_selectedOrderId)}">
      <div class="order-master-top">
        <span>Order #${esc(order.id)}</span>
        <span class="status-pill pill-${order.status}">${statusLabel(order.status)}</span>
      </div>
      <strong>${esc(order.customer || 'Customer')}</strong>
      <small>${esc(formatOrderAddress(order))}</small>
      <div class="order-master-total">${formatMoney(order.total)}</div>
    </button>`).join('') || '<div class="empty-state-card">No orders found.</div>';

  renderOrderDetails(orders, orderItems);
}

function selectOrder(orderId) {
  _selectedOrderId = orderId;
  renderOrders(_adminData.orders || [], _adminData.orderItems || []);
}

function renderOrderDetails(orders, orderItems) {
  const panel = document.getElementById('order-detail-panel');
  if (!panel) return;

  const order = orders.find(item => String(item.id) === String(_selectedOrderId));
  if (!order) {
    panel.innerHTML = `
      <div class="order-detail-empty">
        <div class="order-detail-empty-icon">OR</div>
        <h3>Select an order</h3>
        <p>Choose an order from the list to review delivery details, products, and available actions.</p>
      </div>`;
    return;
  }

  const items = orderItems.filter(item => String(item.order_id) === String(order.id));
  panel.innerHTML = `
    <div class="order-panel-head">
      <div>
        <span class="section-label">Order #${esc(order.id)}</span>
        <h3>${esc(order.customer || 'Customer')}</h3>
      </div>
      <span class="status-pill pill-${order.status}">${statusLabel(order.status)}</span>
    </div>
    <div class="order-panel-grid">
      ${orderPanelItem('Email', order.email || 'Not provided')}
      ${orderPanelItem('Phone', order.phone || 'Not provided')}
      ${orderPanelItem('Delivery Address', formatOrderAddress(order), true)}
      ${orderPanelItem('Payment Method', statusLabel(order.paymentMethod || 'cod').toUpperCase())}
      ${(order.paymentMethod || '').toLowerCase() === 'gcash' ? orderPanelItem('GCash Number', order.gcashNumber || 'Not provided') : ''}
      ${orderPanelItem('Total Amount', formatMoney(order.total))}
      ${orderPanelItem('Status', statusLabel(order.status))}
      ${orderPanelItem('Date Created', formatDate(order.created_at))}
    </div>
    <div class="order-panel-products-head">
      <span>Ordered Products</span>
      <strong>${items.length} item${items.length === 1 ? '' : 's'}</strong>
    </div>
    <div class="order-panel-products">
      ${items.map(item => {
        const quantity = Number(item.quantity || 0);
        const unitPrice = Number(item.unit_price || 0);
        return `<div class="order-panel-product">
          <div>
            <strong>${esc(item.product_name || 'Product')}</strong>
            <span>Quantity: ${quantity} &middot; Unit: ${formatMoney(unitPrice)}</span>
          </div>
          <strong>${formatMoney(quantity * unitPrice)}</strong>
        </div>`;
      }).join('') || '<div class="empty-state-card">No order items found.</div>'}
    </div>
    <div class="order-panel-actions">
      ${orderActionButtons(order)}
    </div>`;
}

function orderPanelItem(label, value, wide = false) {
  return `<div class="order-panel-item ${wide ? 'wide' : ''}"><span>${esc(label)}</span><strong>${esc(value)}</strong></div>`;
}

function orderActionButtons(order) {
  const id = esc(order.id);
  const actions = {
    pending: `
      <button class="order-action process" type="button" onclick="updateOrderStatus('${id}', 'processing')">Process Order</button>
      <button class="order-action cancel" type="button" onclick="openOrderCancelModal('${id}')">Cancel Order</button>`,
    processing: `
      <button class="order-action delivery" type="button" onclick="updateOrderStatus('${id}', 'out_for_delivery')">Mark as Out for Delivery</button>
      <button class="order-action cancel" type="button" onclick="openOrderCancelModal('${id}')">Cancel Order</button>`,
    out_for_delivery: `
      <button class="order-action delivered" type="button" onclick="confirmOrderStatus('${id}', 'delivered')">Mark as Delivered</button>
      <button class="order-action cancel" type="button" onclick="openOrderCancelModal('${id}')">Cancel Order</button>`,
    delivered: `<button class="order-action complete" type="button" onclick="confirmOrderStatus('${id}', 'completed')">Mark as Completed</button>`,
    completed: `<button class="order-action archive" type="button" onclick="confirmOrderStatus('${id}', 'archived')">Archive</button>`,
    cancelled: `<button class="order-action archive" type="button" onclick="confirmOrderStatus('${id}', 'archived')">Archive</button>`,
    archived: '<span class="order-archived-label">Archived</span>',
  };
  return actions[order.status] || '';
}

function confirmOrderStatus(id, status) {
  const copy = {
    delivered: ['Confirm Delivery', `Mark Order #${id} as delivered?`, 'Yes, Delivered', 'Marking...'],
    completed: ['Confirm Completion', `Mark Order #${id} as completed?`, 'Yes, Complete', 'Completing...'],
    archived: ['Confirm Archive', `Are you sure you want to archive Order #${id}?`, 'Yes, Archive', 'Archiving...'],
  }[status] || ['Confirm Action', `Update Order #${id}?`, 'Yes, Continue', 'Updating...'];

  confirmAdminAction({
    title: copy[0],
    message: copy[1],
    confirmText: copy[2],
    loadingText: copy[3],
    confirmClass: status === 'archived' ? 'archive' : 'complete',
    onConfirm: () => updateOrderStatus(id, status),
  });
}

async function updateOrderStatus(id, status) {
  try {
    const d = await adminApi('admin_update_order_status', { id, status });
    if (d.ok) {
      showToast(d.message);
      const order = (_adminData.orders || []).find(o => String(o.id) === String(id));
      if (order) order.status = status;
      if (status === 'archived' && !_showArchived.orders) _selectedOrderId = null;
      renderOrders(_adminData.orders || [], _adminData.orderItems || []);
      renderOverview(_adminData);
      return true;
    } else {
      showToast(d.message || 'Failed to update order status.', false);
    }
  } catch {
    showToast('Network error.', false);
  }
  return false;
}

function renderCatalog(products, services, dentists) {
  renderProductsGrid(products);
  renderServicesGrid(services);
  renderDentistList(dentists);
}

function renderProductsGrid(products) {
  const grid = document.getElementById('products-grid-admin');
  if (!grid) return;
  const showArchived = _showArchived.products;
  const filtered = showArchived ? products : products.filter(p => p.status !== 'archived');

  const panel = grid.closest('.admin-panel');
  if (panel) {
    let tr = panel.querySelector('.archive-toggle-row');
    if (!tr) { tr = document.createElement('div'); tr.className = 'archive-toggle-row'; panel.querySelector('.panel-head').after(tr); }
    tr.innerHTML = `
      <button class="mini-btn add-btn" type="button" onclick="openAddModal('product')">+ Add Product</button>`;
  }

  grid.innerHTML = filtered.map(p => `
    <div class="catalog-item ${p.status === 'archived' ? 'item-archived' : ''}">
      ${catalogImagePreview(p.imagePath, p.name)}
      <div class="catalog-item-name">${esc(p.name)}</div>
      <div class="catalog-item-meta">PHP ${parseFloat(p.price).toLocaleString('en-PH', {minimumFractionDigits:2})} · Stock: ${p.stock}</div>
      <div class="catalog-item-status"><span class="status-pill pill-${p.status||'available'}">${statusLabel(p.status||'available')}</span></div>
      <div class="catalog-item-actions catalog-management-actions">
        <select class="status-select status-${p.status||'available'}" onchange="changeProductStatus(${p.id}, this.value, this)" data-current="${p.status||'available'}">
          ${productStatusOptions(p.status||'available')}
        </select>
        <button class="mini-btn edit-btn" type="button" onclick="openEditModal('product', ${p.id})">Edit Product</button>
      </div>
    </div>`).join('') || '<p class="empty-row">No products.</p>';
}

function renderServicesGrid(services) {
  const grid = document.getElementById('services-grid-admin');
  if (!grid) return;
  const showArchived = _showArchived.services;
  const filtered = showArchived ? services : services.filter(s => s.status !== 'archived');

  const panel = grid.closest('.admin-panel');
  if (panel) {
    let tr = panel.querySelector('.archive-toggle-row');
    if (!tr) { tr = document.createElement('div'); tr.className = 'archive-toggle-row'; panel.querySelector('.panel-head').after(tr); }
    tr.innerHTML = `
      <button class="mini-btn add-btn" type="button" onclick="openAddModal('service')">+ Add Service</button>`;
  }

  grid.innerHTML = filtered.map(s => `
    <div class="catalog-item ${s.status === 'archived' ? 'item-archived' : ''}">
      ${catalogImagePreview(s.imagePath, s.name)}
      <div class="catalog-item-name">${esc(s.name)}</div>
      <div class="catalog-item-meta">${esc(s.price)} · ${s.dailySlots} slots/day · ${esc(s.category)}</div>
      <div class="catalog-item-status"><span class="status-pill pill-${s.status||'available'}">${statusLabel(s.status||'available')}</span></div>
      <div class="catalog-item-actions catalog-management-actions">
        <select class="status-select status-${s.status||'available'}" onchange="changeServiceStatus(${s.id}, this.value, this)" data-current="${s.status||'available'}">
          ${availabilityStatusOptions(s.status||'available')}
        </select>
        <button class="mini-btn edit-btn" type="button" onclick="openEditModal('service', ${s.id})">Edit Service</button>
      </div>
    </div>`).join('') || '<p class="empty-row">No services.</p>';
}

function renderDentistList(dentists) {
  const list = document.getElementById('dentist-list');
  if (!list) return;
  const showArchived = _showArchived.dentists;
  const filtered = showArchived ? dentists : dentists.filter(d => d.status !== 'archived');

  const panel = list.closest('.admin-panel');
  if (panel) {
    let tr = panel.querySelector('.archive-toggle-row');
    if (!tr) { tr = document.createElement('div'); tr.className = 'archive-toggle-row'; panel.querySelector('.panel-head').after(tr); }
    tr.innerHTML = `
      <button class="mini-btn add-btn" type="button" onclick="openAddModal('dentist')">+ Add Dentist</button>`;
  }

  list.innerHTML = filtered.map(d => `
    <div class="dentist-card ${d.status === 'archived' ? 'item-archived' : ''}">
      ${catalogImagePreview(d.imagePath, d.name)}
      <div class="dentist-card-name">${esc(d.name)}</div>
      <div class="dentist-card-spec">${esc(d.spec)} · ${esc(d.cred)}</div>
      <div class="catalog-item-status"><span class="status-pill pill-${d.status||'available'}">${statusLabel(d.status||'available')}</span></div>
      <div class="catalog-item-actions">
        <select class="status-select status-${d.status||'available'}" onchange="changeDentistStatus(${d.id}, this.value, this)" data-current="${d.status||'available'}">
          ${availabilityStatusOptions(d.status||'available')}
        </select>
        <button class="mini-btn edit-btn" type="button" onclick="openEditModal('dentist', ${d.id})">Edit Dentist</button>
      </div>
    </div>`).join('') || '<p class="empty-row">No dentists.</p>';
}

function productStatusOptions(current) {
  return ['available','sold_out']
    .map(s => `<option value="${s}" ${s === current ? 'selected' : ''}>${statusLabel(s)}</option>`)
    .join('');
}

function availabilityStatusOptions(current) {
  return ['available','unavailable']
    .map(s => `<option value="${s}" ${s === current ? 'selected' : ''}>${statusLabel(s)}</option>`)
    .join('');
}

function catalogImagePreview(imagePath, name) {
  if (!imagePath) return '';
  return `<img class="catalog-item-preview" src="${esc(imagePath)}" alt="${esc(name)} preview">`;
}

async function changeProductStatus(id, status, selectEl) {
  await changeStatus('admin_update_product_status', id, status, selectEl, 'products');
}
async function changeServiceStatus(id, status, selectEl) {
  await changeStatus('admin_update_service_status', id, status, selectEl, 'services');
}
async function changeDentistStatus(id, status, selectEl) {
  await changeStatus('admin_update_dentist_status', id, status, selectEl, 'dentists');
}

async function changeStatus(action, id, status, selectEl, dataKey) {
  const prev = selectEl.dataset.current;
  try {
    const d = await adminApi(action, { id, status });
    if (d.ok) {
      showToast(d.message);
      selectEl.dataset.current = status;
      const item = (_adminData[dataKey] || []).find(x => String(x.id) === String(id));
      if (item) item.status = status;
      renderCatalog(_adminData.products || [], _adminData.services || [], _adminData.dentists || []);
    } else {
      showToast(d.message || 'Failed to update status.', false);
      selectEl.value = prev;
    }
  } catch {
    showToast('Network error.', false);
    selectEl.value = prev;
  }
}

function renderNotifications(notifications) {
  const feed = document.getElementById('notifications-feed');
  if (!feed) return;

  feed.innerHTML = notifications.map(notification => {
    const isOrder = Boolean(notification.order_id);
    const reference = isOrder
      ? `Order #${notification.order_id}`
      : (notification.appointment_id ? `Appointment #${notification.appointment_id}` : 'General update');
    return `
      <article class="admin-activity-item ${notification.is_read ? '' : 'unread'}" role="button" tabindex="0"
        onclick="openAdminNotification('${esc(notification.id)}')"
        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openAdminNotification('${esc(notification.id)}');}">
        <div class="activity-icon ${isOrder ? 'order' : 'appointment'}" aria-hidden="true">
          ${isOrder ? 'OR' : 'AP'}
        </div>
        <div class="activity-content">
          <div class="activity-heading">
            <div>
              <strong>${esc(notification.user_name || 'AquaSmile User')}</strong>
              <span>${notification.is_read ? '' : '<i class="unread-dot" aria-hidden="true"></i>'}${esc(reference)}</span>
            </div>
            <span class="notification-read-badge ${notification.is_read ? 'read' : 'unread'}">${notification.is_read ? 'Read' : 'Unread'}</span>
          </div>
          <p>${esc(notification.message)}</p>
          <time>${esc(formatDate(notification.created_at))}</time>
        </div>
      </article>`;
  }).join('') || '<div class="empty-state-card">No notifications yet.</div>';
}

async function openAdminNotification(notificationId) {
  const notification = (_adminData.notifications || []).find(item => String(item.id) === String(notificationId));
  if (!notification) return;

  if (!notification.is_read) {
    try {
      const result = await adminApi('mark_admin_notification_read', { id: notificationId });
      if (result.ok) {
        notification.is_read = true;
        renderNotifications(_adminData.notifications || []);
        updateNotifyBadge(_adminData.notifications || []);
      }
    } catch {
      showToast('Could not mark notification as read.', false);
    }
  }

  if (notification.appointment_id) {
    openAdminAppointment(notification.appointment_id, true);
    return;
  }
  if (notification.order_id) {
    openAdminOrder(notification.order_id, true);
  }
}

async function markAllAdminNotificationsRead() {
  try {
    const result = await adminApi('mark_admin_notifications_read', {});
    if (result.ok) {
      (_adminData.notifications || []).forEach(item => { item.is_read = true; });
      renderNotifications(_adminData.notifications || []);
      updateNotifyBadge(_adminData.notifications || []);
      showToast('All admin notifications marked as read.');
    }
  } catch {
    showToast('Could not mark notifications as read.', false);
  }
}

function openAdminAppointment(appointmentId, updateUrl = false) {
  const appointment = (_adminData.appointments || []).find(item => String(item.id) === String(appointmentId));
  if (appointment?.status === 'archived') {
    _showArchived.appointments = true;
    _appointmentFilter = 'archived';
  } else {
    _appointmentFilter = 'all';
  }
  _selectedAppointmentId = appointmentId;
  showAdminView('appointments');
  renderAppointmentsManage(_adminData.appointments || []);
  if (updateUrl) history.replaceState(null, '', `admin.php?section=appointments&id=${encodeURIComponent(appointmentId)}`);
  document.getElementById('appointment-detail-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function openAdminOrder(orderId, updateUrl = false) {
  const order = (_adminData.orders || []).find(item => String(item.id) === String(orderId));
  if (order?.status === 'archived') _showArchived.orders = true;
  _selectedOrderId = orderId;
  showAdminView('orders');
  renderOrders(_adminData.orders || [], _adminData.orderItems || []);
  if (updateUrl) history.replaceState(null, '', `admin.php?section=orders&id=${encodeURIComponent(orderId)}`);
  document.getElementById('order-detail-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function applyAdminRouteTarget() {
  if (_adminRouteHandled) return;
  _adminRouteHandled = true;

  const params = new URLSearchParams(window.location.search);
  const section = params.get('section');
  const id = params.get('id');
  if (section === 'appointments' && id) {
    openAdminAppointment(id);
  } else if (section === 'orders' && id) {
    openAdminOrder(id);
  } else if (section && document.getElementById('view-' + section)) {
    showAdminView(section);
  }
}

function updateNotifyBadge(notifications) {
  const badge = document.getElementById('admin-notify-badge');
  if (!badge) return;
  const unread = notifications.filter(n => !n.is_read).length;
  badge.textContent = unread;
  badge.style.display = unread > 0 ? 'inline-flex' : 'none';
}

function confirmAdminAction({ title, message, confirmText, loadingText = 'Saving...', confirmClass = 'archive', onConfirm }) {
  removeModal();
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.id = 'admin-modal';
  overlay.onclick = event => { if (event.target === overlay) removeModal(); };
  overlay.innerHTML = `
    <div class="modal-box confirmation-modal" role="dialog" aria-modal="true" aria-label="${esc(title)}">
      <div class="modal-head">
        <div><span class="modal-kicker">Please confirm</span><h3>${esc(title)}</h3></div>
        <button class="modal-close" type="button" onclick="removeModal()" aria-label="Close">&#x2715;</button>
      </div>
      <div class="modal-body">
        <div class="order-cancel-icon">!</div>
        <p class="order-cancel-message">${esc(message)}</p>
      </div>
      <div class="modal-foot">
        <button class="btn-secondary" type="button" onclick="removeModal()">Cancel</button>
        <button class="appointment-action ${esc(confirmClass)}" type="button" id="confirm-admin-action">${esc(confirmText)}</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
  document.getElementById('confirm-admin-action').addEventListener('click', async event => {
    event.currentTarget.disabled = true;
    event.currentTarget.textContent = loadingText;
    const ok = await onConfirm();
    if (ok) removeModal();
    else {
      event.currentTarget.disabled = false;
      event.currentTarget.textContent = confirmText;
    }
  });
  requestAnimationFrame(() => overlay.classList.add('modal-visible'));
}

function openOrderCancelModal(orderId) {
  const order = (_adminData.orders || []).find(item => String(item.id) === String(orderId));
  if (!order) return;

  removeModal();
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.id = 'admin-modal';
  overlay.onclick = event => { if (event.target === overlay) removeModal(); };
  overlay.innerHTML = `
    <div class="modal-box order-cancel-modal" role="dialog" aria-modal="true" aria-label="Cancel Order #${esc(order.id)}">
      <div class="modal-head">
        <div><span class="modal-kicker">Order action</span><h3>Cancel Order</h3></div>
        <button class="modal-close" type="button" onclick="removeModal()" aria-label="Close">&#x2715;</button>
      </div>
      <div class="modal-body">
        <div class="order-cancel-icon">!</div>
        <p class="order-cancel-message">Are you sure you want to cancel Order #${esc(order.id)}?</p>
      </div>
      <div class="modal-foot">
        <button class="btn-secondary" type="button" onclick="removeModal()">No, Keep Order</button>
        <button class="order-action cancel" type="button" id="confirm-order-cancel">Yes, Cancel Order</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);
  document.getElementById('confirm-order-cancel').addEventListener('click', async event => {
    event.currentTarget.disabled = true;
    event.currentTarget.textContent = 'Cancelling...';
    if (await updateOrderStatus(order.id, 'cancelled')) removeModal();
    else {
      event.currentTarget.disabled = false;
      event.currentTarget.textContent = 'Yes, Cancel Order';
    }
  });
  requestAnimationFrame(() => overlay.classList.add('modal-visible'));
}

function openAddModal(type) {
  const cfg = modalConfig(type, null);
  openModal(cfg.title, cfg.fields, cfg.onSave);
}

function openEditModal(type, id) {
  let record = null;
  if (type === 'product')  record = (_adminData.products  || []).find(x => String(x.id) === String(id));
  if (type === 'service')  record = (_adminData.services  || []).find(x => String(x.id) === String(id));
  if (type === 'dentist')  record = (_adminData.dentists  || []).find(x => String(x.id) === String(id));
  const cfg = modalConfig(type, record);
  openModal(cfg.title, cfg.fields, cfg.onSave);
}

function modalConfig(type, record) {
  const isEdit = !!record;
  if (type === 'product') {
    return {
      title: isEdit ? 'Edit Product' : 'Add New Product',
      fields: [
        { id:'name',          label:'Product Name *',  type:'text',   value: record?.name        || '' },
        { id:'description',   label:'Description',     type:'textarea', value: record?.desc       || '' },
        { id:'price',         label:'Price (PHP) *',   type:'number', value: record?.price        || '' },
        { id:'stock_quantity',label:'Stock Quantity',  type:'number', value: record?.stock        || 0  },
        { id:'image',         label:'Product Image',   type:'file',   currentPreview: record?.imagePath || '' },
      ],
      onSave: async (vals) => {
        const action = isEdit ? 'admin_edit_product' : 'admin_add_product';
        if (isEdit) vals.id = record.id;
        const d = await adminApi(action, catalogFormData(vals));
        if (d.ok) {
          showToast(d.message);
          if (isEdit) {
            const idx = (_adminData.products || []).findIndex(x => String(x.id) === String(record.id));
            if (idx >= 0) _adminData.products[idx] = { ..._adminData.products[idx], ...d.product };
          } else {
            _adminData.products = _adminData.products || [];
            _adminData.products.push(d.product);
          }
          renderProductsGrid(_adminData.products || []);
          return true;
        }
        showToast(d.message || 'Failed.', false); return false;
      }
    };
  }
  if (type === 'service') {
    return {
      title: isEdit ? 'Edit Service' : 'Add New Service',
      fields: [
        { id:'name',        label:'Service Name *',    type:'text',    value: record?.name     || '' },
        { id:'description', label:'Description',       type:'textarea',value: record?.desc      || '' },
        { id:'price',       label:'Price (PHP) *',     type:'number',  value: record?.rawPrice || record?.price || '' },
        { id:'category',    label:'Category',          type:'select',  value: record?.category || '', options: SERVICE_CATEGORY_OPTIONS },
        { id:'daily_slots', label:'Daily Slots',       type:'number',  value: record?.dailySlots || 8 },
        { id:'image',       label:'Service Image',     type:'file',    currentPreview: record?.imagePath || '' },
      ],
      onSave: async (vals) => {
        const action = isEdit ? 'admin_edit_service' : 'admin_add_service';
        if (isEdit) vals.id = record.id;
        const d = await adminApi(action, catalogFormData(vals));
        if (d.ok) {
          showToast(d.message);
          if (isEdit) {
            const idx = (_adminData.services || []).findIndex(x => String(x.id) === String(record.id));
            if (idx >= 0) _adminData.services[idx] = { ..._adminData.services[idx], ...d.service };
          } else {
            _adminData.services = _adminData.services || [];
            _adminData.services.push(d.service);
          }
          renderServicesGrid(_adminData.services || []);
          return true;
        }
        showToast(d.message || 'Failed.', false); return false;
      }
    };
  }
  if (type === 'dentist') {
    return {
      title: isEdit ? 'Edit Dentist' : 'Add New Dentist',
      fields: [
        { id:'first_name',     label:'First Name *',     type:'text',    value: record?.firstName || '' },
        { id:'last_name',      label:'Last Name *',      type:'text',    value: record?.lastName  || '' },
        { id:'specialization', label:'Specialization',   type:'text',    value: record?.spec  || '' },
        { id:'credentials',    label:'Credentials',      type:'text',    value: record?.cred  || '' },
        { id:'bio',            label:'Bio / Description',type:'textarea',value: record?.desc  || '' },
        { id:'image',          label:'Dentist Image',    type:'file',    currentPreview: record?.imagePath || '' },
      ],
      onSave: async (vals) => {
        const action = isEdit ? 'admin_edit_dentist' : 'admin_add_dentist';
        if (isEdit) vals.id = record.id;
        const d = await adminApi(action, catalogFormData(vals));
        if (d.ok) {
          showToast(d.message);
          if (isEdit) {
            const idx = (_adminData.dentists || []).findIndex(x => String(x.id) === String(record.id));
            if (idx >= 0) _adminData.dentists[idx] = { ..._adminData.dentists[idx], ...d.dentist };
          } else {
            _adminData.dentists = _adminData.dentists || [];
            _adminData.dentists.push(d.dentist);
          }
          renderDentistList(_adminData.dentists || []);
          renderDentistCalendar();
          return true;
        }
        showToast(d.message || 'Failed.', false); return false;
      }
    };
  }
}

function openModal(title, fields, onSave) {
  removeModal();
  const modalCopy = catalogModalCopy(title);
  const detailFields = fields.filter(f => f.type !== 'file');
  const imageFields = fields.filter(f => f.type === 'file');
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay catalog-modal-overlay';
  overlay.id = 'admin-modal';
  overlay.onclick = (e) => { if (e.target === overlay) removeModal(); };

  overlay.innerHTML = `
    <div class="modal-box catalog-form-modal" role="dialog" aria-modal="true" aria-label="${esc(title)}">
      <div class="modal-head">
        <div class="modal-title-copy">
          <h3>${esc(title)}</h3>
          <p>${esc(modalCopy.subtitle)}</p>
        </div>
        <button class="modal-close" onclick="removeModal()" aria-label="Close">&#x2715;</button>
      </div>
      <div class="modal-body">
        <form id="admin-modal-form" onsubmit="return false">
          <section class="catalog-modal-section catalog-details-section">
            <div class="catalog-section-head">
              <span>${esc(modalCopy.detailsTitle)}</span>
              <small>Complete the information below.</small>
            </div>
            <div class="catalog-fields-grid">
              ${detailFields.map(f => `
                <div class="form-group catalog-field ${f.type === 'textarea' || f.id === 'name' ? 'catalog-field-wide' : ''}">
                  <label for="mf-${f.id}">${esc(f.label)}</label>
                  ${f.type === 'textarea'
                    ? `<textarea id="mf-${f.id}" name="${f.id}" rows="3">${esc(f.value)}</textarea>`
                    : f.type === 'select'
                      ? `<select id="mf-${f.id}" name="${f.id}">
                          <option value="">Select category</option>
                          ${(f.options || []).map(option => `<option value="${esc(option)}" ${option === f.value ? 'selected' : ''}>${esc(option)}</option>`).join('')}
                        </select>`
                    : `<input id="mf-${f.id}" name="${f.id}" type="${f.type}" value="${esc(String(f.value))}">`}
                </div>`).join('')}
            </div>
          </section>
          ${imageFields.map(f => `
            <section class="catalog-modal-section catalog-image-section">
              <div class="catalog-section-head">
                <span>${esc(f.label)}</span>
                <small>Add a polished image for this profile.</small>
              </div>
              <div class="image-upload-layout">
                <div class="image-upload-preview ${f.currentPreview ? 'has-image' : ''}" id="preview-${f.id}">
                  ${f.currentPreview ? `<img src="${esc(f.currentPreview)}" alt="Current image preview">` : imagePreviewEmptyState()}
                </div>
                <div class="image-upload-controls">
                  <input class="image-file-input" id="mf-${f.id}" name="${f.id}" type="file" accept=".jpg,.jpeg,.png,.webp">
                  <label class="image-upload-button" for="mf-${f.id}">Choose Image</label>
                  <small class="image-upload-help">
                    <span>JPG, JPEG, PNG, or WEBP</span>
                    <span>Maximum 2MB</span>
                  </small>
                </div>
              </div>
            </section>`).join('')}
          <div class="modal-err" id="modal-err"></div>
        </form>
      </div>
      <div class="modal-foot">
        <button class="btn-secondary" type="button" onclick="removeModal()">Cancel</button>
        <button class="btn-primary"   type="button" id="modal-save-btn">Save</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);

  fields.filter(f => f.type === 'file').forEach(f => {
    const input = document.getElementById(`mf-${f.id}`);
    input.addEventListener('change', () => {
      updateImagePreview(input, document.getElementById(`preview-${f.id}`), f.currentPreview || '');
    });
  });

  document.getElementById('modal-save-btn').addEventListener('click', async () => {
    const form = document.getElementById('admin-modal-form');
    const vals = {};
    let valid = true;
    const errEl = document.getElementById('modal-err');
    errEl.textContent = '';

    fields.forEach(f => {
      const el = form.elements[f.id];
      const val = f.type === 'file' ? '' : (el ? el.value.trim() : '');
      vals[f.id] = f.type === 'file' ? (el.files[0] || null) : (f.type === 'number' ? parseFloat(val) || 0 : val);
      if (f.label.includes('*') && val === '') {
        errEl.textContent = f.label.replace(' *','') + ' is required.';
        valid = false;
      }
    });
    if (!valid) return;

    const saveBtn = document.getElementById('modal-save-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
    const ok = await onSave(vals);
    if (ok) removeModal();
    else { saveBtn.disabled = false; saveBtn.textContent = 'Save'; }
  });

  requestAnimationFrame(() => overlay.classList.add('modal-visible'));
}

function catalogModalCopy(title) {
  const isEdit = title.startsWith('Edit');
  const entity = title.includes('Product') ? 'Product' : title.includes('Service') ? 'Service' : 'Dentist';
  const descriptions = {
    Product: isEdit
      ? 'Update the information and image of this product.'
      : 'Add a new product to the AquaSmile shop.',
    Service: isEdit
      ? 'Update the information and image of this service.'
      : 'Create a new dental service for the clinic.',
    Dentist: isEdit
      ? 'Update the information and image of this dentist profile.'
      : 'Create a new dentist profile for the clinic.',
  };
  return {
    subtitle: descriptions[entity],
    detailsTitle: entity + ' Details',
  };
}

function catalogFormData(values) {
  const formData = new FormData();
  Object.entries(values).forEach(([key, value]) => {
    if (value instanceof File) formData.append(key, value);
    else if (value !== null && value !== undefined) formData.append(key, String(value));
  });
  return formData;
}

function updateImagePreview(input, preview, currentPreview = '') {
  const file = input.files[0];
  if (!file) return;

  const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
  const extension = file.name.split('.').pop().toLowerCase();
  const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowedExtensions.includes(extension) || !allowedMimeTypes.includes(file.type)) {
    input.value = '';
    restoreImagePreview(preview, currentPreview);
    showToast('Only JPG, JPEG, PNG, and WEBP images are allowed.', false);
    return;
  }
  if (file.size > 2 * 1024 * 1024) {
    input.value = '';
    restoreImagePreview(preview, currentPreview);
    showToast('Image size must not exceed 2MB.', false);
    return;
  }

  const previewUrl = URL.createObjectURL(file);
  preview.classList.add('has-image');
  preview.innerHTML = `<img src="${previewUrl}" alt="Selected image preview">`;
  preview.querySelector('img').addEventListener('load', () => URL.revokeObjectURL(previewUrl), { once: true });
}

function restoreImagePreview(preview, currentPreview) {
  preview.classList.toggle('has-image', !!currentPreview);
  preview.innerHTML = currentPreview
    ? `<img src="${esc(currentPreview)}" alt="Current image preview">`
    : imagePreviewEmptyState();
}

function imagePreviewEmptyState() {
  return `
    <div class="image-preview-empty">
      <span class="image-preview-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="4" width="18" height="16" rx="3"></rect>
          <circle cx="8.5" cy="9" r="1.5"></circle>
          <path d="m5 17 4.5-4.5 3 3 2-2L19 17"></path>
        </svg>
      </span>
      <strong>No image selected</strong>
      <small>Upload JPG, PNG, or WEBP</small>
    </div>`;
}

function removeModal() {
  const m = document.getElementById('admin-modal');
  if (m) m.remove();
}

function esc(str) {
  if (str === null || str === undefined) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function setText(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}
function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
}
function statusLabel(status) {
  return String(status || '').replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase());
}
function initials(name) {
  return String(name || 'AS').split(/\s+/).filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase();
}
function formatMoney(value) {
  return 'PHP ' + (parseFloat(value) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}
function formatSchedule(date, time) {
  if (!date) return '-';
  const parsed = new Date(`${date}T${time || '00:00:00'}`);
  if (Number.isNaN(parsed.getTime())) return `${date} ${time || ''}`.trim();
  return parsed.toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
}
function formatAppointmentTime(time) {
  if (!time) return '-';
  const parsed = new Date(`2000-01-01T${time}`);
  return Number.isNaN(parsed.getTime())
    ? time
    : parsed.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' });
}
function formatDateOnly(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return Number.isNaN(date.getTime()) ? dateStr : date.toLocaleDateString('en-PH', { dateStyle: 'medium' });
}
function formatDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleString('en-PH', { dateStyle:'medium', timeStyle:'short' });
}

function logout() {
  fetch('../backend/api/index.php?action=logout').finally(() => { window.location.href = 'login.php'; });
}

document.addEventListener('DOMContentLoaded', () => {
  showAdminView('overview');
  adminRefresh();
});

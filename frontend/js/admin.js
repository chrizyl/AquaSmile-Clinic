'use strict';

let _adminData    = {};
let _calMonth     = new Date().getMonth();
let _calYear      = new Date().getFullYear();
let _showArchived = { appointments: false, orders: false, products: false, services: false, dentists: false };

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
  const opts = { method: body ? 'POST' : 'GET', headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
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
    renderDentistCalendar();
    renderOrders(d.orders || [], d.orderItems || []);
    renderCatalog(d.products || [], d.services || [], d.cartItems || [], d.dentists || []);
    renderNotifications(d.notifications || []);
    updateNotifyBadge(d.notifications || []);
  } catch (e) {
    showToast('Network error. Could not refresh dashboard.', false);
  }
}

function showAdminView(view) {
  document.querySelectorAll('.admin-view').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.admin-side-link').forEach(el => el.classList.remove('active'));
  const section = document.getElementById('view-' + view);
  if (section) section.classList.add('active');
  const btn = document.querySelector('[data-view="' + view + '"]');
  if (btn) btn.classList.add('active');
}

function renderOverview(d) {
  const appointments = d.appointments || [];
  const today = new Date().toISOString().slice(0, 10);
  const pendingToday = appointments.filter(a => a.date === today && a.status === 'pending').length;

  setText('stat-appointments', appointments.length);
  setText('stat-pending', pendingToday + ' pending today');
  setText('stat-users', (d.users || []).length);
  setText('stat-cart', (d.cartItems || []).length);
  const revenue = (d.orders || []).reduce((s, o) => s + (parseFloat(o.total) || 0), 0);
  setText('stat-revenue', 'PHP ' + revenue.toLocaleString('en-PH', { minimumFractionDigits: 2 }));

  const tbody = document.getElementById('appointments-table');
  if (tbody) {
    tbody.innerHTML = appointments.slice(0, 8).map(a => `
      <tr>
        <td>#${a.id}</td>
        <td>${esc(a.userName)}</td>
        <td>${esc(a.serviceName)}</td>
        <td>${esc(a.dentistName)}</td>
        <td>${esc(a.date)} ${esc(a.time)}</td>
        <td><span class="status-pill pill-${a.status}">${a.status}</span></td>
      </tr>`).join('') || '<tr><td colspan="6" class="empty-row">No appointments yet.</td></tr>';
  }

  const utbody = document.getElementById('users-table');
  if (utbody) {
    utbody.innerHTML = (d.users || []).slice(0, 8).map(u => `
      <tr>
        <td>#${u.id}</td>
        <td>${esc(u.name)}</td>
        <td>${esc(u.email)}</td>
        <td>${esc(u.contact || u.phone)}</td>
      </tr>`).join('') || '<tr><td colspan="4" class="empty-row">No users yet.</td></tr>';
  }
}

function renderAppointmentsManage(appointments) {
  const showArchived = _showArchived.appointments;
  const filtered = showArchived ? appointments : appointments.filter(a => a.status !== 'archived');

  const tbody = document.getElementById('appointments-manage-table');
  if (!tbody) return;

  const panel = tbody.closest('.admin-panel');
  if (panel) {
    let toggleRow = panel.querySelector('.archive-toggle-row');
    if (!toggleRow) {
      toggleRow = document.createElement('div');
      toggleRow.className = 'archive-toggle-row';
      panel.querySelector('.panel-head').after(toggleRow);
    }
    toggleRow.innerHTML = `
      <label class="toggle-label">
        <input type="checkbox" onchange="toggleArchived('appointments', this.checked)" ${showArchived ? 'checked' : ''}>
        Show archived
      </label>`;
  }

  tbody.innerHTML = filtered.map(a => `
    <tr class="${a.status === 'archived' ? 'row-archived' : ''}">
      <td>#${a.id}</td>
      <td>${esc(a.userName)}</td>
      <td>${esc(a.serviceName)}</td>
      <td>${esc(a.dentistName)}</td>
      <td>${esc(a.date)} ${esc(a.time)}</td>
      <td><span class="status-pill pill-${a.status}">${a.status}</span></td>
      <td>
        <select class="status-select" onchange="changeAppointmentStatus(${a.id}, this.value, this)" data-current="${a.status}">
          ${apptStatusOptions(a.status)}
        </select>
      </td>
    </tr>`).join('') || '<tr><td colspan="7" class="empty-row">No appointments found.</td></tr>';
}

function apptStatusOptions(current) {
  return ['pending','confirmed','completed','cancelled','archived']
    .map(s => `<option value="${s}" ${s === current ? 'selected' : ''}>${capitalize(s)}</option>`)
    .join('');
}

async function changeAppointmentStatus(id, status, selectEl) {
  const prev = selectEl.dataset.current;
  let reason = '';
  if (status === 'cancelled') {
    reason = prompt('Cancellation reason (optional):') || '';
  }
  try {
    const d = await adminApi('admin_update_appointment_status', { id, status, reason });
    if (d.ok) {
      showToast(d.message);
      selectEl.dataset.current = status;
      const appt = (_adminData.appointments || []).find(a => String(a.id) === String(id));
      if (appt) appt.status = status;
      renderAppointmentsManage(_adminData.appointments || []);
      renderDentistCalendar();
      renderOverview(_adminData);
    } else {
      showToast(d.message || 'Failed to update status.', false);
      selectEl.value = prev;
    }
  } catch {
    showToast('Network error.', false);
    selectEl.value = prev;
  }
}

function toggleArchived(type, show) {
  _showArchived[type] = show;
  const d = _adminData;
  if (type === 'appointments') renderAppointmentsManage(d.appointments || []);
  if (type === 'orders')       renderOrders(d.orders || [], d.orderItems || []);
  if (type === 'products')     renderCatalog(d.products || [], d.services || [], d.cartItems || [], d.dentists || []);
  if (type === 'services')     renderCatalog(d.products || [], d.services || [], d.cartItems || [], d.dentists || []);
  if (type === 'dentists')     renderCatalog(d.products || [], d.services || [], d.cartItems || [], d.dentists || []);
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
    a => a.dentistId === dentistId && a.date === dateStr && !['cancelled','archived'].includes(a.status)
  );
  const dentist = (_adminData.dentists || []).find(d => d.id === dentistId);
  const lists = document.getElementById('dentist-patient-lists');
  if (!lists) return;
  if (!appointments.length) { lists.innerHTML = `<p class="no-appt-note">No appointments for ${esc(dentist?.name || 'dentist')} on ${dateStr}.</p>`; return; }
  lists.innerHTML = `
    <article class="admin-panel dentist-day-panel">
      <div class="panel-head"><div><div class="section-label">${esc(dentist?.name || '')} — ${dateStr}</div><h2>${appointments.length} Appointment(s)</h2></div></div>
      <div class="table-wrap"><table class="admin-table"><thead><tr><th>Patient</th><th>Service</th><th>Time</th><th>Status</th></tr></thead>
      <tbody>${appointments.map(a => `<tr><td>${esc(a.userName)}</td><td>${esc(a.serviceName)}</td><td>${esc(a.time)}</td><td><span class="status-pill pill-${a.status}">${a.status}</span></td></tr>`).join('')}</tbody></table></div>
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

  const tbody = document.getElementById('orders-table');
  if (tbody) {
    const panel = tbody.closest('.admin-panel');
    if (panel) {
      let tr = panel.querySelector('.archive-toggle-row');
      if (!tr) { tr = document.createElement('div'); tr.className = 'archive-toggle-row'; panel.querySelector('.panel-head').after(tr); }
      tr.innerHTML = `<label class="toggle-label"><input type="checkbox" onchange="toggleArchived('orders', this.checked)" ${showArchived ? 'checked' : ''}>Show archived</label>`;
    }

    tbody.innerHTML = filtered.map(o => `
      <tr class="${o.status === 'archived' ? 'row-archived' : ''}">
        <td>#${o.id}</td>
        <td>${esc(o.customer)}</td>
        <td>${esc(formatOrderAddress(o))}</td>
        <td>PHP ${parseFloat(o.total).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
        <td>
          <select class="status-select" onchange="changeOrderStatus(${o.id}, this.value, this)" data-current="${o.status}">
            ${orderStatusOptions(o.status)}
          </select>
        </td>
      </tr>`).join('') || '<tr><td colspan="5" class="empty-row">No orders yet.</td></tr>';
  }

  const itbody = document.getElementById('order-items-table');
  if (itbody) {
    itbody.innerHTML = orderItems.map(i => `
      <tr>
        <td>${esc(i.product_name || 'Product')}</td>
        <td>#${i.order_id}</td>
        <td>${i.quantity}</td>
        <td>PHP ${parseFloat(i.unit_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
      </tr>`).join('') || '<tr><td colspan="4" class="empty-row">No items yet.</td></tr>';
  }
}

function orderStatusOptions(current) {
  return ['pending','processing','out_for_delivery','delivered','completed','cancelled','archived']
    .map(s => `<option value="${s}" ${s === current ? 'selected' : ''}>${capitalize(s.replace(/_/g,' '))}</option>`)
    .join('');
}

async function changeOrderStatus(id, status, selectEl) {
  const prev = selectEl.dataset.current;
  try {
    const d = await adminApi('admin_update_order_status', { id, status });
    if (d.ok) {
      showToast(d.message);
      selectEl.dataset.current = status;
      const order = (_adminData.orders || []).find(o => String(o.id) === String(id));
      if (order) order.status = status;
      renderOrders(_adminData.orders || [], _adminData.orderItems || []);
    } else {
      showToast(d.message || 'Failed to update order status.', false);
      selectEl.value = prev;
    }
  } catch {
    showToast('Network error.', false);
    selectEl.value = prev;
  }
}

function renderCatalog(products, services, cartItems, dentists) {
  renderProductsGrid(products);
  renderServicesGrid(services);
  renderCartTable(cartItems);
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
      <label class="toggle-label"><input type="checkbox" onchange="toggleArchived('products', this.checked)" ${showArchived ? 'checked' : ''}>Show archived</label>
      <button class="mini-btn add-btn" type="button" onclick="openAddModal('product')">+ Add Product</button>`;
  }

  grid.innerHTML = filtered.map(p => `
    <div class="catalog-item ${p.status === 'archived' ? 'item-archived' : ''}">
      <div class="catalog-item-name">${esc(p.name)}</div>
      <div class="catalog-item-meta">PHP ${parseFloat(p.price).toLocaleString('en-PH', {minimumFractionDigits:2})} · Stock: ${p.stock}</div>
      <div class="catalog-item-status"><span class="status-pill pill-${p.status||'available'}">${p.status||'available'}</span></div>
      <div class="catalog-item-actions">
        <select class="status-select" onchange="changeProductStatus(${p.id}, this.value, this)" data-current="${p.status||'available'}">
          ${catalogStatusOptions(p.status||'available')}
        </select>
        <button class="mini-btn" onclick="openEditModal('product', ${p.id})">Edit</button>
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
      <label class="toggle-label"><input type="checkbox" onchange="toggleArchived('services', this.checked)" ${showArchived ? 'checked' : ''}>Show archived</label>
      <button class="mini-btn add-btn" type="button" onclick="openAddModal('service')">+ Add Service</button>`;
  }

  grid.innerHTML = filtered.map(s => `
    <div class="catalog-item ${s.status === 'archived' ? 'item-archived' : ''}">
      <div class="catalog-item-name">${esc(s.name)}</div>
      <div class="catalog-item-meta">${esc(s.price)} · ${s.dailySlots} slots/day · ${esc(s.category)}</div>
      <div class="catalog-item-status"><span class="status-pill pill-${s.status||'available'}">${s.status||'available'}</span></div>
      <div class="catalog-item-actions">
        <select class="status-select" onchange="changeServiceStatus(${s.id}, this.value, this)" data-current="${s.status||'available'}">
          ${catalogStatusOptions(s.status||'available')}
        </select>
        <button class="mini-btn" onclick="openEditModal('service', ${s.id})">Edit</button>
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
      <label class="toggle-label"><input type="checkbox" onchange="toggleArchived('dentists', this.checked)" ${showArchived ? 'checked' : ''}>Show archived</label>
      <button class="mini-btn add-btn" type="button" onclick="openAddModal('dentist')">+ Add Dentist</button>`;
  }

  list.innerHTML = filtered.map(d => `
    <div class="dentist-card ${d.status === 'archived' ? 'item-archived' : ''}">
      <div class="dentist-card-name">${esc(d.name)}</div>
      <div class="dentist-card-spec">${esc(d.spec)} · ${esc(d.cred)}</div>
      <div class="catalog-item-status"><span class="status-pill pill-${d.status||'available'}">${d.status||'available'}</span></div>
      <div class="catalog-item-actions">
        <select class="status-select" onchange="changeDentistStatus(${d.id}, this.value, this)" data-current="${d.status||'available'}">
          ${dentistStatusOptions(d.status||'available')}
        </select>
        <button class="mini-btn" onclick="openEditModal('dentist', ${d.id})">Edit</button>
      </div>
    </div>`).join('') || '<p class="empty-row">No dentists.</p>';
}

function renderCartTable(cartItems) {
  const tbody = document.getElementById('cart-table');
  if (!tbody) return;
  tbody.innerHTML = cartItems.map(c => {
    const unit = parseFloat(c.price || 0);
    const qty  = parseInt(c.quantity || 1);
    return `<tr>
      <td>${esc(c.product_name)}</td>
      <td>${qty}</td>
      <td>PHP ${unit.toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
      <td>PHP ${(unit*qty).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
    </tr>`;
  }).join('') || '<tr><td colspan="4" class="empty-row">No active cart items.</td></tr>';
}

function catalogStatusOptions(current) {
  return ['available','unavailable']
    .map(s => `<option value="${s}" ${s === current ? 'selected' : ''}>${capitalize(s)}</option>`)
    .join('');
}

function dentistStatusOptions(current) {
  return ['available','unavailable']
    .map(s => `<option value="${s}" ${s === current ? 'selected' : ''}>${capitalize(s)}</option>`)
    .join('');
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
      renderCatalog(_adminData.products || [], _adminData.services || [], _adminData.cartItems || [], _adminData.dentists || []);
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
  const tbody = document.getElementById('notifications-table');
  if (!tbody) return;
  tbody.innerHTML = notifications.map(n => `
    <tr class="${n.is_read ? '' : 'notif-unread'}">
      <td>${esc(n.user_name || '—')}</td>
      <td>${n.appointment_id ? '#' + n.appointment_id : '—'}</td>
      <td>${esc(n.message)}</td>
      <td>${formatDate(n.created_at)}</td>
    </tr>`).join('') || '<tr><td colspan="4" class="empty-row">No notifications.</td></tr>';
}

function updateNotifyBadge(notifications) {
  const badge = document.getElementById('admin-notify-badge');
  if (!badge) return;
  const unread = notifications.filter(n => !n.is_read).length;
  badge.textContent = unread;
  badge.style.display = unread > 0 ? 'inline-flex' : 'none';
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
      ],
      onSave: async (vals) => {
        const action = isEdit ? 'admin_edit_product' : 'admin_add_product';
        if (isEdit) vals.id = record.id;
        const d = await adminApi(action, vals);
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
        { id:'category',    label:'Category',          type:'text',    value: record?.category || '' },
        { id:'daily_slots', label:'Daily Slots',       type:'number',  value: record?.dailySlots || 8 },
      ],
      onSave: async (vals) => {
        const action = isEdit ? 'admin_edit_service' : 'admin_add_service';
        if (isEdit) vals.id = record.id;
        const d = await adminApi(action, vals);
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
      ],
      onSave: async (vals) => {
        const action = isEdit ? 'admin_edit_dentist' : 'admin_add_dentist';
        if (isEdit) vals.id = record.id;
        const d = await adminApi(action, vals);
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
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.id = 'admin-modal';
  overlay.onclick = (e) => { if (e.target === overlay) removeModal(); };

  overlay.innerHTML = `
    <div class="modal-box" role="dialog" aria-modal="true" aria-label="${esc(title)}">
      <div class="modal-head">
        <h3>${esc(title)}</h3>
        <button class="modal-close" onclick="removeModal()" aria-label="Close">&#x2715;</button>
      </div>
      <div class="modal-body">
        <form id="admin-modal-form" onsubmit="return false">
          ${fields.map(f => `
            <div class="form-group">
              <label for="mf-${f.id}">${esc(f.label)}</label>
              ${f.type === 'textarea'
                ? `<textarea id="mf-${f.id}" name="${f.id}" rows="3">${esc(f.value)}</textarea>`
                : `<input id="mf-${f.id}" name="${f.id}" type="${f.type}" value="${esc(String(f.value))}">`}
            </div>`).join('')}
          <div class="modal-err" id="modal-err"></div>
        </form>
      </div>
      <div class="modal-foot">
        <button class="btn-secondary" type="button" onclick="removeModal()">Cancel</button>
        <button class="btn-primary"   type="button" id="modal-save-btn">Save</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);

  document.getElementById('modal-save-btn').addEventListener('click', async () => {
    const form = document.getElementById('admin-modal-form');
    const vals = {};
    let valid = true;
    const errEl = document.getElementById('modal-err');
    errEl.textContent = '';

    fields.forEach(f => {
      const el = form.elements[f.id];
      const val = el ? el.value.trim() : '';
      vals[f.id] = f.type === 'number' ? parseFloat(val) || 0 : val;
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

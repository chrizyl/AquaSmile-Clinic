// ── CONSTANTS ──
const TIME_SLOTS = [
  '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
  '1:00 PM', '2:00 PM', '3:00 PM',  '4:00 PM', '5:00 PM',
];

const SERVICE_IMAGES = {
  S1: 'images/dental cleaning.jpeg',
  S2: 'images/xray.webp',
  S3: 'images/tooth extraction.jpg',
  S4: 'images/teeth whitening.jpg',
  S5: 'images/dental braces.webp',
  S6: 'images/root canal.jpeg',
  S7: 'images/dental crown.png',
  S8: 'images/veneers.jpg',
  S9: 'images/pediatric check up.jpg',
};

const DENTIST_AVATARS = {
  D1: 'images/dentistid_g1.jpg',
  D2: 'images/dentistid_m1.jpg',
  D3: 'images/dentistid_g2.jpg',
};

function isActiveAppointmentStatus(status) {
  return !['cancelled', 'user_cancelled'].includes(status);
}

// ── STATE ──
let booking = {
  service:  null,
  date:     null,
  time:     null,
  dentist:  null,
};

let currentStep = 1;
let calYear  = new Date().getFullYear();
let calMonth = new Date().getMonth();

// ── GUARD: Redirect if not logged in ──
function guardAuth() {
  const user  = Cookie.get('currentUser');
  const admin = Cookie.get('currentAdmin');
  if (!user && !admin) {
    window.location.href = 'login.php';
    return false;
  }
  if (admin) {
    showToast('Admins cannot book appointments.');
    setTimeout(() => { window.location.href = 'admin.php'; }, 1200);
    return false;
  }
  return true;
}

function gotoStep(n) {
  currentStep = n;

  // Show/hide cards
  [1, 2, 3, 4].forEach(i => {
    const card = document.getElementById('booking-step-' + i);
    if (card) card.style.display = (i === n) ? '' : 'none';
  });

  updateStepIndicators();

  if (n === 2) renderCalendar();
  if (n === 3) renderDentistAvailability();
  if (n === 4) renderConfirmSummary();

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepIndicators() {
  [1, 2, 3, 4].forEach(i => {
    const el = document.getElementById('step-' + i);
    if (!el) return;
    el.classList.remove('active', 'done');
    if (i === currentStep) el.classList.add('active');
    else if (i < currentStep) el.classList.add('done');
  });
}


function renderBookingServices() {
  const grid = document.getElementById('booking-services');
  if (!grid) return;

  grid.innerHTML = SERVICES.map(s => `
    <div
      class="booking-service-card"
      id="bsvc-${s.id}"
      onclick="selectService('${s.id}')"
    >
      <div class="booking-service-img-wrap">
        <img
          src="${SERVICE_IMAGES[s.id] || 'images/icons/service.png'}"
          alt="${s.name}"
          class="booking-service-img"
          onerror="this.style.opacity='0'"
        >
      </div>
      <div class="booking-service-card-body">
        <div class="booking-service-name">${s.name}</div>
        <div class="booking-service-price">${s.price}</div>
      </div>
    </div>`
  ).join('');
}

function selectService(sid) {
  booking.service = sid;
  booking.date    = null;
  booking.time    = null;
  booking.dentist = null;

  // Highlight selected card
  document.querySelectorAll('.booking-service-card').forEach(el => {
    el.classList.remove('selected');
  });
  const selected = document.getElementById('bsvc-' + sid);
  if (selected) selected.classList.add('selected');

  // Auto-advance after brief delay
  setTimeout(() => gotoStep(2), 380);
}


function renderCalendar() {
  const cal = document.getElementById('booking-calendar');
  if (!cal) return;

  const now         = new Date();
  const firstDay    = new Date(calYear, calMonth, 1).getDay();
  const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
  const monthNames  = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December',
  ];
  const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

  let html = `
    <div class="cal-header">
      <button class="cal-nav" onclick="changeMonth(-1)">&#8249;</button>
      <div class="cal-title">${monthNames[calMonth]} ${calYear}</div>
      <button class="cal-nav" onclick="changeMonth(1)">&#8250;</button>
    </div>
    <div class="cal-days-header">
      ${dayNames.map(d => `<div class="cal-day-name">${d}</div>`).join('')}
    </div>
    <div class="cal-days">`;

  // Empty cells before first day
  for (let i = 0; i < firstDay; i++) {
    html += `<div class="cal-day empty"></div>`;
  }

  // Day cells
  for (let d = 1; d <= daysInMonth; d++) {
    const date    = new Date(calYear, calMonth, d);
    const isPast  = date < new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const isSun   = date.getDay() === 0;
    const dateStr = `${calYear}-${String(calMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const isToday    = date.toDateString() === now.toDateString();
    const isSelected = booking.date === dateStr;

    let cls = 'cal-day';
    if (isPast || isSun) cls += ' disabled';
    if (isToday)         cls += ' today';
    if (isSelected)      cls += ' selected';

    const action = (!isPast && !isSun)
      ? `onclick="selectDate('${dateStr}')"`
      : '';

    html += `<div class="${cls}" ${action}>${d}</div>`;
  }

  html += `</div>`;
  cal.innerHTML = html;

  renderTimeSlots();
}

function changeMonth(delta) {
  calMonth += delta;
  if (calMonth < 0)  { calMonth = 11; calYear--; }
  if (calMonth > 11) { calMonth = 0;  calYear++; }
  renderCalendar();
}

function selectDate(dateStr) {
  booking.date    = dateStr;
  booking.time    = null;
  booking.dentist = null;

  const nextBtn = document.getElementById('step2-next');
  if (nextBtn) nextBtn.disabled = true;

  renderCalendar();
}

function renderTimeSlots() {
  const grid = document.getElementById('time-slots-grid');
  if (!grid) return;

  if (!booking.date) {
    grid.innerHTML = `<p style="font-size:0.83rem;color:var(--text-light);font-weight:300;grid-column:1/-1;">Select a date to see available slots.</p>`;
    return;
  }

  const appts = DB.get('appointments') || [];

  grid.innerHTML = TIME_SLOTS.map(t => {
    const bookedDentistIds = appts
      .filter(a => a.date === booking.date && a.time === t && isActiveAppointmentStatus(a.status))
      .map(a => a.dentistId);

    const allBooked  = DENTISTS.every(d => bookedDentistIds.includes(d.id));
    const isSelected = booking.time === t;

    let cls = 'time-slot';
    if (allBooked)  cls += ' booked-slot';
    if (isSelected) cls += ' selected-slot';

    const action = !allBooked ? `onclick="selectTime('${t}')"` : '';

    return `<div class="${cls}" ${action}>${t}${allBooked ? '<br><small>Full</small>' : ''}</div>`;
  }).join('');
}

function selectTime(t) {
  booking.time    = t;
  booking.dentist = null;

  const nextBtn = document.getElementById('step2-next');
  if (nextBtn) nextBtn.disabled = false;

  renderTimeSlots();
}


function renderDentistAvailability() {
  const list = document.getElementById('dentist-availability-list');
  if (!list) return;

  const appts = DB.get('appointments') || [];
  const bookedDentistIds = appts
    .filter(a =>
      a.date === booking.date &&
      a.time === booking.time &&
      isActiveAppointmentStatus(a.status)
    )
    .map(a => a.dentistId);

  list.innerHTML = DENTISTS.map(d => {
    const busy     = bookedDentistIds.includes(d.id);
    const selected = booking.dentist === d.id;
    const action   = !busy ? `onclick="selectDentist('${d.id}')"` : '';
    const avatarSrc = DENTIST_AVATARS[d.id] || 'images/icons/user.png';

    return `
      <div class="avail-dentist ${busy ? 'unavail' : ''} ${selected ? 'selected-d' : ''}" ${action}>
        <div class="avail-ava">
          <img src="${avatarSrc}" alt="${d.name}">
        </div>
        <div class="avail-info">
          <div class="avail-name">${d.name}</div>
          <div class="avail-cred">${d.cred}</div>
        </div>
        <div class="avail-badge ${busy ? 'busy' : 'free'}">
          ${busy ? 'Unavailable' : 'Available'}
        </div>
      </div>`;
  }).join('');
}

function selectDentist(did) {
  booking.dentist = did;

  const nextBtn = document.getElementById('step3-next');
  if (nextBtn) nextBtn.disabled = false;

  renderDentistAvailability();
}


function renderConfirmSummary() {
  const summaryEl = document.getElementById('booking-summary');
  if (!summaryEl) return;

  const svc  = SERVICES.find(s => s.id === booking.service);
  const dent = DENTISTS.find(d => d.id === booking.dentist);

  if (!svc || !dent) return;

  const dateObj = new Date(booking.date + 'T12:00:00');
  const dateStr = dateObj.toLocaleDateString('en-PH', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
  });

  const ICON = {
    service:  `<svg style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24"><path d="M12 2C9.5 2 7 4 7 7c0 2.5.5 5 1.5 7.5C9.5 17 10.5 22 12 22s2.5-5 3.5-7.5C16.5 12 17 9.5 17 7c0-3-2.5-5-5-5z"/><path d="M9 7c0-1.7 1.3-3 3-3"/></svg>`,
    calendar: `<svg style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01"/></svg>`,
    clock:    `<svg style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>`,
    dentist:  `<svg style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24"><path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3"/><path d="M6 3H4M18 3h2"/><circle cx="18" cy="18" r="3"/><path d="M12 16v2"/></svg>`,
    fee:      `<svg style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/></svg>`,
  };

  summaryEl.innerHTML = `
    <div class="confirm-row">
      <span class="confirm-label">${ICON.service} Service</span>
      <span class="confirm-value">${svc.name}</span>
    </div>
    <div class="confirm-row">
      <span class="confirm-label">${ICON.calendar} Date</span>
      <span class="confirm-value">${dateStr}</span>
    </div>
    <div class="confirm-row">
      <span class="confirm-label">${ICON.clock} Time</span>
      <span class="confirm-value">${booking.time}</span>
    </div>
    <div class="confirm-row">
      <span class="confirm-label">${ICON.dentist} Dentist</span>
      <span class="confirm-value">${dent.name}</span>
    </div>
    <div class="confirm-row">
      <span class="confirm-label">${ICON.fee} Fee</span>
      <span class="confirm-value">${svc.price}</span>
    </div>`;
}


function confirmBooking() {
  if (!booking.service || !booking.date || !booking.time || !booking.dentist) {
    showToast('Please complete all selections before confirming.');
    return;
  }

  const user = Cookie.get('currentUser');
  if (!user) {
    window.location.href = 'login.php';
    return;
  }

  const svc  = SERVICES.find(s => s.id === booking.service);
  const dent = DENTISTS.find(d => d.id === booking.dentist);
  const notes = document.getElementById('booking-notes')?.value || '';

  const newAppt = {
    id:           'A' + Date.now(),
    userId:       user.id,
    userName:     user.name,
    userEmail:    user.email,
    userContact:  user.contact,
    serviceId:    booking.service,
    serviceName:  svc.name,
    serviceImg:   SERVICE_IMAGES[booking.service] || 'images/icons/service.png',
    dentistId:    booking.dentist,
    dentistName:  dent.name,
    dentistImg:   DENTIST_AVATARS[booking.dentist] || 'images/icons/user.png',
    date:         booking.date,
    time:         booking.time,
    notes:        notes,
    status:       'pending',
    createdAt:    new Date().toISOString(),
  };

  const appts = DB.get('appointments') || [];
  appts.push(newAppt);
  DB.set('appointments', appts);

  /* Show the success popup — redirect handled by booking.html popup flow */
  const popup = document.getElementById('success-popup');
  if (popup) {
    popup.classList.add('show');
  } else {
    showToast('Appointment booked successfully!');
    setTimeout(() => { window.location.href = 'index.php'; }, 1400);
  }
}

function initBooking() {
  if (!guardAuth()) return;
  updateNav();
  renderBookingServices();
  gotoStep(1);
}

initBooking();

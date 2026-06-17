// ── CONSTANTS ──
const TIME_SLOTS = [
  '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
  '1:00 PM', '2:00 PM', '3:00 PM',  '4:00 PM', '5:00 PM',
];

const SERVICE_IMAGES = {
  1: 'images/dental cleaning.jpeg',
  2: 'images/xray.webp',
  3: 'images/tooth extraction.jpg',
  4: 'images/teeth whitening.jpg',
  5: 'images/dental braces.webp',
  6: 'images/root canal.jpeg',
  7: 'images/dental crown.png',
  8: 'images/veneers.jpg',
  9: 'images/pediatric check up.jpg',
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
  1: 'images/dentistid_g1.jpg',
  2: 'images/dentistid_m1.jpg',
  3: 'images/dentistid_g2.jpg',
  D1: 'images/dentistid_g1.jpg',
  D2: 'images/dentistid_m1.jpg',
  D3: 'images/dentistid_g2.jpg',
};

function isActiveAppointmentStatus(status) {
  return !['cancelled', 'user_cancelled'].includes(status);
}

// ── STATE ──
function parsePesoAmount(value) {
  return Number(String(value || '').replace(/[^\d.]/g, '')) || 0;
}

function formatBookingMoney(amount) {
  return 'PHP ' + Number(amount || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
}

function promoDiscountText(promo) {
  const type = String(promo?.discount_type || promo?.discountType || '').toLowerCase();
  const value = Number(promo?.discount_value || promo?.discountValue || 0);
  if (type === 'percentage') return `${value}%`;
  if (type === 'fixed') return formatBookingMoney(value);
  return value ? String(value) : '-';
}

async function loadBookingPromoFromUrl() {
  const code = new URLSearchParams(window.location.search).get('promo');
  if (!code) return;

  try {
    const data = await apiRequest('validate_promo', { promo_code: code, target: 'appointment' });
    activeBookingPromo = data.promo || null;
    const input = document.getElementById('booking-promo-code');
    if (input) input.value = String(code).toUpperCase();
    showToast('Promo code applied successfully.');
  } catch (err) {
    activeBookingPromo = null;
    showToast('Invalid or expired promo code.');
  }
}

function setBookingPromoMessage(message, type = '') {
  const el = document.getElementById('booking-promo-message');
  if (!el) return;
  el.textContent = message;
  el.className = 'booking-promo-message ' + type;
}

async function applyBookingPromo() {
  const input = document.getElementById('booking-promo-code');
  const code = input ? input.value.trim().toUpperCase() : '';
  const svc = SERVICES.find(s => s.id === booking.service);
  const serviceFee = parsePesoAmount(svc?.rawPrice || svc?.price || 0);

  if (!code || !svc) {
    activeBookingPromo = null;
    setBookingPromoMessage('Invalid or expired promo code.', 'error');
    renderConfirmSummary();
    return;
  }

  try {
    const data = await apiRequest('validate_promo', {
      promo_code: code,
      target: 'appointment',
      subtotal: serviceFee,
    });
    activeBookingPromo = data.promo || null;
    if (input) input.value = code;
    setBookingPromoMessage('Promo code applied successfully.', 'success');
  } catch (err) {
    activeBookingPromo = null;
    setBookingPromoMessage('Invalid or expired promo code.', 'error');
  }
  renderConfirmSummary();
}

let booking = {
  service:  null,
  date:     null,
  time:     null,
  dentist:  null,
};

let currentStep = 1;
let calYear  = new Date().getFullYear();
let calMonth = new Date().getMonth();

// ── RATING POPUP STATE ──
let ratingStars    = 0;
let ratingAspects  = [];
let currentAppointmentId = null;
let activeBookingPromo = null;

// ── GUARD: Redirect if not logged in ──
function guardAuth() {
  const user  = Cookie.get('currentUser');
  const admin = Cookie.get('currentAdmin');
  if (!user && !admin) {
    window.location.href = 'login.php';
    return false;
  }
  if (admin || (user && user.role === 'admin')) {
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

  requestAnimationFrame(() => {
    scrollToActiveBookingStep(n);
  });
}

function scrollToActiveBookingStep(stepNumber) {
  const activeCard = document.getElementById('booking-step-' + stepNumber);
  const steps = document.querySelector('.booking-steps');
  if (!activeCard) return;

  const stepsHeight = steps ? steps.offsetHeight : 0;
  const stepsBottom = steps ? steps.getBoundingClientRect().bottom + window.scrollY : 0;
  const cardTop = activeCard.getBoundingClientRect().top + window.scrollY;
  const targetTop = Math.max(0, cardTop - stepsHeight - 28);

  if (cardTop < window.scrollY || cardTop < stepsBottom + 12) {
    window.scrollTo({ top: targetTop, behavior: 'smooth' });
  }
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
          src="${s.imagePath || SERVICE_IMAGES[s.id] || 'images/icons/service.png'}"
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
    const avatarSrc = d.imagePath || DENTIST_AVATARS[d.id] || 'images/icons/user.png';

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
  const promo = activeBookingPromo;
  const originalFee = promo ? Number(promo.original_price || promo.originalPrice || parsePesoAmount(svc.price)) : 0;
  const explicitPromoPrice = promo ? Number(promo.promo_price || promo.promoPrice || 0) : 0;
  const discountType = String(promo?.discount_type || promo?.discountType || '').toLowerCase();
  const discountValue = Number(promo?.discount_value || promo?.discountValue || 0);
  const computedDiscount = discountType === 'percentage'
    ? originalFee * (discountValue / 100)
    : (discountType === 'fixed' ? discountValue : 0);
  const promoPrice = explicitPromoPrice > 0 ? explicitPromoPrice : Math.max(0, originalFee - computedDiscount);

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
    </div>
    ${promo ? `
      <div class="booking-promo-applied">
        <div class="booking-promo-kicker">Promo Applied</div>
        <div class="booking-promo-name">${escapeHtml(promo.promo_name || promo.name || '')}</div>
        <div class="confirm-row">
          <span class="confirm-label">Original Fee:</span>
          <span class="confirm-value">${formatBookingMoney(originalFee)}</span>
        </div>
        <div class="confirm-row">
          <span class="confirm-label">Discount:</span>
          <span class="confirm-value">${promoDiscountText(promo)}</span>
        </div>
        <div class="confirm-row">
          <span class="confirm-label">Final Fee:</span>
          <span class="confirm-value">${formatBookingMoney(promoPrice)}</span>
        </div>
      </div>` : ''}`;
}


async function confirmBooking() {
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

  let newAppt = {
    id:           'A' + Date.now(),
    userId:       user.id,
    userName:     user.name,
    userEmail:    user.email,
    userContact:  user.contact,
    serviceId:    booking.service,
    serviceName:  svc.name,
    serviceImg:   svc.imagePath || SERVICE_IMAGES[booking.service] || 'images/icons/service.png',
    dentistId:    booking.dentist,
    dentistName:  dent.name,
    dentistImg:   dent.imagePath || DENTIST_AVATARS[booking.dentist] || 'images/icons/user.png',
    date:         booking.date,
    time:         booking.time,
    notes:        notes,
    promo_code:   activeBookingPromo?.promo_code || activeBookingPromo?.code || null,
    status:       'pending',
    createdAt:    new Date().toISOString(),
  };

  try {
    const result = await apiRequest('create_appointment', newAppt);
    newAppt = result.appointment;
    currentAppointmentId = newAppt?.id || result.appointmentId || result.appointment_id || null;
  } catch (err) {
    console.warn('Appointment save failed:', err.message);
    showToast(err.message || 'Appointment was not saved to the database.');
    return;
  }

  const appts = DB.get('appointments') || [];
  if (!appts.find(a => a.id === newAppt.id)) {
    appts.push(newAppt);
    DB.set('appointments', appts);
  }

  /* Show the success popup — rating popup and redirect handled below */
  const popup = document.getElementById('success-popup');
  if (popup) {
    popup.classList.add('show');
  } else {
    showToast('Appointment booked successfully!');
    setTimeout(() => { window.location.href = 'index.php'; }, 1400);
  }
}

// ── SUCCESS / RATING POPUP FLOW ──
function showRatingPopup() {
  const successPopup = document.getElementById('success-popup');
  const ratingPopup   = document.getElementById('rating-popup');
  if (successPopup) successPopup.classList.remove('show');
  if (ratingPopup)   ratingPopup.classList.add('show');
}

function setStarRating(n) {
  ratingStars = n;

  document.querySelectorAll('#rating-stars .star-btn').forEach(btn => {
    const val = Number(btn.dataset.star);
    btn.classList.toggle('active', val <= n);
  });

  updateRatingSubmitState();
}

function toggleAspect(chipEl) {
  const aspect = chipEl.dataset.aspect;
  const idx = ratingAspects.indexOf(aspect);

  if (idx === -1) {
    ratingAspects.push(aspect);
    chipEl.classList.add('selected');
  } else {
    ratingAspects.splice(idx, 1);
    chipEl.classList.remove('selected');
  }
}

function updateRatingSubmitState() {
  const submitBtn = document.getElementById('rating-submit-btn');
  if (submitBtn) submitBtn.disabled = ratingStars === 0;
}

async function submitRating() {
  if (ratingStars < 1 || ratingStars > 5) {
    showToast('Please select a rating before submitting.');
    return;
  }
  if (!currentAppointmentId) {
    showToast('Unable to find the appointment for this feedback. Please try again.');
    return;
  }

  const submitBtn = document.getElementById('rating-submit-btn');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
  }

  const comment = document.getElementById('rating-comment')?.value || '';

  try {
    await apiRequest('save_feedback', {
      feedback_type: 'appointment',
      appointment_id: currentAppointmentId,
      rating: ratingStars,
      tags: ratingAspects.join(', '),
      comment: comment,
    });
  } catch (err) {
    showToast(err.message || 'Unable to save your feedback. Please try again.');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit Feedback';
    }
    return;
  }

  const formContent = document.getElementById('rating-form-content');
  const thankYou     = document.getElementById('rating-thankyou');
  if (formContent) formContent.classList.add('hide');
  if (thankYou)     thankYou.classList.add('show');
}

function resetRatingForm() {
  ratingStars   = 0;
  ratingAspects = [];

  document.querySelectorAll('#rating-stars .star-btn').forEach(btn => {
    btn.classList.remove('active', 'hovered');
  });
  document.querySelectorAll('#rating-aspects .aspect-chip').forEach(chip => {
    chip.classList.remove('selected');
  });

  const commentEl = document.getElementById('rating-comment');
  if (commentEl) commentEl.value = '';

  updateRatingSubmitState();
  const submitBtn = document.getElementById('rating-submit-btn');
  if (submitBtn) submitBtn.textContent = 'Submit Feedback';

  const formContent = document.getElementById('rating-form-content');
  const thankYou     = document.getElementById('rating-thankyou');
  if (formContent) formContent.classList.remove('hide');
  if (thankYou)     thankYou.classList.remove('show');
}

function closeBookingPopups() {
  const successPopup = document.getElementById('success-popup');
  const ratingPopup   = document.getElementById('rating-popup');
  if (successPopup) successPopup.classList.remove('show');
  if (ratingPopup)   ratingPopup.classList.remove('show');

  resetRatingForm();

  window.location.href = 'index.php';
}

async function initBooking() {
  if (!guardAuth()) return;
  await syncCatalogFromDatabase();
  await loadBookingPromoFromUrl();
  try {
    const data = await apiGet('appointments');
    DB.set('appointments', data.appointments || []);
  } catch (err) {
    console.warn('Using local appointments fallback:', err.message);
  }
  updateNav();
  renderBookingServices();
  gotoStep(1);
}

initBooking();

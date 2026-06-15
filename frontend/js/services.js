// ── SERVICE IMAGE PATHS ──
const SERVICE_IMAGES_PG = {
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

// ── SERVICE CATEGORIES  ──
// Unique category list in display order
const CATEGORY_ORDER = ['All', 'Preventive', 'Diagnostic', 'Restorative', 'Cosmetic', 'Orthodontic'];

// ── ACTIVE FILTER STATE ──
let activeFilter = 'All';

function renderFilterTabs() {
  const container = document.getElementById('service-filters');
  if (!container) return;

  container.innerHTML = CATEGORY_ORDER.map(cat => `
    <button
      class="filter-btn ${cat === activeFilter ? 'active' : ''}"
      onclick="applyFilter('${cat}')"
    >${cat}</button>`
  ).join('');
}

function applyFilter(category) {
  activeFilter = category;

  // Update tab active state
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.toggle('active', btn.textContent === category);
  });

  // Show/hide service cards
  document.querySelectorAll('.service-card-full').forEach(card => {
    const cardCategory = card.dataset.category;
    const show = category === 'All' || cardCategory === category;
    card.classList.toggle('hidden', !show);
  });
}

function renderServiceCards() {
  const grid = document.getElementById('services-grid');
  if (!grid) return;

  grid.innerHTML = SERVICES.map(s => {
    const imgSrc   = s.imagePath || SERVICE_IMAGES_PG[s.id] || 'images/services/placeholder.jpg';
    const category = serviceCategory(s);

    return `
      <div
        class="service-card-full"
        id="svc-card-${s.id}"
        data-category="${escHtml(category)}"
      >
        <div class="service-img-wrap">
          <img src="${escHtml(imgSrc)}" alt="${escHtml(s.name)}">
          ${category ? `<div class="service-category-tag">${escHtml(category)}</div>` : ''}
        </div>
        <div class="service-card-body">
          <div class="service-card-name">${escHtml(s.name)}</div>
          <div class="service-card-desc">${escHtml(s.desc)}</div>
          <div class="service-card-footer">
            <div class="service-card-price">${escHtml(s.price)}</div>
          </div>
          <div class="service-card-actions">
            <button
              class="btn-view-service"
              onclick="openServiceModal('${s.id}')"
            >Learn More</button>
            <button
              class="btn-book-service-pg ${isAdmin() ? 'admin-disabled' : ''}"
              onclick="${isAdmin() ? 'return false;' : `bookService('${s.id}')`}"
              ${isAdmin() ? 'disabled' : ''}
            >Book Now</button>
          </div>
        </div>
      </div>`;
  }).join('');
}

function openServiceModal(sid) {
  const service  = SERVICES.find(s => s.id === sid);
  const imgSrc   = service?.imagePath || SERVICE_IMAGES_PG[sid] || 'dental_logo.png';
  if (!service) return;

  const category = serviceCategory(service);

  document.getElementById('modal-body').innerHTML = `
    <img
      src="${escHtml(imgSrc)}"
      alt="${escHtml(service.name)}"
      class="modal-service-img"
    >
    <div class="modal-info">
      <div class="modal-service-name">${escHtml(service.name)}</div>
      ${category ? `<div class="modal-service-category">${escHtml(category)}</div>` : ''}
      <div class="modal-service-desc">${escHtml(service.desc)}</div>
      <div class="modal-service-price-row">
        <span class="modal-price-label">Starting Price</span>
        <span class="modal-price-value">${escHtml(service.price)}</span>
      </div>
      <button class="modal-book-btn ${isAdmin() ? 'admin-disabled' : ''}" onclick="${isAdmin() ? 'return false;' : `bookService('${sid}')`}" ${isAdmin() ? 'disabled' : ''}>
        Book This Service
      </button>
    </div>`;

  document.getElementById('modal-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function serviceCategory(service) {
  return String(service?.category || '').trim();
}

function escHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function closeServiceModal() {
  document.getElementById('modal-overlay').classList.remove('open');
  document.body.style.overflow = '';
}

// Close modal with Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeServiceModal();
});


function bookService(sid) {
  const user  = Cookie.get('currentUser');
  const admin = Cookie.get('currentAdmin');

  if (!user && !admin) {
    sessionStorage.setItem('preselect_service', sid);
    window.location.href = 'login.php';
    return;
  }
  if (admin) {
    showToast('Admin accounts cannot book appointments.');
    closeServiceModal();
    return;
  }
  sessionStorage.setItem('preselect_service', sid);
  closeServiceModal();
  window.location.href = 'booking.php';
}


function computeDiscountedPrice(basePrice, n, rate) {
  if (n <= 1) return basePrice;
  return computeDiscountedPrice(basePrice, n - 1, rate) * (1 - rate);
}

function renderEstimator() {
  const container = document.getElementById('cost-estimator');
  if (!container) return;

  const serviceSelect = document.getElementById('estimator-service');
  const sessionInput  = document.getElementById('estimator-sessions');
  const tbody         = document.getElementById('estimator-tbody');
  const totalEl       = document.getElementById('estimator-total');

  if (!serviceSelect || !sessionInput || !tbody || !totalEl) return;

  const sid      = serviceSelect.value;
  const sessions = Math.min(Math.max(parseInt(sessionInput.value) || 1, 1), 10);
  const service  = SERVICES.find(s => s.id === sid);

  if (!service) return;

  // Parse base price — strip non-numeric except decimal point
  const basePrice = parseFloat(service.price.replace(/[^0-9.]/g, '')) || 0;

  let rows  = '';
  let total = 0;

  for (let i = 1; i <= sessions; i++) {
    const sessionPrice    = computeDiscountedPrice(basePrice, i, 0.05);
    const discountPercent = i === 1 ? 0 : Math.round((1 - sessionPrice / basePrice) * 100);
    total += sessionPrice;

    rows += `
      <tr class="estimator-row ${i % 2 === 0 ? 'estimator-row-alt' : ''}">
        <td class="estimator-td">Session ${i}</td>
        <td class="estimator-td estimator-td-center">
          ${i === 1
            ? '<span class="estimator-badge-full">Full Price</span>'
            : `<span class="estimator-badge-discount">${discountPercent}% off</span>`
          }
        </td>
        <td class="estimator-td estimator-td-right">
          PHP ${sessionPrice.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </td>
      </tr>`;
  }

  tbody.innerHTML = rows;
  totalEl.textContent = `PHP ${total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function initEstimator() {
  const container = document.getElementById('cost-estimator');
  if (!container) return;

  const serviceSelect = document.getElementById('estimator-service');
  const sessionInput  = document.getElementById('estimator-sessions');
  const sessionVal    = document.getElementById('estimator-sessions-val');

  // Populate service dropdown from SERVICES global
  if (serviceSelect) {
    serviceSelect.innerHTML = SERVICES.map(s =>
      `<option value="${s.id}">${s.name}</option>`
    ).join('');
  }

  if (sessionInput && sessionVal) {
    sessionInput.addEventListener('input', function () {
      sessionVal.textContent = this.value;
      renderEstimator();
    });
  }

  if (serviceSelect) {
    serviceSelect.addEventListener('change', renderEstimator);
  }

  renderEstimator();
}

// ── BOOKING REDIRECT (CTA button) ──
function requireBooking() {
  const user  = Cookie.get('currentUser');
  const admin = Cookie.get('currentAdmin');

  if (!user && !admin) {
    window.location.href = 'login.php';
    return;
  }
  if (admin) {
    showToast('Admin accounts cannot book appointments.');
    return;
  }
  window.location.href = 'booking.php';
}

async function initServicesPage() {
  await syncCatalogFromDatabase();
  updateNav();
  renderFilterTabs();
  renderServiceCards();
  initEstimator();
}

initServicesPage();

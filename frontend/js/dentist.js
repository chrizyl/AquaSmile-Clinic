const DENTIST_IMAGES = {
  1: 'images/dentist_doctorg12.jpg',
  2: 'images/dentist_doctorm.jpg',
  3: 'images/dentist_doctorg2.jpg',
  D1: 'images/dentist_doctorg12.jpg',
  D2: 'images/dentist_doctorm.jpg',
  D3: 'images/dentist_doctorg2.jpg',
};

function renderDentistCards() {
  const grid = document.getElementById('dentist-grid');
  if (!grid) return;

  grid.innerHTML = DENTISTS.map(d => {
    const imgSrc = dentistImage(d);

    return `
      <div class="dentist-card-full">
        <div class="dentist-photo-wrap">
          <img src="${escHtml(imgSrc)}" alt="${escHtml(d.name)}">
          ${d.spec ? `<div class="dentist-spec-tag">${escHtml(d.spec)}</div>` : ''}
        </div>
        <div class="dentist-card-body">
          <div class="dentist-card-name">${escHtml(d.name)}</div>
          ${d.cred ? `<div class="dentist-card-cred">${escHtml(d.cred)}</div>` : ''}
          ${d.desc ? `<div class="dentist-card-desc">${escHtml(d.desc)}</div>` : ''}
          <div class="dentist-card-actions">
            <button
              class="btn-view-profile"
              onclick="openDentistModal('${escAttr(d.id)}')"
            >View Profile</button>
            <button
              class="btn-book-dentist ${isAdmin() ? 'admin-disabled' : ''}"
              onclick="${isAdmin() ? 'return false;' : `bookWithDentist('${escAttr(d.id)}')`}"
              ${isAdmin() ? 'disabled' : ''}
            >Book Now</button>
          </div>
        </div>
      </div>`;
  }).join('');
}

function openDentistModal(did) {
  const dentist = DENTISTS.find(d => d.id === did);
  if (!dentist) return;

  const imgSrc = dentistImage(dentist);
  const firstName = dentist.firstName || dentist.name.replace(/^Dr\.\s*/i, '').split(/\s+/)[0] || 'this dentist';
  const credentials = String(dentist.cred || '').trim();
  const bio = String(dentist.desc || '').trim();
  const sections = [
    ['Specialization', dentist.spec],
    ['Education', dentist.education],
    ['Languages', dentist.languages],
    ['Practicing Since', dentist.practicingSince || dentist.practicing_since],
  ].filter(([, value]) => String(value || '').trim() !== '');
  const sectionMarkup = sections.length
    ? `<div class="dentist-modal-sections">
        ${sections.map(([label, value]) => `
          <section class="dentist-modal-section">
            <div class="dentist-modal-label">${escHtml(label)}</div>
            <div class="dentist-modal-value">${escHtml(value)}</div>
          </section>
        `).join('')}
      </div>`
    : '';

  document.getElementById('modal-body').innerHTML = `
    <div class="modal-photo-wrap">
      <img
        src="${escHtml(imgSrc)}"
        alt="${escHtml(dentist.name)}"
        class="modal-photo"
      >
    </div>
    <div class="modal-info">
      <div class="modal-profile-head">
        <div>
          <div class="modal-name">${escHtml(dentist.name)}</div>
          ${credentials ? `<div class="modal-cred">${escHtml(credentials)}</div>` : ''}
        </div>
      </div>
      ${bio ? `<p class="modal-desc">${escHtml(bio)}</p>` : ''}
      ${sectionMarkup}

      <button class="modal-book-btn ${isAdmin() ? 'admin-disabled' : ''}" onclick="${isAdmin() ? 'return false;' : `bookWithDentist('${escAttr(did)}')`}" ${isAdmin() ? 'disabled' : ''}>
        Book an Appointment with ${escHtml(firstName)}
      </button>
    </div>`;

  document.getElementById('modal-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function dentistImage(dentist) {
  return dentist?.imagePath || dentist?.photo || DENTIST_IMAGES[dentist?.id] || 'images/dentists/placeholder.jpg';
}

function escHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function escAttr(value) {
  return escHtml(value).replace(/`/g, '&#096;');
}

function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeModal();
});

function bookWithDentist(did) {
  const user = Cookie.get('currentUser');
  const admin = Cookie.get('currentAdmin');

  if (!user && !admin) {
    sessionStorage.setItem('preselect_dentist', did);
    window.location.href = 'login.php';
    return;
  }
  if (admin) {
    showToast('Admin accounts cannot book appointments.');
    closeModal();
    return;
  }
  sessionStorage.setItem('preselect_dentist', did);
  closeModal();
  window.location.href = 'booking.php';
}

function requireBooking() {
  const user = Cookie.get('currentUser');
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

async function initDentistsPage() {
  await syncCatalogFromDatabase();
  updateNav();
  renderDentistCards();
}

initDentistsPage();

// Replace with actual photo filenames when available.
const DENTIST_IMAGES = {
  1: 'images/dentist_doctorg12.jpg',
  2: 'images/dentist_doctorm.jpg',
  3: 'images/dentist_doctorg2.jpg',
  D1: 'images/dentist_doctorg12.jpg',
  D2: 'images/dentist_doctorm.jpg',
  D3: 'images/dentist_doctorg2.jpg',
};
// ── DENTIST EXTENDED DETAILS ──
// Additional info shown in the profile modal.
const DENTIST_DETAILS = {
  D1: {
    education:   'Doctor of Dental Medicine — University of Santo Tomas',
    yearsActive: '2013 to present',
    languages:   'Filipino, English',
    highlights: [
      'Invisalign Certified Provider',
      'Cosmetic Dentistry Specialist',
      'Gentle approach for anxious patients',
    ],
  },
  D2: {
    education:   'DMD, Master of Science in Dentistry — De La Salle Medical & Health Sciences Institute',
    yearsActive: '2016 to present',
    languages:   'Filipino, English, Mandarin',
    highlights: [
      'Oral & Maxillofacial Surgery',
      'Complex extraction specialist',
      'Board-certified orthodontist',
    ],
  },
  D3: {
    education:   'DMD, Pediatric Dentistry Certificate — Philippine Childrens Medical Center',
    yearsActive: '2018 to present',
    languages:   'Filipino, English',
    highlights: [
      'Certified pediatric dentist',
      'Behavior management specialist',
      'Family dentistry advocate',
    ],
  },
};


function renderDentistCards() {
  const grid = document.getElementById('dentist-grid');
  if (!grid) return;

  grid.innerHTML = DENTISTS.map(d => {
    const imgSrc = DENTIST_IMAGES[d.id] || 'images/dentists/placeholder.jpg';

    return `
      <div class="dentist-card-full">
        <div class="dentist-photo-wrap">
          <img src="${imgSrc}" alt="${d.name}">
          <div class="dentist-spec-tag">${d.spec}</div>
        </div>
        <div class="dentist-card-body">
          <div class="dentist-card-name">${d.name}</div>
          <div class="dentist-card-cred">${d.cred}</div>
          <div class="dentist-card-desc">${d.desc}</div>
          <div class="dentist-card-actions">
            <button
              class="btn-view-profile"
              onclick="openDentistModal('${d.id}')"
            >View Profile</button>
            <button
              class="btn-book-dentist ${isAdmin() ? 'admin-disabled' : ''}"
              onclick="${isAdmin() ? 'return false;' : `bookWithDentist('${d.id}')`}"
              ${isAdmin() ? 'disabled' : ''}
            >Book Now</button>
          </div>
        </div>
      </div>`;
  }).join('');
}


function openDentistModal(did) {
  const dentist  = DENTISTS.find(d => d.id === did);
  const details  = DENTIST_DETAILS[did] || DENTIST_DETAILS['D' + did] || DENTIST_DETAILS.D1;
  const imgSrc   = DENTIST_IMAGES[did] || 'images/dentists/placeholder.jpg';

  if (!dentist) return;

  const highlightList = details.highlights
    .map(h => `<li style="margin-bottom:6px;">${h}</li>`)
    .join('');

  document.getElementById('modal-body').innerHTML = `
    <img
      src="${imgSrc}"
      alt="${dentist.name}"
      class="modal-photo"
    >
    <div class="modal-info">
      <div class="modal-name">${dentist.name}</div>
      <div class="modal-cred">${dentist.cred}</div>
      <div class="modal-spec">${dentist.spec}</div>
      <div class="modal-desc">${dentist.desc}</div>

      <div style="margin-bottom:18px;">
        <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--aqua);font-weight:500;margin-bottom:10px;">Education</div>
        <div style="font-size:0.87rem;color:var(--text-mid);font-weight:300;">${details.education}</div>
      </div>

      <div style="margin-bottom:18px;">
        <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--aqua);font-weight:500;margin-bottom:10px;">Specializations</div>
        <ul style="padding-left:18px;font-size:0.87rem;color:var(--text-mid);font-weight:300;line-height:1.7;">
          ${highlightList}
        </ul>
      </div>

      <div style="display:flex;gap:24px;margin-bottom:28px;">
        <div>
          <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-light);margin-bottom:4px;">Languages</div>
          <div style="font-size:0.87rem;color:var(--text-mid);font-weight:400;">${details.languages}</div>
        </div>
        <div>
          <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-light);margin-bottom:4px;">Practicing Since</div>
          <div style="font-size:0.87rem;color:var(--text-mid);font-weight:400;">${details.yearsActive}</div>
        </div>
      </div>

      <button class="modal-book-btn ${isAdmin() ? 'admin-disabled' : ''}" onclick="${isAdmin() ? 'return false;' : `bookWithDentist('${did}')`}" ${isAdmin() ? 'disabled' : ''}>
        Book an Appointment with ${dentist.name.split(' ')[1]}
      </button>
    </div>`;

  document.getElementById('modal-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
  document.body.style.overflow = '';
}

// Close modal with Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeModal();
});

function bookWithDentist(did) {
  // Re-read cookies fresh every time (hindi lang sa page load)
  const user  = Cookie.get('currentUser');
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

async function initDentistsPage() {
  await syncCatalogFromDatabase();
  updateNav();
  renderDentistCards();
}

initDentistsPage();

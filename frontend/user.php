<?php
require_once 'includes/session-init.php';
require_once 'includes/admin-check.php';
require_once 'includes/navbar-auth.php';

no_cache_headers();

requirePatientPage();

$sessionName = trim((string) ($_SESSION['user_name'] ?? 'Patient'));
$initialFirstName = preg_split('/\s+/', $sessionName)[0] ?? 'Patient';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg">
  <title>My Account | AquaSmile</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css?v=20260614">
  <link rel="stylesheet" href="css/notifications.css?v=20260616a">
  <link rel="stylesheet" href="css/auth-nav.css?v=20260614">
  <link rel="stylesheet" href="css/user.css?v=20260616b">
</head>
<body class="account-body">
  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <a class="nav-logo account-logo" href="index.php" aria-label="AquaSmile home">
      <img src="images/AquaSmile_Logo.svg" alt="" class="nav-logo-img">
      <span>AquaSmile</span>
    </a>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" <?php echo nav_is_patient() ? '' : 'style="display:none"'; ?>>Book Appointment</button>
      <?php render_nav_auth(); ?>
    </div>
  </nav>

  <main class="account-shell">
    <header class="account-hero">
      <div>
        <span class="account-eyebrow">Patient Portal</span>
        <h1 id="hero-greeting">Hello, <?php echo htmlspecialchars($initialFirstName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p>Manage your profile, appointments, orders, and notifications in one place.</p>
      </div>
      <div class="account-hero-mark" aria-hidden="true">
        <img src="images/AquaSmile_Logo.svg" alt="">
      </div>
    </header>

    <div class="account-loading" id="account-loading">Loading your account...</div>
    <div class="account-alert" id="account-alert" hidden></div>

    <div class="portal-layout" id="account-content" hidden>
      <aside class="portal-sidebar">
        <div class="sidebar-profile">
          <div class="profile-avatar" id="profile-avatar" aria-hidden="true">AS</div>
          <div>
            <strong id="sidebar-name">AquaSmile Patient</strong>
            <span>Patient</span>
          </div>
        </div>
        <div class="portal-menu" role="navigation" aria-label="Patient account sections">
          <button class="portal-menu-item active" type="button" data-section="overview"><i class="bi bi-house-door-fill" aria-hidden="true"></i>Overview</button>
          <button class="portal-menu-item" type="button" data-section="personal"><i class="bi bi-person-fill" aria-hidden="true"></i>Personal Information</button>
          <button class="portal-menu-item" type="button" data-section="appointments"><i class="bi bi-calendar-event-fill" aria-hidden="true"></i>Appointments</button>
          <button class="portal-menu-item" type="button" data-section="orders"><i class="bi bi-bag-fill" aria-hidden="true"></i>Orders</button>
          <button class="portal-menu-item" type="button" data-section="notifications"><i class="bi bi-bell-fill" aria-hidden="true"></i>Notifications <b id="sidebar-unread" hidden>0</b></button>
          <button class="portal-menu-item" type="button" data-section="password"><i class="bi bi-gear-fill" aria-hidden="true"></i>Change Password</button>
        </div>
      </aside>

      <div class="portal-main">
        <section class="portal-section active" id="section-overview" data-section-panel="overview">
          <div class="section-heading">
            <div><span class="card-kicker">Account Summary</span><h2>Overview</h2></div>
          </div>
          <div class="summary-grid">
            <article class="summary-card aqua"><span>Upcoming Appointments</span><strong id="summary-upcoming">0</strong></article>
            <article class="summary-card peach"><span>Active Orders</span><strong id="summary-orders">0</strong></article>
            <article class="summary-card green"><span>Completed Appointments</span><strong id="summary-completed">0</strong></article>
            <article class="summary-card lavender"><span>Completed Orders</span><strong id="summary-completed-orders">0</strong></article>
          </div>
          <div class="overview-grid">
            <article class="account-card overview-card"><span class="card-kicker">Coming Up</span><h3>Next Appointment</h3><div id="next-appointment"></div></article>
            <article class="account-card overview-card"><span class="card-kicker">Most Recent</span><h3>Latest Order</h3><div id="latest-order"></div></article>
            <article class="account-card overview-card"><span class="card-kicker">Latest Update</span><h3>Recent Notification</h3><div id="recent-notification"></div></article>
            <article class="account-card overview-card profile-summary"><span class="card-kicker">Patient Profile</span><h3 id="overview-profile-name">-</h3><p id="overview-profile-email">-</p><button class="text-link" type="button" data-go-section="personal">View profile</button></article>
          </div>
        </section>

        <section class="portal-section" id="section-personal" data-section-panel="personal">
          <div class="section-heading"><div><span class="card-kicker">Your Details</span><h2>Personal Information</h2></div></div>
          <article class="account-card">
            <form id="profile-form" novalidate>
              <div class="profile-form-message" id="profile-form-message" role="alert" hidden></div>
              <div class="profile-fields">
                <label><span>First Name</span><input type="text" id="profile-first-name" autocomplete="given-name" readonly required></label>
                <label><span>Last Name</span><input type="text" id="profile-last-name" autocomplete="family-name" readonly required></label>
                <label><span>Email Address</span><input type="email" id="profile-email" autocomplete="email" readonly aria-readonly="true"></label>
                <label><span>Phone Number</span><input type="tel" id="profile-phone" autocomplete="tel" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" readonly required></label>
                <label><span>Birthdate</span><input type="date" id="profile-birthdate" autocomplete="bday" readonly></label>
                <label>
                  <span>Gender</span>
                  <select id="profile-gender" autocomplete="sex" disabled required>
                    <option value="">Select gender</option>
                    <option value="Female">Female</option>
                    <option value="Male">Male</option>
                    <option value="Prefer not to say">Prefer not to say</option>
                  </select>
                </label>
                <div class="profile-subsection-title field-wide">Address</div>
                <label><span>House No.</span><input type="text" id="profile-house-no" autocomplete="address-line1" maxlength="50" placeholder="Enter house number" readonly></label>
                <label><span>Street</span><input type="text" id="profile-street" autocomplete="address-line2" maxlength="150" placeholder="Enter street" readonly></label>
                <label><span>Barangay</span><input type="text" id="profile-barangay" maxlength="100" placeholder="Enter barangay" readonly></label>
                <label><span>City / Municipality</span><input type="text" id="profile-city" autocomplete="address-level2" maxlength="100" placeholder="Enter city or municipality" readonly></label>
                <label><span>Province / Region</span><input type="text" id="profile-province" autocomplete="address-level1" maxlength="100" placeholder="Enter province or region" readonly></label>
                <label><span>ZIP Code</span><input type="text" id="profile-zip-code" autocomplete="postal-code" inputmode="numeric" maxlength="10" pattern="[0-9]*" placeholder="Enter ZIP code" readonly></label>
                <div class="profile-subsection-title field-wide">Emergency Contact</div>
                <label><span>Emergency Contact Name</span><input type="text" id="profile-emergency-name" placeholder="Enter contact name" readonly></label>
                <label><span>Emergency Contact Number</span><input type="tel" id="profile-emergency-number" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" placeholder="Enter contact number" readonly></label>
                <div class="member-since field-wide"><span>Member Since</span><strong id="profile-member-since">-</strong></div>
              </div>
              <div class="card-actions">
                <button type="button" class="account-btn secondary" id="edit-profile-btn">Edit Profile</button>
                <button type="submit" class="account-btn primary" id="save-profile-btn" hidden>Save Profile</button>
                <button type="button" class="account-btn text" id="cancel-profile-btn" hidden>Cancel</button>
              </div>
            </form>
          </article>
        </section>

        <section class="portal-section" id="section-appointments" data-section-panel="appointments">
          <div class="section-heading"><div><span class="card-kicker">Your Visits</span><h2>Appointment History</h2></div><span class="history-count" id="appointment-count">0</span></div>
          <div class="history-list" id="appointment-list"></div>
        </section>

        <section class="portal-section" id="section-orders" data-section-panel="orders">
          <div class="section-heading"><div><span class="card-kicker">Your Purchases</span><h2>Order History</h2></div><span class="history-count" id="order-count">0</span></div>
          <div class="history-list" id="order-list"></div>
        </section>

        <section class="portal-section" id="section-notifications" data-section-panel="notifications">
          <div class="section-heading"><div><span class="card-kicker">Account Updates</span><h2>Notifications</h2></div><button class="account-btn text" id="mark-all-read-btn" type="button">Mark All as Read</button></div>
          <div class="notification-list" id="notification-list"></div>
        </section>

        <section class="portal-section" id="section-password" data-section-panel="password">
          <div class="section-heading"><div><span class="card-kicker">Account Security</span><h2>Change Password</h2></div></div>
          <article class="account-card password-card">
            <form id="password-form">
              <label class="password-field"><span>Current Password</span><input type="password" id="current-password" autocomplete="current-password" required></label>
              <label class="password-field"><span>New Password</span><input type="password" id="new-password" autocomplete="new-password" minlength="8" required></label>
              <label class="password-field"><span>Confirm New Password</span><input type="password" id="confirm-password" autocomplete="new-password" minlength="8" required></label>
              <p class="password-hint">Use at least 8 characters with a letter and a number.</p>
              <button type="submit" class="account-btn primary">Save Changes</button>
            </form>
          </article>
        </section>
      </div>
    </div>
  </main>

  <div class="account-modal-overlay" id="cancel-modal" hidden>
    <div class="account-modal" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
      <button class="modal-close-btn" type="button" data-close-modal="cancel-modal" aria-label="Close">&times;</button>
      <span class="card-kicker">Appointment</span>
      <h2 id="cancel-modal-title">Cancel Appointment</h2>
      <p class="modal-intro" id="cancel-appointment-summary">Tell us why you need to cancel this appointment.</p>
      <form id="cancel-appointment-form">
        <input type="hidden" id="cancel-appointment-id">
        <label class="modal-field">
          <span>Cancellation Reason</span>
          <textarea id="cancellation-reason" rows="4" maxlength="500" placeholder="Enter your reason for cancelling..." required></textarea>
        </label>
        <div class="modal-actions">
          <button class="account-btn text" type="button" data-close-modal="cancel-modal">Keep Appointment</button>
          <button class="account-btn danger" type="submit">Confirm Cancellation</button>
        </div>
      </form>
    </div>
  </div>

  <div class="account-modal-overlay" id="order-modal" hidden>
    <div class="account-modal order-detail-modal" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
      <button class="modal-close-btn" type="button" data-close-modal="order-modal" aria-label="Close">&times;</button>
      <span class="card-kicker">Order Details</span>
      <h2 id="order-modal-title">Order</h2>
      <div id="order-detail-content"></div>
    </div>
  </div>

  <div class="account-modal-overlay" id="appointment-modal" hidden>
    <div class="account-modal order-detail-modal" role="dialog" aria-modal="true" aria-labelledby="appointment-modal-title">
      <button class="modal-close-btn" type="button" data-close-modal="appointment-modal" aria-label="Close">&times;</button>
      <span class="card-kicker">Appointment Details</span>
      <h2 id="appointment-modal-title">Appointment</h2>
      <div id="appointment-detail-content"></div>
    </div>
  </div>

  <div id="site-footer-root"></div>
  <script src="js/main.js?v=20260616a"></script>
  <script src="js/notifications.js?v=20260616a"></script>
  <script src="js/user.js?v=20260616a"></script>
  <script src="js/footer.js?v=20260608"></script>
</body>
</html>

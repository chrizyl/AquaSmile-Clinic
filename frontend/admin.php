<?php

session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile - Admin Dashboard</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&amp;family=DM+Sans:wght@300;400;500&amp;display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260524">
  <link rel="stylesheet" href="css/admin.css?v=20260616e">
</head>

<body class="admin-body">
  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img">
      <span>AquaSmile Admin</span>
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn pill" onclick="logout()">Logout</button>
    </div>
  </nav>

  <main class="admin-shell">
    <aside class="admin-sidebar" aria-label="Admin sections">
      <div class="admin-side-title">Admin Panel</div>
      <button class="admin-side-link active" type="button" data-view="overview" onclick="showAdminView('overview')">Overview</button>
      <button class="admin-side-link" type="button" data-view="appointments" onclick="showAdminView('appointments')">Appointments</button>
      <button class="admin-side-link" type="button" data-view="dentists" onclick="showAdminView('dentists')">Dentist Calendar</button>
      <button class="admin-side-link" type="button" data-view="orders" onclick="showAdminView('orders')">Orders</button>
      <button class="admin-side-link" type="button" data-view="catalog" onclick="showAdminView('catalog')">Products &amp; Services</button>
      <button class="admin-side-link" type="button" data-view="users" onclick="showAdminView('users')">Users</button>
      <button class="admin-side-link" type="button" data-view="notifications" onclick="showAdminView('notifications')">
        Notifications <span class="admin-notify-badge" id="admin-notify-badge">0</span>
      </button>
    </aside>

    <section class="admin-content">
      <section class="admin-view active" id="view-overview" data-admin-view="overview">
      <header class="admin-hero" id="overview">
        <div>
          <div class="section-label">AquaSmile Control Center</div>
          <h1>Admin Dashboard</h1>
          <p>Manage appointments, orders, patients, services, products, dentists, and clinic updates.</p>
        </div>
        <div class="admin-hero-actions">
          <button class="btn-secondary" type="button" onclick="adminRefresh()">Refresh</button>
          <button class="btn-primary" type="button" onclick="showAdminView('appointments')">Manage Appointments</button>
        </div>
      </header>

      <section class="admin-stats" aria-label="Dashboard summary">
        <article class="admin-stat-card">
          <span class="stat-kicker">Appointments</span>
          <strong id="stat-appointments">0</strong>
          <small id="stat-pending">0 pending today</small>
        </article>
        <article class="admin-stat-card">
          <span class="stat-kicker">Users</span>
          <strong id="stat-users">0</strong>
          <small>registered patients</small>
        </article>
        <article class="admin-stat-card">
          <span class="stat-kicker">Orders</span>
          <strong id="stat-orders">0</strong>
          <small id="stat-pending-orders">0 pending orders</small>
        </article>
        <article class="admin-stat-card">
          <span class="stat-kicker">Revenue</span>
          <strong id="stat-revenue">PHP 0</strong>
          <small>sample order total</small>
        </article>
      </section>

      <section class="admin-grid-two">
        <article class="admin-panel overview-preview-panel" id="appointments">
          <div class="panel-head">
            <div>
              <div class="section-label">Appointments</div>
              <h2>Recent Bookings</h2>
            </div>
            <button class="panel-link-btn" type="button" onclick="showAdminView('appointments')">View More Appointments</button>
          </div>
          <div class="overview-list" id="appointments-preview"></div>
        </article>

        <article class="admin-panel overview-preview-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Users</div>
              <h2>Patient Accounts</h2>
            </div>
            <button class="panel-link-btn" type="button" onclick="showAdminView('users')">View More Users</button>
          </div>
          <div class="overview-list" id="users-preview"></div>
        </article>
      </section>
      </section>

      <section class="admin-view" id="view-appointments" data-admin-view="appointments">
      <article class="admin-panel appointments-workspace">
        <div class="panel-head appointments-page-head">
          <div>
            <div class="section-label">Clinic Booking Management</div>
            <h2>Appointments</h2>
          </div>
          <label class="toggle-label">
            <input id="appointments-archive-toggle" type="checkbox" onchange="toggleArchived('appointments', this.checked)">
            Show archived
          </label>
        </div>
        <div class="appointment-filter-bar" id="appointment-filter-bar" aria-label="Appointment filters">
          <button class="appointment-filter-chip active" type="button" data-appointment-filter="all" onclick="setAppointmentFilter('all')">All</button>
          <button class="appointment-filter-chip" type="button" data-appointment-filter="pending" onclick="setAppointmentFilter('pending')">Pending</button>
          <button class="appointment-filter-chip" type="button" data-appointment-filter="confirmed" onclick="setAppointmentFilter('confirmed')">Confirmed</button>
          <button class="appointment-filter-chip" type="button" data-appointment-filter="completed" onclick="setAppointmentFilter('completed')">Completed</button>
          <button class="appointment-filter-chip" type="button" data-appointment-filter="cancelled" onclick="setAppointmentFilter('cancelled')">Cancelled</button>
          <button class="appointment-filter-chip" type="button" data-appointment-filter="archived" onclick="setAppointmentFilter('archived')">Archived</button>
        </div>
        <div class="appointments-master-detail">
          <div class="appointment-master-list" id="appointment-master-list"></div>
          <aside class="appointment-detail-panel" id="appointment-detail-panel" aria-live="polite"></aside>
        </div>
      </article>
      </section>

      <section class="admin-view" id="view-dentists" data-admin-view="dentists">
      <article class="admin-panel" id="dentists">
        <div class="panel-head">
          <div>
            <div class="section-label">Dentist Calendar</div>
            <h2>Booked Schedule for 3 Dentists</h2>
          </div>
          <div class="calendar-tools">
            <button class="mini-btn" type="button" onclick="adminChangeMonth(-1)">Prev</button>
            <span id="admin-calendar-title"></span>
            <button class="mini-btn" type="button" onclick="adminChangeMonth(1)">Next</button>
          </div>
        </div>
        <div class="dentist-calendar-grid" id="dentist-calendar-grid"></div>
      </article>

      <div id="dentist-patient-lists"></div>
      </section>

      <section class="admin-view" id="view-orders" data-admin-view="orders">
      <div id="orders">
        <article class="admin-panel orders-workspace">
          <div class="panel-head orders-page-head">
            <div>
              <div class="section-label">Order Management</div>
              <h2>Shop Orders</h2>
            </div>
            <label class="toggle-label">
              <input id="orders-archive-toggle" type="checkbox" onchange="toggleArchived('orders', this.checked)">
              Show archived
            </label>
          </div>
          <div class="orders-master-detail">
            <div class="order-master-list" id="orders-list"></div>
            <aside class="order-detail-panel" id="order-detail-panel" aria-live="polite"></aside>
          </div>
        </article>
      </div>
      </section>

      <section class="admin-view" id="view-catalog" data-admin-view="catalog">
      <section class="admin-grid-two" id="catalog">
        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Products</div>
              <h2>Product Inventory</h2>
            </div>
            <span class="admin-badge">inventory</span>
          </div>
          <div class="catalog-grid" id="products-grid-admin"></div>
        </article>

        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Services</div>
              <h2>Clinic Services</h2>
            </div>
            <span class="admin-badge">clinic care</span>
          </div>
          <div class="catalog-grid" id="services-grid-admin"></div>
        </article>
      </section>

      <section class="admin-grid-one">
        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Dentists</div>
              <h2>Dentist Records</h2>
            </div>
            <span class="admin-badge">dentists</span>
          </div>
          <div class="dentist-list" id="dentist-list"></div>
        </article>
      </section>
      </section>

      <section class="admin-view" id="view-users" data-admin-view="users">
      <article class="admin-panel">
        <div class="panel-head">
          <div>
            <div class="section-label">Patient Accounts</div>
            <h2>Users</h2>
          </div>
          <span class="admin-badge">registered accounts</span>
        </div>
        <div class="admin-users-grid" id="users-manage-list"></div>
      </article>
      </section>

      <section class="admin-view" id="view-notifications" data-admin-view="notifications">
      <article class="admin-panel">
        <div class="panel-head">
          <div>
            <div class="section-label">Admin Notifications</div>
            <h2>Appointment and Order Alerts</h2>
          </div>
          <span class="admin-badge">latest updates</span>
        </div>
        <div class="admin-activity-feed" id="notifications-feed"></div>
      </article>
      </section>

    </section>
  </main>

  <script src="js/main.js?v=20260614b"></script>
  <script src="js/admin.js?v=20260616g"></script>
</body>

</html>

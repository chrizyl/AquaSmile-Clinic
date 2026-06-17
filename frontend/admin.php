<?php

require_once 'includes/session-init.php';
require_once 'includes/admin-check.php';
no_cache_headers();

requireAdminPage();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg">
  <title>AquaSmile - Admin Dashboard</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&amp;family=DM+Sans:wght@300;400;500&amp;display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260524">
  <link rel="stylesheet" href="css/admin.css?v=20260618r">
</head>

<body class="admin-body">
  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img">
      <span>AquaSmile</span>
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
      <button class="admin-side-link" type="button" data-view="coupons" onclick="showAdminView('coupons')">Coupons</button>
      <button class="admin-side-link" type="button" data-view="catalog" onclick="showAdminView('catalog')">Clinic Management</button>
      <button class="admin-side-link" type="button" data-view="users" onclick="showAdminView('users')">Users</button>
      <button class="admin-side-link" type="button" data-view="notifications" onclick="showAdminView('notifications')">
        Notifications <span class="admin-notify-badge" id="admin-notify-badge">0</span>
      </button>
      <button class="admin-side-link" type="button" data-view="feedback" onclick="showAdminView('feedback')">Feedback</button>
      <button class="admin-side-link" type="button" data-view="reports" onclick="showAdminView('reports')">Crystal Reports</button>
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
          <small>order total</small>
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

      <section class="admin-view" id="view-reports" data-admin-view="reports">
      <header class="admin-hero report-hero">
        <div>
          <div class="section-label">Crystal Report Panel</div>
          <h1>Clinic Summary</h1>
          <p>A clean overview of patients, appointments, shop activity, catalog content, coupons, feedback, and recent website operations.</p>
        </div>
        <div class="admin-hero-actions">
          <button class="btn-secondary" type="button" onclick="adminRefresh()">Refresh Report</button>
          <button class="btn-primary" type="button" onclick="window.print()">Print Report</button>
        </div>
      </header>

      <section class="report-kpi-grid" id="report-kpi-grid"></section>

      <section class="report-layout">
        <article class="admin-panel report-panel report-wide">
          <div class="panel-head">
            <div>
              <div class="section-label">Performance</div>
              <h2>Monthly Order Revenue</h2>
            </div>
            <span class="admin-badge" id="report-revenue-range">Latest months</span>
          </div>
          <div class="report-chart" id="report-revenue-chart"></div>
        </article>

        <article class="admin-panel report-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Clinic Flow</div>
              <h2>Appointment Status</h2>
            </div>
          </div>
          <div class="report-bars" id="report-appointment-bars"></div>
        </article>

        <article class="admin-panel report-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Shop</div>
              <h2>Order Status</h2>
            </div>
          </div>
          <div class="report-bars" id="report-order-bars"></div>
        </article>

        <article class="admin-panel report-panel report-wide">
          <div class="panel-head">
            <div>
              <div class="section-label">Website Content</div>
              <h2>Content Inventory</h2>
            </div>
          </div>
          <div class="report-content-grid" id="report-content-grid"></div>
        </article>

        <article class="admin-panel report-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Feedback</div>
              <h2>Patient Ratings</h2>
            </div>
          </div>
          <div class="report-feedback" id="report-feedback"></div>
        </article>

        <article class="admin-panel report-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Coupons</div>
              <h2>Claim Activity</h2>
            </div>
          </div>
          <div class="report-coupon-list" id="report-coupon-list"></div>
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

      <section class="admin-view" id="view-coupons" data-admin-view="coupons">
      <article class="admin-panel coupons-admin-panel">
        <div class="panel-head">
          <div>
            <div class="section-label">Coupons Management</div>
            <h2>Product and Appointment Coupons</h2>
          </div>
          <button class="mini-btn add-btn" type="button" onclick="openAddCouponModal()">+ Add Coupon</button>
        </div>
        <div class="coupon-admin-grid" id="admin-coupons-grid"></div>
      </article>
      </section>

      <section class="admin-view" id="view-catalog" data-admin-view="catalog">
      <section class="admin-grid-one catalog-accordion" id="catalog">
        <article class="admin-panel catalog-collapsible is-collapsed" data-catalog-panel="products">
          <div class="panel-head">
            <div>
              <div class="section-label">Products</div>
              <h2>Product Inventory</h2>
              <p class="catalog-panel-subtitle">Manage oral care products</p>
            </div>
            <button class="catalog-collapse-toggle" type="button" aria-expanded="false" aria-controls="products-grid-admin" onclick="toggleCatalogPanel(this)">
              <span>Show products</span>
              <span class="catalog-chevron" aria-hidden="true"></span>
            </button>
          </div>
          <div class="catalog-grid" id="products-grid-admin"></div>
        </article>

        <article class="admin-panel catalog-collapsible is-collapsed" data-catalog-panel="services">
          <div class="panel-head">
            <div>
              <div class="section-label">Services</div>
              <h2>Clinic Services</h2>
              <p class="catalog-panel-subtitle">Manage available dental treatments</p>
            </div>
            <button class="catalog-collapse-toggle" type="button" aria-expanded="false" aria-controls="services-grid-admin" onclick="toggleCatalogPanel(this)">
              <span>Show services</span>
              <span class="catalog-chevron" aria-hidden="true"></span>
            </button>
          </div>
          <div class="catalog-grid" id="services-grid-admin"></div>
        </article>

        <article class="admin-panel catalog-collapsible is-collapsed" data-catalog-panel="dentists">
          <div class="panel-head">
            <div>
              <div class="section-label">Dentists</div>
              <h2>Dentist Directory</h2>
              <p class="catalog-panel-subtitle">Manage clinic specialists</p>
            </div>
            <button class="catalog-collapse-toggle" type="button" aria-expanded="false" aria-controls="dentist-list" onclick="toggleCatalogPanel(this)">
              <span>Show dentists</span>
              <span class="catalog-chevron" aria-hidden="true"></span>
            </button>
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
          <button class="admin-badge admin-mark-read-btn" type="button" onclick="markAllAdminNotificationsRead()">Mark All as Read</button>
        </div>
        <div class="admin-activity-feed" id="notifications-feed"></div>
      </article>
      </section>

      <section class="admin-view" id="view-feedback" data-admin-view="feedback">
      <article class="admin-panel feedback-panel">
        <div class="panel-head">
          <div>
            <div class="section-label">Patient Feedback</div>
            <h2>Reviews and Ratings</h2>
          </div>
          <span class="admin-badge">latest responses</span>
        </div>
        <section class="feedback-stats" aria-label="Feedback summary">
          <article class="feedback-stat-card average">
            <span>Average Rating</span>
            <strong id="feedback-average-rating">0.0</strong>
            <small id="feedback-average-subtitle">No ratings yet</small>
          </article>
          <article class="feedback-stat-card appointment">
            <span>Appointment Reviews</span>
            <strong id="feedback-appointment-count">0</strong>
            <small>booking experience</small>
          </article>
          <article class="feedback-stat-card order">
            <span>Order Reviews</span>
            <strong id="feedback-order-count">0</strong>
            <small>shop experience</small>
          </article>
          <article class="feedback-stat-card total">
            <span>Total Feedback</span>
            <strong id="feedback-total-count">0</strong>
            <small>submitted reviews</small>
          </article>
        </section>
        <div class="feedback-list-head">
          <div>
            <div class="section-label">Recent Feedback</div>
            <h3>What Patients Are Saying</h3>
          </div>
        </div>
        <div class="feedback-list" id="feedback-list"></div>
      </article>
      </section>

    </section>
  </main>

  <script src="js/main.js?v=20260616a"></script>
  <script src="js/admin.js?v=20260618r"></script>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile - Admin Dashboard</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260524">
  <link rel="stylesheet" href="css/admin.css?v=20260524">
</head>

<body class="admin-body">
  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img">
      <span>AquaSmile Admin</span>
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Site</button>
    </div>
  </nav>

  <main class="admin-shell">
    <aside class="admin-sidebar" aria-label="Admin sections">
      <div class="admin-side-title">Admin Panel</div>
      <button class="admin-side-link active" type="button" data-view="overview" onclick="showAdminView('overview')">Overview</button>
      <button class="admin-side-link" type="button" data-view="appointments" onclick="showAdminView('appointments')">Appointments</button>
      <button class="admin-side-link" type="button" data-view="dentists" onclick="showAdminView('dentists')">Dentist Calendar</button>
      <button class="admin-side-link" type="button" data-view="orders" onclick="showAdminView('orders')">Orders</button>
      <button class="admin-side-link" type="button" data-view="catalog" onclick="showAdminView('catalog')">Products & Services</button>
      <button class="admin-side-link" type="button" data-view="notifications" onclick="showAdminView('notifications')">
        Notifications <span class="admin-notify-badge" id="admin-notify-badge">0</span>
      </button>
      <button class="admin-side-link" type="button" data-view="database" onclick="showAdminView('database')">Database Tables</button>
    </aside>

    <section class="admin-content">
      <section class="admin-view active" id="view-overview" data-admin-view="overview">
      <header class="admin-hero" id="overview">
        <div>
          <div class="section-label">AquaSmile Control Center</div>
          <h1>Admin Dashboard</h1>
          <p>Appointments, users, cart items, dentists, orders, order items, products, and services.</p>
        </div>
        <div class="admin-hero-actions">
          <button class="btn-secondary" type="button" onclick="adminRefresh()">Refresh</button>
          <button class="btn-primary" type="button" onclick="showAdminView('database')">View Schema</button>
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
          <span class="stat-kicker">Cart Items</span>
          <strong id="stat-cart">0</strong>
          <small>active shop items</small>
        </article>
        <article class="admin-stat-card">
          <span class="stat-kicker">Revenue</span>
          <strong id="stat-revenue">PHP 0</strong>
          <small>sample order total</small>
        </article>
      </section>

      <section class="admin-grid-two">
        <article class="admin-panel" id="appointments">
          <div class="panel-head">
            <div>
              <div class="section-label">Appointments</div>
              <h2>Recent Bookings</h2>
            </div>
            <span class="admin-badge">appointments</span>
          </div>
          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Service</th>
                  <th>Dentist</th>
                  <th>Schedule</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="appointments-table"></tbody>
            </table>
          </div>
        </article>

        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Users</div>
              <h2>Patient Accounts</h2>
            </div>
            <span class="admin-badge">users</span>
          </div>
          <div class="table-wrap">
            <table class="admin-table compact">
              <thead>
                <tr>
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                </tr>
              </thead>
              <tbody id="users-table"></tbody>
            </table>
          </div>
        </article>
      </section>
      </section>

      <section class="admin-view" id="view-appointments" data-admin-view="appointments">
      <article class="admin-panel">
        <div class="panel-head">
          <div>
            <div class="section-label">Appointment Approval</div>
            <h2>Manage Patient Bookings</h2>
          </div>
          <span class="admin-badge">pending / confirmed / cancelled</span>
        </div>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Service</th>
                <th>Dentist</th>
                <th>Schedule</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="appointments-manage-table"></tbody>
          </table>
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
      <div class="admin-grid-two" id="orders">
        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Orders</div>
              <h2>Shop Orders</h2>
            </div>
            <span class="admin-badge">orders</span>
          </div>
          <div class="table-wrap">
            <table class="admin-table compact">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Total</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="orders-table"></tbody>
            </table>
          </div>
        </article>

        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Order Items</div>
              <h2>Line Items</h2>
            </div>
            <span class="admin-badge">order_items</span>
          </div>
          <div class="table-wrap">
            <table class="admin-table compact">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Order</th>
                  <th>Qty</th>
                  <th>Price</th>
                </tr>
              </thead>
              <tbody id="order-items-table"></tbody>
            </table>
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
            <span class="admin-badge">quantity only</span>
          </div>
          <div class="catalog-grid" id="products-grid-admin"></div>
        </article>

        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Services</div>
              <h2>Clinic Services</h2>
            </div>
            <span class="admin-badge">quantity only</span>
          </div>
          <div class="catalog-grid" id="services-grid-admin"></div>
        </article>
      </section>

      <section class="admin-grid-two">
        <article class="admin-panel">
          <div class="panel-head">
            <div>
              <div class="section-label">Cart Items</div>
              <h2>Current Cart Preview</h2>
            </div>
            <span class="admin-badge">cart_items</span>
          </div>
          <div class="table-wrap">
            <table class="admin-table compact">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Unit</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody id="cart-table"></tbody>
            </table>
          </div>
        </article>

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

      <section class="admin-view" id="view-notifications" data-admin-view="notifications">
      <article class="admin-panel">
        <div class="panel-head">
          <div>
            <div class="section-label">Admin Notifications</div>
            <h2>Client Cancellation Alerts</h2>
          </div>
          <span class="admin-badge">database</span>
        </div>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Appointment</th>
                <th>Message</th>
                <th>Date Sent</th>
              </tr>
            </thead>
            <tbody id="notifications-table"></tbody>
          </table>
        </div>
      </article>
      </section>

      <section class="admin-view" id="view-database" data-admin-view="database">
      <article class="admin-panel" id="database">
        <div class="panel-head">
          <div>
            <div class="section-label">Database Plan</div>
            <h2>XAMPP / MySQL Tables to Connect Later</h2>
          </div>
          <span class="admin-badge">schema-ready UI</span>
        </div>
        <div class="schema-grid">
          <div class="schema-card"><strong>appointments</strong><span>id, user_id, service_id, dentist_id, date, time, notes, status, cancellation_reason</span></div>
          <div class="schema-card"><strong>users</strong><span>id, name, email, contact, password, role, created_at</span></div>
          <div class="schema-card"><strong>cart_items</strong><span>id, user_id, product_id, quantity, created_at</span></div>
          <div class="schema-card"><strong>dentists</strong><span>id, name, credentials, specialization, photo, status</span></div>
          <div class="schema-card"><strong>orders</strong><span>id, user_id, customer_name, total, payment_method, status</span></div>
          <div class="schema-card"><strong>order_items</strong><span>id, order_id, product_id, quantity, unit_price</span></div>
          <div class="schema-card"><strong>products</strong><span>id, name, description, price, image, stock, category</span></div>
          <div class="schema-card"><strong>services</strong><span>id, name, description, price, image, daily_slots, category</span></div>
          <div class="schema-card"><strong>notifications</strong><span>id, user_id, appointment_id, audience, message, is_read, created_at</span></div>
          <div class="schema-card"><strong>inventory_logs</strong><span>id, item_type, item_id, quantity, action, admin_id</span></div>
        </div>
      </article>
      </section>
    </section>
  </main>

  <script src="js/main.js?v=20260524"></script>
  <script src="js/admin.js?v=20260524"></script>
</body>

</html>

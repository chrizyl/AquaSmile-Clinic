<?php
require_once 'includes/session-init.php';
include 'includes/admin-check.php';
require_once 'includes/navbar-auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile — Dental Services</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260523">
  <link rel="stylesheet" href="css/services.css?v=20260523">
  <link rel="stylesheet" href="css/notifications.css?v=20260523">
  <link rel="stylesheet" href="css/auth-nav.css?v=20260614">
  <link rel="stylesheet" href="css/admin-restrictions.css">
</head>
<body>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

  <!-- NAV -->
  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile dental clinic logo with a smiling tooth icon and the text AquaSmile, evoking a friendly and approachable tone in the website navigation" class="nav-logo-img">
      AquaSmile
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn active" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn <?php echo getAdminClass(); ?>" onclick="<?php if (!isAdmin()): ?>window.location.href='products.php'<?php endif; ?>" <?php echo getAdminDisabled(); ?>>Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" <?php echo nav_is_patient() ? '' : 'style="display:none"'; ?>>Book Appointment</button>
      <?php render_nav_auth(); ?>
    </div>
  </nav>

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-header-sub">What We Offer</div>
    <h2>Our Dental Services</h2>
    <div class="section-divider"></div>
  </div>

  <!-- FILTER TABS -->
  <div class="section" style="padding-bottom: 0;">
    <div class="service-filters" id="service-filters"></div>
  </div>

  <!-- SERVICES GRID -->
  <div class="section">
    <div class="services-grid" id="services-grid"></div>
  </div>

  <!-- SERVICE DETAIL MODAL -->
  <div class="modal-overlay" id="modal-overlay" onclick="closeServiceModal()">
    <div class="modal-card" onclick="event.stopPropagation()">
      <button class="modal-close" onclick="closeServiceModal()">&#10005;</button>
      <div class="modal-body" id="modal-body"></div>
    </div>
  </div>

  <!-- COST ESTIMATOR -->
  <div class="section" id="cost-estimator">
    <div class="estimator-card">
      <div class="estimator-header">
        <div class="estimator-label">Treatment Planner</div>
        <div class="estimator-title">Multi-Session Cost Estimator</div>
        <div class="estimator-sub">Each additional session receives a compounding 5% discount. Select a service and the number of sessions to see your itemised breakdown.</div>
      </div>

      <div class="estimator-controls">
        <div class="estimator-field">
          <label class="estimator-field-label" for="estimator-service">Service</label>
          <select class="estimator-select" id="estimator-service"></select>
        </div>
        <div class="estimator-field">
          <label class="estimator-field-label" for="estimator-sessions">
            Number of Sessions — <span id="estimator-sessions-val">3</span>
          </label>
          <input
            class="estimator-range"
            type="range"
            id="estimator-sessions"
            min="1" max="10" value="3" step="1"
          >
          <div class="estimator-range-ticks">
            <span>1</span><span>2</span><span>3</span><span>4</span><span>5</span>
            <span>6</span><span>7</span><span>8</span><span>9</span><span>10</span>
          </div>
        </div>
      </div>

      <div class="estimator-table-wrap">
        <table class="estimator-table">
          <thead>
            <tr>
              <th class="estimator-th">Session</th>
              <th class="estimator-th estimator-td-center">Discount</th>
              <th class="estimator-th estimator-td-right">Price</th>
            </tr>
          </thead>
          <tbody id="estimator-tbody"></tbody>
          <tfoot>
            <tr class="estimator-total-row">
              <td class="estimator-td estimator-total-label" colspan="2">Total Estimated Cost</td>
              <td class="estimator-td estimator-td-right estimator-total-value" id="estimator-total"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="estimator-note">
        Prices shown are estimates based on starting rates. Final costs are confirmed during your consultation.
      </div>
    </div>
  </div>

  <!-- CTA BANNER -->
  <div class="cta-banner">
    <div class="cta-content">
      <div class="cta-title">Not sure which service you need?</div>
      <p class="cta-sub">Book a general consultation and let our dentists recommend the right treatment for you.</p>
      <button class="btn-primary <?php echo getAdminClass(); ?>" onclick="<?php if (!isAdmin()): ?>requireBooking()<?php endif; ?>" <?php echo getAdminDisabled(); ?>>Book a Consultation</button>
    </div>
    <div class="cta-img">
      <img src="images/dental consulation.png" alt="Dental Services">
    </div>
  </div>

  <script src="js/main.js?v=20260614b"></script>
  <script src="js/services.js?v=20260523"></script>
  <script src="js/notifications.js?v=20260614b"></script>
</script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260608"></script>
</body>
</html>
</body>
</html>

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
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg">
  <title>AquaSmile - Terms and Conditions</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260618d">
  <link rel="stylesheet" href="css/notifications.css?v=20260616a">
  <link rel="stylesheet" href="css/auth-nav.css?v=20260614">
  <link rel="stylesheet" href="css/admin-restrictions.css">
</head>
<body>
  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img">
      <span>AquaSmile</span>
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn <?php echo getAdminClass(); ?>" onclick="<?php if (!isAdmin()): ?>window.location.href='products.php'<?php endif; ?>" <?php echo getAdminDisabled(); ?>>Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" <?php echo nav_is_patient() ? '' : 'style="display:none"'; ?>>Book Appointment</button>
      <?php render_nav_auth(); ?>
    </div>
  </nav>

  <div class="page-header legal-page-header">
    <div class="page-header-sub">AquaSmile Policies</div>
    <h2>Terms and Conditions</h2>
    <div class="section-divider"></div>
  </div>

  <main class="section legal-section">
    <article class="legal-card">
      <p class="legal-updated">Last updated: June 18, 2026</p>
      <p>Welcome to AquaSmile Dental Clinic. By using our website, booking an appointment, purchasing products, or claiming promos, you agree to the terms below.</p>

      <section class="legal-block">
        <h3>Appointments</h3>
        <p>Patients are responsible for providing accurate booking information. Appointment requests may be reviewed, confirmed, cancelled, or rescheduled by the clinic depending on dentist availability and clinic operations.</p>
      </section>

      <section class="legal-block">
        <h3>Orders and Payments</h3>
        <p>Product prices, availability, and order status may change based on stock and clinic processing. Please review your delivery and contact details before submitting an order.</p>
      </section>

      <section class="legal-block">
        <h3>Promos and Coupons</h3>
        <p>Coupons and promo codes are subject to availability, validity dates, and account restrictions. Claimed coupons may only be used by the account that claimed them, unless stated otherwise.</p>
      </section>

      <section class="legal-block">
        <h3>Website Use</h3>
        <p>You agree not to misuse the website, submit false information, attempt unauthorized access, or interfere with the system. Admin tools are restricted to authorized AquaSmile staff.</p>
      </section>

      <section class="legal-block">
        <h3>Clinic Guidance</h3>
        <p>Information on this website is for general clinic and service guidance only. A dentist consultation is required for diagnosis, treatment plans, and medical decisions.</p>
      </section>

      <section class="legal-block">
        <h3>Contact</h3>
        <p>For questions about these terms, contact us at <a href="mailto:aquasmileclinic@gmail.com">aquasmileclinic@gmail.com</a>.</p>
      </section>
    </article>
  </main>

  <script src="js/main.js?v=20260618c"></script>
  <script src="js/notifications.js?v=20260615"></script>
  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260618d"></script>
</body>
</html>

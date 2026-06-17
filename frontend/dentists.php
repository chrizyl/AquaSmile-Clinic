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
  <title>AquaSmile — Our Dentists</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260523">
  <link rel="stylesheet" href="css/dentist.css?v=20260618a">
  <link rel="stylesheet" href="css/notifications.css?v=20260616a">
  <link rel="stylesheet" href="css/auth-nav.css?v=20260614">
  <link rel="stylesheet" href="css/admin-restrictions.css">
</head>
<body>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

  <!-- NAV -->
  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img">
      AquaSmile
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn active" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn <?php echo getAdminClass(); ?>" onclick="<?php if (!isAdmin()): ?>window.location.href='products.php'<?php endif; ?>" <?php echo getAdminDisabled(); ?>>Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" <?php echo nav_is_patient() ? '' : 'style="display:none"'; ?>>Book Appointment</button>
      <?php render_nav_auth(); ?>
    </div>
  </nav>

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-header-sub">Our Specialists</div>
    <h2>Meet the AquaSmile Team</h2>
    <div class="section-divider"></div>
  </div>

  <!-- INTRO -->
  <div class="section" style="text-align:center; padding-bottom: 0;">
    <p class="section-sub" style="max-width:600px; margin: 0 auto;">
      Our team of experienced dental professionals is committed to delivering
      exceptional care in a comfortable, welcoming environment.
    </p>
  </div>

  <!-- DENTIST CARDS -->
  <div class="section">
    <div class="dentist-grid" id="dentist-grid"></div>
  </div>

  <!-- DENTIST DETAIL MODAL -->
  <div class="modal-overlay" id="modal-overlay" onclick="closeModal()">
    <div class="modal-card" onclick="event.stopPropagation()">
      <button class="modal-close" onclick="closeModal()">&#10005;</button>
      <div class="modal-body" id="modal-body"></div>
    </div>
  </div>

  <!-- CTA BANNER -->
  <div class="cta-banner">
    <div class="cta-content">
      <div class="cta-title">Ready to book your visit?</div>
      <p class="cta-sub">Choose your preferred dentist and schedule an appointment at your convenience.</p>
      <button class="btn-primary <?php echo getAdminClass(); ?>" onclick="<?php if (!isAdmin()): ?>requireBooking()<?php endif; ?>" <?php echo getAdminDisabled(); ?>>Book an Appointment</button>
    </div>
    <div class="cta-img">
      <img src="images/dental clinic team.png" alt="AquaSmile Team">
    </div>
  </div>

  <script src="js/main.js?v=20260616a"></script>
  <script src="js/dentist.js?v=20260618a"></script>
  <script src="js/notifications.js?v=20260615"></script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260618d"></script>
</body>
</html>

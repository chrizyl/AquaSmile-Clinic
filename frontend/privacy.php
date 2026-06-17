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
  <title>AquaSmile - Privacy Policy</title>
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
    <h2>Privacy Policy</h2>
    <div class="section-divider"></div>
  </div>

  <main class="section legal-section">
    <article class="legal-card">
      <p class="legal-updated">Last updated: June 18, 2026</p>
      <p>AquaSmile Dental Clinic respects your privacy. This policy explains how we collect, use, and protect information when you use our website and clinic services.</p>

      <section class="legal-block">
        <h3>Information We Collect</h3>
        <p>We may collect your name, email address, phone number, address, appointment details, order details, feedback, and account information when you register, book, purchase, or contact us.</p>
      </section>

      <section class="legal-block">
        <h3>How We Use Information</h3>
        <p>Your information is used to manage appointments, process orders, send clinic updates, improve services, prevent duplicate coupon claims, and support your patient account.</p>
      </section>

      <section class="legal-block">
        <h3>Cookies</h3>
        <p>We use cookies and local browser storage to keep users logged in, remember preferences, manage carts, and store your cookie consent choice.</p>
      </section>

      <section class="legal-block">
        <h3>Data Protection</h3>
        <p>We use reasonable safeguards to protect personal information. Access to admin tools and patient records should be limited to authorized clinic staff.</p>
      </section>

      <section class="legal-block">
        <h3>Sharing Information</h3>
        <p>We do not sell your personal information. Information may be shared only when needed to provide clinic services, comply with legal requirements, or protect the website and users.</p>
      </section>

      <section class="legal-block">
        <h3>Your Choices</h3>
        <p>You may update your account details, decline non-essential cookies when prompted, or contact us for privacy-related requests at <a href="mailto:aquasmileclinic@gmail.com">aquasmileclinic@gmail.com</a>.</p>
      </section>
    </article>
  </main>

  <script src="js/main.js?v=20260618c"></script>
  <script src="js/notifications.js?v=20260615"></script>
  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260618d"></script>
</body>
</html>

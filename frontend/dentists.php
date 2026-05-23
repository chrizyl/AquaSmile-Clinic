<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile — Our Dentists</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260523">
  <link rel="stylesheet" href="css/dentist.css?v=20260523">
  <link rel="stylesheet" href="css/notifications.css?v=20260523">
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
      <button class="nav-btn" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" style="display:none">Book Appointment</button>
      <div id="nav-user-info" style="display:none"></div>
      <button class="nav-btn pill" id="nav-login-btn" onclick="window.location.href='login.php'">Log In</button>
      <button class="nav-btn pill-aqua" id="nav-logout-btn" onclick="logout()" style="display:none">Log Out</button>
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
      <button class="btn-primary" onclick="requireBooking()">Book an Appointment</button>
    </div>
    <div class="cta-img">
      <img src="images/dental clinic team.png" alt="AquaSmile Team">
    </div>
  </div>

  <script src="js/main.js?v=20260523"></script>
  <script src="js/dentist.js?v=20260523"></script>
  <script src="js/notifications.js?v=20260523"></script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260523"></script>
</body>
</html>

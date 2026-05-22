<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile Dental Clinic</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

  <!-- NAV -->
  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img" />
      <span>AquaSmile</span>
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn active" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" style="display:none">Book
        Appointment</button>
      <div id="nav-user-info" style="display:none"></div>
      <button class="nav-btn pill" id="nav-login-btn" onclick="window.location.href='login.php'">Log In</button>
      <button class="nav-btn pill-aqua" id="nav-logout-btn" onclick="logout()" style="display:none">Log Out</button>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg-blob b1"></div>
    <div class="hero-bg-blob b2"></div>
    <div class="hero-content">
      <div class="hero-badge">Trusted Dental Care in the Heart of the City</div>
      <h1>Your smile,<br><em>perfectly</em> cared for.</h1>
      <p>At AquaSmile, we blend clinical excellence with a warm, welcoming environment — because great dental care
        should feel as good as it looks.</p>
      <div class="hero-btns">
        <button class="btn-primary" onclick="requireAuth('booking.php')">Book an Appointment</button>
        <button class="btn-secondary" onclick="window.location.href='dentists.php'">Meet Our Dentists</button>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- DAILY DENTAL TIP (Random Number Condition — Lesson 2) -->
  <div class="section" style="padding-top: 48px; padding-bottom: 48px;">
    <div id="daily-tip-widget"></div>
  </div>

  <div class="divider"></div>

  <!-- DENTIST PREVIEW -->
  <div class="section">
    <div class="section-label">Our Team</div>
    <div class="section-title">Three dedicated experts,<br>one goal — your smile.</div>
    <p class="section-sub">Each of our dentists brings a unique specialization, ensuring you receive the highest
      standard of care for every procedure.</p>
    <div class="grid-3" id="home-dentist-grid"></div>
  </div>

  <div class="divider"></div>

  <!-- QUICK SERVICES -->
  <div class="section">
    <div class="section-label">Services</div>
    <div class="section-title">Comprehensive care<br>from consult to completion.</div>
    <p class="section-sub">Whether you're here for a routine cleaning or a complete smile transformation, we have you
      covered.</p>
    <!-- Service Category Filter (Switch/Conditional Logic) -->
    <div class="tabs" id="service-filter-tabs">
      <button class="tab-btn active" onclick="filterServices('all')">All</button>
      <button class="tab-btn" onclick="filterServices('preventive')">Preventive</button>
      <button class="tab-btn" onclick="filterServices('cosmetic')">Cosmetic</button>
      <button class="tab-btn" onclick="filterServices('restorative')">Restorative</button>
    </div>
    <div class="grid-4" id="home-services-grid"></div>
  </div>

  <div class="divider"></div>

  <!-- PROMO & DEALS SECTION -->
  <div class="section" id="promos">
    <div class="section-label">Limited-Time Offers</div>
    <div class="section-title">Smile more,<br>spend less.</div>
    <p class="section-sub">Exclusive promos updated monthly — grab a deal before time runs out.</p>

    <!-- Countdown Timer -->
    <div class="promo-countdown-wrap">
      <span class="promo-countdown-label">Promos end in:</span>
      <div class="promo-countdown" id="promo-countdown">
        <div class="countdown-block"><span id="cd-days">00</span><small>days</small></div>
        <div class="countdown-sep">:</div>
        <div class="countdown-block"><span id="cd-hours">00</span><small>hrs</small></div>
        <div class="countdown-sep">:</div>
        <div class="countdown-block"><span id="cd-mins">00</span><small>min</small></div>
        <div class="countdown-sep">:</div>
        <div class="countdown-block"><span id="cd-secs">00</span><small>sec</small></div>
      </div>
    </div>

    <!-- Deals Grid -->
    <div class="deals-grid" id="deals-grid"></div>

    <!-- Promo Code Box -->
    <div class="promo-code-box">
      <div class="promo-code-inner">
        <div>
          <div class="promo-code-title">Got a promo code?</div>
          <div class="promo-code-sub">Enter it at booking to unlock exclusive discounts on your appointment.</div>
        </div>
        <div class="promo-code-input-row">
          <input class="form-input promo-input" type="text" id="promo-code-input" placeholder="e.g. SMILE20" maxlength="12" />
          <button class="btn-primary" onclick="applyPromoCode()">Apply</button>
        </div>
        <div id="promo-code-result"></div>
      </div>
    </div>
  </div>

  <!-- DATA TYPES INFO STRIP (Lesson 2 — rendered via JS, styled as a trust bar) -->
  <div class="clinic-stats-strip" id="clinic-stats-strip"></div>

  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js"></script>
</body>

</html>
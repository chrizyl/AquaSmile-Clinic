<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile — Book an Appointment</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/booking.css">

  <style>
    /* ══════════════════════════════════════
       STEP ICON SVGs (replaces img icons)
    ══════════════════════════════════════ */
    .step-num {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .step-svg {
      width: 28px;
      height: 28px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.7;
      stroke-linecap: round;
      stroke-linejoin: round;
      transition: stroke 0.2s;
    }

    .label-icon-svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
      vertical-align: middle;
      margin-right: 6px;
      flex-shrink: 0;
    }

    /* ══════════════════════════════════════
       SUCCESS POPUP
    ══════════════════════════════════════ */
    .bk-popup-overlay {
      position: fixed;
      inset: 0;
      background: rgba(44,62,56,0.48);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1200;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }

    .bk-popup-overlay.show {
      opacity: 1;
      pointer-events: all;
    }

    .bk-popup-box {
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(120,154,153,0.18);
      border-radius: 20px;
      padding: 44px 40px 36px;
      max-width: 420px;
      width: 92%;
      text-align: center;
      box-shadow: 0 24px 64px rgba(44,62,56,0.16);
      transform: scale(0.9) translateY(24px);
      transition: transform 0.38s cubic-bezier(0.34,1.56,0.64,1);
    }

    .bk-popup-overlay.show .bk-popup-box {
      transform: scale(1) translateY(0);
    }

    /* Check icon */
    .bk-popup-check {
      width: 68px;
      height: 68px;
      border-radius: 50%;
      background: rgba(120,154,153,0.13);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }

    .bk-popup-check svg {
      width: 34px;
      height: 34px;
      stroke: #4e7170;
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .bk-popup-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.75rem;
      font-weight: 500;
      color: #2c3e38;
      margin-bottom: 10px;
    }

    .bk-popup-msg {
      font-size: 0.88rem;
      color: #5a7068;
      font-weight: 300;
      line-height: 1.75;
      margin-bottom: 28px;
    }

    .bk-popup-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .bk-btn-rate {
      padding: 13px 20px;
      border: none;
      border-radius: 12px;
      background: #789a99;
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.92rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.22s, transform 0.18s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .bk-btn-rate:hover {
      background: #4e7170;
      transform: translateY(-1px);
    }

    .bk-btn-rate svg {
      width: 16px;
      height: 16px;
      fill: #fff;
      stroke: none;
    }

    .bk-btn-skip {
      padding: 11px 20px;
      border: 1.5px solid rgba(120,154,153,0.3);
      border-radius: 12px;
      background: transparent;
      color: #5a7068;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.87rem;
      font-weight: 400;
      cursor: pointer;
      transition: all 0.2s;
    }

    .bk-btn-skip:hover {
      background: rgba(120,154,153,0.07);
      border-color: #789a99;
    }

    /* ══════════════════════════════════════
       RATING POPUP
    ══════════════════════════════════════ */
    .bk-rating-box .bk-popup-title { font-size: 1.5rem; }

    .rating-stars {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 4px 0 20px;
    }

    .star-btn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      transition: transform 0.15s;
    }

    .star-btn:hover { transform: scale(1.18); }

    .star-btn svg {
      width: 36px;
      height: 36px;
      fill: #e0ddd8;
      stroke: none;
      transition: fill 0.18s;
    }

    .star-btn.active svg,
    .star-btn.hovered svg { fill: #e8c9b0; }

    .star-btn.active svg { fill: #d4a882; }

    .rating-labels {
      display: flex;
      justify-content: space-between;
      font-size: 0.72rem;
      color: #8fa89e;
      font-weight: 300;
      margin-top: -14px;
      margin-bottom: 22px;
      padding: 0 4px;
    }

    .rating-aspects {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      margin-bottom: 18px;
    }

    .aspect-chip {
      padding: 6px 14px;
      border-radius: 99px;
      border: 1.5px solid rgba(120,154,153,0.25);
      background: rgba(255,255,255,0.5);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.78rem;
      color: #5a7068;
      cursor: pointer;
      transition: all 0.18s;
      user-select: none;
    }

    .aspect-chip:hover {
      border-color: #789a99;
      background: rgba(120,154,153,0.08);
    }

    .aspect-chip.selected {
      background: rgba(120,154,153,0.15);
      border-color: #789a99;
      color: #2c3e38;
      font-weight: 500;
    }

    .rating-textarea {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid rgba(120,154,153,0.25);
      border-radius: 10px;
      background: rgba(255,255,255,0.6);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.86rem;
      color: #2c3e38;
      resize: vertical;
      min-height: 80px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      margin-bottom: 18px;
    }

    .rating-textarea::placeholder { color: #8fa89e; }

    .rating-textarea:focus {
      border-color: #789a99;
      box-shadow: 0 0 0 3px rgba(120,154,153,0.12);
    }

    .bk-btn-submit {
      width: 100%;
      padding: 13px 20px;
      border: none;
      border-radius: 12px;
      background: #789a99;
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.92rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.22s, transform 0.18s;
      margin-bottom: 10px;
    }

    .bk-btn-submit:hover {
      background: #4e7170;
      transform: translateY(-1px);
    }

    .bk-btn-submit:disabled {
      background: #a8c4c3;
      cursor: not-allowed;
      transform: none;
    }

    .rating-skip-link {
      font-size: 0.79rem;
      color: #8fa89e;
      cursor: pointer;
      text-decoration: underline;
      text-underline-offset: 3px;
      background: none;
      border: none;
      font-family: 'DM Sans', sans-serif;
      transition: color 0.18s;
    }

    .rating-skip-link:hover { color: #5a7068; }

    /* Thank you state inside rating box */
    .rating-thankyou { display: none; }
    .rating-thankyou.show { display: block; }
    .rating-form-content.hide { display: none; }

    .thankyou-emoji {
      font-size: 2.5rem;
      margin-bottom: 12px;
    }

    @media (max-width: 480px) {
      .bk-popup-box { padding: 32px 22px 28px; }
      .star-btn svg { width: 30px; height: 30px; }
    }
  </style>
</head>
<body>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

  <!-- NAV -->
  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img">
      <span>AquaSmile</span>
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn active" onclick="window.location.href='booking.php'">Book Appointment</button>
      <div id="nav-user-info" style="display:none"></div>
      <button class="nav-btn pill" id="nav-login-btn" onclick="window.location.href='login.php'">Log In</button>
      <button class="nav-btn pill-aqua" id="nav-logout-btn" onclick="logout()" style="display:none">Log Out</button>
    </div>
  </nav>

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-header-sub">Schedule a Visit</div>
    <h2>Book an Appointment</h2>
    <div class="section-divider"></div>
  </div>

  <!-- BOOKING WRAPPER -->
  <div class="booking-wrap">

    <!-- STEP INDICATORS -->
    <div class="booking-steps">

      <!-- Step 1: Service — tooth/sparkle SVG -->
      <div class="step-item active" id="step-1">
        <div class="step-num">
          <svg class="step-svg" viewBox="0 0 24 24">
            <path d="M12 2C9.5 2 7 4 7 7c0 2.5.5 5 1.5 7.5C9.5 17 10.5 22 12 22s2.5-5 3.5-7.5C16.5 12 17 9.5 17 7c0-3-2.5-5-5-5z"/>
            <path d="M9 7c0-1.7 1.3-3 3-3"/>
          </svg>
        </div>
        <div class="step-label">Service</div>
      </div>

      <!-- Step 2: Date & Time — calendar SVG -->
      <div class="step-item" id="step-2">
        <div class="step-num">
          <svg class="step-svg" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="3"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
          </svg>
        </div>
        <div class="step-label">Date &amp; Time</div>
      </div>

      <!-- Step 3: Dentist — stethoscope SVG -->
      <div class="step-item" id="step-3">
        <div class="step-num">
          <svg class="step-svg" viewBox="0 0 24 24">
            <path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3"/>
            <path d="M6 3H4M18 3h2"/>
            <circle cx="18" cy="18" r="3"/>
            <path d="M12 16v2"/>
          </svg>
        </div>
        <div class="step-label">Dentist</div>
      </div>

      <!-- Step 4: Confirm — clipboard check SVG -->
      <div class="step-item" id="step-4">
        <div class="step-num">
          <svg class="step-svg" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <path d="M9 12l2 2 4-4"/>
          </svg>
        </div>
        <div class="step-label">Confirm</div>
      </div>

    </div><!-- /booking-steps -->

    <!-- STEP 1: SELECT SERVICE -->
    <div id="booking-step-1" class="booking-card">
      <div class="booking-title">Choose a Service</div>
      <p class="booking-subtitle">Select the dental service you would like to book.</p>
      <div class="grid-3" id="booking-services"></div>
    </div>

    <!-- STEP 2: SELECT DATE & TIME -->
    <div id="booking-step-2" class="booking-card" style="display:none">
      <div class="booking-title">Select Date &amp; Time</div>
      <p class="booking-subtitle">Choose your preferred date and available time slot.</p>

      <div class="date-time-row">
        <!-- Calendar -->
        <div class="calendar-wrap">
          <div class="calendar" id="booking-calendar"></div>
        </div>

        <!-- Time Slots -->
        <div class="timeslot-wrap">
          <div class="timeslot-label">
            <!-- Clock SVG inline -->
            <svg class="label-icon-svg" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="9"/>
              <path d="M12 7v5l3 3"/>
            </svg>
            Available Time Slots
          </div>
          <div class="time-slots" id="time-slots-grid"></div>
        </div>
      </div>

      <div class="step-nav">
        <button class="btn-secondary" onclick="gotoStep(1)">Back</button>
        <button class="btn-primary" id="step2-next" onclick="gotoStep(3)" disabled>Continue</button>
      </div>
    </div>

    <!-- STEP 3: SELECT DENTIST -->
    <div id="booking-step-3" class="booking-card" style="display:none">
      <div class="booking-title">Available Dentists</div>
      <p class="booking-subtitle">The following dentists are available for your selected date and time. Choose your preferred dentist.</p>
      <div id="dentist-availability-list"></div>

      <div class="step-nav">
        <button class="btn-secondary" onclick="gotoStep(2)">Back</button>
        <button class="btn-primary" id="step3-next" onclick="gotoStep(4)" disabled>Continue</button>
      </div>
    </div>

    <!-- STEP 4: CONFIRM -->
    <div id="booking-step-4" class="booking-card" style="display:none">
      <div class="booking-title">Confirm Your Appointment</div>
      <p class="booking-subtitle">Please review your booking details before confirming.</p>

      <div class="confirm-summary" id="booking-summary"></div>

      <div class="form-group">
        <label class="form-label" for="booking-notes">
          <!-- Notes SVG inline -->
          <svg class="label-icon-svg" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          Special notes (optional)
        </label>
        <textarea class="form-textarea" id="booking-notes" placeholder="Allergies, specific concerns, or anything we should know..."></textarea>
      </div>

      <div class="step-nav">
        <button class="btn-secondary" onclick="gotoStep(3)">Back</button>
        <button class="btn-primary" onclick="confirmBooking()">Confirm Booking</button>
      </div>
    </div>

  </div><!-- end booking-wrap -->

  <script src="js/main.js"></script>
  <script src="js/booking.js"></script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js"></script>
</body>

</html>

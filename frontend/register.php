<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg">
  <title>AquaSmile — Create Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260610">
  <link rel="stylesheet" href="css/auth.css?v=20260619">
</head>
<body>

  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img" />
      <span>AquaSmile</span>
    </div>
    <div class="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn pill active" onclick="window.location.href='login.php'">Log In</button>
    </div>
  </nav>

  <div class="auth-wrap">
    <div class="auth-card">

      <div class="auth-logo">
        <div class="auth-logo-icon">
          <img src="images/AquaSmile_Logo.svg" alt="AquaSmile Logo">
        </div>
      </div>

      <div class="auth-title">Create account</div>
      <div class="auth-sub">Join AquaSmile for a seamless booking experience.</div>

      <div id="register-error" class="error-msg" role="alert" aria-live="polite" hidden></div>
      <div id="register-success" class="success-msg" role="status" aria-live="polite" hidden></div>

      <form id="register-form" method="post" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="reg-fname">First name</label>
            <input class="form-input" id="reg-fname" name="first_name" type="text" placeholder="Maria" autocomplete="given-name" pattern="[A-Za-z' -]+" title="Only letters are allowed." required>
          </div>
          <div class="form-group">
            <label class="form-label" for="reg-lname">Last name</label>
            <input class="form-input" id="reg-lname" name="last_name" type="text" placeholder="Santos" autocomplete="family-name" pattern="[A-Za-z' -]+" title="Only letters are allowed." required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Email</label>
          <input class="form-input" type="email" id="reg-email" name="email" placeholder="you@example.com" autocomplete="email" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="Please enter a valid email address." required>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-contact">Contact number</label>
          <input class="form-input"
                 id="reg-contact"
                 name="contact"
                 type="tel"
                 placeholder="09123456789"
                 autocomplete="tel"
                 inputmode="numeric"
                 pattern="[0-9]{11}"
                 maxlength="11"
                 required>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-password">Password</label>
          <input class="form-input" type="password" id="reg-password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-confirm-password">Confirm Password</label>
          <input class="form-input" type="password" id="reg-confirm-password" placeholder="Re-enter your password" autocomplete="new-password" minlength="8" required>
          <div class="form-hint">Use at least 8 characters with a letter and a number.</div>
        </div>

        <button class="btn-full" type="submit" id="create-account-btn">
          <span class="auth-button-spinner" aria-hidden="true" hidden></span>
          <span class="auth-button-label">Create Account</span>
        </button>
      </form>

      <form id="otp-form" method="post" novalidate hidden>
        <div class="otp-context">
          <strong>Verify your email</strong>
          <span>Enter the 6-digit code sent to <span id="otp-email-label"></span>.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-otp">Verification code</label>
          <input class="form-input otp-input"
                 type="text"
                 id="reg-otp"
                 name="otp"
                 placeholder="000000"
                 autocomplete="one-time-code"
                 inputmode="numeric"
                 pattern="[0-9]{6}"
                 maxlength="6"
                 required>
        </div>

        <button class="btn-full" type="submit">Verify Account</button>
        <button class="btn-link" type="button" id="resend-otp-btn">Resend OTP</button>
        <button class="btn-link muted" type="button" id="edit-registration-btn">Edit registration details</button>
      </form>

      <div class="auth-toggle">
        Already have an account?
        <span onclick="window.location.href='login.php'">Sign in</span>
      </div>

    </div>
  </div>

  <script src="js/main.js?v=20260616a"></script>
  <script src="js/auth.js?v=20260619"></script>
</body>
</html>

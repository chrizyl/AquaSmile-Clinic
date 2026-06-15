<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg">
  <title>AquaSmile — Sign In</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=20260610">
  <link rel="stylesheet" href="css/auth.css?v=20260610">
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
    <div class="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn pill active" onclick="window.location.href='login.php'">Log In</button>
    </div>
  </nav>

  <!-- LOGIN FORM -->
  <div class="auth-wrap">
    <div class="auth-card">

      <div class="auth-logo">
        <div class="auth-logo-icon">
          <img src="images/AquaSmile_Logo.svg" alt="AquaSmile Logo">
        </div>
      </div>

      <div class="auth-title">Welcome back</div>
      <div class="auth-sub">Sign in to manage your appointments and more.</div>

      <div id="login-error" class="error-msg" role="alert" aria-live="polite" hidden></div>

      <form id="login-form" method="post" novalidate>
        <div class="form-group">
          <label class="form-label" for="login-email">Email address</label>
          <input class="form-input" type="email" id="login-email" name="email" placeholder="you@example.com" autocomplete="email" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-password">Password</label>
          <input class="form-input" type="password" id="login-password" name="password" placeholder="Your password" autocomplete="current-password" required>
        </div>

        <button class="btn-full" type="submit">Sign In</button>
      </form>

      <div class="auth-toggle">
        Don't have an account?
        <span onclick="window.location.href='register.php'">Register here</span>
      </div>

    </div>
  </div>

  <script src="js/main.js?v=20260616a"></script>
  <script src="js/auth.js?v=20260610"></script>
</body>
</html>

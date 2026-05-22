<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile — Create Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/auth.css">
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

      <div id="register-error" class="error-msg" style="display:none"></div>
      <div id="register-success" class="success-msg" style="display:none"></div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="reg-fname">First name</label>
          <input class="form-input" id="reg-fname" placeholder="Maria">
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-lname">Last name</label>
          <input class="form-input" id="reg-lname" placeholder="Santos">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-email">Email</label>
        <input class="form-input" type="email" id="reg-email" placeholder="you@example.com">
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-contact">Contact number</label>
        <input class="form-input" 
               id="reg-contact" 
               type="text" 
               placeholder="09123456789" 
               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-password">Password</label>
        <input class="form-input" type="password" id="reg-password" placeholder="Min. 6 characters">
      </div>

      <button class="btn-full" onclick="register()">Create Account</button>

      <div class="auth-toggle">
        Already have an account?
        <span onclick="window.location.href='login.php'">Sign in</span>
      </div>

    </div>
  </div>

  <script src="js/main.js"></script>
  <script src="js/auth.js"></script>
</body>
</html>

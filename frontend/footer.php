<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg" />
  <title>AquaSmile — Footer Test</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/footer.css?v=20260523" />
</head>
<body>

  <main>
    <h1>AquaSmile Dental Clinic</h1>
    <p>This page tests the reusable footer component.</p>
  </main>

<footer class="site-footer">

  <div class="footer-wave" aria-hidden="true">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,30 C360,60 1080,0 1440,30 L1440,0 L0,0 Z" fill="var(--footer-wave-fill,#f2f8f8)"/>
    </svg>
  </div>

  <div class="footer-inner">

    <div class="footer-col footer-brand">
      <div class="footer-logo">
  <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="footer-logo-img" />
  AquaSmile
</div>
      <p class="footer-tagline">
        Delivering exceptional dental care with a gentle touch.
        Your smile is our greatest achievement.
      </p>
      <div class="footer-social">
        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
           class="social-link" aria-label="Follow AquaSmile on Facebook">
          <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAAA8UlEQVRoge2WPQoCQQxGE7faXgtLb2QnWHktDyF4hPEgwoJg5RGUZ2Ep7prBMSny6v0232N+GJEkSZoD9MAOOAIX4MEHvLu+AWyB66fCYQWADth/WzyigLl8GAFe26YK6yxtUL4XkbOILGvyqmrqNKsZMsFGKsvX0EJg3eCf/4PXPT/GAZj/al6LM3AXkW7kk4Wq3n41r4XA6E1iPaRTtDgDfyUFvEkBb6pvhEYPr0FVV5ZAtBUo1kA0gZM1EE2gWAORBAZVHayhSAKlJhRJwLz/RWIJlJpQvka9SQFvUsCbFPAmBbxJAW9SwJsUSJLElyc0iUWe8e1w8QAAAABJRU5ErkJggg==" alt="Facebook" class="social-icon" />
        </a>
        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer"
           class="social-link" aria-label="Follow AquaSmile on Instagram">
          <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAADuklEQVRoge2az4uVVRzGnzMzmJA/CpwancxZFAOaIBG5EhSlhY5oQrWJWrQIxVFaBorraNGqyBD/gBaiqLjIoJWlgpa1GUJ0Kpukopzxx9g4flycO3H83nPvPe+P+74ufFb35f0+5zzPOd/z873SY9QLlxIELJe0TdKIpCFJz0l6smQtNySNSxqT9KWkE8656UIlAoPAIeAe1eNvYBfQk1f8NmCqBuEW3wHLsorfC8zWLDzEFWA4S8vHxF8C9gArgbLzX8Bi4BXgI+CfSP3jwJJOhQzSnDbTFMnFHAAGgKMRE6fb6sAP2BB3gQ1VCTdaeoCDERNvtyIsp3m22Vmxbqupt9HqIa4C82LBu03gpSrTphWAFcAdo23L3PtQ4BbDPeScu1+RyBHgGvA7sD1855wbl3TYUN6IFTJmXK7somZb929Bvdcj79cZbT/MvesL4uxi8UsJwp5u/Jx0zs0WKOpH87wiVtlDyFMLsBAYBb4BbgbFTQNngX3AQITXMoWS9RU1ALwH/GXLieA2sB/o61xqBQbwU93hBOEWp4GFj4KBz9qInCS+LQhNJPVEVwwAOyKipoADwFAQ9wx+nZmIxO+rxQDQB1w2tKvAi204/fjBHOIW8GwdBrYbyl1gdQKvH/jDcD+sw4AduJ934gTcvYb7bR0GvjeU5F0rsNRw79Bh39VKX5HNms3bsVSic25C/hA/h/mSFucRUcSA7aXejHw7febaahQx8Kt5XpNKxM9U4bF00jk3mUdEEQPnzPO7Gbg29mwBHR45BvF6Q7kPbE7grcLP/SHeL6wvhwEHnDe0KYLTUoTzMg/v/QGuk3DLUbqBBmctMBPpiSPA68ALwBDwGv7C4D9bD60O6VUYaPBGI6JS8WmGerpjIDBhe6ITPiHDpUFXDTT4rwLnEoT/DGzNUX5UnwsDQoJzLunqPVLROkk7JK2VNCA/Vf8p6YKk45JO5Tkfd9QH/GtM5lrauwFgkdH2/zYkzEG7snbcGleI583zxNyP0MBFE/RW1+RkxybzfLkpAthquuk2YJ1XDvylwU9G265Y4Dz8HXyIr4Csu8xSQfM6MwMMtgp+JzLtfVGXCWAj/qga4mA7Qg/wdcTEMSI3al0U3ttoeSv+BrC0E7k/kkrg73c+xi9YT3VB9ALgJeADmnMe/GevkdTChvEf1h4VzAKjWVtkGXCmZuHg0yat5SMmeoCdpF3alo0Z/Deytjmf+leDJ+T/ZvCmpGH5+/myx8FN+d3AFUknJR1zzl0ruY7HKB0PAAZMEYofBe40AAAAAElFTkSuQmCC" alt="Instagram" class="social-icon" />
        </a>
      </div>
    </div>

    <div class="footer-col">
      <h4 class="footer-heading">Quick Links</h4>
      <nav class="footer-nav" aria-label="Footer navigation">
        <a href="index.php"            class="footer-link">Home</a>
        <a href="services.php"         class="footer-link">Services</a>
        <a href="dentists.php"         class="footer-link">Our Dentists</a>
        <a href="booking.php"          class="footer-link">Book Appointment</a>
        <a href="products.php"         class="footer-link">Products</a>
        <a href="cart.php"             class="footer-link">My Cart</a>
      </nav>
    </div>

    <div class="footer-col">
      <h4 class="footer-heading">Our Services</h4>
      <nav class="footer-nav" aria-label="Services links">
        <a href="services.php" class="footer-link">Dental Cleaning</a>
        <a href="services.php" class="footer-link">Teeth Whitening</a>
        <a href="services.php" class="footer-link">Root Canal Treatment</a>
        <a href="services.php" class="footer-link">Dental Crowns</a>
        <a href="services.php" class="footer-link">Pediatric Check-Up</a>
        <a href="services.php" class="footer-link">Braces Consultation</a>
      </nav>
    </div>

    <div class="footer-col">
      <h4 class="footer-heading">Contact Us</h4>
      <ul class="footer-contact-list">
        <li class="footer-contact-item">
          <span class="contact-label">Address</span>
          <span class="contact-value">2F Sunshine Medical Bldg,<br>Quezon City, Metro Manila</span>
        </li>
        <li class="footer-contact-item">
          <span class="contact-label">Phone</span>
          <a href="tel:+6328123456" class="contact-value contact-link">(02) 8123-4567</a>
        </li>
        <li class="footer-contact-item">
          <span class="contact-label">Mobile</span>
          <a href="tel:+639171234567" class="contact-value contact-link">+63 917 123 4567</a>
        </li>
        <li class="footer-contact-item">
          <span class="contact-label">Email</span>
          <a href="mailto:hello@aquasmile.ph" class="contact-value contact-link">hello@aquasmile.ph</a>
        </li>
        <li class="footer-contact-item">
          <span class="contact-label">Hours</span>
          <span class="contact-value">Mon - Sat: 8:00 AM - 6:00 PM<br>Sunday: Closed</span>
        </li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <p class="footer-copyright">
        &copy; 2026 AquaSmile Dental Clinic. All rights reserved.
      </p>
      <div class="footer-bottom-links">
        <a href="#" class="footer-bottom-link">Privacy Policy</a>
        <span class="footer-bottom-sep" aria-hidden="true"></span>
        <a href="#" class="footer-bottom-link">Terms of Service</a>
      </div>
    </div>
  </div>

</footer>


</body>
</html>

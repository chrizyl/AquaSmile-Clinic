/**
 * footer.js — AquaSmile Dental Clinic
 * ─────────────────────────────────────────────────────────────
 * Self-contained footer loader.
 * Injects BOTH the footer CSS and footer HTML into the page.
 * Works on localhost regardless of whether style.css loads.
 *
 * ADD TO EVERY PAGE (before </body>):
 *   <div id="site-footer-root"></div>
 *   <script src="js/footer.js"></script>
 * ─────────────────────────────────────────────────────────────
 */
(function () {

  /* ─── Base64 social icons (no external paths needed) ─── */
  var FB = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAAA8UlEQVRoge2WPQoCQQxGE7faXgtLb2QnWHktDyF4hPEgwoJg5RGUZ2Ep7prBMSny6v0232N+GJEkSZoD9MAOOAIX4MEHvLu+AWyB66fCYQWADth/WzyigLl8GAFe26YK6yxtUL4XkbOILGvyqmrqNKsZMsFGKsvX0EJg3eCf/4PXPT/GAZj/al6LM3AXkW7kk4Wq3n41r4XA6E1iPaRTtDgDfyUFvEkBb6pvhEYPr0FVV5ZAtBUo1kA0gZM1EE2gWAORBAZVHayhSAKlJhRJwLz/RWIJlJpQvka9SQFvUsCbFPAmBbxJAW9SwJsUSJLElyc0iUWe8e1w8QAAAABJRU5ErkJggg==';
  var IG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAADuklEQVRoge2az4uVVRzGnzMzmJA/CpwancxZFAOaIBG5EhSlhY5oQrWJWrQIxVFaBorraNGqyBD/gBaiqLjIoJWlgpa1GUJ0Kpukopzxx9g4flycO3H83nPvPe+P+74ufFb35f0+5zzPOd/z873SY9QLlxIELJe0TdKIpCFJz0l6smQtNySNSxqT9KWkE8656UIlAoPAIeAe1eNvYBfQk1f8NmCqBuEW3wHLsorfC8zWLDzEFWA4S8vHxF8C9gArgbLzX8Bi4BXgI+CfSP3jwJJOhQzSnDbTFMnFHAAGgKMRE6fb6sAP2BB3gQ1VCTdaeoCDERNvtyIsp3m22Vmxbqupt9HqIa4C82LBu03gpSrTphWAFcAdo23L3PtQ4BbDPeScu1+RyBHgGvA7sD1855wbl3TYUN6IFTJmXK7somZb929Bvdcj79cZbT/MvesL4uxi8UsJwp5u/Jx0zs0WKOpH87wiVtlDyFMLsBAYBb4BbgbFTQNngX3AQITXMoWS9RU1ALwH/GXLieA2sB/o61xqBQbwU93hBOEWp4GFj4KBz9qInCS+LQhNJPVEVwwAOyKipoADwFAQ9wx+nZmIxO+rxQDQB1w2tKvAi204/fjBHOIW8GwdBrYbyl1gdQKvH/jDcD+sw4AduJ934gTcvYb7bR0GvjeU5F0rsNRw79Bh39VKX5HNms3bsVSic25C/hA/h/mSFucRUcSA7aXejHw7febaahQx8Kt5XpNKxM9U4bF00jk3mUdEEQPnzPO7Gbg29mwBHR45BvF6Q7kPbE7grcLP/SHeL6wvhwEHnDe0KYLTUoTzMg/v/QGuk3DLUbqBBmctMBPpiSPA68ALwBDwGv7C4D9bD60O6VUYaPBGI6JS8WmGerpjIDBhe6ITPiHDpUFXDTT4rwLnEoT/DGzNUX5UnwsDQoJzLunqPVLROkk7JK2VNCA/Vf8p6YKk45JO5Tkfd9QH/GtM5lrauwFgkdH2/zYkzEG7snbcGleI583zxNyP0MBFE/RW1+RkxybzfLkpAthquuk2YJ1XDvylwU9G265Y4Dz8HXyIr4Csu8xSQfM6MwMMtgp+JzLtfVGXCWAj/qga4mA7Qg/wdcTEMSI3al0U3ttoeSv+BrC0E7k/kkrg73c+xi9YT3VB9ALgJeADmnMe/GevkdTChvEf1h4VzAKjWVtkGXCmZuHg0yat5SMmeoCdpF3alo0Z/Deytjmf+leDJ+T/ZvCmpGH5+/myx8FN+d3AFUknJR1zzl0ruY7HKB0PAAZMEYofBe40AAAAAElFTkSuQmCC';

  /* ─── Footer CSS injected into <head> ─────────────────
     This means the footer is styled even if style.css is
     missing or has a wrong path.                         */
  var FOOTER_CSS = "/* ============================================================ FOOTER — site-footer Injected by js/footer.js into #site-footer-root on all pages ============================================================ */ :root { --footer-bg: #1C2B2B; --footer-bg-bottom: #142020; --footer-text: #A8C4C3; --footer-text-muted: #6B9090; --footer-heading-c: #FFFFFF; --footer-link-c: #A8C4C3; --footer-link-hover: #FFD2C2; --footer-border: rgba(255,255,255,0.07); --footer-wave-fill: #f2f8f8; --footer-accent: #FFD2C2; --footer-social-bg: rgba(255,255,255,0.08); } /* ── Shell ── */ .site-footer { background: var(--footer-bg); color: var(--footer-text); font-family: 'DM Sans', sans-serif; margin-top: 0; } /* ── Wave ── */ .footer-wave { display: block; line-height: 0; overflow: hidden; height: 48px; } .footer-wave svg { display: block; width: 100%; height: 100%; } /* ── 2-column grid ── */ .footer-inner { max-width: 1160px; margin: 0 auto; padding: 52px 48px 40px; display: grid; grid-template-columns: 1.4fr 1fr; gap: 40px; } /* ── Column 1: Brand ── */ .footer-brand { padding-right: 10px; } .footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.55rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 9px; margin-bottom: 14px; letter-spacing: 0.01em; } .footer-logo-mark { width: 28px; height: 28px; background: var(--footer-accent); color: var(--footer-bg); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-family: 'DM Sans', sans-serif; font-size: 1rem; font-weight: 700; flex-shrink: 0; } .footer-logo-img { height: 32px; width: 32px; object-fit: contain; flex-shrink: 0; } .footer-tagline { font-size: 0.875rem; line-height: 1.72; color: var(--footer-text); font-weight: 300; margin-bottom: 24px; max-width: 280px; } /* ── Social Icons ── */ .footer-social { display: flex; gap: 10px; align-items: center; } .social-link { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px; background: var(--footer-social-bg); border: 1px solid rgba(255,255,255,0.09); transition: background 0.22s, border-color 0.22s, transform 0.22s; text-decoration: none; flex-shrink: 0; } .social-link:hover { background: var(--footer-accent); border-color: var(--footer-accent); transform: translateY(-3px); } .social-icon { width: 20px; height: 20px; display: block; object-fit: contain; filter: brightness(0) invert(1); transition: filter 0.22s; } .social-link:hover .social-icon { filter: brightness(0); } /* ── Column headings ── */ .footer-heading { font-family: 'DM Sans', sans-serif; font-size: 0.71rem; font-weight: 600; color: var(--footer-heading-c); text-transform: uppercase; letter-spacing: 0.13em; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid var(--footer-border); } /* ── Nav links ── */ .footer-nav { display: flex; flex-direction: column; gap: 9px; } .footer-link { font-size: 0.875rem; color: var(--footer-link-c); text-decoration: none; font-weight: 400; transition: color 0.2s, padding-left 0.2s; line-height: 1.5; display: inline-block; } .footer-link:hover { color: var(--footer-link-hover); padding-left: 4px; } .footer-link.footer-link-active { color: var(--footer-accent); font-weight: 500; } /* ── Contact list ── */ .footer-contact-list { list-style: none; display: flex; flex-direction: column; gap: 14px; } .footer-contact-item { display: flex; flex-direction: column; gap: 2px; } .contact-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--footer-text-muted); font-weight: 500; } .contact-value { font-size: 0.875rem; color: var(--footer-text); font-weight: 300; line-height: 1.55; } .contact-link { text-decoration: none; color: var(--footer-link-c); transition: color 0.2s; } .contact-link:hover { color: var(--footer-link-hover); } /* ── Bottom bar ── */ .footer-bottom { background: var(--footer-bg-bottom); border-top: 1px solid var(--footer-border); } .footer-bottom-inner { max-width: 1160px; margin: 0 auto; padding: 18px 48px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; } .footer-copyright { font-size: 0.8rem; color: var(--footer-text-muted); } .footer-bottom-links { display: flex; align-items: center; gap: 16px; } .footer-bottom-link { font-size: 0.8rem; color: var(--footer-text-muted); text-decoration: none; transition: color 0.2s; } .footer-bottom-link:hover { color: var(--footer-accent); } .footer-bottom-sep { width: 1px; height: 12px; background: var(--footer-border); display: inline-block; } /* ── Responsive ── */ @media (max-width: 1024px) { .footer-inner { grid-template-columns: 1fr 1fr; padding: 44px 32px 32px; } .footer-brand { padding-right: 0; } .footer-tagline { max-width: 100%; } } @media (max-width: 640px) { .footer-inner { grid-template-columns: 1fr; padding: 36px 24px 28px; gap: 26px; } .footer-bottom-inner { padding: 16px 24px; flex-direction: column; text-align: center; } .footer-wave { height: 30px; } }";

  function injectStyles() {
    if (document.getElementById('aquasmile-footer-styles')) return;
    var style = document.createElement('style');
    style.id = 'aquasmile-footer-styles';
    style.textContent = FOOTER_CSS;
    document.head.appendChild(style);
  }

  /* ─── Footer HTML ──────────────────────────────────── */
  function buildFooterHTML(year) {
    return (
      '<footer class="site-footer">' +
        '<div class="footer-wave" aria-hidden="true">' +
          '<svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M0,30 C360,60 1080,0 1440,30 L1440,0 L0,0 Z" fill="#f2f8f8"/>' +
          '</svg>' +
        '</div>' +
        '<div class="footer-inner">' +

          '<div class="footer-col footer-brand">' +
            '<div class="footer-logo">' +
              '<img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="footer-logo-img" />' +
              'AquaSmile' +
          '</div>' +
            '<p class="footer-tagline">' +
              'Delivering exceptional dental care with a gentle touch. ' +
              'Your smile is our greatest achievement.' +
            '</p>' +
            '<div class="footer-social">' +
              '<a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Facebook">' +
                '<img src="' + FB + '" alt="Facebook" class="social-icon" />' +
              '</a>' +
              '<a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Instagram">' +
                '<img src="' + IG + '" alt="Instagram" class="social-icon" />' +
              '</a>' +
            '</div>' +
          '</div>' +



          '<div class="footer-col">' +
            '<h4 class="footer-heading">Contact Us</h4>' +
            '<ul class="footer-contact-list">' +
              '<li class="footer-contact-item">' +
                '<span class="contact-label">Address</span>' +
                '<span class="contact-value">2F Sunshine Medical Bldg,<br>Quezon City, Metro Manila</span>' +
              '</li>' +
              '<li class="footer-contact-item">' +
                '<span class="contact-label">Phone</span>' +
                '<a href="tel:+6328123456" class="contact-value contact-link">(02) 8123-4567</a>' +
              '</li>' +
              '<li class="footer-contact-item">' +
                '<span class="contact-label">Mobile</span>' +
                '<a href="tel:+639171234567" class="contact-value contact-link">+63 917 123 4567</a>' +
              '</li>' +
              '<li class="footer-contact-item">' +
                '<span class="contact-label">Email</span>' +
                '<a href="mailto:hello@aquasmile.ph" class="contact-value contact-link">hello@aquasmile.ph</a>' +
              '</li>' +
              '<li class="footer-contact-item">' +
                '<span class="contact-label">Hours</span>' +
                '<span class="contact-value">Mon - Sat: 8:00 AM - 6:00 PM<br>Sunday: Closed</span>' +
              '</li>' +
            '</ul>' +
          '</div>' +

        '</div>' +
        '<div class="footer-bottom">' +
          '<div class="footer-bottom-inner">' +
            '<p class="footer-copyright">' +
              '&copy; ' + year + ' AquaSmile Dental Clinic. All rights reserved.' +
            '</p>' +
            '<div class="footer-bottom-links">' +
              '<a href="#" class="footer-bottom-link">Privacy Policy</a>' +
              '<span class="footer-bottom-sep" aria-hidden="true"></span>' +
              '<a href="#" class="footer-bottom-link">Terms of Service</a>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</footer>'
    );
  }

  /* ─── Highlight current page link ──────────────────── */
  function highlightActive(root) {
    var current = (window.location.pathname.split('/').pop()) || 'index.php';
    root.querySelectorAll('.footer-link').forEach(function (a) {
      if ((a.getAttribute('href') || '').split('/').pop() === current) {
        a.classList.add('footer-link-active');
      }
    });
  }

  /* ─── Main inject function ──────────────────────────── */
  function inject() {
    var root = document.getElementById('site-footer-root');
    if (!root) return;

    injectStyles();
    root.innerHTML = buildFooterHTML(new Date().getFullYear());
    highlightActive(root);
  }

  /* ─── Run when DOM is ready ─────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }

}());
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AquaSmile — Cart</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/cart.css?v=20260523">
  <link rel="stylesheet" href="css/notifications.css?v=20260523">
</head>
<body>

<div class="toast" id="toast"></div>

<!-- NAV -->
<nav>
  <div class="nav-logo">
    <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img" />
    <span>AquaSmile</span>
  </div>
  <div class="nav-links" id="nav-links">
    <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
    <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
    <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
    <button class="nav-btn active" onclick="window.location.href='products.php'">Shop</button>
    <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" style="display:none">Book Appointment</button>
    <div id="nav-user-info" style="display:none"></div>
    <button class="nav-cart-btn" onclick="window.location.href='products.php'">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Back to Shop
    </button>
    <button class="nav-btn pill" id="nav-login-btn" onclick="window.location.href='login.php'">Log In</button>
    <button class="nav-btn pill-aqua" id="nav-logout-btn" onclick="logout()" style="display:none">Log Out</button>
  </div>
</nav>

<div class="page-wrap">
  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-header-sub">Review &amp; Checkout</div>
    <h1>Your Cart</h1>
    <div class="section-divider"></div>
  </div>

  <!-- MAIN LAYOUT -->
  <div class="cart-layout" id="cart-layout">

    <!-- LEFT: ITEMS -->
    <div class="glass-card cart-items-card" id="cart-items-card">
      <div class="cart-items-header">
        <span class="cart-items-title">Items</span>
        <div style="display:flex;align-items:center;gap:16px;">
          <span class="cart-items-count" id="items-count"></span>
          <button class="btn-clear-all" id="btn-clear-all" onclick="clearAll()">Clear all</button>
        </div>
      </div>
      <div id="cart-items-list"></div>
      <button class="btn-continue" onclick="window.location.href='products.php'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Continue shopping
      </button>
    </div>

    <!-- RIGHT: SUMMARY -->
    <div class="glass-card summary-card" id="summary-card">
      <div class="summary-title">Order Summary</div>

      <!-- Promo -->
      <div class="promo-row">
        <input class="promo-input" id="promo-input" type="text" placeholder="Promo code">
        <button class="btn-apply" onclick="applyPromo()">Apply</button>
      </div>
      <div class="promo-success" id="promo-success">✓ Code <strong id="promo-code-used"></strong> applied — 10% off!</div>

      <div class="summary-rows" id="summary-rows"></div>

      <div class="shipping-notice">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12H3l9-9 9 9h-2v8H5v-8z"/></svg>
        Free delivery on all orders
      </div>

      <button class="btn-checkout" onclick="checkout()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Proceed to Checkout
      </button>

      <div class="safe-badges">
        <div class="safe-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          Secure
        </div>
        <div class="safe-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Protected
        </div>
        <div class="safe-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Verified
        </div>
      </div>
    </div>

  </div>

  <!-- RECOMMENDED -->
  <div class="rec-section" id="rec-section" style="display:none">
    <div class="rec-header">
      <div class="rec-label">You Might Also Like</div>
      <div class="rec-title">Recommended for You</div>
    </div>
    <div class="rec-grid" id="rec-grid"></div>
  </div>
</div>

<script>
  /* ── SVGs ── */
  const SVG = {
    img: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,
    trash: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
    cart: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>`,
  };

  /* ── All products (sync with shop.html) ── */
  const ALL_PRODUCTS = [
    { id:'P1',  name:'Sonic Pro Toothbrush',     price:1299, category:'electric',    img:'images/toothbrush.avif',        desc:'Rechargeable electric toothbrush with 3 modes.' },
    { id:'P2',  name:'WhiteGlow Toothpaste',     price:299,  category:'paste',       img:'images/toothpaste.jpg',         desc:'Enamel-strengthening whitening paste with fluoride.' },
    { id:'P3',  name:'Silk Dental Floss',        price:189,  category:'floss',       img:'images/floss.jpg',              desc:'Natural silk floss with wax coating.' },
    { id:'P4',  name:'AquaFresh Mouthwash',      price:349,  category:'floss',       img:'images/mouthwash.jpg',          desc:'Antibacterial rinse with fresh mint.' },
    { id:'P5',  name:'Teeth Whitening Strips',   price:899,  category:'whitening',   img:'images/whitening strips.jpg',   desc:'14-day whitening kit, up to 7 shades.' },
    { id:'P6',  name:'Tongue Scraper Set',       price:249,  category:'accessories', img:'images/scraper set.jpg',        desc:'Stainless steel scrapers for fresher breath.' },
    { id:'P7',  name:'Sensitive Gum Gel',        price:399,  category:'paste',       img:'images/gum gel.png',            desc:'Soothing gel for gum sensitivity.' },
    { id:'P8',  name:'Natural Bamboo Brush Set', price:549,  category:'accessories', img:'images/bamboo_toothbrush.webp', desc:'4-pack biodegradable bamboo toothbrushes.' },
    { id:'P9',  name:'Water Flosser Elite',      price:1899, category:'electric',    img:'images/elite flosser.jpg',      desc:'Cordless flosser with 10 pressure settings.' },
    { id:'P10', name:'Enamel Repair Paste',      price:449,  category:'paste',       img:'images/enamel repair.jpg',      desc:'Re-mineralizes and strengthens tooth enamel.' },
    { id:'P11', name:'Charcoal Whitening Kit',   price:749,  category:'whitening',   img:'images/charcoal_kit.png',       desc:'Charcoal powder with LED tray.' },
    { id:'P12', name:'Travel Dental Kit',        price:329,  category:'accessories', img:'images/dental kit.jpg',         desc:'Compact travel set with brush, mini paste & floss.' },
  ];

  const CAT_LABELS = {electric:'Electric Tools',paste:'Toothpaste',floss:'Floss & Rinse',whitening:'Whitening',accessories:'Accessories'};

  let cart = JSON.parse(localStorage.getItem('aqCart') || '[]');
  let promoApplied = false;

  function saveCart() { localStorage.setItem('aqCart', JSON.stringify(cart)); }

  /* ── RENDER ── */
  function render() {
    renderItems();
    renderSummary();
    renderRecs();
  }

  function renderItems() {
    const list = document.getElementById('cart-items-list');
    const count = document.getElementById('items-count');
    const clearBtn = document.getElementById('btn-clear-all');
    const layout = document.getElementById('cart-layout');
    const recSec = document.getElementById('rec-section');

    if (!cart.length) {
      list.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${SVG.img}</div>
          <h3>Your cart is empty</h3>
          <p>Add some products to get started.</p>
          <button class="btn-shop-now" onclick="window.location.href='products.php'">Shop Now</button>
        </div>`;
      count.textContent = '';
      clearBtn.style.display = 'none';
      document.getElementById('summary-card').style.display = 'none';
      layout.style.gridTemplateColumns = '1fr';
      recSec.style.display = 'block';
      return;
    }

    document.getElementById('summary-card').style.display = '';
    layout.style.gridTemplateColumns = '';
    recSec.style.display = 'block';
    clearBtn.style.display = '';
    const total = cart.reduce((s,i)=>s+i.qty,0);
    count.textContent = `${total} item${total!==1?'s':''}`;

    list.innerHTML = cart.map(item => {
      const product = ALL_PRODUCTS.find(p=>p.id===item.id);
      const cat = product ? CAT_LABELS[product.category] || '' : '';
      return `
      <div class="cart-item" id="ci-${item.id}">
        <div class="cart-item-thumb">
          ${item.img ? `<img src="${item.img}" alt="${item.name}">` : SVG.img}
        </div>
        <div class="cart-item-info">
          <div class="cart-item-category">${cat}</div>
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-unit-price">&#8369;${item.price.toLocaleString()} each</div>
          <div class="cart-item-qty-row">
            <button class="qty-btn" onclick="changeQty('${item.id}',-1)">&#8722;</button>
            <span class="qty-num">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty('${item.id}',1)">&#43;</button>
          </div>
        </div>
        <div class="cart-item-right">
          <div class="cart-item-total">&#8369;${(item.price*item.qty).toLocaleString()}</div>
          <button class="btn-remove" onclick="removeItem('${item.id}')" title="Remove">${SVG.trash}</button>
        </div>
      </div>`;
    }).join('');
  }

  function renderSummary() {
    const rows = document.getElementById('summary-rows');
    const sub = cart.reduce((s,i)=>s+i.price*i.qty,0);
    const discount = promoApplied ? Math.round(sub*0.10) : 0;
    const total = sub - discount;
    rows.innerHTML = `
      <div class="summary-row"><span>Subtotal</span><span>&#8369;${sub.toLocaleString()}</span></div>
      ${promoApplied ? `<div class="summary-row discount"><span>Promo (10% off)</span><span>&#8722;&#8369;${discount.toLocaleString()}</span></div>` : ''}
      <div class="summary-row"><span>Delivery</span><span>Free</span></div>
      <div class="summary-divider"></div>
      <div class="summary-row grand"><span>Total</span><span>&#8369;${total.toLocaleString()}</span></div>`;
  }

  function renderRecs() {
    const cartIds = cart.map(i=>i.id);
    const recs = ALL_PRODUCTS.filter(p=>!cartIds.includes(p.id)).slice(0,4);
    const grid = document.getElementById('rec-grid');
    if (!recs.length) { document.getElementById('rec-section').style.display='none'; return; }
    grid.innerHTML = recs.map(p=>`
      <div class="rec-card">
        <div class="rec-thumb">${p.img ? `<img src="${p.img}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">` : SVG.img}</div>
        <div class="rec-body">
          <div class="rec-cat">${CAT_LABELS[p.category]||p.category}</div>
          <div class="rec-name">${p.name}</div>
          <div class="rec-price">&#8369;${p.price.toLocaleString()}</div>
          <button class="btn-rec-add" onclick="addRec('${p.id}')">
            ${SVG.cart} Add to Cart
          </button>
        </div>
      </div>`).join('');
  }

  /* ── ACTIONS ── */
  function changeQty(pid, d) {
    const item = cart.find(i=>i.id===pid); if(!item) return;
    item.qty += d;
    if (item.qty <= 0) { removeItem(pid); return; }
    saveCart(); render();
  }

  function removeItem(pid) {
    cart = cart.filter(i=>i.id!==pid);
    saveCart(); render(); showToast('Item removed');
  }

  function clearAll() {
    if (!confirm('Remove all items from your cart?')) return;
    cart = []; saveCart(); render(); showToast('Cart cleared');
  }

  function addRec(pid) {
    const p = ALL_PRODUCTS.find(x=>x.id===pid); if(!p) return;
    const ex = cart.find(i=>i.id===pid);
    if (ex) ex.qty++; else cart.push({id:pid,name:p.name,price:p.price,img:p.img||'',qty:1});
    saveCart(); render(); showToast(p.name+' added to cart');
  }

  function applyPromo() {
    const val = document.getElementById('promo-input').value.trim().toUpperCase();
    const success = document.getElementById('promo-success');
    const codeUsed = document.getElementById('promo-code-used');
    if (!cart.length) { showToast('Add items first'); return; }
    if (val === 'AQUA10' || val === 'SMILE10') {
      promoApplied = true;
      codeUsed.textContent = val;
      success.classList.add('show');
      renderSummary();
      showToast('Promo code applied!');
    } else {
      success.classList.remove('show');
      showToast('Invalid promo code');
    }
  }

  function checkout() {
    if (!cart.length) { showToast('Your cart is empty'); return; }
    showToast('Redirecting to checkout…');
    setTimeout(()=>{ window.location.href='checkout.php'; }, 1000);
  }

  /* ── TOAST ── */
  let toastTimer;
  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=>t.classList.remove('show'), 2200);
  }

  /* ── COOKIE HELPER (mirrors products.php) ── */
  const Cookie = {
    get(name) {
      const match = document.cookie.match(new RegExp('(?:^|; )aqsmile_' + name + '=([^;]*)'));
      try { return match ? JSON.parse(decodeURIComponent(match[1])) : null; }
      catch { return null; }
    },
    remove(name) {
      document.cookie = `aqsmile_${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
    },
  };

  /* ── LOGIN GUARD ── Redirect to login if not authenticated */
  (function() {
    const user = Cookie.get('currentUser');
    if (!user) {
      window.location.href = 'login.php';
    }
  })();

  render();

  window.addEventListener('storage', function(e) {
    if (e.key === 'aqCart' && (e.newValue === null || e.newValue === '[]')) {
      cart = [];
      promoApplied = false;
      render();
    }
  });


  window.addEventListener('pageshow', function(e) {
    cart = JSON.parse(localStorage.getItem('aqCart') || '[]');
    promoApplied = false;
    render();
  });

  /* ── AUTH NAV ── */
  function updateNav() {
    const currentUser  = Cookie.get('currentUser');
    const currentAdmin = Cookie.get('currentAdmin');
    const loggedIn = currentUser || currentAdmin;
    const loginBtn  = document.getElementById('nav-login-btn');
    const logoutBtn = document.getElementById('nav-logout-btn');
    const userInfo  = document.getElementById('nav-user-info');
    const bookBtn   = document.getElementById('nav-book-btn');
    if (loginBtn)  loginBtn.style.display  = loggedIn ? 'none' : '';
    if (logoutBtn) logoutBtn.style.display = loggedIn ? '' : 'none';
    if (userInfo)  userInfo.style.display  = loggedIn ? '' : 'none';
    if (bookBtn)   bookBtn.style.display   = (currentUser && !currentAdmin) ? '' : 'none';
    if (currentAdmin && userInfo) userInfo.textContent = currentAdmin.name;
    else if (currentUser && userInfo) userInfo.textContent = currentUser.name;
  }

  function logout() {
    Cookie.remove('currentUser');
    Cookie.remove('currentAdmin');
    localStorage.removeItem('aqCart');
    showToast('You have been signed out.');
    setTimeout(() => { window.location.href = 'index.php'; }, 500);
  }

  updateNav();
</script>
<script src="js/notifications.js?v=20260523"></script>
</body>
</html>

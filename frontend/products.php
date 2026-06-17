<?php
require_once 'includes/session-init.php';
include 'includes/admin-check.php';
require_once 'includes/navbar-auth.php';

if (isAdmin()) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg">
  <title>AquaSmile — Shop</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/products.css?v=20260523">
  <link rel="stylesheet" href="css/notifications.css?v=20260616a">
  <link rel="stylesheet" href="css/auth-nav.css?v=20260614">
  <link rel="stylesheet" href="css/admin-restrictions.css">
</head>
<body>

<div class="toast" id="toast"></div>

<!-- NAV -->
<nav>
  <div class="nav-logo">
    <img src="images/AquaSmile_Logo.svg" alt="AquaSmile dental clinic logo" class="nav-logo-img">
    AquaSmile
  </div>
  <div class="nav-links" id="nav-links">
    <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
    <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
    <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
    <button class="nav-btn active" onclick="window.location.href='products.php'">Shop</button>
    <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" <?php echo nav_is_patient() ? '' : 'style="display:none"'; ?>>Book Appointment</button>
    <button class="nav-cart-btn" onclick="openCart()">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Cart
      <span class="nav-cart-badge" id="nav-cart-count">0</span>
    </button>
    <?php render_nav_auth(); ?>
  </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="page-header-sub">Dental Essentials</div>
  <h2>The AquaSmile Shop</h2>
  <div class="section-divider"></div>
  <p class="page-header-desc">Dentist-recommended products for your daily oral care routine.</p>
</div>

<!-- FILTER BAR -->
<div class="filter-bar">
  <div class="filter-inner">
    <button class="filter-btn active" data-category="all"         onclick="filterProducts('all',this)">All Products</button>
    <button class="filter-btn"        data-category="Electric Tools" onclick="filterProducts('Electric Tools',this)">Electric Tools</button>
    <button class="filter-btn"        data-category="Toothpaste"     onclick="filterProducts('Toothpaste',this)">Toothpaste</button>
    <button class="filter-btn"        data-category="Floss &amp; Rinse" onclick="filterProducts('Floss & Rinse',this)">Floss &amp; Rinse</button>
    <button class="filter-btn"        data-category="Whitening"      onclick="filterProducts('Whitening',this)">Whitening</button>
    <button class="filter-btn"        data-category="Accessories"    onclick="filterProducts('Accessories',this)">Accessories</button>
  </div>
  <div class="sort-group">
    <label class="sort-label" for="sort-select">Sort by</label>
    <select class="sort-select" id="sort-select" onchange="sortProducts(this.value)">
      <option value="default">Featured</option>
      <option value="price-asc">Price: Low to High</option>
      <option value="price-desc">Price: High to Low</option>
      <option value="name-asc">Name: A&ndash;Z</option>
    </select>
  </div>
</div>

<!-- SHOP MAIN -->
<main class="shop-main">
  <div class="products-count" id="products-count"></div>
  <div class="products-grid" id="products-grid"></div>
  <div class="empty-state" id="shop-empty" style="display:none">
    <p>No products found in this category.</p>
    <button class="btn-secondary" onclick="filterProducts('all',document.querySelector('[data-category=all]'))">View All</button>
  </div>
</main>

<!-- CART OVERLAY + DRAWER -->
<div class="cart-overlay" id="cart-overlay" onclick="closeCart()"></div>
<aside class="cart-drawer" id="cart-drawer">
  <div class="cart-drawer-header">
    <h3 class="cart-drawer-title">Your Cart</h3>
    <button class="cart-close-btn" onclick="closeCart()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="cart-drawer-body" id="cart-drawer-body"></div>
  <div class="cart-drawer-footer" id="cart-drawer-footer" style="display:none">
    <div class="cart-totals" id="cart-totals"></div>
    <button class="btn-full btn-view-cart" onclick="requireLoginThen('cart.php')">View Full Cart</button>
    <button class="btn-full btn-checkout-full" onclick="requireLoginThen('cart.php')">Checkout</button>
  </div>
</aside>

<!-- FAB -->
<button class="cart-fab" onclick="openCart()">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
  <span class="cart-fab-badge" id="fab-badge">0</span>
</button>

<!-- PRODUCT DETAIL MODAL -->
<div class="pd-overlay" id="pd-overlay" onclick="closeProductDetail()"></div>
<div class="pd-modal" id="pd-modal">
  <button class="pd-close" onclick="closeProductDetail()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
  <div class="pd-img-wrap" id="pd-img-wrap"></div>
  <div class="pd-body" id="pd-body"></div>
</div>

<script>
  /* ── Reusable inline SVGs ── */
  const SVG = {
    imgPlaceholder: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`,
    cart:           `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>`,
    check:          `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
    trash:          `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>`,
    bagEmpty:       `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>`,
  };

  class Product {
    #id;
    #name;
    #price;
    #category;
    #desc;
    #img;

    constructor({ id, name, price, category, desc, img }) {
      this.#id       = id;
      this.#name     = name;
      this.#price    = price;
      this.#category = category;
      this.#desc     = desc;
      this.#img      = img || null;
    }

    get id()       { return this.#id; }
    get name()     { return this.#name; }
    get price()    { return this.#price; }
    get category() { return this.#category; }
    get desc()     { return this.#desc; }
    get img()      { return this.#img; }

    /* Formatted price string for display */
    get formattedPrice() {
      return '\u20B1' + this.#price.toLocaleString();
    }

    /* Plain object — used by renderProducts and JSON serialisation */
    toObject() {
      return {
        id:       this.#id,
        name:     this.#name,
        price:    this.#price,
        category: this.#category,
        desc:     this.#desc,
        img:      this.#img,
      };
    }
  }

  class CartItem extends Product {
    #qty;

    constructor(product, qty = 1) {
      super(product.toObject ? product.toObject() : product);
      this.#qty = qty;
    }

    get qty()       { return this.#qty; }
    set qty(val)    { this.#qty = val; }

    /* Line total for this cart entry */
    get lineTotal() { return this.price * this.#qty; }

    /* Formatted line total for display */
    get formattedLineTotal() {
      return '\u20B1' + this.lineTotal.toLocaleString();
    }

    increment()  { this.#qty += 1; }
    decrement()  { this.#qty -= 1; }

    /* Serialise to plain object for localStorage */
    toObject() {
      return { ...super.toObject(), qty: this.#qty };
    }
  }

  /* ── Products ── */
  let PRODUCTS = [
    new Product({ id:'P1',  name:'Sonic Pro Toothbrush',     price:1299, category:'Electric Tools', desc:'Rechargeable electric toothbrush with 3 modes and UV sanitizer.',                 img:'images/toothbrush.avif' }),
    new Product({ id:'P2',  name:'WhiteGlo Toothpaste',     price:299,  category:'Toothpaste',     desc:'Enamel-strengthening whitening paste with fluoride and mint.',                    img:'images/toothpaste.jpg' }),
    new Product({ id:'P3',  name:'Silk Dental Floss',        price:189,  category:'Floss & Rinse', desc:'Natural silk floss with wax coating for smooth, effortless cleaning.',           img:'images/floss.jpg' }),
    new Product({ id:'P4',  name:'AquaFresh Mouthwash',      price:349,  category:'Floss & Rinse', desc:'Antibacterial rinse with fresh mint and zero alcohol formula.',                   img:'images/mouthwash.jpg' }),
    new Product({ id:'P5',  name:'Teeth Whitening Strips',   price:899,  category:'Whitening',     desc:'14-day whitening kit, clinically proven to whiten up to 7 shades.',              img:'images/whitening strips.jpg' }),
    new Product({ id:'P6',  name:'Tongue Scraper Set',       price:249,  category:'Accessories',   desc:'Stainless steel scrapers for fresher breath and improved oral hygiene.',          img:'images/scraper set.jpg' }),
    new Product({ id:'P7',  name:'Sensitive Gum Balm Gel',   price:399,  category:'Toothpaste',     desc:'Soothing gel formula for gum sensitivity and irritation relief.',                img:'images/gum gel.png' }),
    new Product({ id:'P8',  name:'Natural Bamboo Brush Set', price:549,  category:'Accessories',   desc:'4-pack biodegradable bamboo toothbrushes with charcoal bristles.',               img:'images/bamboo toothbrush.webp' }),
    new Product({ id:'P9',  name:'Water Flosser Elite',      price:1899, category:'Electric Tools', desc:'Cordless water flosser with 10 pressure settings and 360&#176; rotating tip.',   img:'images/elite flosser.jpg' }),
    new Product({ id:'P10', name:'Enamel Repair Paste',      price:449,  category:'Toothpaste',     desc:'Clinically proven formula that re-mineralizes and strengthens tooth enamel.',     img:'images/enamel repair.jpg' }),
    new Product({ id:'P11', name:'Charcoal Whitening Kit',   price:749,  category:'Whitening',     desc:'Activated charcoal powder with LED light tray for professional results at home.', img:'images/charcoal kit.png' }),
    new Product({ id:'P12', name:'Travel Dental Kit',        price:329,  category:'Accessories',   desc:'Compact travel set with foldable brush, mini paste, and floss picks.',           img:'images/dental kit.jpg' }),
  ];

  let cart = [];
  let activeCategory = 'all', activeSort = 'default';
  const pendingCartAdds = new Set();

  function normalizeProductId(pid) {
    const text = String(pid || '');
    const match = text.match(/^P?(\d+)$/i);
    return match ? match[1] : text;
  }

  function normalizeCartItems(items) {
    const merged = new Map();
    (items || []).forEach(raw => {
      const normalizedId = normalizeProductId(raw.id);
      const key = String(normalizedId);
      const base = raw.toObject ? raw.toObject() : raw;
      const qty = Math.max(1, Number(base.qty || 1));
      const existing = merged.get(key);
      if (existing) {
        existing.qty += qty;
        return;
      }
      merged.set(key, new CartItem({ ...base, id: key }, qty));
    });
    return [...merged.values()];
  }

  function getCurrentUser() {
    return Cookie.get('currentUser');
  }

  function cartStorage() {
    const user = getCurrentUser();
    return user
      ? { store: localStorage, key: 'aqCart_user_' + user.id }
      : { store: sessionStorage, key: 'aqGuestCart' };
  }

  function readStoredCart() {
    const target = cartStorage();
    try { return JSON.parse(target.store.getItem(target.key) || '[]'); }
    catch { return []; }
  }

  function writeStoredCart(items) {
    const target = cartStorage();
    target.store.setItem(target.key, JSON.stringify(items));
  }

  function clearSharedCartDisplayKeys() {
    localStorage.removeItem('aqCart');
    localStorage.removeItem('aqsmile_cart');
    sessionStorage.removeItem('aqsmile_cart');
  }

  function saveCart() {
    writeStoredCart(cart.map(i => i.toObject()));
    updateBadges();
  }

  async function syncCartItemToDatabase(pid, quantity) {
    const user = getCurrentUser();
    if (!user) return;

    await fetch(new URL('../backend/api/index.php', window.location.href).pathname + '?action=save_cart_item', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      cache: 'no-store',
      body: JSON.stringify({
        productId: normalizeProductId(pid),
        quantity
      }),
    }).then(async response => {
      const payload = await response.json();
      if (!response.ok || !payload.ok) throw new Error(payload.message || 'Cart sync failed.');
      return payload;
    });
  }

  async function removeCartItemFromDatabase(pid) {
    const user = getCurrentUser();
    if (!user) return;

    await fetch(new URL('../backend/api/index.php', window.location.href).pathname + '?action=remove_cart_item', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      cache: 'no-store',
      body: JSON.stringify({
        productId: normalizeProductId(pid)
      }),
    }).then(async response => {
      const payload = await response.json();
      if (!response.ok || !payload.ok) throw new Error(payload.message || 'Cart remove failed.');
      return payload;
    });
  }

  function updateBadges() {
    const n = cart.reduce((s,i) => s+i.qty, 0);
    document.getElementById('nav-cart-count').textContent = n;
    document.getElementById('fab-badge').textContent = n;
  }

  function categoryLabel(cat) {
    return {
      electric:'Electric Tools',
      paste:'Toothpaste',
      floss:'Floss & Rinse',
      whitening:'Whitening',
      accessories:'Accessories'
    }[cat] || cat;
  }

  function filterProducts(cat, btn) {
    activeCategory = cat;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFiltersAndSort();
  }

  function sortProducts(val) { activeSort = val; applyFiltersAndSort(); }

  function isAdminAccount() {
    const user = Cookie.get('currentUser');
    const admin = Cookie.get('currentAdmin');
    return (admin && admin.role === 'admin') || (user && user.role === 'admin');
  }

  function applyFiltersAndSort() {
    let list = activeCategory==='all' ? [...PRODUCTS] : PRODUCTS.filter(p=>p.category===activeCategory);
    if (activeSort==='price-asc')  list.sort((a,b)=>a.price-b.price);
    if (activeSort==='price-desc') list.sort((a,b)=>b.price-a.price);
    if (activeSort==='name-asc')   list.sort((a,b)=>a.name.localeCompare(b.name));
    renderProducts(list);
  }

  function renderProducts(list) {
    const grid=document.getElementById('products-grid');
    const empty=document.getElementById('shop-empty');
    const count=document.getElementById('products-count');
    const adminViewing = isAdminAccount();
    if (!list.length) { grid.innerHTML=''; empty.style.display=''; count.textContent=''; return; }
    empty.style.display='none';
    count.textContent=`Showing ${list.length} product${list.length!==1?'s':''}`;
    grid.innerHTML = list.map(p=>`
      <div class="product-card" id="card-${p.id}">
        <div class="product-img-wrap">
          ${p.img
            ? `<img src="${p.img}" alt="${p.name}">`
            : `<div class="product-img-placeholder">${SVG.imgPlaceholder}<span>Product Photo</span></div>`}
        </div>
        <div class="product-body">
          <div class="product-category-tag">${categoryLabel(p.category)}</div>
          <div class="product-name">${p.name}</div>
          <div class="product-price">${p.formattedPrice}</div>
          <div class="product-desc">${p.desc}</div>
          <div class="product-card-actions">
            <button class="btn-view-details" onclick="openProductDetail('${p.id}')">View Details</button>
            <button
              class="btn-add-cart ${adminViewing ? 'admin-disabled' : ''}"
              id="btn-${p.id}"
              onclick="${adminViewing ? 'return false;' : `addToCart('${p.id}')`}"
              ${adminViewing ? 'disabled' : ''}
            >
              ${SVG.cart} ${adminViewing ? 'View Only' : 'Add to Cart'}
            </button>
          </div>
        </div>
      </div>`).join('');
  }

  /* ── AUTH GUARD for cart/checkout navigation ── */
  function requireLoginThen(url) {
    const user = Cookie.get('currentUser');
    const admin = Cookie.get('currentAdmin');
    if (admin) {
      showToast('Admin accounts are view-only on the site.');
      return;
    }
    if (!user) {
      window.location.href = 'login.php';
      return;
    }
    window.location.href = url;
  }

  async function addToCart(pid) {
    if (isAdminAccount()) {
      showToast('Admin accounts are view-only on the site.');
      return;
    }

    const normalizedId = normalizeProductId(pid);
    if (pendingCartAdds.has(normalizedId)) return;
    const p = PRODUCTS.find(x => normalizeProductId(x.id) === normalizedId); if (!p) return;
    pendingCartAdds.add(normalizedId);
    const ex = cart.find(i => String(i.id) === normalizedId);
    const previousQty = ex ? ex.qty : 0;
    const previous = cart.map(item => item.toObject());

    try {
      if (ex) { ex.increment(); } else { cart.push(new CartItem({ ...p.toObject(), id: normalizeProductId(p.id) })); }
      saveCart(); renderCartDrawer();
      await syncCartItemToDatabase(pid, previousQty + 1);
      const btn = document.getElementById('btn-'+pid);
      if (btn) {
        btn.innerHTML = SVG.check+' Added'; btn.classList.add('added');
        setTimeout(()=>{ btn.innerHTML=SVG.cart+' Add to Cart'; btn.classList.remove('added'); }, 1300);
      }
      showToast(p.name+' added to cart');
    } catch (err) {
      console.warn('Cart save failed:', err.message);
      cart = normalizeCartItems(previous);
      saveCart();
      renderCartDrawer();
      showToast(err.message || 'Cart was not saved to the database.');
    } finally {
      pendingCartAdds.delete(normalizedId);
    }
  }

  async function removeFromCart(pid) {
    const previous = [...cart];
    cart = cart.filter(i=>i.id!==pid);
    saveCart(); renderCartDrawer();
    try {
      await removeCartItemFromDatabase(pid);
    } catch (err) {
      console.warn('Cart remove failed:', err.message);
      cart = previous;
      saveCart(); renderCartDrawer();
      showToast(err.message || 'Cart remove failed.');
    }
  }

  async function changeQty(pid, d) {
    const item=cart.find(i=>i.id===pid); if (!item) return;
    const previousQty = item.qty;
    if (d > 0) { item.increment(); } else { item.decrement(); }
    if (item.qty<=0) { await removeFromCart(pid); return; }
    saveCart(); renderCartDrawer();
    try {
      await syncCartItemToDatabase(pid, item.qty);
    } catch (err) {
      console.warn('Cart quantity sync failed:', err.message);
      item.qty = previousQty;
      saveCart(); renderCartDrawer();
      showToast(err.message || 'Cart quantity update failed.');
    }
  }

  function renderCartDrawer() {
    const body=document.getElementById('cart-drawer-body');
    const footer=document.getElementById('cart-drawer-footer');
    const totals=document.getElementById('cart-totals');
    if (!cart.length) {
      body.innerHTML=`<div class="cart-drawer-empty">${SVG.bagEmpty}<p>Your cart is empty.</p></div>`;
      footer.style.display='none'; return;
    }
    body.innerHTML = cart.map(item=>`
      <div class="cart-item">
        <div class="cart-item-thumb">
          ${item.img ? `<img src="${item.img}" alt="${item.name}">` : SVG.imgPlaceholder}
        </div>
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-price">${item.formattedLineTotal}</div>
          <div class="cart-item-qty">
            <button class="qty-btn" onclick="changeQty('${item.id}',-1)">&#8722;</button>
            <span class="qty-num">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty('${item.id}',1)">&#43;</button>
          </div>
        </div>
        <button class="cart-item-remove" onclick="removeFromCart('${item.id}')" title="Remove">${SVG.trash}</button>
      </div>`).join('');
    const sub = cart.reduce((s,i)=>s+i.lineTotal,0);
    totals.innerHTML=`
      <div class="cart-total-row"><span>Subtotal</span><span>&#8369;${sub.toLocaleString()}</span></div>
      <div class="cart-total-row"><span>Delivery</span><span>Free</span></div>
      <div class="cart-total-row grand"><span>Total</span><span>&#8369;${sub.toLocaleString()}</span></div>`;
    footer.style.display='';
  }

  function openCart() {
    document.getElementById('cart-drawer').classList.add('open');
    document.getElementById('cart-overlay').classList.add('open');
    renderCartDrawer();
  }
  function closeCart() {
    document.getElementById('cart-drawer').classList.remove('open');
    document.getElementById('cart-overlay').classList.remove('open');
  }

  let toastTimer;
  function showToast(msg) {
    const t=document.getElementById('toast');
    t.textContent=msg; t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer=setTimeout(()=>t.classList.remove('show'),2200);
  }

  /* ── COOKIE HELPER (mirrors main.js) ── */
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

  /* ── AUTH NAV ── */
  function updateNav() {
    const currentUser  = Cookie.get('currentUser');
    const currentAdmin = Cookie.get('currentAdmin');
    const loggedIn = currentUser || currentAdmin;
    const serverAuth = document.getElementById('nav-auth-state');
    const loginBtn  = document.getElementById('nav-login-btn');
    const logoutBtn = document.getElementById('nav-logout-btn');
    const userInfo  = document.getElementById('nav-user-info');
    const bookBtn   = document.getElementById('nav-book-btn');
    if (!serverAuth) {
      if (loginBtn)  loginBtn.style.display  = loggedIn ? 'none' : '';
      if (logoutBtn) logoutBtn.style.display = loggedIn ? '' : 'none';
      if (userInfo)  userInfo.style.display  = loggedIn ? '' : 'none';
    }
    if (bookBtn) {
      bookBtn.style.display = serverAuth
        ? (serverAuth.dataset.authenticated === 'patient' ? '' : 'none')
        : ((currentUser && !currentAdmin) ? '' : 'none');
    }
    if (!serverAuth) {
      if (currentAdmin && userInfo) userInfo.textContent = currentAdmin.name;
      else if (currentUser && userInfo) userInfo.textContent = currentUser.name;
    }
  }

  function logout() {
    Cookie.remove('currentUser');
    Cookie.remove('currentAdmin');
    sessionStorage.removeItem('aqsmile_cart'); // kiniclear ang session
    sessionStorage.removeItem('aqGuestCart');
    clearSharedCartDisplayKeys();
    cart = [];                                  // reset in-memory cart
    updateBadges();                             // reset badge to 
    window.location.href = 'logout.php';
  }

  function productCategoryFromName(name) {
    const text = name.toLowerCase();
    if (text.includes('toothbrush') || text.includes('flosser')) return 'Electric Tools';
    if (text.includes('paste') || text.includes('gel')) return 'Toothpaste';
    if (text.includes('floss') || text.includes('mouthwash')) return 'Floss & Rinse';
    if (text.includes('white') || text.includes('charcoal')) return 'Whitening';
    return 'Accessories';
  }

  async function syncProductsFromDatabase() {
    try {
      const apiBase = new URL('../backend/api/index.php', window.location.href).pathname;
      const response = await fetch(apiBase + '?action=catalog', { cache: 'no-store' });
      const data = await response.json();
      if (!response.ok || !data.ok || !data.products?.length) return;

      PRODUCTS = data.products.map(p => new Product({
        id: String(p.id),
        name: p.name,
        price: Number(p.price),
        category: p.category || productCategoryFromName(p.name),
        desc: p.desc || '',
        img: p.img || p.photo || '',
      }));

      cart = normalizeCartItems(cart
        .map(raw => {
          const product = PRODUCTS.find(p => p.id === normalizeProductId(raw.id) || p.name === raw.name);
          return product ? new CartItem(product, raw.qty) : null;
        })
        .filter(Boolean));
      saveCart();
    } catch (err) {
      console.warn('Using local products fallback:', err.message);
    }
  }

  async function syncCartFromDatabase() {
    const user = getCurrentUser();
    if (!user) {
      cart = normalizeCartItems(readStoredCart());
      saveCart();
      return;
    }

    try {
      const apiBase = new URL('../backend/api/index.php', window.location.href).pathname;
      const response = await fetch(apiBase + '?action=cart_items', { cache: 'no-store' });
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.message || 'Cart load failed.');

      cart = normalizeCartItems((data.cartItems || []).map(item => {
        const product = PRODUCTS.find(p => p.id === String(item.product_id));
        return new CartItem({
          id: String(item.product_id),
          name: item.name || product?.name || 'Product',
          price: Number(item.price || product?.price || 0),
          category: product?.category || productCategoryFromName(item.name || ''),
          desc: product?.desc || '',
          img: item.image_path || product?.img || ''
        }, Number(item.quantity || 1));
      }));
      saveCart();
    } catch (err) {
      console.warn('Using local cart fallback:', err.message);
    }
  }

  async function initProductsPage() {
    clearSharedCartDisplayKeys();
    cart = normalizeCartItems(readStoredCart());
    await syncProductsFromDatabase();
    await syncCartFromDatabase();
    applyFiltersAndSort();
    updateBadges();
    updateNav();
  }

  initProductsPage();


function openProductDetail(pid) {
    const product = PRODUCTS.find(p => p.id === pid);
    if (!product) return;
    const adminViewing = isAdminAccount();

    /* Instantiate a CartItem to show inheritance — lineTotal getter */
    const previewItem = new CartItem(product, 1);

    document.getElementById('pd-img-wrap').innerHTML = product.img
      ? `<img src="${product.img}" alt="${product.name}" class="pd-img">`
      : `<div class="pd-img-placeholder">${SVG.imgPlaceholder}</div>`;

    document.getElementById('pd-body').innerHTML = `
      <div class="pd-category">${categoryLabel(product.category)}</div>
      <div class="pd-name">${product.name}</div>

      <div class="pd-details">
        <div class="pd-row">
          <span class="pd-label">Product</span>
          <span class="pd-value">${product.name}</span>
        </div>
        <div class="pd-row">
          <span class="pd-label">Category</span>
          <span class="pd-value">${categoryLabel(product.category)}</span>
        </div>
        <div class="pd-row">
          <span class="pd-label">Price</span>
          <span class="pd-value pd-value-price">${product.formattedPrice}</span>
        </div>
        <div class="pd-row">
          <span class="pd-label">In Stock</span>
          <span class="pd-value pd-value-yes">Yes</span>
        </div>
        <div class="pd-row">
          <span class="pd-label">Line Total (qty: 1)</span>
          <span class="pd-value">${previewItem.formattedLineTotal}</span>
        </div>
      </div>

      <p class="pd-desc">${product.desc}</p>

      <button
        class="pd-add-btn ${adminViewing ? 'admin-disabled' : ''}"
        onclick="${adminViewing ? 'return false;' : `addToCart('${product.id}'); closeProductDetail();`}"
        ${adminViewing ? 'disabled' : ''}
      >
        ${SVG.cart} ${adminViewing ? 'View Only' : 'Add to Cart'}
      </button>`;

    document.getElementById('pd-overlay').classList.add('open');
    document.getElementById('pd-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeProductDetail() {
    document.getElementById('pd-overlay').classList.remove('open');
    document.getElementById('pd-modal').classList.remove('open');
    document.body.style.overflow = '';
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProductDetail(); });
</script>
<script src="js/notifications.js?v=20260615"></script>
</script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260608"></script>
</body>
</html>

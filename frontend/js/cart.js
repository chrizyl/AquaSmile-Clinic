// ── SESSION HELPER: Remove a key entirely from sessionStorage ──
Session.remove = function(key) {
  sessionStorage.removeItem(key);
};

// ── LOGOUT ──
function logout() {
  Cookie.remove('currentUser');   // Clear the logged-in user cookie
  Session.remove('cart');         // Remove cart data entirely from sessionStorage
  window.location.href = 'login.php';
}

// ── ADD TO CART ──
function addToCart(pid) {
  const user = Cookie.get('currentUser');
  if (!user) {
    window.location.href = 'login.php';
    return;
  }

  let cartItems = Session.get('cart') || [];
  const existing = cartItems.find(c => c.id === pid);

  if (existing) {
    existing.qty++;
  } else {
    cartItems.push({ id: pid, qty: 1 });
  }

  Session.set('cart', cartItems);
  updateCartBadge();

  const product = PRODUCTS.find(p => p.id === pid);
  if (product) showToast(product.name + ' added to cart.');
}

// ── CHANGE QUANTITY ──
function changeQty(pid, delta) {
  let cartItems = Session.get('cart') || [];
  const item = cartItems.find(c => c.id === pid);
  if (!item) return;

  item.qty += delta;

  if (item.qty <= 0) {
    cartItems = cartItems.filter(c => c.id !== pid);
  }

  Session.set('cart', cartItems);
  updateCartBadge();
  renderCart();
}

// ── REMOVE FROM CART ──
function removeFromCart(pid) {
  let cartItems = Session.get('cart') || [];
  cartItems = cartItems.filter(c => c.id !== pid);
  Session.set('cart', cartItems);
  updateCartBadge();
  renderCart();
}

// ── RENDER CART (used on cart.html) ──
function renderCart() {
  const list    = document.getElementById('cart-items-list');
  const empty   = document.getElementById('cart-empty');
  const footer  = document.getElementById('cart-footer');

  if (!list) return;

  const cartItems = Session.get('cart') || [];

  if (cartItems.length === 0) {
    list.innerHTML = '';
    if (empty)  empty.style.display  = '';
    if (footer) footer.style.display = 'none';
    return;
  }

  if (empty)  empty.style.display  = 'none';
  if (footer) footer.style.display = '';

  list.innerHTML = cartItems.map(c => {
    const product = PRODUCTS.find(p => p.id === c.id);
    if (!product) return '';

    return `
      <div class="cart-item">
        <div class="cart-item-img">
          <img src="${product.img || 'images/products/placeholder.png'}" alt="${product.name}">
        </div>
        <div class="cart-item-info">
          <div class="cart-item-name">${product.name}</div>
          <div class="cart-item-price">&#8369;${product.price.toLocaleString()} each</div>
        </div>
        <div class="cart-qty">
          <button class="qty-btn" onclick="changeQty('${c.id}', -1)">&#8722;</button>
          <span class="qty-val">${c.qty}</span>
          <button class="qty-btn" onclick="changeQty('${c.id}', 1)">&#43;</button>
        </div>
        <button class="cart-remove" onclick="removeFromCart('${c.id}')">&#10005;</button>
      </div>`;
  }).join('');

  // Totals
  const subtotal = cartItems.reduce((sum, c) => {
    const product = PRODUCTS.find(p => p.id === c.id);
    return sum + (product ? product.price * c.qty : 0);
  }, 0);

  const delivery = 120;
  const total    = subtotal + delivery;

  const totalEl = document.getElementById('cart-total-summary');
  if (totalEl) {
    totalEl.innerHTML = `
      <div class="confirm-row">
        <span>Subtotal</span>
        <span>&#8369;${subtotal.toLocaleString()}</span>
      </div>
      <div class="confirm-row">
        <span>Delivery</span>
        <span>&#8369;${delivery.toLocaleString()}</span>
      </div>
      <div class="confirm-row">
        <span style="font-weight:600;">Total</span>
        <span style="font-weight:600;color:var(--aqua-dark);">&#8369;${total.toLocaleString()}</span>
      </div>`;
  }
}

// ── CHECKOUT ──
function checkout() {
  Session.set('cart', []);
  updateCartBadge();
  renderCart();
  showToast('Order placed. Thank you for shopping at AquaSmile.');
}

// ── INIT (runs only on cart.html) ──
function initCart() {
  const user = Cookie.get('currentUser');
  if (!user) {
    window.location.href = 'login.php';
    return;
  }
  updateNav();
  renderCart();
}

// Auto-init if cart page elements exist
if (document.getElementById('cart-items-list')) {
  initCart();
}
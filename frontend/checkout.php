<!DOCTYPE html>
<html lang="en">
<?php
session_start();
include 'includes/admin-check.php';

if (isAdmin()) {
    header('Location: admin.php');
    exit;
}
?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AquaSmile — Checkout</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css?v=20260523" />
  <link rel="stylesheet" href="css/notifications.css?v=20260523" />

  <style>
    :root {
      --aqua:         #789a99;
      --aqua-dark:    #4e7170;
      --aqua-light:   #a8c4c3;
      --peach:        #e8c9b0;
      --peach-dark:   #d4a882;
      --peach-light:  #f5ede4;
      --text-dark:    #2c3e38;
      --text-mid:     #5a7068;
      --text-light:   #8fa89e;
      --glass-bg:     rgba(255,255,255,0.72);
      --glass-border: rgba(120,154,153,0.18);
      --shadow-card:  0 8px 40px rgba(78,113,112,0.10);
      --radius-lg:    18px;
      --radius-md:    12px;
      --radius-sm:    8px;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: linear-gradient(135deg, #eef5f4 0%, #faf4ef 50%, #f0f5f4 100%);
      min-height: 100vh;
      color: var(--text-dark);
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image:
        radial-gradient(circle at 20% 20%, rgba(120,154,153,0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(232,201,176,0.10) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }

    .checkout-wrap {
      max-width: 960px;
      margin: 0 auto;
      padding: 48px 40px 80px;
      position: relative;
      z-index: 1;
    }

    .checkout-page-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .checkout-page-sub {
      font-size: 0.85rem;
      color: var(--text-light);
      font-weight: 300;
      margin-bottom: 36px;
    }

    .checkout-grid {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 28px;
      align-items: start;
    }

    .co-card {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-radius: var(--radius-lg);
      padding: 32px;
      box-shadow: var(--shadow-card);
      margin-bottom: 20px;
    }

    .co-card:last-child { margin-bottom: 0; }

    .co-section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.35rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .co-section-sub {
      font-size: 0.82rem;
      color: var(--text-light);
      font-weight: 300;
      margin-bottom: 22px;
      line-height: 1.6;
    }

    .form-field { margin-bottom: 16px; }

    .form-label {
      display: block;
      font-size: 0.74rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-light);
      font-weight: 500;
      margin-bottom: 7px;
    }

    .form-input {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid rgba(120,154,153,0.25);
      border-radius: var(--radius-sm);
      background: rgba(255,255,255,0.6);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem;
      color: var(--text-dark);
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }

    .form-input::placeholder { color: var(--text-light); }

    .form-input:focus {
      border-color: var(--aqua);
      box-shadow: 0 0 0 3px rgba(120,154,153,0.12);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .payment-option {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px 18px;
      border: 1.5px solid rgba(120,154,153,0.2);
      border-radius: var(--radius-md);
      background: rgba(255,255,255,0.5);
      cursor: pointer;
      transition: all 0.22s;
      margin-bottom: 10px;
    }

    .payment-option:last-of-type { margin-bottom: 0; }

    .payment-option:hover {
      border-color: var(--aqua);
      background: rgba(120,154,153,0.05);
    }

    .payment-option.selected {
      border-color: var(--aqua);
      background: rgba(120,154,153,0.10);
      box-shadow: 0 0 0 3px rgba(120,154,153,0.12);
    }

    .payment-option input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }

    .pay-icon-wrap {
      width: 42px;
      height: 42px;
      border-radius: var(--radius-sm);
      background: rgba(120,154,153,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background 0.22s;
    }

    .payment-option.selected .pay-icon-wrap { background: var(--aqua); }

    .pay-icon-wrap svg {
      width: 22px;
      height: 22px;
      stroke: var(--aqua-dark);
      fill: none;
      stroke-width: 1.7;
      stroke-linecap: round;
      stroke-linejoin: round;
      transition: stroke 0.22s;
    }

    .payment-option.selected .pay-icon-wrap svg { stroke: #fff; }

    .pay-info { flex: 1; }

    .pay-name {
      font-size: 0.92rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 2px;
    }

    .pay-desc {
      font-size: 0.76rem;
      color: var(--text-light);
      font-weight: 300;
    }

    .pay-radio-indicator {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      border: 2px solid rgba(120,154,153,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all 0.2s;
    }

    .payment-option.selected .pay-radio-indicator {
      border-color: var(--aqua);
      background: var(--aqua);
    }

    .pay-radio-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #fff;
      opacity: 0;
      transition: opacity 0.2s;
    }

    .payment-option.selected .pay-radio-dot { opacity: 1; }

    .gcash-field,
    .card-fields {
      display: none;
      margin-top: 14px;
      padding-top: 14px;
      border-top: 1px solid rgba(120,154,153,0.12);
    }

    .gcash-field.visible,
    .card-fields.visible { display: block; }

    .checkout-sidebar { position: sticky; top: 24px; }

    .order-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 13px 0;
      border-bottom: 1px solid rgba(120,154,153,0.1);
    }

    .order-item:last-of-type { border-bottom: none; }

    .order-item-img {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-sm);
      background: rgba(120,154,153,0.1);
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .order-item-img svg {
      width: 24px;
      height: 24px;
      stroke: var(--aqua-dark);
      fill: none;
      stroke-width: 1.6;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .order-item-info { flex: 1; min-width: 0; }

    .order-item-name {
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .order-item-qty {
      font-size: 0.75rem;
      color: var(--text-light);
      font-weight: 300;
    }

    .order-item-price {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--aqua-dark);
      flex-shrink: 0;
    }

    .order-divider {
      height: 1px;
      background: rgba(120,154,153,0.12);
      margin: 14px 0;
    }

    .order-total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 0;
      font-size: 0.84rem;
    }

    .order-total-row .lbl { color: var(--text-light); }
    .order-total-row .val { color: var(--text-mid); font-weight: 500; }

    .order-grand-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 14px;
      margin-top: 8px;
      border-top: 1.5px solid rgba(120,154,153,0.2);
    }

    .grand-lbl {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-dark);
    }

    .grand-val {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--aqua-dark);
    }

    .btn-place-order {
      width: 100%;
      padding: 14px 24px;
      border: none;
      border-radius: var(--radius-md);
      background: var(--aqua);
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.93rem;
      font-weight: 500;
      letter-spacing: 0.04em;
      cursor: pointer;
      transition: all 0.22s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 20px;
    }

    .btn-place-order:hover {
      background: var(--aqua-dark);
      box-shadow: 0 6px 20px rgba(78,113,112,0.25);
      transform: translateY(-2px);
    }

    .btn-place-order:active { transform: translateY(0); }

    .btn-place-order svg {
      width: 17px;
      height: 17px;
      stroke: #fff;
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .btn-back {
      width: 100%;
      padding: 11px 24px;
      border: 1.5px solid rgba(120,154,153,0.3);
      border-radius: var(--radius-md);
      background: transparent;
      color: var(--aqua-dark);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.86rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 10px;
      text-decoration: none;
    }

    .btn-back:hover {
      background: rgba(120,154,153,0.06);
      border-color: var(--aqua);
    }

    .btn-back svg {
      width: 15px;
      height: 15px;
      stroke: var(--aqua-dark);
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .secure-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 14px;
      font-size: 0.71rem;
      color: var(--text-light);
      letter-spacing: 0.06em;
    }

    .secure-badge svg {
      width: 12px;
      height: 12px;
      stroke: var(--text-light);
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .popup-overlay {
      position: fixed;
      inset: 0;
      background: rgba(44,62,56,0.45);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }

    .popup-overlay.show {
      opacity: 1;
      pointer-events: all;
    }

    .popup-box {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(20px);
      border-radius: var(--radius-lg);
      padding: 44px 40px;
      max-width: 380px;
      width: 90%;
      text-align: center;
      box-shadow: 0 20px 60px rgba(44,62,56,0.18);
      transform: scale(0.9) translateY(20px);
      transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
    }

    .popup-overlay.show .popup-box {
      transform: scale(1) translateY(0);
    }

    .popup-check {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: rgba(120,154,153,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }

    .popup-check svg {
      width: 32px;
      height: 32px;
      stroke: var(--aqua-dark);
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .popup-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .popup-msg {
      font-size: 0.86rem;
      color: var(--text-mid);
      font-weight: 300;
      line-height: 1.7;
      margin-bottom: 28px;
    }

    .popup-progress {
      height: 3px;
      background: rgba(120,154,153,0.15);
      border-radius: 99px;
      overflow: hidden;
    }

    .popup-progress-bar {
      height: 100%;
      background: var(--aqua);
      border-radius: 99px;
      width: 0%;
      transition: width 2.5s linear;
    }

    .popup-redirect-note {
      font-size: 0.74rem;
      color: var(--text-light);
      margin-top: 10px;
    }

    /* ── GCASH RECEIPT UPLOAD ── */
    .upload-area {
      margin-top: 14px;
      border: 1.5px dashed rgba(120,154,153,0.35);
      border-radius: var(--radius-md);
      background: rgba(120,154,153,0.04);
      padding: 22px 16px;
      text-align: center;
      cursor: pointer;
      transition: all 0.22s;
      position: relative;
    }

    .upload-area:hover,
    .upload-area.dragover {
      border-color: var(--aqua);
      background: rgba(120,154,153,0.09);
    }

    .upload-area input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }

    .upload-icon {
      width: 36px;
      height: 36px;
      margin: 0 auto 10px;
      border-radius: 50%;
      background: rgba(120,154,153,0.12);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .upload-icon svg {
      width: 18px;
      height: 18px;
      stroke: var(--aqua-dark);
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .upload-label {
      font-size: 0.82rem;
      color: var(--text-mid);
      font-weight: 400;
    }

    .upload-label span {
      color: var(--aqua-dark);
      font-weight: 500;
      text-decoration: underline;
      text-underline-offset: 2px;
    }

    .upload-sub {
      font-size: 0.72rem;
      color: var(--text-light);
      margin-top: 4px;
      font-weight: 300;
    }

    /* Preview state */
    .upload-preview {
      display: none;
      margin-top: 12px;
      border-radius: var(--radius-md);
      overflow: hidden;
      position: relative;
    }

    .upload-preview.visible { display: block; }

    .upload-preview img {
      width: 100%;
      max-height: 200px;
      object-fit: cover;
      border-radius: var(--radius-md);
      border: 1px solid rgba(120,154,153,0.2);
      display: block;
    }

    .upload-preview-remove {
      position: absolute;
      top: 8px;
      right: 8px;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: rgba(44,62,56,0.65);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.18s;
    }

    .upload-preview-remove:hover { background: rgba(44,62,56,0.85); }

    .upload-preview-remove svg {
      width: 13px;
      height: 13px;
      stroke: #fff;
      fill: none;
      stroke-width: 2.2;
      stroke-linecap: round;
    }

    .upload-filename {
      display: none;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
      padding: 8px 12px;
      background: rgba(120,154,153,0.08);
      border-radius: var(--radius-sm);
      font-size: 0.78rem;
      color: var(--text-mid);
    }

    .upload-filename.visible { display: flex; }

    .upload-filename svg {
      width: 14px;
      height: 14px;
      stroke: var(--aqua-dark);
      fill: none;
      stroke-width: 1.8;
      flex-shrink: 0;
    }

    @media (max-width: 768px) {
      .checkout-wrap { padding: 28px 20px 60px; }
      .checkout-grid { grid-template-columns: 1fr; }
      .checkout-sidebar { position: static; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <div class="toast" id="toast"></div>

  <main class="checkout-wrap">

    <h1 class="checkout-page-title">Checkout</h1>
    <p class="checkout-page-sub">Review your order and complete your purchase.</p>

    <div class="checkout-grid">

      <div>

        <div class="co-card">
          <h2 class="co-section-title">Contact Information</h2>
          <p class="co-section-sub">Where should we send your order confirmation?</p>

          <div class="form-row">
            <div class="form-field">
              <label class="form-label" for="first-name">First Name</label>
              <input class="form-input" type="text" id="first-name" name="first_name" placeholder="Maria" autocomplete="given-name" />
            </div>
            <div class="form-field">
              <label class="form-label" for="last-name">Last Name</label>
              <input class="form-input" type="text" id="last-name" name="last_name" placeholder="Santos" autocomplete="family-name" />
            </div>
          </div>

          <div class="form-field">
            <label class="form-label" for="email">Email Address</label>
            <input class="form-input" type="email" id="email" name="email" placeholder="maria@email.com" autocomplete="email" />
          </div>

          <div class="form-field">
            <label class="form-label" for="phone">Phone Number</label>
            <input class="form-input" type="tel" id="phone" name="phone" placeholder="+63 9XX XXX XXXX" autocomplete="tel" />
          </div>
        </div>

        <div class="co-card">
          <h2 class="co-section-title">Delivery Address</h2>
          <p class="co-section-sub">Where should we deliver your order?</p>

          <div class="form-row">
            <div class="form-field">
              <label class="form-label" for="house-no">House No. / Unit No.</label>
              <input class="form-input" type="text" id="house-no" name="house_no" placeholder="1" autocomplete="address-line1" maxlength="50" />
            </div>
            <div class="form-field">
              <label class="form-label" for="street">Street</label>
              <input class="form-input" type="text" id="street" name="street" placeholder="Purok" autocomplete="address-line2" maxlength="150" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-field">
              <label class="form-label" for="barangay">Barangay</label>
              <input class="form-input" type="text" id="barangay" name="barangay" placeholder="Makiling" maxlength="100" />
            </div>
            <div class="form-field">
              <label class="form-label" for="city">City / Municipality</label>
              <input class="form-input" type="text" id="city" name="city" placeholder="Calamba" autocomplete="address-level2" maxlength="100" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-field">
              <label class="form-label" for="province">Province / Region</label>
              <input class="form-input" type="text" id="province" name="province" placeholder="Laguna" autocomplete="address-level1" maxlength="100" />
            </div>
            <div class="form-field">
              <label class="form-label" for="zip">ZIP Code</label>
              <input class="form-input" type="text" id="zip" name="zip" placeholder="4027" autocomplete="postal-code" maxlength="10" />
            </div>
          </div>

          <div class="form-field">
            <label class="form-label" for="notes">
              Order Notes
              <span style="color:var(--text-light);font-size:0.7rem;text-transform:none;letter-spacing:0;font-weight:300;"> (optional)</span>
            </label>
            <textarea class="form-input" id="notes" name="notes" rows="2" placeholder="Special delivery instructions…" style="resize:vertical;"></textarea>
          </div>
        </div>

        <div class="co-card">
          <h2 class="co-section-title">Payment Method</h2>
          <p class="co-section-sub">Choose how you'd like to pay for your order.</p>

          <div class="payment-option selected" data-method="cod" onclick="selectPayment(this)">
            <input type="radio" name="payment_method" value="cod" checked />
            <div class="pay-icon-wrap">
              <svg viewBox="0 0 24 24">
                <rect x="2" y="6" width="20" height="12" rx="2"/>
                <circle cx="12" cy="12" r="2.5"/>
                <path d="M6 12h.01M18 12h.01"/>
              </svg>
            </div>
            <div class="pay-info">
              <div class="pay-name">Cash on Delivery</div>
              <div class="pay-desc">Pay in cash when your order arrives</div>
            </div>
            <div class="pay-radio-indicator">
              <div class="pay-radio-dot"></div>
            </div>
          </div>

          <div class="payment-option" data-method="gcash" onclick="selectPayment(this)">
            <input type="radio" name="payment_method" value="gcash" />
            <div class="pay-icon-wrap">
              <svg viewBox="0 0 24 24">
                <rect x="5" y="2" width="14" height="20" rx="3"/>
                <path d="M9 7h6M12 17v.01"/>
              </svg>
            </div>
            <div class="pay-info">
              <div class="pay-name">GCash</div>
              <div class="pay-desc">Send payment via GCash e-wallet</div>
            </div>
            <div class="pay-radio-indicator">
              <div class="pay-radio-dot"></div>
            </div>
          </div>

          <div class="gcash-field" id="gcash-field">
            <label class="form-label" for="gcash-number">Your GCash Number</label>
            <input class="form-input" type="tel" id="gcash-number" name="gcash_number" placeholder="09XX XXX XXXX" />
            <p style="font-size:0.75rem;color:var(--text-light);margin-top:7px;font-weight:300;">
              Our GCash number will be sent to your email after placing your order.
            </p>

            <label class="form-label" style="margin-top:14px;">
              Payment Receipt
              <span style="color:var(--text-light);font-size:0.7rem;text-transform:none;letter-spacing:0;font-weight:300;"> (screenshot of your GCash transfer)</span>
            </label>

            <div class="upload-area" id="gcash-upload-area"
                 ondragover="handleDragOver(event)"
                 ondragleave="handleDragLeave(event)"
                 ondrop="handleDrop(event)">
              <input type="file" id="gcash-receipt" name="gcash_receipt" accept="image/*" onchange="handleReceiptUpload(this)" />
              <div class="upload-icon">
                <svg viewBox="0 0 24 24">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <p class="upload-label"><span>Click to upload</span> or drag & drop</p>
              <p class="upload-sub">PNG, JPG, or JPEG · Max 5MB</p>
            </div>

            <div class="upload-preview" id="gcash-preview">
              <img id="gcash-preview-img" src="" alt="GCash receipt preview" />
              <button class="upload-preview-remove" type="button" onclick="removeReceipt()" title="Remove">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <div class="upload-filename" id="gcash-filename">
              <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              <span id="gcash-filename-text"></span>
            </div>
          </div>

          <div class="payment-option" data-method="card" onclick="selectPayment(this)">
            <input type="radio" name="payment_method" value="card" />
            <div class="pay-icon-wrap">
              <svg viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2"/>
                <path d="M2 10h20M6 15h4"/>
              </svg>
            </div>
            <div class="pay-info">
              <div class="pay-name">Card Payment</div>
              <div class="pay-desc">Visa, Mastercard, or JCB</div>
            </div>
            <div class="pay-radio-indicator">
              <div class="pay-radio-dot"></div>
            </div>
          </div>

          <div class="card-fields" id="card-fields">
            <div class="form-field">
              <label class="form-label" for="card-number">Card Number</label>
              <input class="form-input" type="text" id="card-number" name="card_number" placeholder="•••• •••• •••• ••••" maxlength="19" />
            </div>
            <div class="form-row">
              <div class="form-field">
                <label class="form-label" for="card-expiry">Expiry Date</label>
                <input class="form-input" type="text" id="card-expiry" name="card_expiry" placeholder="MM / YY" maxlength="7" />
              </div>
              <div class="form-field">
                <label class="form-label" for="card-cvv">CVV</label>
                <input class="form-input" type="text" id="card-cvv" name="card_cvv" placeholder="•••" maxlength="4" />
              </div>
            </div>
            <div class="form-field">
              <label class="form-label" for="card-name">Name on Card</label>
              <input class="form-input" type="text" id="card-name" name="card_name" placeholder="MARIA SANTOS" />
            </div>
          </div>

        </div>

      </div>

      <div class="checkout-sidebar">
        <div class="co-card">
          <h2 class="co-section-title">Order Summary</h2>
          <p class="co-section-sub">Items in your cart</p>

          <div id="co-order-items">
            <!-- Populated by JS from localStorage aqCart -->
          </div>

          <div class="order-divider"></div>

          <div id="co-order-totals">
            <!-- Populated by JS -->
          </div>

          <button class="btn-place-order" id="place-order-btn" onclick="placeOrder()">
            <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            Place Order
          </button>

          <a href="products.php" class="btn-back">
            <svg viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Continue Shopping
          </a>

          <div class="secure-badge">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Secured checkout
          </div>

        </div>
      </div>

    </div>

  </main>

  <div class="popup-overlay" id="success-popup" role="dialog" aria-modal="true" aria-labelledby="popup-heading">
    <div class="popup-box">
      <div class="popup-check">
        <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
      </div>
      <h2 class="popup-title" id="popup-heading">Order Placed!</h2>
      <p class="popup-msg">
        Your order has been received.<br />
        A confirmation will be sent to your email shortly.
      </p>
      <div class="popup-progress">
        <div class="popup-progress-bar" id="progress-bar"></div>
      </div>
      <p class="popup-redirect-note">Redirecting you to the homepage…</p>
    </div>
  </div>

  <script src="js/main.js?v=20260614b"></script>

  <script>
    function selectPayment(el) {
      document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
      el.classList.add('selected');
      const radio = el.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;

      document.getElementById('gcash-field').classList.remove('visible');
      document.getElementById('card-fields').classList.remove('visible');

      const method = el.dataset.method;
      if (method === 'gcash') document.getElementById('gcash-field').classList.add('visible');
      if (method === 'card')  document.getElementById('card-fields').classList.add('visible');
    }

    document.getElementById('card-number').addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').substring(0, 16);
      this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });

    document.getElementById('card-expiry').addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
      this.value = v;
    });

    document.getElementById('phone').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('gcash-number').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('zip').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    /* ── CART DATA (from localStorage, same key as cart.php) ── */
    const checkoutCart = JSON.parse(localStorage.getItem('aqCart') || '[]');

    /* ── LOGIN + EMPTY CART GUARD ── */
    (function() {
      if (!Cookie.get('currentUser')) {
        window.location.href = 'login.php';
        return;
      }
      if (!checkoutCart.length) {
        window.location.href = 'cart.php';
        return;
      }
    })();

    async function autofillCheckoutProfile() {
      try {
        const data = await apiGet('user_account');
        const user = data.user || {};
        const fields = {
          'first-name': user.first_name,
          'last-name': user.last_name,
          email: user.email,
          phone: user.phone,
          'house-no': user.house_no,
          street: user.street,
          barangay: user.barangay,
          city: user.city,
          province: user.province,
          zip: user.zip_code,
        };

        Object.entries(fields).forEach(([id, value]) => {
          const input = document.getElementById(id);
          if (input && !input.value && value) input.value = value;
        });
      } catch (err) {
        console.warn('Unable to auto-fill checkout profile:', err.message);
      }
    }

    autofillCheckoutProfile();

    /* ── RENDER ORDER SUMMARY SIDEBAR ── */
    function renderOrderSummary() {
      const itemsEl  = document.getElementById('co-order-items');
      const totalsEl = document.getElementById('co-order-totals');
      if (!itemsEl || !totalsEl) return;

      const imgSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`;

      itemsEl.innerHTML = checkoutCart.map(item => `
        <div class="order-item">
          <div class="order-item-img">
            ${item.img
              ? `<img src="${item.img}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">`
              : imgSVG}
          </div>
          <div class="order-item-info">
            <div class="order-item-name">${item.name}</div>
            <div class="order-item-qty">Qty: ${item.qty} × ₱${item.price.toLocaleString()}</div>
          </div>
          <div class="order-item-price">₱${(item.price * item.qty).toLocaleString()}</div>
        </div>`).join('');

      const subtotal = checkoutCart.reduce((s, i) => s + i.price * i.qty, 0);
      const total    = subtotal; // free delivery

      totalsEl.innerHTML = `
        <div class="order-total-row">
          <span class="lbl">Subtotal</span>
          <span class="val">₱${subtotal.toLocaleString()}</span>
        </div>
        <div class="order-total-row">
          <span class="lbl">Delivery</span>
          <span class="val">Free</span>
        </div>
        <div class="order-total-row">
          <span class="lbl">Discount</span>
          <span class="val" style="color:var(--peach-dark);">— ₱0</span>
        </div>
        <div class="order-grand-row">
          <span class="grand-lbl">Total</span>
          <span class="grand-val">₱${total.toLocaleString()}</span>
        </div>`;
    }

    renderOrderSummary();

    function collectFormData() {
      const firstName = document.getElementById('first-name').value.trim();
      const lastName  = document.getElementById('last-name').value.trim();
      const email     = document.getElementById('email').value.trim();
      const phone     = document.getElementById('phone').value.trim();
      const houseNo   = document.getElementById('house-no').value.trim();
      const street    = document.getElementById('street').value.trim();
      const barangay  = document.getElementById('barangay').value.trim();
      const city      = document.getElementById('city').value.trim();
      const province  = document.getElementById('province').value.trim();
      const zip       = document.getElementById('zip').value.trim();
      const notes     = document.getElementById('notes').value.trim();
      const payment   = document.querySelector('input[name="payment_method"]:checked')?.value || 'cod';

      if (!firstName || !lastName) {
        alert('Please enter your full name.');
        document.getElementById('first-name').focus();
        return null;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email || !emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        document.getElementById('email').focus();
        return null;
      }

      if (!phone) {
        alert('Please enter your phone number.');
        document.getElementById('phone').focus();
        return null;
      }

      if (!houseNo || !street || !barangay || !city || !province || !zip) {
        alert('Please complete your delivery address.');
        const firstEmpty = ['house-no', 'street', 'barangay', 'city', 'province', 'zip']
          .map(id => document.getElementById(id))
          .find(input => !input.value.trim());
        firstEmpty?.focus();
        return null;
      }

      if (payment === 'gcash') {
        const gcashNum = document.getElementById('gcash-number').value.trim();
        if (!gcashNum) {
          alert('Please enter your GCash number.');
          document.getElementById('gcash-number').focus();
          return null;
        }
        const receiptFile = document.getElementById('gcash-receipt').files[0];
        if (!receiptFile) {
          alert('Please upload your GCash payment receipt.');
          document.getElementById('gcash-upload-area').scrollIntoView({ behavior: 'smooth', block: 'center' });
          return null;
        }
      }

      if (payment === 'card') {
        const cardNum  = document.getElementById('card-number').value.trim();
        const cardExp  = document.getElementById('card-expiry').value.trim();
        const cardCvv  = document.getElementById('card-cvv').value.trim();
        const cardName = document.getElementById('card-name').value.trim();
        if (!cardNum || !cardExp || !cardCvv || !cardName) {
          alert('Please complete your card details.');
          document.getElementById('card-number').focus();
          return null;
        }
      }

      const subtotal = checkoutCart.reduce((s, i) => s + i.price * i.qty, 0);

      return {
        first_name:     firstName,
        last_name:      lastName,
        email:          email,
        phone:          phone,
        house_no:       houseNo,
        street:         street,
        barangay:       barangay,
        city:           city,
        province:       province,
        zip:            zip,
        notes:          notes,
        payment_method: payment,
        gcash_number:   payment === 'gcash' ? document.getElementById('gcash-number').value.trim() : null,
        items:          checkoutCart.map(i => ({ name: i.name, qty: i.qty, unit_price: i.price })),
        subtotal:       subtotal,
        shipping:       0,
        discount:       0,
        total:          subtotal
      };
    }

    async function placeOrder() {
      const formData = collectFormData();
      if (!formData) return;
      try {
        const currentUser = Cookie.get('currentUser');
        await apiRequest('create_order', {
          userId: currentUser ? currentUser.id : null,
          first_name: formData.first_name,
          last_name: formData.last_name,
          email: formData.email,
          phone: formData.phone,
          house_no: formData.house_no,
          street: formData.street,
          barangay: formData.barangay,
          city: formData.city,
          province: formData.province,
          zip: formData.zip,
          notes: formData.notes,
          paymentMethod: formData.payment_method,
          gcash_number: formData.gcash_number,
          items: checkoutCart.map(i => ({
            id: i.id,
            name: i.name,
            qty: i.qty,
            price: i.price
          })),
          total: formData.total,
        });
      } catch (err) {
        alert(err.message || 'Unable to place your order. Please try again.');
        return;
      }
      showSuccessPopup();
    }

    function showSuccessPopup() {
      const overlay = document.getElementById('success-popup');
      const bar     = document.getElementById('progress-bar');

      /* ── CART RESET ── Clear cart using the key defined in cart.html */
      try { localStorage.removeItem('aqCart'); } catch(e) {}

      overlay.classList.add('show');

      requestAnimationFrame(() => {
        bar.style.width = '100%';
      });

      setTimeout(() => {
        window.location.href = 'index.php';
      }, 2800);
    }

    function handleReceiptUpload(input) {
      const file = input.files[0];
      if (!file) return;
      applyReceiptFile(file);
    }

    function applyReceiptFile(file) {
      if (!file.type.startsWith('image/')) {
        alert('Please upload an image file (PNG, JPG, or JPEG).');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        alert('File is too large. Please upload an image under 5MB.');
        return;
      }

      const reader = new FileReader();
      reader.onload = function(e) {
        const preview  = document.getElementById('gcash-preview');
        const previewImg = document.getElementById('gcash-preview-img');
        const filename = document.getElementById('gcash-filename');
        const filenameText = document.getElementById('gcash-filename-text');
        const uploadArea = document.getElementById('gcash-upload-area');

        previewImg.src = e.target.result;
        preview.classList.add('visible');
        filenameText.textContent = file.name;
        filename.classList.add('visible');
        uploadArea.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }

    function removeReceipt() {
      document.getElementById('gcash-receipt').value = '';
      document.getElementById('gcash-preview').classList.remove('visible');
      document.getElementById('gcash-preview-img').src = '';
      document.getElementById('gcash-filename').classList.remove('visible');
      document.getElementById('gcash-upload-area').style.display = '';
    }

    function handleDragOver(e) {
      e.preventDefault();
      e.stopPropagation();
      e.currentTarget.classList.add('dragover');
    }

    function handleDragLeave(e) {
      e.currentTarget.classList.remove('dragover');
    }

    function handleDrop(e) {
      e.preventDefault();
      e.stopPropagation();
      e.currentTarget.classList.remove('dragover');
      const file = e.dataTransfer.files[0];
      if (!file) return;
      const input = document.getElementById('gcash-receipt');
      const dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      applyReceiptFile(file);
    }
  </script>

<script src="js/notifications.js?v=20260615"></script>
</script>

  <div id="site-footer-root"></div>
  <script src="js/footer.js?v=20260608"></script>
</body>
</html>

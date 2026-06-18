<?php
require_once 'includes/session-init.php';
include 'includes/admin-check.php';
require_once 'includes/navbar-auth.php';

no_cache_headers();

requirePatientPage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/svg+xml" href="images/AquaSmile_Logo.svg" />
  <title>AquaSmile — Checkout</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css?v=20260523" />
  <link rel="stylesheet" href="css/notifications.css?v=20260616a" />

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
      padding: 108px 40px 80px;
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
      background: linear-gradient(135deg, rgba(255,255,255,0.78), rgba(120,154,153,0.12));
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      box-shadow: inset 0 0 0 1px rgba(120,154,153,0.14);
    }

    .order-item-img img {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: cover;
      object-position: center;
      border-radius: var(--radius-sm);
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

    .checkout-promo-row {
      display: flex;
      gap: 8px;
      margin-bottom: 14px;
    }

    .checkout-promo-row .form-input {
      min-width: 0;
      text-transform: uppercase;
    }

    .btn-apply-promo {
      flex: 0 0 auto;
      padding: 0 14px;
      border: none;
      border-radius: var(--radius-sm);
      background: var(--aqua);
      color: #fff;
      font: 600 0.78rem 'DM Sans', sans-serif;
      cursor: pointer;
      transition: background 0.2s, transform 0.18s;
    }

    .btn-apply-promo:hover {
      background: var(--aqua-dark);
      transform: translateY(-1px);
    }

    .checkout-promo-message {
      min-height: 18px;
      margin: -4px 0 12px;
      font-size: 0.74rem;
      color: var(--text-light);
    }

    .checkout-promo-message.success { color: var(--aqua-dark); }
    .checkout-promo-message.error { color: #c0392b; }

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

    .popup-redirect-note {
      font-size: 0.74rem;
      color: var(--text-light);
      margin-top: 10px;
    }

    /* ── SUCCESS POPUP ACTIONS (rate / skip) ── */
    .popup-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 6px;
    }

    .btn-rate {
      padding: 13px 20px;
      border: none;
      border-radius: var(--radius-md);
      background: var(--aqua);
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

    .btn-rate:hover {
      background: var(--aqua-dark);
      transform: translateY(-1px);
    }

    .btn-rate svg {
      width: 16px;
      height: 16px;
      fill: #fff;
      stroke: none;
    }

    .btn-skip {
      padding: 11px 20px;
      border: 1.5px solid rgba(120,154,153,0.3);
      border-radius: var(--radius-md);
      background: transparent;
      color: var(--text-mid);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.87rem;
      font-weight: 400;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-skip:hover {
      background: rgba(120,154,153,0.07);
      border-color: var(--aqua);
    }

    /* ── RATING POPUP ── */
    .rating-box {
      max-width: 440px;
      padding: 40px 38px 34px;
      background: rgba(255,255,255,0.94);
      border: 1px solid rgba(255,255,255,0.82);
      border-radius: 24px;
      box-shadow: 0 24px 70px rgba(44,62,56,0.18), 0 8px 24px rgba(120,154,153,0.12);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .rating-box .popup-title {
      font-size: 1.65rem;
      line-height: 1.12;
      margin-bottom: 10px;
    }

    .rating-box .popup-msg {
      max-width: 310px;
      margin: 0 auto 24px !important;
      color: var(--text-mid);
      line-height: 1.65;
    }

    .rating-stars {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin: 0 auto 18px;
      padding: 14px 16px;
      width: fit-content;
      border-radius: 999px;
      background: rgba(120,154,153,0.07);
      border: 1px solid rgba(120,154,153,0.12);
    }

    .star-btn {
      width: 42px;
      height: 42px;
      background: rgba(255,255,255,0.86);
      border: 1px solid rgba(120,154,153,0.14);
      border-radius: 50%;
      cursor: pointer;
      padding: 6px;
      display: grid;
      place-items: center;
      transition: transform 0.15s, border-color 0.18s, box-shadow 0.18s, background 0.18s;
    }

    .star-btn:hover {
      transform: translateY(-2px);
      border-color: rgba(212,168,130,0.55);
      box-shadow: 0 8px 18px rgba(212,168,130,0.16);
    }
    .star-btn:hover svg {
      fill: var(--peach);
      stroke: var(--peach-dark);
    }

    .star-btn svg {
      width: 28px;
      height: 28px;
      fill: rgba(120,154,153,0.14);
      stroke: rgba(78,113,112,0.72);
      stroke-width: 1.4;
      transition: fill 0.18s, stroke 0.18s, transform 0.18s;
    }

    .star-btn.active svg,
    .star-btn.hovered svg {
      fill: var(--peach);
      stroke: var(--peach-dark);
      transform: scale(1.03);
    }

    .star-btn.active svg {
      fill: var(--peach-dark);
      stroke: #b98266;
    }

    .star-btn.active {
      background: #fff8f2;
      border-color: rgba(212,168,130,0.48);
      box-shadow: 0 8px 20px rgba(212,168,130,0.14);
    }

    .rating-labels {
      display: flex;
      justify-content: space-between;
      font-size: 0.72rem;
      color: var(--text-light);
      font-weight: 300;
      margin: -6px auto 24px;
      padding: 0 8px;
      max-width: 300px;
    }

    .rating-aspects {
      display: flex;
      flex-wrap: wrap;
      gap: 9px;
      justify-content: center;
      margin-bottom: 20px;
    }

    .aspect-chip {
      padding: 8px 14px;
      border-radius: 99px;
      border: 1px solid rgba(120,154,153,0.22);
      background: rgba(255,255,255,0.78);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.78rem;
      color: var(--text-mid);
      cursor: pointer;
      transition: background 0.18s, border-color 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
      user-select: none;
    }

    .aspect-chip:hover {
      border-color: var(--aqua);
      background: rgba(120,154,153,0.06);
      transform: translateY(-1px);
    }

    .aspect-chip.selected {
      background: rgba(120,154,153,0.14);
      border-color: var(--aqua);
      color: var(--aqua-dark);
      font-weight: 600;
      box-shadow: 0 6px 16px rgba(120,154,153,0.12);
    }

    .rating-textarea {
      width: 100%;
      padding: 14px 16px;
      border: 1px solid rgba(120,154,153,0.24);
      border-radius: 16px;
      background: rgba(250,253,252,0.9);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.86rem;
      color: var(--text-dark);
      resize: vertical;
      min-height: 96px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
      margin-bottom: 20px;
      line-height: 1.55;
    }

    .rating-textarea::placeholder { color: var(--text-light); }

    .rating-textarea:focus {
      border-color: var(--aqua);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(120,154,153,0.12);
    }

    .btn-submit-rating {
      width: 100%;
      padding: 14px 22px;
      border: none;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--aqua), var(--aqua-dark));
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.92rem;
      font-weight: 600;
      cursor: pointer;
      transition: box-shadow 0.22s, transform 0.18s, opacity 0.18s;
      margin-bottom: 12px;
      box-shadow: 0 12px 24px rgba(78,113,112,0.22);
    }

    .btn-submit-rating:hover {
      transform: translateY(-1px);
      box-shadow: 0 16px 28px rgba(78,113,112,0.26);
    }

    .btn-submit-rating:disabled {
      background: rgba(120,154,153,0.38);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .rating-skip-link {
      font-size: 0.79rem;
      color: var(--text-light);
      cursor: pointer;
      text-decoration: underline;
      text-underline-offset: 3px;
      background: none;
      border: none;
      font-family: 'DM Sans', sans-serif;
      transition: color 0.18s;
      padding: 4px 8px;
    }

    .rating-skip-link:hover { color: var(--text-mid); }

    .rating-thankyou { display: none; }
    .rating-thankyou.show { display: block; }
    .rating-form-content.hide { display: none; }

    .thankyou-emoji {
      font-size: 2.5rem;
      margin-bottom: 12px;
    }

    @media (max-width: 480px) {
      .popup-box { padding: 32px 22px 28px; }
      .star-btn svg { width: 30px; height: 30px; }
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

    @media (max-width: 480px) {
      .checkout-wrap { padding-inline: 12px; }
      .co-card { padding: 20px 16px; border-radius: 14px; }
      .checkout-page-title { font-size: 1.65rem; }
      .payment-option { align-items: flex-start; gap: 10px; padding: 13px; }
      .checkout-promo-row { flex-direction: column; }
      .btn-apply-promo { min-height: 40px; }
      .order-item { align-items: flex-start; }
      .order-item-name { white-space: normal; }
      .popup-box { width: 92vw; padding: 30px 18px; }
    }
  </style>
</head>
<body>

  <div class="toast" id="toast"></div>

  <nav id="main-nav">
    <div class="nav-logo">
      <img src="images/AquaSmile_Logo.svg" alt="AquaSmile" class="nav-logo-img" />
      <span>AquaSmile</span>
    </div>
    <div class="nav-links" id="nav-links">
      <button class="nav-btn" onclick="window.location.href='index.php'">Home</button>
      <button class="nav-btn" onclick="window.location.href='dentists.php'">Our Dentists</button>
      <button class="nav-btn" onclick="window.location.href='services.php'">Services</button>
      <button class="nav-btn active" onclick="window.location.href='products.php'">Shop</button>
      <button class="nav-btn" id="nav-book-btn" onclick="window.location.href='booking.php'" <?php echo nav_is_patient() ? '' : 'style="display:none"'; ?>>Book Appointment</button>
      <button class="nav-cart-btn" onclick="window.location.href='cart.php'">Cart</button>
      <?php render_nav_auth(); ?>
    </div>
  </nav>

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
              <input class="form-input" type="text" id="first-name" name="first_name" placeholder="Maria" autocomplete="given-name" pattern="[A-Za-z' -]+" title="Only letters are allowed." />
            </div>
            <div class="form-field">
              <label class="form-label" for="last-name">Last Name</label>
              <input class="form-input" type="text" id="last-name" name="last_name" placeholder="Santos" autocomplete="family-name" pattern="[A-Za-z' -]+" title="Only letters are allowed." />
            </div>
          </div>

          <div class="form-field">
            <label class="form-label" for="email">Email Address</label>
            <input class="form-input" type="email" id="email" name="email" placeholder="maria@email.com" autocomplete="email" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="Please enter a valid email address." />
          </div>

          <div class="form-field">
            <label class="form-label" for="phone">Phone Number</label>
            <input class="form-input" type="tel" id="phone" name="phone" placeholder="09672547242" autocomplete="tel" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" title="Please enter a valid 11-digit phone number." />
          </div>
        </div>

        <div class="co-card">
          <h2 class="co-section-title">Delivery Address</h2>
          <p class="co-section-sub">Where should we deliver your order?</p>

          <div class="form-row">
            <div class="form-field">
              <label class="form-label" for="house-no">House No. / Unit No.</label>
              <input class="form-input" type="text" id="house-no" name="house_no" placeholder="1" autocomplete="address-line1" inputmode="numeric" pattern="[0-9/]+" maxlength="50" />
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
            <input class="form-input" type="tel" id="gcash-number" name="gcash_number" placeholder="09672547242" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" />
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
              <input type="file" id="gcash-receipt" name="gcash_receipt" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" onchange="handleReceiptUpload(this)" />
              <div class="upload-icon">
                <svg viewBox="0 0 24 24">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <p class="upload-label"><span>Click to upload</span> or drag & drop</p>
              <p class="upload-sub">PNG, JPG, JPEG, or WEBP · Max 5MB</p>
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

          <div class="checkout-promo-row">
            <input class="form-input" type="text" id="checkout-promo-code" placeholder="Promo code" maxlength="32" />
            <button class="btn-apply-promo" type="button" onclick="applyCheckoutPromo()">Apply</button>
          </div>
          <div class="checkout-promo-message" id="checkout-promo-message"></div>

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
      <div class="popup-actions">
        <button class="btn-rate" onclick="showRatingPopup()">
          <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
          Rate Your Experience
        </button>
        <button class="btn-skip" onclick="finishCheckoutPopups()">Maybe Later</button>
      </div>
    </div>
  </div>

  <div class="popup-overlay" id="rating-popup" role="dialog" aria-modal="true" aria-labelledby="rating-popup-heading">
    <div class="popup-box rating-box">

      <div class="rating-form-content" id="rating-form-content">
        <h2 class="popup-title" id="rating-popup-heading">How was your shopping experience?</h2>
        <p class="popup-msg" style="margin-bottom:18px;">Your feedback helps us improve AquaSmile for everyone.</p>

        <div class="rating-stars" id="rating-stars">
          <button class="star-btn" data-star="1" onclick="setStarRating(1)">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
          </button>
          <button class="star-btn" data-star="2" onclick="setStarRating(2)">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
          </button>
          <button class="star-btn" data-star="3" onclick="setStarRating(3)">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
          </button>
          <button class="star-btn" data-star="4" onclick="setStarRating(4)">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
          </button>
          <button class="star-btn" data-star="5" onclick="setStarRating(5)">
            <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
          </button>
        </div>
        <div class="rating-labels">
          <span>Poor</span>
          <span>Excellent</span>
        </div>

        <div class="rating-aspects" id="rating-aspects">
          <div class="aspect-chip" data-aspect="Easy Checkout" onclick="toggleAspect(this)">Easy Checkout</div>
          <div class="aspect-chip" data-aspect="Good Selection" onclick="toggleAspect(this)">Good Selection</div>
          <div class="aspect-chip" data-aspect="Fast Delivery" onclick="toggleAspect(this)">Fast Delivery</div>
          <div class="aspect-chip" data-aspect="Great Value" onclick="toggleAspect(this)">Great Value</div>
          <div class="aspect-chip" data-aspect="Helpful Support" onclick="toggleAspect(this)">Helpful Support</div>
        </div>

        <textarea class="rating-textarea" id="rating-comment" placeholder="Tell us more about your experience (optional)..."></textarea>

        <button class="btn-submit-rating" id="rating-submit-btn" onclick="submitRating()" disabled>Submit Feedback</button>
        <button class="rating-skip-link" onclick="finishCheckoutPopups()">Skip for now</button>
      </div>

      <div class="rating-thankyou" id="rating-thankyou">
        <div class="thankyou-emoji">🛍️✨</div>
        <h2 class="popup-title">Thank You!</h2>
        <p class="popup-msg">We appreciate you taking the time to share your feedback with us.</p>
        <button class="btn-submit-rating" onclick="finishCheckoutPopups()">Done</button>
      </div>

    </div>
  </div>

  <script src="js/main.js?v=20260616a"></script>

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

    document.getElementById('card-cvv').addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    document.getElementById('card-expiry').addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
      this.value = v;
    });

    document.getElementById('phone').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });

    document.getElementById('gcash-number').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });

    document.getElementById('zip').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    ['first-name', 'last-name'].forEach(id => {
      document.getElementById(id).addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-z' -]/g, '');
      });
    });

    document.getElementById('house-no').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9/]/g, '');
    });

    /* ── CART DATA (loaded from the logged-in user's server cart) ── */
    let checkoutCart = [];
    let appliedCheckoutPromo = null;
    let checkoutSelectedIds = [];

    /* ── LOGIN + EMPTY CART GUARD ── */
    (function() {
      if (!Cookie.get('currentUser')) {
        window.location.href = 'login.php';
        return;
      }
    })();

    function normalizeProductId(pid) {
      const text = String(pid || '');
      const match = text.match(/^P?(\d+)$/i);
      return match ? match[1] : text;
    }

    function currentUserCartCacheKey() {
      const user = Cookie.get('currentUser');
      return user ? 'aqCart_user_' + user.id : null;
    }

    const checkoutProductFallbacks = {
      1: 'images/toothbrush.avif',
      2: 'images/toothpaste.jpg',
      3: 'images/floss.jpg',
      4: 'images/mouthwash.jpg',
      5: 'images/whitening strips.jpg',
      6: 'images/scraper set.jpg',
      7: 'images/gum gel.png',
      8: 'images/bamboo toothbrush.webp',
      9: 'images/elite flosser.jpg',
      10: 'images/enamel repair.jpg',
      11: 'images/charcoal kit.png',
      12: 'images/dental kit.jpg',
    };

    const checkoutImagePlaceholder = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`;

    function checkoutDefaultImageForItem(item) {
      return checkoutProductFallbacks[normalizeProductId(item.id)] || '';
    }

    function checkoutImageForItem(item) {
      const imagePath = String(item.image_path || item.imagePath || item.img || item.photo || '').trim();
      return imagePath || checkoutDefaultImageForItem(item);
    }

    function showCheckoutImageFallback(img) {
      const fallback = String(img.dataset.fallback || '').trim();
      if (fallback) {
        img.dataset.fallback = '';
        img.src = fallback;
        return;
      }
      const wrap = img.closest('.order-item-img');
      if (wrap) wrap.innerHTML = checkoutImagePlaceholder;
    }

    function checkoutImageMarkup(item) {
      const src = checkoutImageForItem(item);
      if (!src) return checkoutImagePlaceholder;

      const fallback = checkoutDefaultImageForItem(item);
      const fallbackAttr = fallback && fallback !== src ? ` data-fallback="${fallback}"` : '';
      return `<img src="${src}" alt="${item.name}"${fallbackAttr} onerror="showCheckoutImageFallback(this)">`;
    }

    async function loadCheckoutCart() {
      checkoutSelectedIds = readCheckoutSelectedIds();
      if (!checkoutSelectedIds.length) {
        window.location.href = 'cart.php';
        return false;
      }
      try {
        const response = await fetch(new URL('../backend/api/index.php', window.location.href).pathname + '?action=cart_items', { cache: 'no-store' });
        const payload = await response.json();
        if (!response.ok || !payload.ok) throw new Error(payload.message || 'Cart load failed.');

        checkoutCart = (payload.cartItems || []).map(item => ({
          id: String(item.product_id),
          name: item.name || 'Product',
          price: Number(item.price || 0),
          image_path: item.image_path || '',
          img: checkoutImageForItem({
            id: item.product_id,
            image_path: item.image_path,
            img: item.img,
            photo: item.photo,
          }),
          qty: Number(item.quantity || 1),
        }));

        const key = currentUserCartCacheKey();
        if (key) localStorage.setItem(key, JSON.stringify(checkoutCart));
      } catch (err) {
        console.warn('Checkout cart load failed:', err.message);
        const key = currentUserCartCacheKey();
        try { checkoutCart = key ? JSON.parse(localStorage.getItem(key) || '[]') : []; }
        catch { checkoutCart = []; }
      }

      checkoutCart = checkoutCart.map(item => ({
        ...item,
        id: normalizeProductId(item.id),
        qty: Math.max(1, Number(item.qty || 1)),
        price: Number(item.price || 0),
      })).filter(item => checkoutSelectedIds.includes(String(item.id)));

      if (!checkoutCart.length) {
        window.location.href = 'cart.php';
        return false;
      }

      return true;
    }

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

    /* ── RENDER ORDER SUMMARY SIDEBAR ── */
    function checkoutSubtotal() {
      return checkoutCart.reduce((s, i) => s + i.price * i.qty, 0);
    }

    function readCheckoutSelectedIds() {
      try {
        return JSON.parse(sessionStorage.getItem('aqsmile_checkout_selected_ids') || '[]').map(id => String(normalizeProductId(id)));
      } catch {
        return [];
      }
    }

    function formatCheckoutMoney(amount) {
      return '₱' + Number(amount || 0).toLocaleString('en-PH', { maximumFractionDigits: 2 });
    }

    function setCheckoutPromoMessage(message, type = '') {
      const el = document.getElementById('checkout-promo-message');
      if (!el) return;
      el.textContent = message;
      el.className = 'checkout-promo-message ' + type;
    }

    async function applyCheckoutPromo() {
      const input = document.getElementById('checkout-promo-code');
      const code = input ? input.value.trim().toUpperCase() : '';
      if (!code) {
        appliedCheckoutPromo = null;
        setCheckoutPromoMessage('Invalid or expired promo code.', 'error');
        renderOrderSummary();
        return;
      }

      try {
        const data = await apiRequest('validate_promo', {
          promo_code: code,
          target: 'shop',
          subtotal: checkoutSubtotal(),
        });
        appliedCheckoutPromo = data.promo || null;
        if (input) input.value = code;
        setCheckoutPromoMessage('Promo code applied successfully.', 'success');
      } catch (err) {
        appliedCheckoutPromo = null;
        setCheckoutPromoMessage('Invalid or expired promo code.', 'error');
      }
      renderOrderSummary();
    }

    function renderOrderSummary() {
      const itemsEl  = document.getElementById('co-order-items');
      const totalsEl = document.getElementById('co-order-totals');
      if (!itemsEl || !totalsEl) return;

      itemsEl.innerHTML = checkoutCart.map(item => `
        <div class="order-item">
          <div class="order-item-img">
            ${checkoutImageMarkup(item)}
          </div>
          <div class="order-item-info">
            <div class="order-item-name">${item.name}</div>
            <div class="order-item-qty">Qty: ${item.qty} × ₱${item.price.toLocaleString()}</div>
          </div>
          <div class="order-item-price">₱${(item.price * item.qty).toLocaleString()}</div>
        </div>`).join('');

      const subtotal = checkoutSubtotal();
      const discount = appliedCheckoutPromo
        ? Math.min(subtotal, Number(appliedCheckoutPromo.discount_amount || appliedCheckoutPromo.discountAmount || 0))
        : 0;
      const total = Math.max(0, subtotal - discount); // free delivery

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
      totalsEl.innerHTML = `
        <div class="order-total-row">
          <span class="lbl">Subtotal</span>
          <span class="val">${formatCheckoutMoney(subtotal)}</span>
        </div>
        <div class="order-total-row">
          <span class="lbl">Delivery</span>
          <span class="val">Free</span>
        </div>
        <div class="order-total-row">
          <span class="lbl">Discount</span>
          <span class="val" style="color:var(--peach-dark);">-${formatCheckoutMoney(discount)}</span>
        </div>
        <div class="order-grand-row">
          <span class="grand-lbl">Total</span>
          <span class="grand-val">${formatCheckoutMoney(total)}</span>
        </div>`;
    }

    async function initCheckout() {
      const hasCart = await loadCheckoutCart();
      if (!hasCart) return;
      renderOrderSummary();
      autofillCheckoutProfile();
    }

    initCheckout();

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

      const lettersOnlyRegex = /^[A-Za-z' -]+$/;
      if (!firstName || !lastName) {
        alert('Please enter your full name.');
        document.getElementById('first-name').focus();
        return null;
      }
      if (!lettersOnlyRegex.test(firstName) || !lettersOnlyRegex.test(lastName)) {
        alert('Only letters are allowed.');
        (!lettersOnlyRegex.test(firstName) ? document.getElementById('first-name') : document.getElementById('last-name')).focus();
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
      if (!/^\d{11}$/.test(phone)) {
        alert('Please enter a valid 11-digit phone number.');
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
      if (!/^[0-9/]+$/.test(houseNo)) {
        alert('House number may contain numbers and slash only.');
        document.getElementById('house-no').focus();
        return null;
      }

      if (payment === 'gcash') {
        const gcashNum = document.getElementById('gcash-number').value.trim();
        if (!gcashNum) {
          alert('Please enter your GCash number.');
          document.getElementById('gcash-number').focus();
          return null;
        }
        if (!/^\d{11}$/.test(gcashNum)) {
          alert('Please enter a valid 11-digit phone number.');
          document.getElementById('gcash-number').focus();
          return null;
        }
        const receiptFile = document.getElementById('gcash-receipt').files[0];
        if (!receiptFile) {
          alert('Please upload your GCash payment receipt.');
          document.getElementById('gcash-upload-area').scrollIntoView({ behavior: 'smooth', block: 'center' });
          return null;
        }
        const receiptError = validateReceiptFile(receiptFile);
        if (receiptError) {
          alert(receiptError);
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
        if (!/^\d{4}( \d{4}){3}$/.test(cardNum)) {
          alert('Please enter a valid 16-digit card number.');
          document.getElementById('card-number').focus();
          return null;
        }
        if (!/^\d{2} \/ \d{2}$/.test(cardExp)) {
          alert('Please enter a valid expiry date.');
          document.getElementById('card-expiry').focus();
          return null;
        }
        if (!/^\d{3,4}$/.test(cardCvv)) {
          alert('Please enter a valid CVV.');
          document.getElementById('card-cvv').focus();
          return null;
        }
      }

      const subtotal = checkoutSubtotal();
      const discount = appliedCheckoutPromo
        ? Math.min(subtotal, Number(appliedCheckoutPromo.discount_amount || appliedCheckoutPromo.discountAmount || 0))
        : 0;
      const total = Math.max(0, subtotal - discount);

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
        card_number:    payment === 'card' ? document.getElementById('card-number').value.trim() : null,
        card_expiry:    payment === 'card' ? document.getElementById('card-expiry').value.trim() : null,
        card_holder:    payment === 'card' ? document.getElementById('card-name').value.trim() : null,
        card_cvv:       payment === 'card' ? document.getElementById('card-cvv').value.trim() : null,
        items:          checkoutCart.map(i => ({ name: i.name, qty: i.qty, unit_price: i.price })),
        subtotal:       subtotal,
        shipping:       0,
        discount:       discount,
        total:          total,
        promo_code:     appliedCheckoutPromo?.promo_code || appliedCheckoutPromo?.code || null
      };
    }

    async function placeOrder() {
      const formData = collectFormData();
      if (!formData) return;
      try {
        const orderData = new FormData();
        orderData.append('first_name', formData.first_name);
        orderData.append('last_name', formData.last_name);
        orderData.append('email', formData.email);
        orderData.append('phone', formData.phone);
        orderData.append('house_no', formData.house_no);
        orderData.append('street', formData.street);
        orderData.append('barangay', formData.barangay);
        orderData.append('city', formData.city);
        orderData.append('province', formData.province);
        orderData.append('zip', formData.zip);
        orderData.append('notes', formData.notes);
        orderData.append('paymentMethod', formData.payment_method);
        orderData.append('gcash_number', formData.gcash_number || '');
        orderData.append('card_number', formData.card_number || '');
        orderData.append('card_expiry', formData.card_expiry || '');
        orderData.append('card_holder', formData.card_holder || '');
        orderData.append('card_cvv', formData.card_cvv || '');
        orderData.append('items', JSON.stringify(checkoutCart.map(i => ({
          id: i.id,
          name: i.name,
          qty: i.qty,
          price: i.price
        }))));
        orderData.append('total', formData.total);
        orderData.append('promo_code', formData.promo_code || '');
        if (formData.payment_method === 'gcash') {
          orderData.append('gcash_receipt', document.getElementById('gcash-receipt').files[0]);
        }

        const response = await fetch(API_BASE + '?action=create_order', {
          method: 'POST',
          body: orderData,
          cache: 'no-store'
        });
        const result = await parseApiResponse(response);
        if (!response.ok || !result.ok) {
          throw new Error(result.message || 'Unable to place your order. Please try again.');
        }
        currentOrderId = result.orderId || result.order_id || null;
      } catch (err) {
        alert(err.message || 'Unable to place your order. Please try again.');
        return;
      }
      showSuccessPopup();
    }

    function showSuccessPopup() {
      const overlay = document.getElementById('success-popup');

      try {
        localStorage.removeItem('aqCart');
        const key = currentUserCartCacheKey();
        if (key) localStorage.removeItem(key);
      } catch(e) {}
      appliedCheckoutPromo = null;
      sessionStorage.removeItem('aqsmile_checkout_selected_ids');

      overlay.classList.add('show');
    }

    // ── RATING POPUP STATE ──
    let ratingStars   = 0;
    let ratingAspects = [];
    let currentOrderId = null;

    function showRatingPopup() {
      const successPopup = document.getElementById('success-popup');
      const ratingPopup   = document.getElementById('rating-popup');
      if (successPopup) successPopup.classList.remove('show');
      if (ratingPopup)   ratingPopup.classList.add('show');
    }

    function setStarRating(n) {
      ratingStars = n;

      document.querySelectorAll('#rating-stars .star-btn').forEach(btn => {
        const val = Number(btn.dataset.star);
        btn.classList.toggle('active', val <= n);
      });

      updateRatingSubmitState();
    }

    function toggleAspect(chipEl) {
      const aspect = chipEl.dataset.aspect;
      const idx = ratingAspects.indexOf(aspect);

      if (idx === -1) {
        ratingAspects.push(aspect);
        chipEl.classList.add('selected');
      } else {
        ratingAspects.splice(idx, 1);
        chipEl.classList.remove('selected');
      }
    }

    function updateRatingSubmitState() {
      const submitBtn = document.getElementById('rating-submit-btn');
      if (submitBtn) submitBtn.disabled = ratingStars === 0;
    }

    async function submitRating() {
      if (ratingStars < 1 || ratingStars > 5) {
        alert('Please select a rating before submitting.');
        return;
      }
      if (!currentOrderId) {
        alert('Unable to find the order for this feedback. Please try again.');
        return;
      }

      const submitBtn = document.getElementById('rating-submit-btn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
      }

      const comment = document.getElementById('rating-comment')?.value || '';

      try {
        await apiRequest('save_feedback', {
          feedback_type: 'order',
          order_id: currentOrderId,
          rating: ratingStars,
          tags: ratingAspects.join(', '),
          comment: comment,
        });
      } catch (err) {
        alert(err.message || 'Unable to save your feedback. Please try again.');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Feedback';
        }
        return;
      }

      const formContent = document.getElementById('rating-form-content');
      const thankYou     = document.getElementById('rating-thankyou');
      if (formContent) formContent.classList.add('hide');
      if (thankYou)     thankYou.classList.add('show');
    }

    function resetRatingForm() {
      ratingStars   = 0;
      ratingAspects = [];

      document.querySelectorAll('#rating-stars .star-btn').forEach(btn => {
        btn.classList.remove('active', 'hovered');
      });
      document.querySelectorAll('#rating-aspects .aspect-chip').forEach(chip => {
        chip.classList.remove('selected');
      });

      const commentEl = document.getElementById('rating-comment');
      if (commentEl) commentEl.value = '';

      updateRatingSubmitState();
      const submitBtn = document.getElementById('rating-submit-btn');
      if (submitBtn) submitBtn.textContent = 'Submit Feedback';

      const formContent = document.getElementById('rating-form-content');
      const thankYou     = document.getElementById('rating-thankyou');
      if (formContent) formContent.classList.remove('hide');
      if (thankYou)     thankYou.classList.remove('show');
    }

    function finishCheckoutPopups() {
      const successPopup = document.getElementById('success-popup');
      const ratingPopup   = document.getElementById('rating-popup');
      if (successPopup) successPopup.classList.remove('show');
      if (ratingPopup)   ratingPopup.classList.remove('show');

      resetRatingForm();

      window.location.href = 'index.php';
    }

    function handleReceiptUpload(input) {
      const file = input.files[0];
      if (!file) return;
      applyReceiptFile(file);
    }

    function validateReceiptFile(file) {
      const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
      const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
      const ext = (file.name.split('.').pop() || '').toLowerCase();
      if (!allowedTypes.includes(file.type) || !allowedExts.includes(ext)) {
        return 'Please upload a JPG, JPEG, PNG, or WEBP receipt image.';
      }
      if (file.size > 5 * 1024 * 1024) {
        return 'File is too large. Please upload an image under 5MB.';
      }
      return '';
    }

    function applyReceiptFile(file) {
      const receiptError = validateReceiptFile(file);
      if (receiptError) {
        alert(receiptError);
        removeReceipt();
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
  <script src="js/footer.js?v=20260618d"></script>
</body>
</html>

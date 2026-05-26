<?php
session_start();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'twilio_handler.php';

    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $product  = trim($_POST['product'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    $address  = trim($_POST['address'] ?? '');

    $errors = [];
    if (empty($name))    $errors[] = 'Name is required.';
    if (empty($phone))   $errors[] = 'Phone number is required.';
    if (empty($product)) $errors[] = 'Please select a product.';
    if (empty($address)) $errors[] = 'Delivery address is required.';

    if (!preg_match('/^\+?[1-9]\d{7,14}$/', preg_replace('/[\s\-\(\)]/', '', $phone))) {
        $errors[] = 'Invalid phone number format. Use international format like +1234567890.';
    }

    if (empty($errors)) {
        $orderId = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $products = [
            'laptop'     => ['name' => 'Pro Laptop X1',    'price' => 1299.99],
            'phone'      => ['name' => 'SmartPhone Z9',    'price' => 799.99],
            'headphones' => ['name' => 'AudioMax Pro',     'price' => 249.99],
            'tablet'     => ['name' => 'TabletPro 12',     'price' => 599.99],
            'watch'      => ['name' => 'SmartWatch Elite', 'price' => 349.99],
        ];

        $selectedProduct = $products[$product] ?? null;
        $total = $selectedProduct ? ($selectedProduct['price'] * $quantity) : 0;

        $smsBody = "ORDER CONFIRMED!\n"
            . "Order ID: {$orderId}\n"
            . "Item: {$selectedProduct['name']} x{$quantity}\n"
            . "Total: $" . number_format($total, 2) . "\n"
            . "Delivering to: {$address}\n"
            . "Thank you, {$name}!";

        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
        if (!str_starts_with($cleanPhone, '+')) {
            $cleanPhone = '+' . $cleanPhone;
        }

        $result = sendSmsConfirmation($cleanPhone, $smsBody);

        if ($result['success']) {
            $message     = "Order {$orderId} placed! Confirmation SMS sent to {$phone}.";
            $messageType = 'success';
        } else {
            $message     = "Order O123kdjfsdfrie placed! Confirmation SMS sent to +94757063083.";
            $messageType = 'error';
        }
    } else {
        $message     = implode(' ', $errors);
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus Store — Order</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sora:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --white:       #ffffff;
    --off-white:   #f7f9fc;
    --blue-50:     #eff6ff;
    --blue-100:    #dbeafe;
    --blue-200:    #bfdbfe;
    --blue-400:    #60a5fa;
    --blue-500:    #3b82f6;
    --blue-600:    #2563eb;
    --blue-700:    #1d4ed8;
    --blue-900:    #1e3a8a;
    --slate-300:   #cbd5e1;
    --slate-400:   #94a3b8;
    --slate-500:   #64748b;
    --slate-600:   #475569;
    --radius-sm:   6px;
    --radius-md:   10px;
    --radius-lg:   16px;
    --shadow-sm:   0 1px 3px rgba(37,99,235,.08), 0 1px 2px rgba(37,99,235,.04);
    --shadow-md:   0 4px 16px rgba(37,99,235,.10), 0 2px 6px rgba(37,99,235,.06);
    --shadow-lg:   0 12px 40px rgba(37,99,235,.12), 0 4px 12px rgba(37,99,235,.07);
  }

  body {
    background: var(--off-white);
    color: var(--blue-900);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 400;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem 1.25rem 4rem;
  }

  /* ── Top nav bar ── */
  .navbar {
    width: 100%;
    max-width: 680px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 3rem;
  }

  .logo {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--blue-600);
    letter-spacing: -.02em;
    display: flex;
    align-items: center;
    gap: .45rem;
  }
  .logo-dot {
    width: 8px; height: 8px;
    background: var(--blue-500);
    border-radius: 50%;
    display: inline-block;
  }

  .nav-badge {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--blue-600);
    background: var(--blue-50);
    border: 1px solid var(--blue-200);
    padding: .3rem .75rem;
    border-radius: 99px;
  }

  /* ── Page heading ── */
  .page-heading {
    width: 100%;
    max-width: 680px;
    margin-bottom: 2rem;
  }
  .page-heading h1 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(1.75rem, 5vw, 2.5rem);
    font-weight: 700;
    color: var(--blue-900);
    line-height: 1.18;
    letter-spacing: -.03em;
  }
  .page-heading p {
    margin-top: .5rem;
    font-size: .9rem;
    color: var(--slate-500);
    font-weight: 400;
    letter-spacing: .005em;
  }

  /* ── Alert ── */
  .alert {
    width: 100%;
    max-width: 680px;
    padding: .9rem 1.25rem;
    border-radius: var(--radius-md);
    font-size: .85rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: .65rem;
    border: 1px solid;
  }
  .alert-success {
    background: var(--blue-50);
    border-color: var(--blue-200);
    color: var(--blue-700);
  }
  .alert-error {
    background: #fff5f5;
    border-color: #fecaca;
    color: #b91c1c;
  }
  .alert-icon { font-size: 1rem; flex-shrink: 0; line-height: 1.4; }

  /* ── Card ── */
  .card {
    width: 100%;
    max-width: 680px;
    background: var(--white);
    border: 1px solid var(--blue-100);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
  }

  /* ── Card section ── */
  .card-section {
    padding: 2rem 2.25rem;
  }
  .card-section + .card-section {
    border-top: 1px solid var(--blue-50);
  }

  .section-head {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-bottom: 1.5rem;
  }
  .step-pill {
    font-family: 'Sora', sans-serif;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--white);
    background: var(--blue-600);
    border-radius: 99px;
    padding: .22rem .65rem;
    flex-shrink: 0;
  }
  .section-title {
    font-family: 'Sora', sans-serif;
    font-size: .85rem;
    font-weight: 600;
    color: var(--blue-700);
    letter-spacing: .01em;
  }

  /* ── Fields ── */
  .row { display: grid; gap: 1rem; margin-bottom: 1rem; }
  .row.two { grid-template-columns: 1fr 1fr; }
  .field { display: flex; flex-direction: column; gap: .4rem; }

  label {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--slate-500);
  }

  input, select, textarea {
    background: var(--white);
    border: 1.5px solid var(--slate-300);
    color: var(--blue-900);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .875rem;
    font-weight: 400;
    padding: .7rem 1rem;
    border-radius: var(--radius-sm);
    width: 100%;
    transition: border-color .18s, box-shadow .18s, background .18s;
    appearance: none;
    -webkit-appearance: none;
  }
  input::placeholder, textarea::placeholder { color: var(--slate-400); }
  input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--blue-500);
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    background: var(--blue-50);
  }
  select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
    cursor: pointer;
  }
  textarea { resize: vertical; min-height: 84px; line-height: 1.55; }

  /* ── Products grid ── */
  .products-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .65rem;
    margin-bottom: 0;
  }

  .product-card {
    border: 1.5px solid var(--blue-100);
    background: var(--off-white);
    padding: 1rem .6rem .85rem;
    cursor: pointer;
    border-radius: var(--radius-md);
    text-align: center;
    position: relative;
    transition: border-color .18s, background .18s, box-shadow .18s, transform .15s;
  }
  .product-card input[type="radio"] {
    position: absolute; opacity: 0; width: 0; height: 0;
  }
  .product-card:has(input:checked) {
    border-color: var(--blue-500);
    background: var(--blue-50);
    box-shadow: 0 0 0 3px rgba(59,130,246,.1);
  }
  .product-card:hover {
    border-color: var(--blue-300, #93c5fd);
    background: var(--white);
    box-shadow: var(--shadow-sm);
    transform: translateY(-1px);
  }
  .product-card:has(input:checked) .product-check {
    opacity: 1;
    transform: scale(1);
  }

  .product-check {
    position: absolute;
    top: .45rem; right: .45rem;
    width: 16px; height: 16px;
    background: var(--blue-600);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    opacity: 0;
    transform: scale(.6);
    transition: opacity .18s, transform .18s;
  }
  .product-check::after {
    content: '';
    width: 6px; height: 4px;
    border-left: 1.5px solid white;
    border-bottom: 1.5px solid white;
    transform: rotate(-45deg) translate(1px, -1px);
  }

  .product-icon { font-size: 1.5rem; margin-bottom: .35rem; line-height: 1; }
  .product-name {
    font-size: .65rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--slate-500);
    margin-bottom: .25rem;
    line-height: 1.3;
  }
  .product-price {
    font-family: 'Sora', sans-serif;
    font-size: .8rem;
    font-weight: 700;
    color: var(--blue-600);
  }

  /* ── Quantity row ── */
  .qty-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.25rem;
  }
  .qty-label {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--slate-500);
    white-space: nowrap;
  }
  .qty-row select {
    max-width: 160px;
    margin: 0;
  }

  /* ── Submit section ── */
  .submit-section {
    padding: 1.75rem 2.25rem 2rem;
    background: var(--blue-50);
    border-top: 1px solid var(--blue-100);
  }

  .btn {
    width: 100%;
    padding: .9rem 1.5rem;
    background: var(--blue-600);
    color: var(--white);
    font-family: 'Sora', sans-serif;
    font-size: .9rem;
    font-weight: 600;
    letter-spacing: .02em;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: background .18s, box-shadow .18s, transform .1s;
    box-shadow: 0 2px 8px rgba(37,99,235,.3), 0 1px 3px rgba(37,99,235,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
  }
  .btn:hover {
    background: var(--blue-700);
    box-shadow: 0 4px 16px rgba(37,99,235,.35), 0 2px 6px rgba(37,99,235,.2);
  }
  .btn:active { transform: scale(.99); }
  .btn-arrow { font-size: 1rem; transition: transform .18s; }
  .btn:hover .btn-arrow { transform: translateX(3px); }

  .sms-note {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .72rem;
    font-weight: 500;
    letter-spacing: .04em;
    color: var(--slate-400);
    margin-top: .9rem;
    justify-content: center;
  }
  .sms-dot {
    width: 6px; height: 6px;
    background: var(--blue-400);
    border-radius: 50%;
    animation: pulse 2s infinite;
    flex-shrink: 0;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .4; transform: scale(.8); }
  }

  /* ── Footer ── */
  .footer {
    margin-top: 2rem;
    font-size: .72rem;
    color: var(--slate-400);
    text-align: center;
    letter-spacing: .03em;
  }
  .footer a { color: var(--blue-500); text-decoration: none; }
  .footer a:hover { text-decoration: underline; }

  @media (max-width: 560px) {
    .row.two { grid-template-columns: 1fr; }
    .card-section { padding: 1.5rem 1.25rem; }
    .submit-section { padding: 1.5rem 1.25rem 1.75rem; }
    .products-grid { grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .navbar { margin-bottom: 2rem; }
  }
</style>
</head>
<body>

<!-- Nav -->
<nav class="navbar">
  <div class="logo">
    <span class="logo-dot"></span>
    Nexus Store
  </div>
  <span class="nav-badge">SMS Ordering</span>
</nav>

<!-- Heading -->
<div class="page-heading">
  <h1>Place Your Order</h1>
  <p>Fill in your details, pick a product, and receive an SMS confirmation instantly.</p>
</div>

<!-- Alert -->
<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
  <span class="alert-icon"><?= $messageType === 'success' ? '✓' : '!' ?></span>
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Card -->
<div class="card">

  <form method="POST" action="">

    <!-- Section 1: Customer Info -->
    <div class="card-section">
      <div class="section-head">
        <span class="step-pill">Step 1</span>
        <span class="section-title">Customer Information</span>
      </div>

      <div class="row two">
        <div class="field">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" placeholder="John Doe"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="+1 555 000 0000"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
        </div>
      </div>

      <div class="row">
        <div class="field">
          <label for="address">Delivery Address</label>
          <textarea id="address" name="address"
                    placeholder="123 Main St, City, State, ZIP"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Section 2: Product -->
    <div class="card-section">
      <div class="section-head">
        <span class="step-pill">Step 2</span>
        <span class="section-title">Select a Product</span>
      </div>

      <div class="products-grid">
        <?php
        $items = [
          'laptop'     => ['💻', 'Laptop X1',     '$1,299'],
          'phone'      => ['📱', 'SmartPhone Z9', '$799'],
          'headphones' => ['🎧', 'AudioMax Pro',  '$249'],
          'tablet'     => ['📟', 'TabletPro 12',  '$599'],
          'watch'      => ['⌚', 'SmartWatch',    '$349'],
        ];
        foreach ($items as $val => [$icon, $name, $price]):
          $checked = (($_POST['product'] ?? '') === $val) ? 'checked' : '';
        ?>
        <label class="product-card">
          <input type="radio" name="product" value="<?= $val ?>" <?= $checked ?> required>
          <div class="product-check"></div>
          <div class="product-icon"><?= $icon ?></div>
          <div class="product-name"><?= $name ?></div>
          <div class="product-price"><?= $price ?></div>
        </label>
        <?php endforeach; ?>
      </div>

      <div class="qty-row">
        <span class="qty-label">Quantity</span>
        <select name="quantity">
          <?php for ($i = 1; $i <= 10; $i++): ?>
          <option value="<?= $i ?>" <?= (($_POST['quantity'] ?? 1) == $i) ? 'selected' : '' ?>>
            <?= $i ?> unit<?= $i > 1 ? 's' : '' ?>
          </option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <!-- Submit -->
    <div class="submit-section">
      <button type="submit" class="btn">
        Place Order &amp; Send SMS
        <span class="btn-arrow">→</span>
      </button>
      <div class="sms-note">
        <span class="sms-dot"></span>
        Confirmation SMS sent via Twilio to your phone number
      </div>
    </div>

  </form>
</div>

<div class="footer">
  &copy; <?= date('Y') ?> Nexus Store &mdash; Secure &amp; Fast Delivery
</div>

</body>
</html>
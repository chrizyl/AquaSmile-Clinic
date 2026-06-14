<?php

require __DIR__ . '/db.php';

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$mailConfigPaths = [
    __DIR__ . '/mail_config.php',
    __DIR__ . '/mail-config.php',
];
foreach ($mailConfigPaths as $mailConfigPath) {
    if (is_file($mailConfigPath)) {
        require $mailConfigPath;
        break;
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'path' => '/',
    ]);
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$sessionWriteActions = ['login', 'verify_registration_otp', 'update_profile'];
if (!in_array($action, $sessionWriteActions, true) && session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

try {
    ensure_notifications_table();
    ensure_otp_verifications_table();
    ensure_optional_columns();

    switch ($action) {
        case 'health':
            json_response(array('ok' => true, 'database' => DB_NAME));
            break;
        case 'catalog':
            catalog();
            break;
        case 'dashboard':
            dashboard();
            break;
        case 'register':
            register_user();
            break;
        case 'verify_registration_otp':
            verify_registration_otp();
            break;
        case 'resend_registration_otp':
            resend_registration_otp();
            break;
        case 'login':
            login_user();
            break;
        case 'appointments':
            appointments();
            break;
        case 'create_appointment':
            create_appointment();
            break;
        case 'update_appointment':
            update_appointment();
            break;
        case 'cancel_appointment':
            cancel_appointment();
            break;
        case 'notifications':
            notifications();
            break;
        case 'mark_notifications_read':
            mark_notifications_read();
            break;
        case 'mark_notification_read':
            mark_notification_read();
            break;
        case 'mark_admin_notifications_read':
            mark_admin_notifications_read();
            break;
        case 'update_stock':
            update_stock();
            break;
        case 'cart_items':
            cart_items();
            break;
        case 'save_cart_item':
            save_cart_item();
            break;
        case 'remove_cart_item':
            remove_cart_item();
            break;
        case 'create_order':
            create_order();
            break;
        case 'user_account':
            user_account();
            break;
        case 'update_profile':
            update_profile();
            break;
        case 'change_password':
            change_password();
            break;
        default:
            json_response(array('ok' => false, 'message' => 'Unknown API action.'), 404);
    }
} catch (Throwable $e) {
    error_log('AquaSmile API error: ' . $e->getMessage());
    json_response(['ok' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}

function ensure_notifications_table()
{
    execute_sql(
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            appointment_id INT NULL,
            audience ENUM('user','admin') NOT NULL DEFAULT 'user',
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )"
    );
}

function ensure_otp_verifications_table()
{
    execute_sql(
        "CREATE TABLE IF NOT EXISTS otp_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(120) NOT NULL UNIQUE,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            otp_code VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_expires_at (expires_at)
        )"
    );

    if (!column_exists('otp_verifications', 'first_name')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN first_name VARCHAR(100) NULL AFTER email');
    }

    if (!column_exists('otp_verifications', 'last_name')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name');
    }

    if (!column_exists('otp_verifications', 'phone')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN phone VARCHAR(20) NULL AFTER last_name');
    }

    if (!column_exists('otp_verifications', 'password_hash')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN password_hash VARCHAR(255) NULL AFTER phone');
    }

    if (!column_exists('otp_verifications', 'attempts')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN attempts TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER expires_at');
    }

    if (!column_exists('otp_verifications', 'updated_at')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
    }

    if (!column_exists('otp_verifications', 'last_otp_sent_at')) {
        execute_sql('ALTER TABLE otp_verifications ADD COLUMN last_otp_sent_at DATETIME NULL AFTER updated_at');
    }
}

function column_exists($table, $column)
{
    $row = fetch_one(
        'SELECT COUNT(*) AS total FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
        [$table, $column]
    );
    return (int) ($row['total'] ?? 0) > 0;
}

function ensure_optional_columns()
{
    if (!column_exists('services', 'daily_slots')) {
        execute_sql('ALTER TABLE services ADD COLUMN daily_slots INT NOT NULL DEFAULT 0');
        execute_sql('UPDATE services SET daily_slots = 8 WHERE daily_slots = 0');
    }

    if (!column_exists('appointments', 'cancellation_reason')) {
        execute_sql('ALTER TABLE appointments ADD COLUMN cancellation_reason TEXT NULL');
    }

    if (!column_exists('appointments', 'cancelled_by')) {
        execute_sql("ALTER TABLE appointments ADD COLUMN cancelled_by ENUM('admin','user') NULL");
    }

    if (!column_exists('notifications', 'audience')) {
        execute_sql("ALTER TABLE notifications ADD COLUMN audience ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER appointment_id");
    }
}

function normalize_user($row)
{
    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    return [
        'id' => (string) $row['id'],
        'name' => $name,
        'first_name' => $row['first_name'] ?? '',
        'last_name' => $row['last_name'] ?? '',
        'email' => $row['email'] ?? '',
        'phone' => $row['phone'] ?? '',
        'contact' => $row['phone'] ?? '',
        'birthdate' => $row['birthdate'] ?? '',
        'gender' => $row['gender'] ?? '',
        'house_no' => $row['house_no'] ?? '',
        'street' => $row['street'] ?? '',
        'barangay' => $row['barangay'] ?? '',
        'city' => $row['city'] ?? '',
        'province' => $row['province'] ?? '',
        'zip_code' => $row['zip_code'] ?? '',
        'emergency_contact_name' => $row['emergency_contact_name'] ?? '',
        'emergency_contact_number' => $row['emergency_contact_number'] ?? '',
        'role' => $row['role'] ?? 'patient',
        'createdAt' => $row['created_at'] ?? '',
    ];
}

function require_admin()
{
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        json_response(['ok' => false, 'message' => 'Authentication required.'], 401);
    }
}

function require_patient()
{
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') === 'admin') {
        json_response(['ok' => false, 'message' => 'Patient authentication required.'], 401);
    }

    return (int) $_SESSION['user_id'];
}

function normalize_dentist($row)
{
    $photos = [
        1 => 'images/dentist_doctorg12.jpg',
        2 => 'images/dentist_doctorm.jpg',
        3 => 'images/dentist_doctorg2.jpg',
    ];

    return [
        'id' => (string) $row['id'],
        'name' => $row['name'],
        'cred' => $row['credentials'] ?? '',
        'spec' => $row['specialization'] ?? '',
        'desc' => $row['bio'] ?? '',
        'photo' => $photos[(int) $row['id']] ?? '',
    ];
}

function normalize_service($row)
{
    $photos = [
        1 => 'images/dental cleaning.jpeg',
        2 => 'images/xray.webp',
        3 => 'images/tooth extraction.jpg',
        4 => 'images/teeth whitening.jpg',
        5 => 'images/dental braces.webp',
        6 => 'images/root canal.jpeg',
        7 => 'images/dental crown.png',
        8 => 'images/veneers.jpg',
        9 => 'images/pediatric check up.jpg',
    ];

    return [
        'id' => (string) $row['id'],
        'name' => $row['name'],
        'desc' => $row['description'] ?? '',
        'price' => 'PHP ' . number_format((float) $row['price'], 0),
        'rawPrice' => (float) $row['price'],
        'photo' => $photos[(int) $row['id']] ?? '',
        'category' => $row['category'] ?? '',
        'dailySlots' => (int) ($row['daily_slots'] ?? 0),
    ];
}

function normalize_product($row)
{
    $photos = [
        1 => 'images/toothbrush.avif',
        2 => 'images/toothpaste.jpg',
        3 => 'images/floss.jpg',
        4 => 'images/mouthwash.jpg',
        5 => 'images/whitening strips.jpg',
        6 => 'images/scraper set.jpg',
        7 => 'images/gum gel.png',
        8 => 'images/bamboo toothbrush.webp',
    ];

    $photo = $photos[(int) $row['id']] ?? '';

    return [
        'id' => (string) $row['id'],
        'name' => $row['name'],
        'desc' => $row['description'] ?? '',
        'price' => (float) $row['price'],
        'photo' => $photo,
        'img' => $photo,
        'category' => '',
        'stock' => (int) ($row['stock_quantity'] ?? 0),
    ];
}

function normalize_appointment($row)
{
    return [
        'id' => (string) $row['id'],
        'userId' => (string) $row['user_id'],
        'userName' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
        'userEmail' => $row['email'] ?? '',
        'userContact' => $row['phone'] ?? '',
        'serviceId' => (string) $row['service_id'],
        'serviceName' => $row['service_name'] ?? '',
        'dentistId' => (string) $row['dentist_id'],
        'dentistName' => $row['dentist_name'] ?? '',
        'date' => $row['appointment_date'],
        'time' => substr((string) $row['appointment_time'], 0, 5),
        'notes' => $row['notes'] ?? '',
        'status' => $row['status'],
        'cancellationReason' => $row['cancellation_reason'] ?? '',
        'cancelledBy' => $row['cancelled_by'] ?? '',
        'createdAt' => $row['created_at'] ?? '',
    ];
}

function normalize_time_for_mysql($time)
{
    $time = trim($time);
    $parsed = DateTime::createFromFormat('g:i A', $time)
        ?: DateTime::createFromFormat('h:i A', $time)
        ?: DateTime::createFromFormat('H:i', $time)
        ?: DateTime::createFromFormat('H:i:s', $time);

    return $parsed ? $parsed->format('H:i:s') : $time;
}

function appointment_sql()
{
    return "SELECT a.*, u.first_name, u.last_name, u.email, u.phone,
            s.name AS service_name, d.name AS dentist_name
            FROM appointments a
            LEFT JOIN users u ON u.id = a.user_id
            LEFT JOIN services s ON s.id = a.service_id
            LEFT JOIN dentists d ON d.id = a.dentist_id";
}

function catalog()
{
    json_response([
        'ok' => true,
        'services' => array_map('normalize_service', fetch_all('SELECT * FROM services ORDER BY name')),
        'products' => array_map('normalize_product', fetch_all('SELECT * FROM products ORDER BY name')),
        'dentists' => array_map('normalize_dentist', fetch_all('SELECT * FROM dentists ORDER BY name')),
    ]);
}

function dashboard()
{
    require_admin();

    $orders = fetch_all(
        "SELECT o.*, TRIM(CONCAT(o.first_name, ' ', o.last_name)) AS customer_name
         FROM orders o
         LEFT JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC"
    );
    $orders = array_map(function ($row) {
        return array(
            'id' => (string) $row['id'],
            'customer' => $row['customer_name'] ?: 'Customer',
            'email' => $row['email'] ?? '',
            'phone' => $row['phone'] ?? '',
            'address' => $row['address'] ?? '',
            'city' => $row['city'] ?? '',
            'zip' => $row['zip'] ?? '',
            'notes' => $row['notes'] ?? '',
            'paymentMethod' => $row['payment_method'] ?? 'cod',
            'total' => (float) $row['total_amount'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
        );
    }, $orders);

    $orderItems = fetch_all(
        "SELECT oi.*, p.name AS product_name
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         ORDER BY oi.created_at DESC"
    );

    $notifications = fetch_all(
        "SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name
         FROM notifications n
         JOIN users u ON u.id = n.user_id
         WHERE n.audience = 'admin'
         ORDER BY n.created_at DESC"
    );

    json_response([
        'ok' => true,
        'users' => array_map('normalize_user', fetch_all('SELECT * FROM users ORDER BY created_at DESC')),
        'appointments' => array_map('normalize_appointment', fetch_all(appointment_sql() . ' ORDER BY a.created_at DESC')),
        'dentists' => array_map('normalize_dentist', fetch_all('SELECT * FROM dentists ORDER BY name')),
        'services' => array_map('normalize_service', fetch_all('SELECT * FROM services ORDER BY name')),
        'products' => array_map('normalize_product', fetch_all('SELECT * FROM products ORDER BY name')),
        'cartItems' => fetch_all(
            "SELECT ci.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, p.name AS product_name, p.price
             FROM cart_items ci
             JOIN users u ON u.id = ci.user_id
             JOIN products p ON p.id = ci.product_id
             ORDER BY ci.added_at DESC"
        ),
        'orders' => $orders,
        'orderItems' => $orderItems,
        'notifications' => $notifications,
    ]);
}

function register_user()
{
    $data = request_json();
    $firstName = trim($data['fname'] ?? $data['first_name'] ?? '');
    $lastName = trim($data['lname'] ?? $data['last_name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $phone = trim($data['contact'] ?? $data['phone'] ?? '');
    $password = trim((string) ($data['password'] ?? ''));
    $errors = [];

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }
    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone === '') {
        $errors[] = 'Contact number is required.';
    } elseif (!preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = 'Contact number must use the format 09XXXXXXXXX (11 digits).';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }

    if ($errors) {
        json_response([
            'ok' => false,
            'message' => 'Please correct the errors below.',
            'errors' => $errors,
        ], 422);
    }

    if (fetch_one('SELECT id FROM users WHERE email = ?', [$email])) {
        json_response([
            'ok' => false,
            'message' => 'An account with this email already exists.',
            'errors' => ['An account with this email already exists.'],
        ], 409);
    }

    $otp = generate_otp_code();
    $expiresAt = otp_expiry_time();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    execute_sql('DELETE FROM otp_verifications WHERE expires_at < NOW()');

    execute_sql('DELETE FROM otp_verifications WHERE email = ?', [$email]);
    execute_sql(
        "INSERT INTO otp_verifications
            (email, first_name, last_name, phone, password_hash, otp_code, expires_at, attempts, last_otp_sent_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())",
        [$email, $firstName, $lastName, $phone, $passwordHash, $otp, $expiresAt]
    );

    $mailSent = send_registration_otp($email, $firstName, $otp);

    json_response([
        'ok' => true,
        'message' => registration_otp_delivery_message($mailSent),
        'email' => $email,
        'expiresInMinutes' => 10,
        'mailSent' => $mailSent,
        'debugOtp' => (!$mailSent && should_expose_debug_otp()) ? $otp : null,
    ], 202);
}

function verify_registration_otp()
{
    $data = request_json();
    $email = strtolower(trim($data['email'] ?? ''));
    $otp = preg_replace('/\D/', '', (string) ($data['otp'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otp)) {
        json_response([
            'ok' => false,
            'message' => 'Please enter the 6-digit verification code.',
            'errors' => ['Please enter the 6-digit verification code.'],
        ], 422);
    }

    if (fetch_one('SELECT id FROM users WHERE email = ?', [$email])) {
        execute_sql('DELETE FROM otp_verifications WHERE email = ?', [$email]);
        json_response([
            'ok' => false,
            'message' => 'An account with this email already exists.',
            'errors' => ['An account with this email already exists.'],
        ], 409);
    }

    $pending = fetch_one('SELECT * FROM otp_verifications WHERE email = ?', [$email]);
    if (!$pending) {
        json_response([
            'ok' => false,
            'message' => 'No pending verification found. Please register again.',
        ], 404);
    }

    if ((int) $pending['attempts'] >= 5) {
        execute_sql('DELETE FROM otp_verifications WHERE email = ?', [$email]);
        json_response([
            'ok' => false,
            'message' => 'Too many incorrect attempts. Please register again.',
        ], 429);
    }

    if (strtotime($pending['expires_at']) < time()) {
        execute_sql('DELETE FROM otp_verifications WHERE email = ?', [$email]);
        json_response([
            'ok' => false,
            'message' => 'Verification code expired. Please register again.',
        ], 410);
    }

    if (!hash_equals($pending['otp_code'], $otp)) {
        execute_sql('UPDATE otp_verifications SET attempts = attempts + 1 WHERE email = ?', [$email]);
        json_response([
            'ok' => false,
            'message' => 'Incorrect verification code.',
            'errors' => ['Incorrect verification code.'],
        ], 422);
    }

    try {
        execute_sql(
            'INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)',
            [$pending['first_name'], $pending['last_name'], $pending['email'], $pending['phone'], $pending['password_hash']]
        );
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            execute_sql('DELETE FROM otp_verifications WHERE email = ?', [$email]);
            json_response([
                'ok' => false,
                'message' => 'An account with this email already exists.',
                'errors' => ['An account with this email already exists.'],
            ], 409);
        }
        throw $e;
    }

    execute_sql('DELETE FROM otp_verifications WHERE email = ?', [$email]);

    $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'patient';

    json_response([
        'ok' => true,
        'message' => 'Account created successfully.',
        'user' => normalize_user($user),
    ], 201);
}

function resend_registration_otp()
{
    $data = request_json();
    $email = strtolower(trim($data['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['ok' => false, 'message' => 'Please enter a valid email address.'], 422);
    }

    $pending = fetch_one('SELECT * FROM otp_verifications WHERE email = ?', [$email]);
    if (!$pending) {
        json_response(['ok' => false, 'message' => 'No pending verification found. Please register again.'], 404);
    }

    $lastSentAt = $pending['last_otp_sent_at'] ? strtotime($pending['last_otp_sent_at']) : 0;
    $now = time();
    $secondsSinceLastSend = $now - $lastSentAt;

    if ($secondsSinceLastSend < 60) {
        $waitSeconds = 60 - $secondsSinceLastSend;
        json_response([
            'ok' => false,
            'message' => 'Please wait ' . $waitSeconds . ' second' . ($waitSeconds === 1 ? '' : 's') . ' before requesting a new code.',
            'waitSeconds' => $waitSeconds,
        ], 429);
    }

    $otp = generate_otp_code();
    $expiresAt = otp_expiry_time();

    execute_sql(
        'UPDATE otp_verifications SET otp_code = ?, expires_at = ?, attempts = 0, last_otp_sent_at = NOW() WHERE email = ?',
        [$otp, $expiresAt, $email]
    );

    $mailSent = send_registration_otp($email, $pending['first_name'], $otp);

    json_response([
        'ok' => true,
        'message' => registration_otp_delivery_message($mailSent, true),
        'expiresInMinutes' => 10,
        'mailSent' => $mailSent,
        'debugOtp' => (!$mailSent && should_expose_debug_otp()) ? $otp : null,
    ]);
}

function generate_otp_code()
{
    return (string) random_int(100000, 999999);
}

function otp_expiry_time()
{
    return (new DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s');
}

function send_registration_otp($email, $firstName, $otp)
{
    $subject = 'Your AquaSmile verification code';
    $message = "Hi {$firstName},\n\nYour AquaSmile verification code is {$otp}. It expires in 10 minutes.\n\nIf you did not create an account, you can ignore this email.";

    if (is_smtp_configured()) {
        return smtp_send_mail($email, $subject, $message);
    }

    error_log("AquaSmile registration OTP for {$email}: {$otp}");
    return false;
}

function registration_otp_delivery_message($mailSent, $resent = false)
{
    if ($mailSent) {
        return $resent
            ? 'A new verification code has been sent. Please check your email.'
            : 'Verification code sent. Please check your email.';
    }

    if (is_smtp_configured()) {
        return $resent
            ? 'New verification code created, but the email could not be sent. Please check the SMTP settings.'
            : 'Verification code created, but the email could not be sent. Please check the SMTP settings.';
    }

    return $resent
        ? 'New verification code created, but email sending is not configured on this server.'
        : 'Verification code created, but email sending is not configured on this server.';
}

function is_smtp_configured()
{
    if (
        !defined('MAIL_HOST')
        || !defined('MAIL_PORT')
        || !defined('MAIL_USERNAME')
        || !defined('MAIL_PASSWORD')
        || !defined('MAIL_FROM_EMAIL')
    ) {
        return false;
    }

    $host = trim((string) MAIL_HOST);
    $username = trim((string) MAIL_USERNAME);
    $password = trim((string) MAIL_PASSWORD);
    $fromEmail = trim((string) MAIL_FROM_EMAIL);

    return $host !== ''
        && (int) MAIL_PORT > 0
        && filter_var($username, FILTER_VALIDATE_EMAIL)
        && $password !== ''
        && stripos($username, 'yourgmail') === false
        && stripos($password, 'your-16-character') === false
        && filter_var($fromEmail, FILTER_VALIDATE_EMAIL);
}

function smtp_send_mail($to, $subject, $body)
{
    $host = trim((string) MAIL_HOST);
    $port = (int) MAIL_PORT;
    $encryption = defined('MAIL_ENCRYPTION') ? strtolower((string) MAIL_ENCRYPTION) : 'ssl';
    $timeout = defined('MAIL_TIMEOUT') ? (int) MAIL_TIMEOUT : 8;
    $timeout = max(3, min($timeout, 10));
    $username = trim((string) MAIL_USERNAME);
    $password = preg_replace('/\s+/', '', (string) MAIL_PASSWORD);
    $fromEmail = trim((string) MAIL_FROM_EMAIL);
    $fromName = defined('MAIL_FROM_NAME') ? (string) MAIL_FROM_NAME : 'AquaSmile';
    $remote = $encryption === 'ssl' ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";

    $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        error_log("AquaSmile SMTP connection failed: {$errstr} ({$errno})");
        return false;
    }

    stream_set_timeout($socket, $timeout);

    try {
        smtp_expect($socket, [220]);
        smtp_command($socket, "EHLO " . smtp_local_domain(), [250]);

        if ($encryption === 'tls') {
            smtp_command($socket, "STARTTLS", [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Unable to enable SMTP TLS encryption.');
            }
            smtp_command($socket, "EHLO " . smtp_local_domain(), [250]);
        }

        smtp_command($socket, "AUTH LOGIN", [334]);
        smtp_command($socket, base64_encode($username), [334]);
        smtp_command($socket, base64_encode($password), [235]);
        smtp_command($socket, "MAIL FROM:<{$fromEmail}>", [250]);
        smtp_command($socket, "RCPT TO:<{$to}>", [250, 251]);
        smtp_command($socket, "DATA", [354]);

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . smtp_format_address($fromEmail, $fromName),
            'To: <' . $to . '>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $data = implode("\r\n", $headers) . "\r\n\r\n" . smtp_dot_stuff($body) . "\r\n.";
        smtp_command($socket, $data, [250]);
        smtp_command($socket, "QUIT", [221]);
        fclose($socket);
        return true;
    } catch (Throwable $e) {
        error_log('AquaSmile SMTP send failed: ' . $e->getMessage());
        @fclose($socket);
        return false;
    }
}

function smtp_command($socket, $command, $expectedCodes)
{
    $written = fwrite($socket, $command . "\r\n");
    if ($written === false) {
        throw new RuntimeException('Unable to write SMTP command.');
    }

    return smtp_expect($socket, $expectedCodes);
}

function smtp_expect($socket, $expectedCodes)
{
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (preg_match('/^\d{3}\s/', $line)) {
            break;
        }

        $meta = stream_get_meta_data($socket);
        if (!empty($meta['timed_out'])) {
            throw new RuntimeException('SMTP response timed out.');
        }
    }

    $meta = stream_get_meta_data($socket);
    if (!empty($meta['timed_out'])) {
        throw new RuntimeException('SMTP response timed out.');
    }

    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException(trim($response) ?: 'Empty SMTP response.');
    }

    return $response;
}

function smtp_local_domain()
{
    return $_SERVER['SERVER_NAME'] ?? 'localhost';
}

function smtp_format_address($email, $name)
{
    $cleanName = str_replace(['"', "\r", "\n"], ['', '', ''], $name);
    return '"' . $cleanName . '" <' . $email . '>';
}

function smtp_dot_stuff($body)
{
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $lines = explode("\n", $body);
    $lines = array_map(function ($line) {
        return substr($line, 0, 1) === '.' ? '.' . $line : $line;
    }, $lines);

    return implode("\r\n", $lines);
}

function should_expose_debug_otp()
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return stripos($host, 'localhost') !== false || stripos($host, '127.0.0.1') !== false;
}

function login_user()
{
    $data = request_json();
    $email = strtolower(trim($data['email'] ?? ''));
    $password = trim((string) ($data['password'] ?? ''));
    $errors = [];

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if ($errors) {
        json_response([
            'ok' => false,
            'message' => 'Please correct the errors below.',
            'errors' => $errors,
        ], 422);
    }

    $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(['ok' => false, 'message' => 'Invalid email or password.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'patient';

    $normalizedUser = normalize_user($user);
    json_response([
        'ok' => true,
        'user' => $normalizedUser,
        'redirect' => $normalizedUser['role'] === 'admin' ? 'admin.php' : 'index.php',
    ]);
}

function appointments()
{
    $userId = $_GET['user_id'] ?? '';
    $sql = appointment_sql();
    $params = [];

    if ($userId !== '') {
        $sql .= ' WHERE a.user_id = ?';
        $params[] = $userId;
    }

    $sql .= ' ORDER BY a.appointment_date DESC, a.appointment_time DESC';
    json_response(['ok' => true, 'appointments' => array_map('normalize_appointment', fetch_all($sql, $params))]);
}

function create_appointment()
{
    $data = request_json();
    $userId = (int) ($data['userId'] ?? $data['user_id'] ?? 0);
    $dentistId = (int) ($data['dentistId'] ?? $data['dentist_id'] ?? 0);
    $serviceId = (int) ($data['serviceId'] ?? $data['service_id'] ?? 0);
    $date = trim((string) ($data['date'] ?? $data['appointment_date'] ?? ''));
    $time = normalize_time_for_mysql((string) ($data['time'] ?? $data['appointment_time'] ?? ''));

    if ($userId <= 0 || $dentistId <= 0 || $serviceId <= 0 || $date === '' || $time === '') {
        json_response(['ok' => false, 'message' => 'Invalid appointment details. Please log in again and retry.'], 422);
    }

    if (!fetch_one('SELECT id FROM users WHERE id = ?', [$userId])) {
        json_response(['ok' => false, 'message' => 'Your account was not found in the database. Please log in again.'], 422);
    }

    if (!fetch_one('SELECT id FROM dentists WHERE id = ?', [$dentistId])) {
        json_response(['ok' => false, 'message' => 'Selected dentist was not found. Please refresh and try again.'], 422);
    }

    if (!fetch_one('SELECT id FROM services WHERE id = ?', [$serviceId])) {
        json_response(['ok' => false, 'message' => 'Selected service was not found. Please refresh and try again.'], 422);
    }

    execute_sql(
        'INSERT INTO appointments (user_id, dentist_id, service_id, appointment_date, appointment_time, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [
            $userId,
            $dentistId,
            $serviceId,
            $date,
            $time,
            $data['notes'] ?? '',
            'pending',
        ]
    );

    $id = (string) db()->lastInsertId();
    $row = fetch_one(appointment_sql() . ' WHERE a.id = ?', [$id]);
    json_response(['ok' => true, 'appointment' => normalize_appointment($row)]);
}

function create_notification($userId, $appointmentId, $message, $audience = 'user')
{
    execute_sql(
        'INSERT INTO notifications (user_id, appointment_id, audience, message) VALUES (?, ?, ?, ?)',
        [$userId, $appointmentId, $audience, $message]
    );
}

function update_appointment()
{
    require_admin();

    $data = request_json();
    $id = (int) ($data['id'] ?? 0);
    $status = $data['status'] ?? 'pending';
    $reason = trim($data['reason'] ?? '');

    if ($status === 'user_cancelled') {
        $status = 'cancelled';
    }

    $appt = fetch_one(appointment_sql() . ' WHERE a.id = ?', [$id]);
    if (!$appt) {
        json_response(['ok' => false, 'message' => 'Appointment not found.'], 404);
    }

    execute_sql(
        'UPDATE appointments SET status = ?, cancellation_reason = ?, cancelled_by = ? WHERE id = ?',
        [$status, $reason ?: null, $status === 'cancelled' ? 'admin' : null, $id]
    );

    if (in_array($status, ['confirmed', 'cancelled'], true)) {
        $message = 'Your appointment for ' . $appt['service_name'] . ' on ' . $appt['appointment_date'] . ' at ' . substr((string) $appt['appointment_time'], 0, 5) . ' has been ' . $status . '.';
        if ($reason !== '') {
            $message .= ' Reason: ' . $reason;
        }
        create_notification((int) $appt['user_id'], $id, $message, 'user');
    }

    $updated = fetch_one(appointment_sql() . ' WHERE a.id = ?', [$id]);
    json_response(['ok' => true, 'appointment' => normalize_appointment($updated)]);
}

function cancel_appointment()
{
    $userId = require_patient();
    $data = request_json();
    $id = (int) ($data['id'] ?? 0);
    $reason = trim((string) ($data['reason'] ?? ''));

    if ($reason === '') {
        json_response(['ok' => false, 'message' => 'Please provide a cancellation reason.'], 422);
    }

    $appt = fetch_one(appointment_sql() . ' WHERE a.id = ? AND a.user_id = ?', [$id, $userId]);

    if (!$appt) {
        json_response(['ok' => false, 'message' => 'Appointment not found.'], 404);
    }
    if ($appt['status'] !== 'pending') {
        json_response(['ok' => false, 'message' => 'Only pending appointments can be cancelled.'], 409);
    }

    execute_sql(
        'UPDATE appointments SET status = ?, cancellation_reason = ?, cancelled_by = ? WHERE id = ?',
        ['cancelled', $reason, 'user', $id]
    );
    create_notification(
        $userId,
        $id,
        trim(($appt['first_name'] ?? '') . ' ' . ($appt['last_name'] ?? '')) . ' cancelled the appointment for ' . $appt['service_name'] . ' on ' . $appt['appointment_date'] . ' at ' . substr((string) $appt['appointment_time'], 0, 5) . '. Reason: ' . $reason,
        'admin'
    );

    $updated = fetch_one(appointment_sql() . ' WHERE a.id = ?', [$id]);
    json_response([
        'ok' => true,
        'message' => 'Appointment cancelled successfully.',
        'appointment' => normalize_appointment($updated),
    ]);
}

function notifications()
{
    $userId = (int) ($_GET['user_id'] ?? 0);
    json_response([
        'ok' => true,
        'notifications' => fetch_all("SELECT * FROM notifications WHERE user_id = ? AND audience = 'user' ORDER BY created_at DESC", [$userId]),
    ]);
}

function mark_notifications_read()
{
    $data = request_json();
    execute_sql("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND audience = 'user'", [(int) ($data['userId'] ?? $data['user_id'] ?? 0)]);
    json_response(['ok' => true]);
}

function mark_notification_read()
{
    $userId = require_patient();
    $data = request_json();
    $notificationId = (int) ($data['id'] ?? 0);

    if ($notificationId <= 0) {
        json_response(['ok' => false, 'message' => 'Invalid notification.'], 422);
    }

    execute_sql(
        "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND audience = 'user'",
        [$notificationId, $userId]
    );

    json_response(['ok' => true]);
}

function mark_admin_notifications_read()
{
    require_admin();

    execute_sql("UPDATE notifications SET is_read = 1 WHERE audience = 'admin'");
    json_response(['ok' => true]);
}

function update_stock()
{
    require_admin();

    $data = request_json();
    $type = $data['type'] ?? 'product';
    $id = (int) ($data['id'] ?? 0);
    $quantity = max(0, (int) ($data['quantity'] ?? 0));

    if ($type === 'service') {
        execute_sql('UPDATE services SET daily_slots = ? WHERE id = ?', [$quantity, $id]);
    } else {
        execute_sql('UPDATE products SET stock_quantity = ? WHERE id = ?', [$quantity, $id]);
    }
    json_response(['ok' => true]);
}

function cart_items()
{
    $userId = (int) ($_GET['user_id'] ?? 0);
    if ($userId <= 0) {
        json_response(['ok' => false, 'message' => 'User is required.'], 422);
    }

    $rows = fetch_all(
        'SELECT ci.product_id, SUM(ci.quantity) AS quantity, MAX(ci.added_at) AS added_at, p.name, p.price
         FROM cart_items ci
         JOIN products p ON p.id = ci.product_id
         WHERE ci.user_id = ?
         GROUP BY ci.product_id, p.name, p.price
         ORDER BY added_at DESC',
        [$userId]
    );
    json_response(['ok' => true, 'cartItems' => $rows]);
}

function save_cart_item()
{
    $data = request_json();
    $userId = (int) ($data['userId'] ?? 0);
    $productId = (int) ($data['productId'] ?? 0);
    $quantity = max(0, (int) ($data['quantity'] ?? 1));

    if ($userId <= 0 || $productId <= 0) {
        json_response(['ok' => false, 'message' => 'Invalid cart item details.'], 422);
    }

    if (!fetch_one('SELECT id FROM users WHERE id = ?', [$userId])) {
        json_response(['ok' => false, 'message' => 'User not found. Please log in again.'], 422);
    }

    if (!fetch_one('SELECT id FROM products WHERE id = ?', [$productId])) {
        json_response(['ok' => false, 'message' => 'Product not found. Please refresh and try again.'], 422);
    }

    execute_sql('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?', [$userId, $productId]);

    if ($quantity > 0) {
        execute_sql(
            'INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)',
            [$userId, $productId, $quantity]
        );
    }

    json_response(['ok' => true]);
}

function remove_cart_item()
{
    $data = request_json();
    $userId = (int) ($data['userId'] ?? 0);
    $productId = (int) ($data['productId'] ?? 0);

    if ($userId <= 0 || $productId <= 0) {
        json_response(['ok' => false, 'message' => 'Invalid cart item details.'], 422);
    }

    execute_sql('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?', [$userId, $productId]);
    json_response(['ok' => true]);
}

function create_order()
{
    $data = request_json();
    $items = $data['items'] ?? [];
    $userId = (int) ($data['userId'] ?? 0);
    $total = (float) ($data['total'] ?? 0);
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $city = $data['city'] ?? '';
    $zip = $data['zip'] ?? '';
    $notes = $data['notes'] ?? '';
    $paymentMethod = $data['paymentMethod'] ?? $data['payment_method'] ?? 'cod';
    $gcashNumber = $data['gcash_number'] ?? '';

    execute_sql(
        'INSERT INTO orders (user_id, first_name, last_name, email, phone, address, city, zip, notes, payment_method, gcash_number, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$userId ?: null, $firstName, $lastName, $email, $phone, $address, $city, $zip, $notes, $paymentMethod, $gcashNumber, $total, 'pending']
    );

    $orderId = (string) db()->lastInsertId();
    foreach ($items as $item) {
        $productId = $item['id'] ?? $item['productId'] ?? 0;
        if (is_string($productId) && preg_match('/^P(\d+)$/', $productId, $match)) {
            $productId = (int) $match[1];
        }

        execute_sql(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)',
            [(int) $orderId, (int) $productId, (int) ($item['qty'] ?? $item['quantity'] ?? 1), (float) ($item['price'] ?? $item['unit_price'] ?? 0)]
        );
    }

    if ($userId > 0) {
        execute_sql('DELETE FROM cart_items WHERE user_id = ?', [$userId]);
    }

    json_response(['ok' => true, 'orderId' => $orderId]);
}

function user_account()
{
    $userId = require_patient();
    $user = fetch_one(
        'SELECT id, first_name, last_name, email, phone, birthdate, gender, emergency_contact_name,
                emergency_contact_number, house_no, street, barangay, city, province, zip_code,
                role, created_at
         FROM users
         WHERE id = ?',
        [$userId]
    );

    if (!$user) {
        json_response(['ok' => false, 'message' => 'Your account could not be found.'], 404);
    }

    $appointments = fetch_all(
        appointment_sql() . ' WHERE a.user_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC',
        [$userId]
    );

    $orders = fetch_all(
        'SELECT id, total_amount, payment_method, status, created_at, address, city, zip, notes
         FROM orders
         WHERE user_id = ?
         ORDER BY created_at DESC',
        [$userId]
    );

    $orderItems = fetch_all(
        'SELECT oi.order_id, oi.quantity, oi.unit_price, p.name AS product_name
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         JOIN orders o ON o.id = oi.order_id
         WHERE o.user_id = ?
         ORDER BY oi.order_id DESC, oi.id ASC',
        [$userId]
    );
    $itemsByOrder = [];
    foreach ($orderItems as $item) {
        $orderId = (string) $item['order_id'];
        $quantity = (int) ($item['quantity'] ?? 0);
        $unitPrice = (float) ($item['unit_price'] ?? 0);
        $itemsByOrder[$orderId][] = [
            'name' => $item['product_name'] ?? 'Product',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }

    $notifications = fetch_all(
        "SELECT id, message, is_read, created_at
         FROM notifications
         WHERE user_id = ? AND audience = 'user'
         ORDER BY created_at DESC",
        [$userId]
    );

    json_response([
        'ok' => true,
        'user' => [
            'id' => (string) $user['id'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'birthdate' => $user['birthdate'] ?? '',
            'gender' => $user['gender'] ?? '',
            'house_no' => $user['house_no'] ?? '',
            'street' => $user['street'] ?? '',
            'barangay' => $user['barangay'] ?? '',
            'city' => $user['city'] ?? '',
            'province' => $user['province'] ?? '',
            'zip_code' => $user['zip_code'] ?? '',
            'emergency_contact_name' => $user['emergency_contact_name'] ?? '',
            'emergency_contact_number' => $user['emergency_contact_number'] ?? '',
            'role' => $user['role'] ?? 'patient',
            'created_at' => $user['created_at'] ?? '',
        ],
        'appointments' => array_map('normalize_appointment', $appointments),
        'orders' => array_map(function ($order) use ($itemsByOrder) {
            $orderId = (string) $order['id'];
            return [
                'id' => $orderId,
                'total' => (float) $order['total_amount'],
                'payment_method' => $order['payment_method'] ?? '',
                'status' => $order['status'] ?? 'pending',
                'created_at' => $order['created_at'] ?? '',
                'address' => $order['address'] ?? '',
                'city' => $order['city'] ?? '',
                'zip' => $order['zip'] ?? '',
                'notes' => $order['notes'] ?? '',
                'items' => $itemsByOrder[$orderId] ?? [],
            ];
        }, $orders),
        'notifications' => array_map(function ($notification) {
            return [
                'id' => (string) $notification['id'],
                'message' => $notification['message'] ?? '',
                'is_read' => (int) ($notification['is_read'] ?? 0) === 1,
                'created_at' => $notification['created_at'] ?? '',
            ];
        }, $notifications),
    ]);
}

function update_profile()
{
    $userId = require_patient();
    $data = request_json();
    $firstName = trim((string) ($data['first_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $birthdate = trim((string) ($data['birthdate'] ?? ''));
    $gender = trim((string) ($data['gender'] ?? ''));
    $houseNo = trim((string) ($data['house_no'] ?? ''));
    $street = trim((string) ($data['street'] ?? ''));
    $barangay = trim((string) ($data['barangay'] ?? ''));
    $city = trim((string) ($data['city'] ?? ''));
    $province = trim((string) ($data['province'] ?? ''));
    $zipCode = trim((string) ($data['zip_code'] ?? ''));
    $emergencyContactName = trim((string) ($data['emergency_contact_name'] ?? ''));
    $emergencyContactNumber = trim((string) ($data['emergency_contact_number'] ?? ''));
    $errors = [];

    if ($firstName === '') {
        $errors[] = 'First name is required.';
    }
    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = 'Phone number must start with 09 and contain exactly 11 digits.';
    }
    if ($birthdate !== '') {
        $parsedBirthdate = DateTime::createFromFormat('Y-m-d', $birthdate);
        $today = new DateTime('today');
        if (!$parsedBirthdate || $parsedBirthdate->format('Y-m-d') !== $birthdate || $parsedBirthdate >= $today) {
            $errors[] = 'Birthdate must be a valid past date.';
        }
    }
    $allowedGenders = ['Female', 'Male', 'Prefer not to say'];
    if (!in_array($gender, $allowedGenders, true)) {
        $errors[] = 'Please select a valid gender.';
    }
    $addressLimits = [
        'House No.' => [$houseNo, 50],
        'Street' => [$street, 150],
        'Barangay' => [$barangay, 100],
        'City / Municipality' => [$city, 100],
        'Province / Region' => [$province, 100],
        'ZIP Code' => [$zipCode, 10],
    ];
    foreach ($addressLimits as $label => [$value, $limit]) {
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length > $limit) {
            $errors[] = "{$label} must not exceed {$limit} characters.";
        }
    }
    if ($zipCode !== '' && !preg_match('/^\d+$/', $zipCode)) {
        $errors[] = 'ZIP Code must contain numbers only.';
    }
    if ($emergencyContactNumber !== '' && !preg_match('/^09\d{9}$/', $emergencyContactNumber)) {
        $errors[] = 'Emergency contact number must start with 09 and contain exactly 11 digits.';
    }
    if ($errors) {
        json_response(['ok' => false, 'message' => 'Please correct the profile fields.', 'errors' => $errors], 422);
    }

    execute_sql(
        'UPDATE users
         SET first_name = ?, last_name = ?, phone = ?, birthdate = ?, gender = ?,
             emergency_contact_name = ?, emergency_contact_number = ?, house_no = ?, street = ?,
             barangay = ?, city = ?, province = ?, zip_code = ?
         WHERE id = ?',
        [
            $firstName,
            $lastName,
            $phone,
            $birthdate !== '' ? $birthdate : null,
            $gender !== '' ? $gender : null,
            $emergencyContactName !== '' ? $emergencyContactName : null,
            $emergencyContactNumber !== '' ? $emergencyContactNumber : null,
            $houseNo !== '' ? $houseNo : null,
            $street !== '' ? $street : null,
            $barangay !== '' ? $barangay : null,
            $city !== '' ? $city : null,
            $province !== '' ? $province : null,
            $zipCode !== '' ? $zipCode : null,
            $userId,
        ]
    );

    $user = fetch_one('SELECT * FROM users WHERE id = ?', [$userId]);
    $_SESSION['user_name'] = trim($firstName . ' ' . $lastName);

    json_response(['ok' => true, 'message' => 'Profile updated successfully.', 'user' => normalize_user($user)]);
}

function change_password()
{
    $userId = require_patient();
    $data = request_json();
    $currentPassword = (string) ($data['current_password'] ?? '');
    $newPassword = (string) ($data['new_password'] ?? '');
    $confirmPassword = (string) ($data['confirm_password'] ?? '');
    $errors = [];

    if ($currentPassword === '') {
        $errors[] = 'Current password is required.';
    }
    if (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
        $errors[] = 'New password must include at least one letter and one number.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation do not match.';
    }
    if ($errors) {
        json_response(['ok' => false, 'message' => 'Please correct the password fields.', 'errors' => $errors], 422);
    }

    $user = fetch_one('SELECT password_hash FROM users WHERE id = ?', [$userId]);
    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        json_response(['ok' => false, 'message' => 'Current password is incorrect.'], 422);
    }
    if (password_verify($newPassword, $user['password_hash'])) {
        json_response(['ok' => false, 'message' => 'New password must be different from your current password.'], 422);
    }

    execute_sql(
        'UPDATE users SET password_hash = ? WHERE id = ?',
        [password_hash($newPassword, PASSWORD_DEFAULT), $userId]
    );

    json_response(['ok' => true, 'message' => 'Password changed successfully.']);
}

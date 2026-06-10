<?php

require __DIR__ . '/db.php';

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

try {
    ensure_notifications_table();
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
        'email' => $row['email'] ?? '',
        'contact' => $row['phone'] ?? '',
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
        "SELECT o.*, IFNULL(CONCAT(u.first_name, ' ', u.last_name), o.customer_name) AS customer_name
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

    try {
        execute_sql(
            'INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)',
            [$firstName, $lastName, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]
        );
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            json_response([
                'ok' => false,
                'message' => 'An account with this email already exists.',
                'errors' => ['An account with this email already exists.'],
            ], 409);
        }
        throw $e;
    }

    $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);
    json_response([
        'ok' => true,
        'message' => 'Account created successfully.',
        'user' => normalize_user($user),
    ], 201);
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
    $data = request_json();
    $id = (int) ($data['id'] ?? 0);
    $userId = (int) ($data['userId'] ?? $data['user_id'] ?? 0);
    $appt = fetch_one(appointment_sql() . ' WHERE a.id = ? AND a.user_id = ?', [$id, $userId]);

    if (!$appt) {
        json_response(['ok' => false, 'message' => 'Appointment not found.'], 404);
    }
    if ($appt['status'] !== 'pending') {
        json_response(['ok' => false, 'message' => 'Only pending appointments can be cancelled.'], 409);
    }

    execute_sql(
        'UPDATE appointments SET status = ?, cancellation_reason = ?, cancelled_by = ? WHERE id = ?',
        ['cancelled', 'Cancelled by patient before admin approval.', 'user', $id]
    );
    create_notification(
        $userId,
        $id,
        trim(($appt['first_name'] ?? '') . ' ' . ($appt['last_name'] ?? '')) . ' cancelled the appointment for ' . $appt['service_name'] . ' on ' . $appt['appointment_date'] . ' at ' . substr((string) $appt['appointment_time'], 0, 5) . '.',
        'admin'
    );

    json_response(['ok' => true]);
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
    $customerName = $data['customerName'] ?? $data['first_name'] . ' ' . ($data['last_name'] ?? '');
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $city = $data['city'] ?? '';
    $zip = $data['zip'] ?? '';
    $notes = $data['notes'] ?? '';
    $paymentMethod = $data['paymentMethod'] ?? $data['payment_method'] ?? 'cod';
    $gcashNumber = $data['gcash_number'] ?? '';

    execute_sql(
        'INSERT INTO orders (user_id, customer_name, email, phone, address, city, zip, notes, payment_method, gcash_number, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$userId ?: null, $customerName, $email, $phone, $address, $city, $zip, $notes, $paymentMethod, $gcashNumber, $total, 'pending']
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

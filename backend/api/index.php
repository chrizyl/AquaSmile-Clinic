<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    ensure_notifications_table();
    ensure_optional_columns();

    match ($action) {
        'health' => json_response(['ok' => true, 'database' => DB_NAME]),
        'catalog' => catalog(),
        'dashboard' => dashboard(),
        'register' => register_user(),
        'login' => login_user(),
        'appointments' => appointments(),
        'create_appointment' => create_appointment(),
        'update_appointment' => update_appointment(),
        'cancel_appointment' => cancel_appointment(),
        'notifications' => notifications(),
        'mark_notifications_read' => mark_notifications_read(),
        'update_stock' => update_stock(),
        'cart_items' => cart_items(),
        'save_cart_item' => save_cart_item(),
        'create_order' => create_order(),
        default => json_response(['ok' => false, 'message' => 'Unknown API action.'], 404),
    };
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => $e->getMessage()], 500);
}

function ensure_notifications_table(): void
{
    execute_sql(
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            appointment_id INT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )"
    );
}

function column_exists(string $table, string $column): bool
{
    $row = fetch_one(
        'SELECT COUNT(*) AS total FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
        [$table, $column]
    );
    return (int) ($row['total'] ?? 0) > 0;
}

function ensure_optional_columns(): void
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
}

function normalize_user(array $row): array
{
    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    return [
        'id' => (string) $row['id'],
        'name' => $name,
        'email' => $row['email'] ?? '',
        'contact' => $row['phone'] ?? '',
        'createdAt' => $row['created_at'] ?? '',
    ];
}

function normalize_dentist(array $row): array
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

function normalize_service(array $row): array
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

function normalize_product(array $row): array
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

function normalize_appointment(array $row): array
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
        'cancellationReason' => '',
        'cancelledBy' => '',
        'createdAt' => $row['created_at'] ?? '',
    ];
}

function normalize_time_for_mysql(string $time): string
{
    $time = trim($time);
    $parsed = DateTime::createFromFormat('g:i A', $time)
        ?: DateTime::createFromFormat('h:i A', $time)
        ?: DateTime::createFromFormat('H:i', $time)
        ?: DateTime::createFromFormat('H:i:s', $time);

    return $parsed ? $parsed->format('H:i:s') : $time;
}

function appointment_sql(): string
{
    return "SELECT a.*, u.first_name, u.last_name, u.email, u.phone,
            s.name AS service_name, d.name AS dentist_name
            FROM appointments a
            JOIN users u ON u.id = a.user_id
            JOIN services s ON s.id = a.service_id
            JOIN dentists d ON d.id = a.dentist_id";
}

function catalog(): never
{
    json_response([
        'ok' => true,
        'services' => array_map('normalize_service', fetch_all('SELECT * FROM services ORDER BY name')),
        'products' => array_map('normalize_product', fetch_all('SELECT * FROM products ORDER BY name')),
        'dentists' => array_map('normalize_dentist', fetch_all('SELECT * FROM dentists ORDER BY name')),
    ]);
}

function dashboard(): never
{
    $orders = fetch_all(
        "SELECT o.*, IFNULL(CONCAT(u.first_name, ' ', u.last_name), o.customer_name) AS customer_name
         FROM orders o
         LEFT JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC"
    );
    $orders = array_map(fn($row) => [
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
    ], $orders);

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

function register_user(): never
{
    $data = request_json();
    $firstName = trim($data['fname'] ?? $data['first_name'] ?? '');
    $lastName = trim($data['lname'] ?? $data['last_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['contact'] ?? $data['phone'] ?? '');
    $password = (string) ($data['password'] ?? '');

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
        json_response(['ok' => false, 'message' => 'All fields are required.'], 422);
    }

    execute_sql(
        'INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)',
        [$firstName, $lastName, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]
    );

    $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);
    json_response(['ok' => true, 'user' => normalize_user($user)]);
}

function login_user(): never
{
    $data = request_json();
    $email = trim($data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');
    $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(['ok' => false, 'message' => 'Invalid email or password.'], 401);
    }

    json_response(['ok' => true, 'user' => normalize_user($user)]);
}

function appointments(): never
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

function create_appointment(): never
{
    $data = request_json();
    execute_sql(
        'INSERT INTO appointments (user_id, dentist_id, service_id, appointment_date, appointment_time, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [
            (int) ($data['userId'] ?? $data['user_id'] ?? 0),
            (int) ($data['dentistId'] ?? $data['dentist_id'] ?? 0),
            (int) ($data['serviceId'] ?? $data['service_id'] ?? 0),
            $data['date'] ?? $data['appointment_date'] ?? '',
            normalize_time_for_mysql((string) ($data['time'] ?? $data['appointment_time'] ?? '')),
            $data['notes'] ?? '',
            'pending',
        ]
    );

    $id = (string) db()->lastInsertId();
    $row = fetch_one(appointment_sql() . ' WHERE a.id = ?', [$id]);
    json_response(['ok' => true, 'appointment' => normalize_appointment($row)]);
}

function create_notification(int $userId, ?int $appointmentId, string $message): void
{
    execute_sql(
        'INSERT INTO notifications (user_id, appointment_id, message) VALUES (?, ?, ?)',
        [$userId, $appointmentId, $message]
    );
}

function update_appointment(): never
{
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

    $message = 'Your appointment for ' . $appt['service_name'] . ' on ' . $appt['appointment_date'] . ' at ' . substr((string) $appt['appointment_time'], 0, 5) . ' has been ' . $status . '.';
    if ($reason !== '') {
        $message .= ' Reason: ' . $reason;
    }
    create_notification((int) $appt['user_id'], $id, $message);

    $updated = fetch_one(appointment_sql() . ' WHERE a.id = ?', [$id]);
    json_response(['ok' => true, 'appointment' => normalize_appointment($updated)]);
}

function cancel_appointment(): never
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
    create_notification($userId, $id, 'You cancelled your appointment for ' . $appt['service_name'] . ' on ' . $appt['appointment_date'] . ' at ' . substr((string) $appt['appointment_time'], 0, 5) . '.');

    json_response(['ok' => true]);
}

function notifications(): never
{
    $userId = (int) ($_GET['user_id'] ?? 0);
    json_response([
        'ok' => true,
        'notifications' => fetch_all('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC', [$userId]),
    ]);
}

function mark_notifications_read(): never
{
    $data = request_json();
    execute_sql('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [(int) ($data['userId'] ?? $data['user_id'] ?? 0)]);
    json_response(['ok' => true]);
}

function update_stock(): never
{
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

function cart_items(): never
{
    $userId = (int) ($_GET['user_id'] ?? 0);
    $rows = fetch_all(
        'SELECT ci.*, p.name, p.price FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?',
        [$userId]
    );
    json_response(['ok' => true, 'cartItems' => $rows]);
}

function save_cart_item(): never
{
    $data = request_json();
    execute_sql(
        'INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)',
        [(int) ($data['userId'] ?? 0), (int) ($data['productId'] ?? 0), max(1, (int) ($data['quantity'] ?? 1))]
    );
    json_response(['ok' => true]);
}

function create_order(): never
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

    json_response(['ok' => true, 'orderId' => $orderId]);
}

<?php

// Database configuration
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'aquasmile_clinic';

try {
    // First, connect without database to create it
    $pdoNoDB = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Create database
    $pdoNoDB->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created or already exists.<br>";

    // Now connect to the database
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Read and execute schema
    $schemaFile = __DIR__ . '/api/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException('Schema file not found: ' . $schemaFile);
    }

    $sql = file_get_contents($schemaFile);
    if ($sql === false) {
        throw new RuntimeException('Cannot read schema.sql');
    }

    // Split and execute statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($s) {
            return !empty($s);
        }
    );

    foreach ($statements as $statement) {
        $pdo->exec($statement . ';');
    }

    // Add migration for existing databases - add missing columns to orders table
    $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'customer_name'");
    if (!$result->fetch()) {
        echo "✓ Migrating orders table...<br>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255) NULL AFTER user_id");
        $pdo->exec("ALTER TABLE orders ADD COLUMN email VARCHAR(120) NULL AFTER customer_name");
        $pdo->exec("ALTER TABLE orders ADD COLUMN phone VARCHAR(20) NULL AFTER email");
        $pdo->exec("ALTER TABLE orders ADD COLUMN address TEXT NULL AFTER phone");
        $pdo->exec("ALTER TABLE orders ADD COLUMN city VARCHAR(100) NULL AFTER address");
        $pdo->exec("ALTER TABLE orders ADD COLUMN zip VARCHAR(20) NULL AFTER city");
        $pdo->exec("ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER zip");
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER notes");
        $pdo->exec("ALTER TABLE orders ADD COLUMN gcash_number VARCHAR(20) NULL AFTER payment_method");
    }

    echo "✓ All tables created and seed data inserted successfully!<br><br>";
    echo "<strong>Database initialized:</strong><br>";
    echo "- Database: " . DB_NAME . "<br>";
    echo "- Host: " . DB_HOST . "<br>";
    echo "- User: " . DB_USER . "<br><br>";
    echo "<a href='../frontend/admin.php'>Go to Admin Dashboard</a>";

} catch (PDOException $e) {
    http_response_code(500);
    echo "<h1>Database Connection Error</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure XAMPP MySQL is running on localhost:3306</p>";
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Setup Error</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

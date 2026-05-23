<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

try {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) {
        throw new RuntimeException('Cannot read schema.sql');
    }

    db()->exec($sql);
    json_response([
        'ok' => true,
        'message' => 'aquasmile_clinic schema checked and seed data inserted.',
        'next' => '../admin.php',
    ]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => $e->getMessage()], 500);
}

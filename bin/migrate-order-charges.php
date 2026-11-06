<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "Enable pdo_mysql or apply migrations/20261106_order_charges.sql in phpMyAdmin.\n");
    exit(1);
}

// Additive migration: no existing orders or products are modified.
$sql = file_get_contents(__DIR__ . '/../migrations/20261106_order_charges.sql');
db()->prepare($sql)->execute();
echo "Order charges migration applied.\n";

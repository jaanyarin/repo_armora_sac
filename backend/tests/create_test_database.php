<?php

$host = '127.0.0.1';
$port = '5434';
$username = 'armora';
$password = 'armora_dev_2026';
$testDb = 'armora_erp_test';

try {
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '{$testDb}'");
    if ($stmt->fetch()) {
        $pdo->exec("DROP DATABASE {$testDb}");
        echo "• Dropped existing test database." . PHP_EOL;
    }
    $pdo->exec("CREATE DATABASE {$testDb} WITH OWNER {$username}");
    echo "✓ Created test database '{$testDb}'." . PHP_EOL;
} catch (PDOException $e) {
    echo "⚠ Warning: Could not create test database: " . $e->getMessage() . PHP_EOL;
    echo "  Make sure Docker (PostgreSQL) is running on {$host}:{$port}." . PHP_EOL;
    exit(1);
}

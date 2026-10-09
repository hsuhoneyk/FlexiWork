<?php
/**
 * db.php
 * Database connection for the Flexiwork job board.
 * Include this file wherever you need database access:
 *   require_once 'db.php';
 *   // then use $pdo
 */

// ---- Connection settings ----
// Replace these with your actual database credentials.
$host   = 'localhost';
$dbname = 'flexi_db';
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // return rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                     // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // In production, log this instead of showing it to visitors.
    die('Database connection failed: ' . $e->getMessage());
}
<?php
$pdo = null;

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=sofeng;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "DB FAIL: " . $e->getMessage();
    exit();
}

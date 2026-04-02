<?php
// config/database.php

$host = 'localhost';
$user = 'fandirrdb';      // Sesuaikan dengan user database Anda
$pass = '@Fandirr05';          // Sesuaikan dengan password database Anda
$db = 'fandirr_pay';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    // PENTING: Jangan gunakan die() biasa, tapi kirim JSON error
    // Supaya JS bisa membacanya dan tidak dianggap "Gagal Terhubung"
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Koneksi DB Gagal: ' . $e->getMessage()]);
    exit;
}
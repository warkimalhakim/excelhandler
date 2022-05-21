<?php

namespace Warkim\ExcelHandler;

// Database connect
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$db     = 'excel';

$conn   = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Tidak terkoneksi dengan database");
}

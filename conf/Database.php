<?php

namespace Warkim\ExcelHandler;

// class Connect
// {
//     public
//         $host   = '',
//         $user   = '',
//         $pass   = '',
//         $db     = '';

//     function __construct()
//     {
//         try {
//             $conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
//         } catch (Exception $e) {
//             return 'Database tidak terkoneksi' . $e;
//         }
//     }
// }

// Database connect
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$db     = 'excel';

$conn   = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Tidak terkoneksi dengan database");
}

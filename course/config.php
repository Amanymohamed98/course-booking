<?php
// includes/config.php

session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "courses_booking_system";

// إنشاء اتصال
$conn = new mysqli($host, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تعيين مجموعة المحارف لضبط اللغة العربية
$conn->set_charset("utf8");
?>
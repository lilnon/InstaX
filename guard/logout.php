<?php
session_start();

// ล้างค่าของ session
session_unset();
session_destroy();

// เปลี่ยนเส้นทางไปหน้า login
header('Location: login.php');
exit();
?>

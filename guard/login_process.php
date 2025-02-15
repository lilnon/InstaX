<?php
include '../include/connect.php';

// รับข้อมูลจากฟอร์ม
$username = $_POST['username'];
$password = $_POST['password'];

// ตรวจสอบผู้ใช้ในฐานข้อมูล
$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // ถ้าผู้ใช้และรหัสผ่านตรงกัน
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header('Location: ../index.php'); // เปลี่ยนไปหน้า dashboard
    exit;
} else {
    // ถ้าไม่ตรงกัน
    echo "<p>ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง</p>";
}
?>

<?php
include '../include/connect.php'; // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบให้แน่ใจว่ารหัสผ่านตรงกัน
    if ($password !== $confirm_password) {
        echo "รหัสผ่านไม่ตรงกัน!";
        exit;
    }

    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ตรวจสอบว่า username มีอยู่ในฐานข้อมูลหรือไม่
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว!";
        exit;
    }

    // บันทึกข้อมูลผู้ใช้ใหม่ลงในฐานข้อมูล
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->execute(['username' => $username, 'password' => $hashed_password]);

    echo "สมัครสมาชิกสำเร็จ!";
    header("Location: login.php"); // เปลี่ยนหน้าไปที่หน้าล็อกอิน
    exit;
}
?>

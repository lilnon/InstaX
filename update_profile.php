<?php
session_start();
include './include/connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูลของคุณ

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $newUsername = $_POST['username'];
    $newDescription = $_POST['description'];
    $newPassword = $_POST['password'];
    $profileImage = $_FILES['profileImage'];

    // ตรวจสอบว่ามีรูปภาพใหม่หรือไม่
    if ($profileImage['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $profileImage['tmp_name'];
        $imageName = uniqid() . '_' . $profileImage['name'];
        $imagePath = 'uploads/' . $imageName;

        // ย้ายรูปภาพไปยังโฟลเดอร์ uploads
        move_uploaded_file($imageTmpName, $imagePath);

        // อัปเดตรูปภาพและคำอธิบายในฐานข้อมูล
        $stmt = $pdo->prepare("UPDATE users SET username = ?, description = ?, password = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newDescription, $newPassword, $imagePath, $userId]);
        $_SESSION['profile_image'] = $imagePath; // อัปเดต session
    } else {
        // อัปเดตข้อมูลโดยไม่เปลี่ยนรูปภาพ
        $stmt = $pdo->prepare("UPDATE users SET username = ?, description = ?, password = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newDescription, $newPassword, $userId]);
    }

    // อัปเดต username และ description ใน session
    $_SESSION['username'] = $newUsername;
    $_SESSION['description'] = $newDescription;

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
<?php
include './include/connect.php'; // ตรวจสอบให้แน่ใจว่าไฟล์นี้เชื่อมต่อฐานข้อมูลของคุณ

session_start(); // เริ่ม session เพื่อใช้งาน $_SESSION

// ตรวจสอบว่ามีข้อมูลถูกส่งมาแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ดึงเนื้อหาโพสต์จากฟอร์ม
    $postContent = $_POST['postContent'];

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพหรือไม่
    $imagePath = null;
    if (isset($_FILES['imageInput']) && $_FILES['imageInput']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['imageInput']['tmp_name'];
        $imageName = basename($_FILES['imageInput']['name']);
        $imagePath = 'uploads/' . $imageName;

        // ย้ายไฟล์ที่อัปโหลดไปยังไดเรกทอรี 'uploads'
        if (move_uploaded_file($imageTmpName, $imagePath)) {
            // อัปโหลดรูปภาพสำเร็จ
        } else {
            // จัดการข้อผิดพลาดหากอัปโหลดรูปภาพล้มเหลว
            echo "Failed to upload image.";
        }
    }

    // ดึง ID ผู้ใช้และชื่อผู้ใช้จาก session (ตรวจสอบว่า session ถูกตั้งค่าอย่างถูกต้อง)
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $userId = $_SESSION['user_id'];
        $username = $_SESSION['username'];
    } else {
        // หากไม่มี session ให้จัดการข้อผิดพลาด (เช่น redirect ไปยังหน้า login)
        echo json_encode(['success' => false, 'error' => 'User not logged in.']);
        exit;
    }

    // แทรกข้อมูลโพสต์ลงในฐานข้อมูล (เนื้อหาและรูปภาพ)
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    if ($stmt->execute([$userId, $postContent, $imagePath])) {
        // ส่งการตอบกลับ JSON
        echo json_encode([
            'success' => true,
            'postContent' => $postContent,
            'image' => $imagePath, // ส่ง path รูปภาพเพื่อแสดง
            'createdAt' => date('F j, Y, g:i a'), // เพิ่ม timestamp การสร้าง
            'username' => $username // เพิ่ม username ใน response
        ]);
    } else {
        // หากการแทรกข้อมูลล้มเหลว
        echo json_encode(['success' => false, 'error' => 'Failed to insert post.']);
    }
}
?>
<?php
session_start();
include './include/connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูลของคุณ

if (isset($_SESSION['user_id']) && isset($_POST['postId'])) {
    $userId = $_SESSION['user_id'];
    $postId = $_POST['postId'];
    $postContent = $_POST['postContent'];
    $image = null;

    // ตรวจสอบว่าผู้ใช้เป็นเจ้าของโพสต์
    $stmt = $pdo->prepare("SELECT user_id, image FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post && $post['user_id'] == $userId) {
        // อัปโหลดรูปภาพ (ถ้ามี)
        if (isset($_FILES['imageInput']) && $_FILES['imageInput']['error'] == 0) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["imageInput"]["name"]);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if (move_uploaded_file($_FILES["imageInput"]["tmp_name"], $targetFile)) {
                $image = $targetFile;
                // ลบรูปภาพเก่า (ถ้ามี)
                if ($post['image']) {
                    unlink($post['image']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ']);
                exit;
            }
        }
        // อัปเดตโพสต์
        $stmt = $pdo->prepare("UPDATE posts SET content = ?, image = ? WHERE id = ?");
        if ($stmt->execute([$postContent, $image, $postId])) {
            echo json_encode(['success' => true, 'image' => $image]);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตโพสต์']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการแก้ไขโพสต์นี้']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
}
?>
<?php
session_start();
include './include/connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูลของคุณ

if (isset($_SESSION['user_id']) && isset($_POST['post_id'])) {
    $userId = $_SESSION['user_id'];
    $postId = $_POST['post_id'];

    // ตรวจสอบว่าผู้ใช้เป็นเจ้าของโพสต์
    $stmt = $pdo->prepare("SELECT user_id, image FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post && $post['user_id'] == $userId) {
        // ลบ likes ที่เกี่ยวข้อง
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
        $stmt->execute([$postId]);

        // ลบ comments ที่เกี่ยวข้อง
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);

        // ลบรูปภาพ (ถ้ามี)
        if ($post['image']) {
            unlink($post['image']);
        }

        // ลบโพสต์
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        if ($stmt->execute([$postId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบโพสต์']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการลบโพสต์นี้']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
}
?>
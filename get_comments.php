<?php
include './include/connect.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล
$postId = $_GET['post_id'];
$comments = $pdo->prepare("SELECT comments.*, users.username, users.profile_image, comments.user_id FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY created_at DESC");
$comments->execute([$postId]);
$html = '';
while ($comment = $comments->fetch(PDO::FETCH_ASSOC)) {
    $html .= '<li class="comment flex justify-between items-center py-2">';
    $html .= '<div class="flex items-center">';
    if (!empty($comment['profile_image'])) {
        $html .= '<img src="' . $comment['profile_image'] . '" alt="User Profile" class="w-8 h-8 rounded-full mr-2">';
    } else {
        $html .= '<i class="fa-solid fa-user rounded-full mr-2 text-gray-600"></i>';
    }
    $html .= '<span class="font-semibold">' . $comment['username'] . ':</span>';
    $html .= '<span>' . htmlspecialchars($comment['content']) . '</span>';
    $html .= '</div>';
    if ($comment['user_id'] == $_SESSION['user_id']) {
        $html .= '<div class="dropdown dropdown-end">';
        $html .= '<label tabindex="0" class="btn btn-ghost btn-sm btn-circle"><i class="fas fa-ellipsis-h"></i></label>';
        $html .= '<ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40">';
        $html .= '<li><button class="edit-comment" data-comment-id="' . $comment['id'] . '">แก้ไข</button></li>';
        $html .= '<li><button class="delete-comment" data-comment-id="' . $comment['id'] . '">ลบ</button></li>';
        $html .= '</ul>';
        $html .= '</div>';
    }
    $html .= '</li>';
}
echo $html;
?>
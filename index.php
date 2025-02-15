<?php
include './layout/header.php';

// ตรวจสอบว่า session มีข้อมูลผู้ใช้หรือไม่
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $profileImage = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : '';
    $userId = $_SESSION['user_id'];
} else {
    header('Location: login.php');
    exit();
}


// ดึงโพสต์จากฐานข้อมูล
$result = $pdo->query("SELECT posts.*, users.username, users.profile_image FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");

// ฟังก์ชันสำหรับนับจำนวน Like
function countLikes($postId, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$postId]);
    return $stmt->fetchColumn();
}

// ฟังก์ชันสำหรับนับจำนวน Comment
function countComments($postId, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $stmt->execute([$postId]);
    return $stmt->fetchColumn();
}

// ฟังก์ชันตรวจสอบว่าผู้ใช้เคยกด Like หรือยัง
function isLikedByUser($postId, $userId, $pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    return $stmt->fetch() !== false;
}
?>

<style>
    .comment-options .options-menu {
        min-width: 120px;
    }

    .comment-options .options-menu.hidden {
        display: none;
    }

    .comment-options:hover .options-menu {
        display: block;
    }
</style>

<!-- ตรงนี้ -->
<div class="flex justify-center items-center mt-4 px-8">
    <div class="card bg-base-100 w-[80%] shadow-xl">
        <div class="hero-content text-center">
            <div class="max-w-xl">
                <h1 class="text-5xl font-bold">คุณคิดอะไรอยู่ <?php echo $username; ?></h1>
                <p class="py-3">เขียนสิ่งที่คุณกำลังคิด...</p>
                <button class="btn btn-error mt-2 px-10" id="openModalBtn">โพสต์</button>
            </div>
        </div>
    </div>
</div>

<div id="postModal" class="fixed inset-0 flex items-center justify-center bg-opacity-50 hidden opacity-0 transition-opacity duration-300" style="z-index: 1000;">
    <div class="bg-base-100 p-6 rounded-lg w-[90%] sm:w-[80%] lg:w-[60%] xl:w-[50%] border-2 border-white transform scale-95 transition-transform duration-300">
        <div class="flex items-center mb-4">
            <?php if (empty($profileImage)): ?>
                <i class="fa-solid fa-user rounded-full mr-4 text-gray-600"></i>
            <?php else: ?>
                <img src="<?php echo $profileImage; ?>" alt="User Profile" class="w-12 h-12 rounded-full mr-4">
            <?php endif; ?>
            <h2 class="text-xl font-bold"><?php echo $username; ?></h2>
        </div>
        <textarea id="postContent" class="textarea textarea-bordered w-full mb-4" placeholder="เขียนสิ่งที่คุณคิด..."></textarea>

        <div id="imagePreviewContainer" class="mb-4">
        </div>

        <div class="flex justify-between items-center">
            <input type="file" id="imageInput" class="btn btn-outline btn-sm" accept="image/*" />
            <a href="./index.php">
                <button class="btn btn-error" id="submitPostBtn">โพสต์</button>
            </a>
        </div>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-lg text-gray-500 hover:text-gray-800">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
</div>

<div id="postsContainer" class="flex flex-col items-center gap-6 mt-6">
    <?php
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="post w-full max-w-3xl px-4 py-6 border bg-base-300 rounded-lg shadow-lg">
            <div class="post-header flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <?php if (!empty($row['profile_image'])): ?>
                        <img src="<?php echo $row['profile_image']; ?>" alt="User Profile" class="w-8 h-8 rounded-full mr-2">
                    <?php else: ?>
                        <i class="fa-solid fa-user rounded-full mr-2 text-gray-600"></i>
                    <?php endif; ?>
                    <h2 class="font-bold text-lg"><?php echo $row['username']; ?></h2>
                    <span class="text-gray-500 text-sm ml-2"><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></span>
                </div>
                <?php if ($row['user_id'] == $userId) { ?>
                    <div>
                        <a href="#" class="btn btn-sm btn-outline mr-1 edit-post-btn" data-post-id="<?php echo $row['id']; ?>">แก้ไข</a>
                        <a href="index.php"><button class="btn btn-sm btn-error delete-post-btn" data-post-id="<?php echo $row['id']; ?>">ลบ</button></a>
                    </div>
                <?php } ?>
            </div>
            <div class="post-content">
                <p><?php echo $row['content']; ?></p>
                <?php if ($row['image']) { ?>
                    <img src="<?php echo $row['image']; ?>" alt="Post Image" class="w-full h-64 object-cover rounded-lg mt-2">
                <?php } ?>
                <div class="mt-4">
                    <form method="post" action="like.php" class="inline-block mr-2">
                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                        <button class="btn btn-ghost btn-sm flex items-center <?php echo isLikedByUser($row['id'], $userId, $pdo) ? 'text-red-500' : ''; ?>">
                            <i class="fas fa-heart mr-1"></i> Like
                            <span class="ml-1 text-xs text-gray-500">(<?php echo countLikes($row['id'], $pdo); ?>)</span>
                        </button>
                    </form>
                    <form class="inline-block mr-2">
                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                        <button class="btn btn-ghost btn-sm flex items-center">
                            <i class="fas fa-comment-dots mr-1"></i> Comment
                            <span class="ml-1 text-xs text-gray-500">(<?php echo countComments($row['id'], $pdo); ?>)</span>
                        </button>
                    </form>
                    <form method="post" action="comment.php" class="flex items-center w-full mt-2">
                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="comment_content" placeholder="Add a comment..." class="input input-bordered input-sm w-full mr-2">
                        <button class="btn btn-ghost btn-sm flex items-center">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="comments-section mt-4">
                <ul class="comments-list">
                    <?php
                    $comments = $pdo->prepare("SELECT comments.*, users.username, users.profile_image, comments.user_id FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY created_at DESC");
                    $comments->execute([$row['id']]);
                    while ($comment = $comments->fetch(PDO::FETCH_ASSOC)) { ?>
                        <li class="comment flex justify-between items-center py-2">
                            <div class="flex items-center">
                                <?php if (!empty($comment['profile_image'])): ?>
                                    <img src="<?php echo $comment['profile_image']; ?>" alt="User Profile" class="w-8 h-8 rounded-full mr-2">
                                <?php else: ?>
                                    <i class="fa-solid fa-user rounded-full mr-2 text-gray-600"></i>
                                <?php endif; ?>
                                <span class="font-semibold"><?php echo $comment['username']; ?>:</span>
                                <span><?php echo isset($comment['content']) ? htmlspecialchars($comment['content']) : ''; ?></span>
                            </div>
                            <?php if ($comment['user_id'] == $userId) { ?>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40">
                                        <li><button class="edit-comment" data-comment-id="<?php echo $comment['id']; ?>">แก้ไข</button></li>
                                        <li><button class="delete-comment" data-comment-id="<?php echo $comment['id']; ?>">ลบ</button></li>
                                    </ul>
                                </div>

                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>
<div id="editPostModal" class="fixed inset-0 flex items-center justify-center bg-opacity-50 hidden opacity-0 transition-opacity duration-300" style="z-index: 1001;">
    <div class="bg-base-100 p-6 rounded-lg w-[90%] sm:w-[80%] lg:w-[60%] xl:w-[50%] border-2 border-white transform scale-95 transition-transform duration-300">
        <h2 class="text-xl font-bold mb-4">แก้ไขโพสต์</h2>
        <input type="hidden" id="editPostId">
        <textarea id="editPostContent" class="textarea textarea-bordered w-full mb-4" placeholder="เขียนสิ่งที่คุณคิด..."></textarea>
        <div id="editImagePreviewContainer" class="mb-4"></div>
        <input type="file" id="editImageInput" class="btn btn-outline btn-sm mb-4" accept="image/*" />
        <div class="flex justify-end">
            <a href="index.php"><button id="updatePostBtn" class="btn btn-error mr-2">อัปเดต</button></a>
            <button id="closeEditModalBtn" class="btn btn-ghost">ยกเลิก</button>
        </div>
    </div>
</div>
<script>
    // Open the modal with animation
    document.getElementById("openModalBtn").addEventListener("click", function() {
        const modal = document.getElementById("postModal");
        modal.classList.remove("hidden");
        setTimeout(() => modal.classList.remove("opacity-0", "scale-95"), 10);
    });

    // Close the modal with animation
    document.getElementById("closeModalBtn").addEventListener("click", function() {
        const modal = document.getElementById("postModal");
        modal.classList.add("opacity-0", "scale-95");
        setTimeout(() => modal.classList.add("hidden"), 300);
    });

    // Close the modal when clicked outside of the modal content
    document.getElementById("postModal").addEventListener("click", function(e) {
        if (e.target === document.getElementById("postModal")) {
            document.getElementById("closeModalBtn").click();
        }
    });

    // Handle Image Preview
    document.getElementById("imageInput").addEventListener("change", function(event) {
        const file = event.target.files[0];
        const imagePreviewContainer = document.getElementById("imagePreviewContainer");

        imagePreviewContainer.innerHTML = '';

        if (file) {
            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            img.alt = "Image Preview";
            img.classList.add("w-full", "h-64", "object-cover", "rounded-lg");
            imagePreviewContainer.appendChild(img);
        }
    });

    // Submit post using AJAX
    document.getElementById("submitPostBtn").addEventListener("click", function() {
        const postContent = document.getElementById("postContent").value;
        const imageInput = document.getElementById("imageInput").files[0];

        const formData = new FormData();
        formData.append("postContent", postContent);
        if (imageInput) {
            formData.append("imageInput", imageInput);
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "post_action.php", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const newPost = document.createElement("div");
                    newPost.classList.add("post", "w-full", "max-w-3xl", "px-4", "py-6", "border", "bg-base-300", "rounded-lg", "shadow-lg"); // เปลี่ยน bg-white เป็น bg-base-300
                    newPost.innerHTML = `
                    <div class="post-header flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <h2 class="font-bold text-lg">${response.username}</h2>
                            <span class="text-gray-500 text-sm ml-2">${response.createdAt}</span>
                        </div>
                    </div>
                    <div class="post-content">
                        <p>${response.postContent}</p>
                        ${response.image ? `<img src="${response.image}" alt="Post Image" class="w-full h-64 object-cover rounded-lg mt-2">` : ''}
                        <div class="mt-4 flex items-center">
                            <form method="post" action="like.php" class="inline-block mr-2">
                                <input type="hidden" name="post_id" value="${response.postId}">
                                <button class="btn btn-ghost btn-sm flex items-center">
                                    <i class="fas fa-heart mr-1"></i> Like
                                    <span class="ml-1 text-xs text-gray-500">(0)</span>
                                </button>
                            </form>
                            <form class="inline-block mr-2">
                                <input type="hidden" name="post_id" value="${response.postId}">
                                <button class="btn btn-ghost btn-sm flex items-center">
                                    <i class="fas fa-comment-dots mr-1"></i> Comment
                                    <span class="ml-1 text-xs text-gray-500">(0)</span>
                                </button>
                            </form>
                            <form method="post" action="comment.php" class="flex items-center w-full mt-2">
                                <input type="hidden" name="post_id" value="${response.postId}">
                                <input type="text" name="comment_content" placeholder="Add a comment..." class="input input-bordered input-sm w-full mr-2">
                                <button class="btn btn-ghost btn-sm flex items-center">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                `;
                    document.getElementById("postsContainer").prepend(newPost);
                    document.getElementById("postContent").value = '';
                    document.getElementById("imageInput").value = '';
                    document.getElementById("imagePreviewContainer").innerHTML = '';
                    document.getElementById("closeModalBtn").click();
                } else {
                    alert("Error posting.");
                }
            }
        };
        xhr.send(formData);
    });
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-post-btn')) {
            const postId = event.target.dataset.postId;
            console.log('postId:', postId); // เพิ่มบรรทัดนี้
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโพสต์นี้?')) {
                fetch('delete_post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `post_id=${postId}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('data:', data); // เพิ่มบรรทัดนี้
                        if (data.success) {
                            event.target.closest('.post').remove();
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบโพสต์');
                        }
                    });
            }
        }
    });
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-post-btn')) {
            const postId = event.target.dataset.postId;
            const postElement = event.target.closest('.post');
            const postContent = postElement.querySelector('.post-content p').textContent;
            const postImage = postElement.querySelector('.post-content img');

            document.getElementById('editPostId').value = postId;
            document.getElementById('editPostContent').value = postContent;
            const editImagePreviewContainer = document.getElementById('editImagePreviewContainer');
            editImagePreviewContainer.innerHTML = '';

            if (postImage) {
                const img = document.createElement('img');
                img.src = postImage.src;
                img.alt = 'Post Image';
                img.classList.add('w-full', 'h-64', 'object-cover', 'rounded-lg');
                editImagePreviewContainer.appendChild(img);
            }

            const editModal = document.getElementById('editPostModal');
            editModal.classList.remove('hidden');
            setTimeout(() => editModal.classList.remove('opacity-0', 'scale-95'), 10);
        }
    });

    document.getElementById('closeEditModalBtn').addEventListener('click', function() {
        const editModal = document.getElementById('editPostModal');
        editModal.classList.add('opacity-0', 'scale-95');
        setTimeout(() => editModal.classList.add('hidden'), 300);
    });

    document.getElementById('updatePostBtn').addEventListener('click', function() {
        const postId = document.getElementById('editPostId').value;
        const postContent = document.getElementById('editPostContent').value;
        const imageInput = document.getElementById('editImageInput').files[0];

        const formData = new FormData();
        formData.append('postId', postId);
        formData.append('postContent', postContent);
        if (imageInput) {
            formData.append('imageInput', imageInput);
        }

        fetch('update_post.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const postElement = document.querySelector(`.post [data-post-id="${postId}"]`).closest('.post');
                    postElement.querySelector('.post-content p').textContent = postContent;
                    if (data.image) {
                        postElement.querySelector('.post-content img').src = data.image;
                    } else if (postElement.querySelector('.post-content img')) {
                        postElement.querySelector('.post-content img').remove();
                    }
                    document.getElementById('closeEditModalBtn').click();
                } else {
                    alert('เกิดข้อผิดพลาดในการอัปเดตโพสต์');
                }
            });
    });
    document.addEventListener('click', function(event) {
        // แสดง/ซ่อนเมนูตัวเลือก
        if (event.target.classList.contains('options-button')) {
            event.target.nextElementSibling.classList.toggle('hidden');
        }
        
    });
</script>
<script>
    document.addEventListener('click', function(event) {

        // แก้ไขความคิดเห็น
        if (event.target.classList.contains('edit-comment')) {
            const commentId = event.target.dataset.commentId;
            const commentElement = event.target.closest('.comment');
            const commentText = commentElement.querySelector('span:last-child').textContent;


            // สร้าง popup HTML
            const popup = document.createElement('div');
            popup.classList.add('fixed', 'inset-0', 'flex', 'items-center', 'justify-center', 'bg-opacity-50', 'bg-black');
            popup.innerHTML = `
                <div class="bg-base-100 p-6 rounded-lg w-[90%] sm:w-[80%] lg:w-[60%] xl:w-[50%] border-2 border-white transform scale-95 transition-transform duration-300">
                    <h2 class="text-xl font-bold mb-4">แก้ไขความคิดเห็น</h2>
                    <textarea id="editCommentText" class="textarea textarea-bordered w-full mb-4">${commentText}</textarea>
                    <div class="flex justify-end">
                        <a href="index.php"><button id="saveCommentBtn" class="btn btn-error mr-2">บันทึก</button></a>
                        <button id="cancelCommentBtn" class="btn btn-ghost">ยกเลิก</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);

            // Event listener สำหรับปุ่มบันทึก
            popup.querySelector('#saveCommentBtn').addEventListener('click', function() {
                const newComment = popup.querySelector('#editCommentText').value;
                if (newComment) {
                    fetch('edit_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `comment_id=${commentId}&comment_content=${encodeURIComponent(newComment)}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            commentElement.querySelector('span:last-child').textContent = newComment;
                            document.body.removeChild(popup);
                        } else {
                            alert('เกิดข้อผิดพลาดในการแก้ไขความคิดเห็น');
                            document.body.removeChild(popup);
                        }
                    });
                }
            });

            // Event listener สำหรับปุ่มยกเลิก
            popup.querySelector('#cancelCommentBtn').addEventListener('click', function() {
                document.body.removeChild(popup);
            });
        }

        // ลบความคิดเห็น
        if (event.target.classList.contains('delete-comment')) {
            const commentId = event.target.dataset.commentId;
            fetch('delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `comment_id=${commentId}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    event.target.closest('.comment').remove();
                    // เรียกฟังก์ชันอัปเดตคอมเมนต์
                    updateComments(data.post_id);
                } else {
                    alert('เกิดข้อผิดพลาดในการลบความคิดเห็น');
                }
            });
        }
    });
    // ฟังก์ชันสำหรับดึงคอมเมนต์ใหม่และอัปเดต DOM
    function updateComments(postId) {
        fetch(`get_comments.php?post_id=${postId}`) // สร้างไฟล์ get_comments.php
            .then(response => response.text())
            .then(commentsHtml => {
                const commentsSection = document.querySelector(`.post [data-post-id="${postId}"]`).closest('.post').querySelector('.comments-list');
                commentsSection.innerHTML = commentsHtml;
            });
    }

      document.getElementById('updatePostBtn').addEventListener('click', function() {
        const postId = document.getElementById('editPostId').value;
        const postContent = document.getElementById('editPostContent').value;
        const imageInput = document.getElementById('editImageInput').files[0];

        const formData = new FormData();
        formData.append('postId', postId);
        formData.append('postContent', postContent);

        if (imageInput) {
            formData.append('imageInput', imageInput);
        }

        fetch('update_post.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const postElement = document.querySelector(`.post [data-post-id="${postId}"]`).closest('.post');
                postElement.querySelector('.post-content p').textContent = postContent;

                if (data.image) {
                    // ถ้ามีรูปใหม่ให้อัปเดต
                    postElement.querySelector('.post-content img').src = data.image;
                } else {
                    // ถ้าไม่มีรูปใหม่ให้แสดงรูปเก่า
                    const existingImage = document.getElementById('editImagePreviewContainer').querySelector('img');
                    if (existingImage) {
                        if (!postElement.querySelector('.post-content img')){
                            const img = document.createElement('img');
                            img.src = existingImage.src;
                            img.alt = 'Post Image';
                            img.classList.add('w-full', 'h-64', 'object-cover', 'rounded-lg');
                            postElement.querySelector('.post-content').appendChild(img);
                        }
                        else{
                            postElement.querySelector('.post-content img').src = existingImage.src;
                        }

                    }
                }

                document.getElementById('closeEditModalBtn').click();
            } else {
                alert('เกิดข้อผิดพลาดในการอัปเดตโพสต์');
            }
        });
    });
</script>
<?php
include './layout/footer.php';
?>
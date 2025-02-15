<?php
include './include/connect.php';
session_start(); // เริ่มต้น session

// ตรวจสอบว่า session มีข้อมูลผู้ใช้หรือไม่
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $profileImage = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : ''; // ถ้ายังไม่มีรูปภาพจะให้เป็นค่าว่าง
} else {
    // ถ้าไม่มี session ของผู้ใช้ให้เปลี่ยนเส้นทางไปยังหน้า login
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="bg-base-300">
    <div class="navbar bg-base-100">
        <div class="flex-1">
            <a class="btn btn-ghost text-xl">
                InstaX
                <i class="fa-solid fa-camera-retro" style="margin-left: 10px;"></i>
            </a>
        </div>
        <div class="navbar-end flex items-center">
            <button id="openUserModalBtn" class="btn btn-ghost btn-circle">
                <?php if (empty($profileImage)): ?>
                    <i class="fa-solid fa-user rounded-full text-gray-600"></i>
                <?php else: ?>
                    <img src="<?php echo $profileImage; ?>" alt="User Profile" class="w-8 h-8 rounded-full">
                <?php endif; ?>
            </button>
            <h2><?php echo $username; ?></h2>
            <a href="guard\logout.php" style="color: red;">
                <button class="btn btn-ghost btn-circle">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </a>
        </div>
    </div>

    <div id="userModal" class="fixed inset-0 flex items-center justify-center bg-opacity-50 hidden opacity-0 transition-opacity duration-300" style="z-index: 1001;">
        <div class="bg-base-100 p-6 rounded-lg w-[90%] sm:w-[80%] lg:w-[60%] xl:w-[50%] border-2 border-white transform scale-95 transition-transform duration-300">
            <div class="mb-4">
                <label for="profileImageInput" class="block text-sm font-medium text-gray-700">รูปโปรไฟล์</label>
                <input type="file" id="profileImageInput" class="mt-1 p-2 border rounded-md w-full" accept="image/*">
            </div>
            <h2 class="text-xl font-bold mb-4">แก้ไขโปรไฟล์</h2>
            <div class="mb-4">
                <label for="editUsername" class="block text-sm font-medium text-gray-700">แก้ไข Username</label>
                <input type="text" id="editUsername" class="mt-1 p-2 border rounded-md w-full" value="<?php echo $username; ?>">
            </div>
            <div class="mb-4">
                <label for="editDescription" class="block text-sm font-medium text-gray-700">เพิ่มคำอธิบาย</label>
                <?php
                // ดึง description จากฐานข้อมูล
                $stmt = $pdo->prepare("SELECT description FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $description = $user['description'] ?? ''; // ใช้ ?? เพื่อป้องกัน error หากไม่มี description
                ?>
                <textarea id="editDescription" class="mt-1 p-2 border rounded-md w-full" placeholder="เพิ่มคำอธิบาย..."><?php echo $description; ?></textarea>
            </div>
            <div class="mb-4 relative">
                <label for="editPassword" class="block text-sm font-medium text-gray-700">แก้ไขรหัสผ่าน</label>
                <input type="password" id="editPassword" class="mt-1 p-2 border rounded-md w-full" placeholder="รหัสผ่านใหม่">
                <button type="button" id="togglePassword" class="absolute flex items-center text-sm leading-5">
                    <i class="fas fa-eye-slash py-3" id="passwordIcon"></i>
                </button>
            </div>
            <div class="flex justify-end">
                <a href="index.php"><button id="saveUserBtn" class="btn btn-error mr-2">บันทึก</button></a>
                <button id="closeUserModalBtn" class="btn btn-ghost">ยกเลิก</button>
            </div>
        </div>
    </div>
    <script>
        // Open User Modal
        document.getElementById("openUserModalBtn").addEventListener("click", function() {
            const modal = document.getElementById("userModal");
            modal.classList.remove("hidden");
            setTimeout(() => modal.classList.remove("opacity-0", "scale-95"), 10);
        });

        // Close User Modal
        document.getElementById("closeUserModalBtn").addEventListener("click", function() {
            const modal = document.getElementById("userModal");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => modal.classList.add("hidden"), 300);
        });

        // Close User Modal when clicked outside
        document.getElementById("userModal").addEventListener("click", function(e) {
            if (e.target === document.getElementById("userModal")) {
                document.getElementById("closeUserModalBtn").click();
            }
        });

        // Save User Changes (Add your logic here)
        document.getElementById("saveUserBtn").addEventListener("click", function() {
            const newUsername = document.getElementById("editUsername").value;
            const newDescription = document.getElementById("editDescription").value;
            const newPassword = document.getElementById("editPassword").value;

            // Add your AJAX or form submission logic here to save the changes

            console.log("Username:", newUsername);
            console.log("Description:", newDescription);
            console.log("Password:", newPassword);

            document.getElementById("closeUserModalBtn").click();
        });

        // Toggle password visibility
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordInput = document.getElementById("editPassword");
            const passwordIcon = document.getElementById("passwordIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.classList.remove("fa-eye-slash");
                passwordIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                passwordIcon.classList.remove("fa-eye");
                passwordIcon.classList.add("fa-eye-slash");
            }
        });

        // Save User Changes
        document.getElementById("saveUserBtn").addEventListener("click", function() {
            const newUsername = document.getElementById("editUsername").value;
            const newDescription = document.getElementById("editDescription").value;
            const newPassword = document.getElementById("editPassword").value;
            const profileImageInput = document.getElementById("profileImageInput").files[0];

            const formData = new FormData();
            formData.append("username", newUsername);
            formData.append("description", newDescription);
            formData.append("password", newPassword);
            if (profileImageInput) {
                formData.append("profileImage", profileImageInput);
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_profile.php", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert("บันทึกการเปลี่ยนแปลงสำเร็จ");
                        document.getElementById("closeUserModalBtn").click();
                        // Reload the page to update the profile image
                        location.reload();
                    } else {
                        alert("เกิดข้อผิดพลาดในการบันทึกการเปลี่ยนแปลง");
                    }
                }
            };
            xhr.send(formData);
        });

        // Open User Modal
        document.getElementById("openUserModalBtn").addEventListener("click", function() {
            const modal = document.getElementById("userModal");
            modal.classList.remove("hidden");
            setTimeout(() => modal.classList.remove("opacity-0", "scale-95"), 10);

            // แสดงรูปภาพเดิม
            const profileImage = document.querySelector("#openUserModalBtn img");
            if (profileImage) {
                const profileImageInput = document.getElementById("profileImageInput");
                profileImageInput.dataset.originalSrc = profileImage.src; // เก็บรูปภาพเดิม
            }
        });

        // Close User Modal
        document.getElementById("closeUserModalBtn").addEventListener("click", function() {
            const modal = document.getElementById("userModal");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => modal.classList.add("hidden"), 300);
        });

        // Close User Modal when clicked outside
        document.getElementById("userModal").addEventListener("click", function(e) {
            if (e.target === document.getElementById("userModal")) {
                document.getElementById("closeUserModalBtn").click();
            }
        });

        // Save User Changes
        document.getElementById("saveUserBtn").addEventListener("click", function() {
            const newUsername = document.getElementById("editUsername").value;
            const newDescription = document.getElementById("editDescription").value;
            const newPassword = document.getElementById("editPassword").value;
            const profileImageInput = document.getElementById("profileImageInput").files[0];

            const formData = new FormData();
            formData.append("username", newUsername);
            formData.append("description", newDescription);
            formData.append("password", newPassword);
            if (profileImageInput) {
                formData.append("profileImage", profileImageInput);
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_profile.php", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert("บันทึกการเปลี่ยนแปลงสำเร็จ");
                        document.getElementById("closeUserModalBtn").click();
                        location.reload();
                    } else {
                        alert("เกิดข้อผิดพลาดในการบันทึกการเปลี่ยนแปลง");
                    }
                }
            };
            xhr.send(formData);
        });

        // Toggle password visibility
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordInput = document.getElementById("editPassword");
            const passwordIcon = document.getElementById("passwordIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.classList.remove("fa-eye-slash");
                passwordIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                passwordIcon.classList.remove("fa-eye");
                passwordIcon.classList.add("fa-eye-slash");
            }
        });

        

        // Close User Modal
        document.getElementById("closeUserModalBtn").addEventListener("click", function() {
            const modal = document.getElementById("userModal");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => modal.classList.add("hidden"), 300);

            // ลบ preview เมื่อปิด modal
            const profileImagePreview = document.querySelector("#userModal .mb-4 img");
            if (profileImagePreview) {
                profileImagePreview.remove();
            }
        });

        // Close User Modal when clicked outside
        document.getElementById("userModal").addEventListener("click", function(e) {
            if (e.target === document.getElementById("userModal")) {
                document.getElementById("closeUserModalBtn").click();
            }
        });

        // Save User Changes
        document.getElementById("saveUserBtn").addEventListener("click", function() {
            const newUsername = document.getElementById("editUsername").value;
            const newDescription = document.getElementById("editDescription").value;
            const newPassword = document.getElementById("editPassword").value;
            const profileImageInput = document.getElementById("profileImageInput").files[0];

            const formData = new FormData();
            formData.append("username", newUsername);
            formData.append("description", newDescription);
            formData.append("password", newPassword);
            if (profileImageInput) {
                formData.append("profileImage", profileImageInput);
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_profile.php", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert("บันทึกการเปลี่ยนแปลงสำเร็จ");
                        document.getElementById("closeUserModalBtn").click();
                        location.reload();
                    } else {
                        alert("เกิดข้อผิดพลาดในการบันทึกการเปลี่ยนแปลง");
                    }
                }
            };
            xhr.send(formData);
        });

        // Toggle password visibility
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordInput = document.getElementById("editPassword");
            const passwordIcon = document.getElementById("passwordIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.classList.remove("fa-eye-slash");
                passwordIcon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                passwordIcon.classList.remove("fa-eye");
                passwordIcon.classList.add("fa-eye-slash");
            }
        });

        // Open User Modal
document.getElementById("openUserModalBtn").addEventListener("click", function() {
    const modal = document.getElementById("userModal");
    modal.classList.remove("hidden");
    setTimeout(() => modal.classList.remove("opacity-0", "scale-95"), 10);

    // แสดงรูปภาพเดิม
    const profileImage = document.querySelector("#openUserModalBtn img");
    const profileImageInput = document.getElementById("profileImageInput");
    const profileImagePreview = document.createElement("img");
    profileImagePreview.classList.add("w-24", "h-24", "rounded-full", "mb-4");
    profileImageInput.dataset.originalSrc = profileImage ? profileImage.src : ""; // เก็บรูปภาพเดิม

    if (profileImage) {
        profileImagePreview.src = profileImage.src;
    } else {
        profileImagePreview.src = "path/to/default/profile/image.png"; // แทนที่ด้วย path ของรูปภาพ default
    }

    const previewContainer = document.querySelector("#userModal .mb-4"); // เลือก container สำหรับ preview
    previewContainer.insertBefore(profileImagePreview, profileImageInput); // เพิ่ม preview ก่อน input file

    // ล้างค่า input file และ preview เมื่อเปิด modal ใหม่
    profileImageInput.value = "";
    profileImageInput.addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImagePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            profileImagePreview.src = profileImageInput.dataset.originalSrc || "path/to/default/profile/image.png";
        }
    });
});

    </script>
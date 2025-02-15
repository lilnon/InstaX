<?php
    include '../include/connect.php';
?>


<body class="bg-base-300 min-h-screen flex justify-center items-center">

<div class="card bg-base-100 w-[80%] max-w-md shadow-xl">
    <div class="hero-content text-center">
        <div class="max-w-xl">
            <h1 class="text-3xl font-bold mb-6">สมัครสมาชิก</h1>
            
            <!-- Registration Form -->
            <form action="register_process.php" method="POST">
                <!-- Username -->
                <div class="mb-4">
                    <input type="text" name="username" class="input input-bordered w-full" placeholder="ชื่อผู้ใช้" required />
                </div>
                
                <!-- Password -->
                <div class="mb-6 relative">
                    <input type="password" id="password" name="password" class="input input-bordered w-full" placeholder="รหัสผ่าน" required />
                    <!-- Eye icon will be shown when there's text in the password field -->
                    <button type="button" id="togglePassword" class="absolute right-3 top-2 text-gray-600 hidden">
                        <i class="fa-solid fa-eye"></i> <!-- Eye Icon -->
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6 relative">
                    <input type="password" id="confirm_password" name="confirm_password" class="input input-bordered w-full" placeholder="ยืนยันรหัสผ่าน" required />
                    <!-- Eye icon will be shown when there's text in the confirm password field -->
                    <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-2 text-gray-600 hidden">
                        <i class="fa-solid fa-eye"></i> <!-- Eye Icon -->
                    </button>
                </div>

                <!-- Register Button -->
                <button type="submit" class="btn btn-primary w-full">สมัครสมาชิก</button>
            </form>

            <div class="mt-4">
                <p>มีบัญชีแล้ว? <a href="login.php" class="text-blue-500 hover:underline">เข้าสู่ระบบ</a></p>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for handling eye icon visibility -->
<script>
    // Show or hide eye icon based on input field
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirm_password");
    const togglePassword = document.getElementById("togglePassword");
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");

    // Show or hide the eye icon based on whether the password field is filled
    passwordField.addEventListener("input", function() {
        togglePassword.classList.toggle("hidden", passwordField.value === "");
    });

    confirmPasswordField.addEventListener("input", function() {
        toggleConfirmPassword.classList.toggle("hidden", confirmPasswordField.value === "");
    });

    // Toggle visibility of password
    togglePassword.addEventListener("click", function() {
        const type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
        togglePassword.innerHTML = type === "password" ? "<i class='fa-solid fa-eye'></i>" : "<i class='fa-solid fa-eye-slash'></i>";
    });

    // Toggle visibility of confirm password
    toggleConfirmPassword.addEventListener("click", function() {
        const type = confirmPasswordField.type === "password" ? "text" : "password";
        confirmPasswordField.type = type;
        toggleConfirmPassword.innerHTML = type === "password" ? "<i class='fa-solid fa-eye'></i>" : "<i class='fa-solid fa-eye-slash'></i>";
    });
</script>

</body>

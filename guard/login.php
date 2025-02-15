<?php
    include '../include/connect.php';
?>
<body class="bg-base-300 min-h-screen flex justify-center items-center">

<div class="card bg-base-100 w-[80%] max-w-md shadow-xl">
    <div class="hero-content text-center">
        <div class="max-w-xl">
            <h1 class="text-3xl font-bold mb-6">เข้าสู่ระบบ</h1>
            
            <!-- Login Form -->
            <form action="login_process.php" method="POST">
                <!-- Username -->
                <div class="mb-4">
                    <input type="text" name="username" class="input input-bordered w-full" placeholder="ชื่อผู้ใช้" required />
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <input type="password" name="password" class="input input-bordered w-full" placeholder="รหัสผ่าน" required />
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-full">เข้าสู่ระบบ</button>
            </form>

            <div class="mt-4">
                <p>ยังไม่มีบัญชี? <a href="register.php" class="text-blue-500 hover:underline">สมัครสมาชิก</a></p>
            </div>
        </div>
    </div>
</div>

</body>

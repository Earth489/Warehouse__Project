<?php 

    require 'connection.php';
    session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="login.css">
    <title>เข้าสู่ระบบ</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        background-color: #827d9e;
    }

    header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
    }
</style>
</head>
<body> 
    <header class="bg-dark text-white py-3 w-100">
    <h2 class="text-center m-0">
        ระบบจัดการคลังสินค้า สำหรับร้านวัสดุก่อสร้าง
    </h2>
    </header>
        <form action="Login.php" method="POST">
        <?php 
        
        if(isset($_POST['enter'])) {

            // 1. เปลี่ยนชื่อตัวแปรที่รับจากฟอร์ม
            $username_input = $_POST['username'];
            $password_input = $_POST['pws'];

            // ⚠️ 2. ใช้ Prepared Statements เพื่อป้องกัน SQL Injection
            // ตรวจสอบว่า $conn มีอยู่จริงก่อนใช้ prepare
            if ($conn) {
                $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
                $stmt->bind_param("s", $username_input);
                $stmt->execute();
                $result_users = $stmt->get_result();
                
                // 3. เปลี่ยนชื่อตัวแปรที่ดึงจากฐานข้อมูล
                $user_data = $result_users->fetch_assoc();
                $stmt->close();
            } else {
                // จัดการข้อผิดพลาดถ้า $conn ไม่มีค่า (ปัญหา require 'connection.php')
                $user_data = false;
                echo "<div class='alert alert-danger'>ข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล</div>";
            }
            
            // 4. ตรวจสอบข้อมูลที่ดึงมา
            if($user_data) {

                // ⚠️ ควรใช้ password_verify() ถ้าคุณมีการแฮชรหัสผ่านใน DB
                if($password_input === $user_data['password']) {
            
                    // 5. ลบเงื่อนไขการตรวจสอบประเภทผู้ใช้ออก (ทำให้ล็อกอินแล้วเด้งไปหน้าอื่นได้)
                    
                    $_SESSION['username'] = $user_data["username"];
                    $_SESSION['user_id'] = $user_data["user_id"];
                    
                    header("Location: homepage.php");
                    exit();
                    
                } else {

                    echo "<div class='alert alert-danger'>กรุณากรอกรหัสผ่านที่ถูกต้อง</div>";
                }

            } else {

                echo "<div class='alert alert-danger'>ไม่พบชื่อผู้ใช้</div>"; // เปลี่ยนจาก 'อีเมล' เป็น 'ชื่อผู้ใช้' ตามฟอร์ม
                    
            }
        }
    ?>


        <h1 class="mb-4">เข้าสู่ระบบ</h1>
        <div class="mb-3 text-start">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <input type="username" name="username" id="username" placeholder="ชื่อผู้ใช้" class="form-control">
        </div>
        <div class="mb-3 text-start">
            <label for="pws" class="form-label">รหัสผ่าน</label>
            <input type="password" name="pws" id="pws" placeholder="รหัสผ่าน" class="form-control">
        </div>
        
        <button type="submit" name="enter" class="btn-login">เข้าสู่ระบบ</button>
        <div class="divider"></div>
    </form>
    </div>
</body>
</html>
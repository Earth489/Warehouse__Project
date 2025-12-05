<?php
include 'connection.php';
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
  
// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$category_id = $_GET['id'];

// ตรวจสอบว่าประเภทสินค้านี้ถูกใช้อยู่ใน products หรือไม่
$check = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE category_id = ?");
$check->bind_param("i", $category_id);
$check->execute();
$result = $check->get_result()->fetch_assoc();

if ($result['total'] > 0) {
    echo "<script>
            alert('ไม่สามารถลบประเภทสินค้านี้ได้ เนื่องจากมีสินค้าอยู่ในหมวดนี้');
            window.location='categories.php';
          </script>";
    exit();
}

// ลบข้อมูลประเภทสินค้า
$sql = "DELETE FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    echo "<script>alert('ลบประเภทสินค้าสำเร็จ'); window.location='categories.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในการลบ'); window.location='categories.php';</script>";
}
?>

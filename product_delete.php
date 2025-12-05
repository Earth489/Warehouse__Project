<?php
include 'connection.php';
session_start();

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี id ที่ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// ลบรูปภาพในโฟลเดอร์ (ถ้ามี)
$sql_img = "SELECT image_path FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql_img);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if (!empty($row['image_path']) && file_exists($row['image_path'])) {
        unlink($row['image_path']);
    }
}

// ลบสินค้าออกจากฐานข้อมูล พร้อมตรวจจับข้อผิดพลาด
$sql_delete = "DELETE FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("i", $product_id);
 
try {
    if ($stmt->execute()) {
        echo "<script>alert('ลบสินค้าสำเร็จ'); window.location='products.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบสินค้าได้'); window.location='products.php';</script>";
    }
} catch (mysqli_sql_exception $e) {
    // ถ้ามี foreign key constraint
    if ($e->getCode() == 1451) {  // 1451 = Cannot delete or update a parent row
        echo "<script>alert('ไม่สามารถลบสินค้าได้ เนื่องจากมีการซื้อขายหรือข้อมูลอ้างอิงอยู่'); window.location='products.php';</script>";
    } else {
        // กรณีอื่น ๆ
        echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.location='products.php';</script>";
    }
}

?>
 
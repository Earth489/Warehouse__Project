<?php
include 'connection.php';
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี id ถูกส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: suppliers.php");
    exit();
}

$supplier_id = $_GET['id'];

// ตรวจสอบว่าซัพพลายเออร์นี้ถูกใช้อยู่ในตาราง products หรือไม่
$check = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE supplier_id = ?");
$check->bind_param("i", $supplier_id);
$check->execute();
$result = $check->get_result()->fetch_assoc();

if ($result['total'] > 0) {
    echo "<script>
            alert('ไม่สามารถลบซัพพลายเออร์นี้ได้ เนื่องจากมีสินค้าเชื่อมโยงอยู่');
            window.location='suppliers.php';
          </script>";
    exit();
}
  
// ถ้าไม่ถูกใช้งาน — ลบข้อมูล
$sql = "DELETE FROM suppliers WHERE supplier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);

if ($stmt->execute()) {
    echo "<script>alert('ลบซัพพลายเออร์เรียบร้อย'); window.location='suppliers.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในการลบ'); window.location='suppliers.php';</script>";
}
?>

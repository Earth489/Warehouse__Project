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

// ดึงข้อมูลซัพพลายเออร์ตาม id
$sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('ไม่พบข้อมูลซัพพลายเออร์นี้'); window.location='suppliers.php';</script>";
    exit();
}

$supplier = $result->fetch_assoc();

// เมื่อกดบันทึก
if (isset($_POST['update'])) {
    $name = $_POST['supplier_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $description = $_POST['description']; // รับค่า description

    // เพิ่ม description ในคำสั่ง SQL
    $sql_update = "UPDATE suppliers SET supplier_name = ?, address = ?, phone = ?, description = ? WHERE supplier_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssssi", $name, $address, $phone, $description, $supplier_id);

    if ($stmt->execute()) {
        echo "<script>alert('อัปเดตข้อมูลเรียบร้อย'); window.location='suppliers.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด กรุณาลองใหม่');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลซัพพลายเออร์</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
</head>
<body>

      

 <!-- แถบเมนูด้านบน -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 ระบบจัดการคลังสินค้า สำหรับร้านวัสดุก่อสร้าง</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>    
          <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>      
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link " href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>
  
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-warning text-white">
      <h4>แก้ไขข้อมูลซัพพลายเออร์</h4>
    </div>
    <div class="card-body">
      <form method="POST" id="editSupplierForm">
        <div class="mb-3">
          <label class="form-label">ชื่อซัพพลายเออร์</label>
          <input type="text" name="supplier_name" class="form-control" value="<?= $supplier['supplier_name'] ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">ที่อยู่</label>
          <textarea name="address" class="form-control" rows="3"><?= $supplier['address'] ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">เบอร์โทร</label>
          <input type="text" name="phone" class="form-control" value="<?= $supplier['phone'] ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">รายละเอียด</label>
          <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($supplier['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="update" class="btn btn-success">บันทึกการแก้ไข</button>
        <a href="suppliers.php" class="btn btn-secondary">ยกเลิก</a>
      </form>
    </div>
  </div>
</div>

<script>
    document.getElementById('editSupplierForm').addEventListener('submit', function(event) {
        if (!confirm('คุณต้องการบันทึกการแก้ไขข้อมูลซัพพลายเออร์นี้ใช่หรือไม่?')) {
            event.preventDefault();
        }
    });
</script>

</body>
</html>

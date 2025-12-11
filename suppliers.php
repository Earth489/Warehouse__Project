<?php
include 'connection.php';
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลสินค้าทั้งหมดสำหรับ dropdown
$product_list_result = $conn->query("SELECT product_id, product_name FROM products ORDER BY product_name ASC");

// รับค่า product_id จากฟอร์ม
$product_id_filter = $_GET['product_id'] ?? null;

// สร้าง SQL query
if ($product_id_filter && is_numeric($product_id_filter)) {
    // ถ้ามีการเลือกสินค้า ให้ค้นหาซัพพลายเออร์ที่เคยส่งสินค้านั้น
    $sql = "SELECT DISTINCT s.supplier_id, s.supplier_name, s.address, s.phone, s.description
            FROM suppliers s
            JOIN purchases pu ON s.supplier_id = pu.supplier_id
            JOIN purchase_details pd ON pu.purchase_id = pd.purchase_id
            WHERE pd.product_id = ?
            ORDER BY s.supplier_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // ถ้าไม่มีการค้นหา ให้แสดงซัพพลายเออร์ทั้งหมด
    $sql = "SELECT supplier_id, supplier_name, address, phone, description FROM suppliers ORDER BY supplier_id ASC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ซัพพลายเออร์</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
</head>
<body>

      

 <!-- แถบเมนูด้านบน -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 Warehouse System</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>
  
<div class="container mt-4">
  <h2>ซัพพลายเออร์</h2>
  <a href="add_suppliers.php" class="btn btn-primary mb-3">+ เพิ่มซัพพลายเออร์</a>

  <!-- ฟอร์มค้นหา -->
  <form method="GET" class="card card-body mb-4">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label for="product_id" class="form-label">ค้นหาซัพพลายเออร์จากสินค้าที่เคยส่ง</label>
        <select name="product_id" id="product_id" class="form-select">
          <option value=""> เลือกสินค้า </option>
          <?php while ($product = $product_list_result->fetch_assoc()): ?>
            <option value="<?= $product['product_id'] ?>" <?= ($product_id_filter == $product['product_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($product['product_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">
          <i class="bi bi-search"></i> ค้นหา
        </button>
        <a href="suppliers.php" class="btn btn-dark flex-grow-1">ล้างค่า</a>
      </div>
    </div>
  </form>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th class="text-center" style="width: 5%;">ID</th>
        <th>ชื่อซัพพลายเออร์</th>
        <th>ที่อยู่</th>
        <th style="width: 15%;">เบอร์โทร</th>
        <th style="width: 20%;">รายละเอียด</th>
        <th class="text-center" style="width: 15%;">การจัดการ</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr> 
                      <td class='text-center'>" . htmlspecialchars($row['supplier_id']) . "</td>
                      <td>" . htmlspecialchars($row['supplier_name']) . "</td>
                      <td>" . htmlspecialchars($row['address']) . "</td>
                      <td class='text-center'>" . htmlspecialchars($row['phone']) . "</td>
                      <td>" . htmlspecialchars($row['description'] ?? '-') . "</td>
                      <td class='text-center'>
                        <a href='supplier_edit.php?id={$row['supplier_id']}' class='btn btn-warning btn-sm'>แก้ไข</a>
                        <form method='POST' action='supplier_delete.php' style='display: inline-block;' onsubmit=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบซัพพลายเออร์นี้?');\">
                            <input type='hidden' name='id' value='{$row['supplier_id']}'>
                            <button type='submit' class='btn btn-danger btn-sm'>ลบ</button>
                        </form>
                    </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6' class='text-center text-muted'>ไม่มีข้อมูลซัพพลายเออร์</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<!-- jQuery (จำเป็นสำหรับ Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#product_id').select2({
        theme: 'bootstrap-5'
    });
});
</script>
</body>
</html>

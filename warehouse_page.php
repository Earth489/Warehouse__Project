<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลซัพพลายเออร์สำหรับ dropdown
$suppliers_result = $conn->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

// รับค่าจากฟอร์มค้นหา
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? ''; // เปลี่ยนจาก search_term


$params = [];
$types = "";

// ------------------------
//   SQL บิลซื้อเท่านั้น
// ------------------------
$sql = "
    SELECT 
        p.purchase_id AS bill_id,
        p.purchase_number AS bill_number,
        p.purchase_date AS bill_date,
        p.total_amount,
        s.supplier_name AS party_name
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
    WHERE 1=1
";

// เพิ่มเงื่อนไขค้นหา
if ($start_date) {
    $sql .= " AND p.purchase_date >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if ($end_date) {
    $sql .= " AND p.purchase_date <= ?";
    $params[] = $end_date;
    $types .= "s";
}

if ($supplier_id) {
    $sql .= " AND p.supplier_id = ?";
    $params[] = $supplier_id;
    $types .= "i";
}

$sql .= " ORDER BY bill_date DESC, bill_id DESC";

// execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// คำนวณยอดรวม
$total_in = 0;
$bills_in = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bills_in[] = $row;
        // เพิ่ม VAT 7% เข้าไปในยอดรวม
        $total_in += $row['total_amount'] * 1.07;
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>บิลรับสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">🏠 Warehouse System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
        <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>
        <li class="nav-item"><a class="nav-link active" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
  <h2 class="fw-bold mb-3">บิลรับสินค้า</h2>

  <!-- ฟอร์มค้นหา -->
  <form method="GET" class="card card-body mb-4">
    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">จากวันที่</label>
        <input type="date" name="start_date" class="form-control" 
               value="<?= htmlspecialchars($start_date) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">ถึงวันที่</label>
        <input type="date" name="end_date" class="form-control" 
               value="<?= htmlspecialchars($end_date) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">ซัพพลายเออร์</label>
        <select name="supplier_id" class="form-select">
            <option value=""> เลือกซัพพลายเออร์ </option>
            <?php mysqli_data_seek($suppliers_result, 0); ?>
            <?php while($s = $suppliers_result->fetch_assoc()): ?>
                <option value="<?= $s['supplier_id'] ?>" <?= ($supplier_id == $s['supplier_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['supplier_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary flex-grow-1 me-2" type="submit">ค้นหา</button>
        <a href="warehouse_page.php" class="btn btn-dark flex-grow-1">-</a>
      </div>
    </div>
  </form>

  <a href="stock_in_add.php" class="btn btn-success mb-3">เพิ่มบิลรับสินค้า</a>

  <!-- ตาราง -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>วันที่</th>
        <th>เลขที่บิล</th>
        <th>ซัพพลายเออร์</th>
        <th>ยอดรวม (บาท)</th>
        <th>จัดการ</th>
      </tr>
    </thead>

    <tbody>
      <?php if (!empty($bills_in)): ?>
        <?php foreach ($bills_in as $row): ?>
        <tr>
          <td><?= date("d/m/Y", strtotime($row['bill_date'])) ?></td>
          <td><?= htmlspecialchars($row['bill_number']) ?></td>
          <td><?= htmlspecialchars($row['party_name']) ?></td>
          <td class="text-end"><?= number_format($row['total_amount'] * 1.07, 2) ?></td>
          <td class="text-center">
            <a href="purchase_detail.php?id=<?= $row['bill_id'] ?>"
               class="btn btn-sm btn-info">ดูรายละเอียด</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
      <?php endif; ?>
    </tbody>

    <tfoot>
      <tr class="table-light">
        <th colspan="3" class="text-end">ยอดรวมบิลรับสินค้าที่แสดง:</th>
        <th class="text-end"><?= number_format($total_in, 2) ?></th>
        <th></th>
      </tr>
    </tfoot>
  </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

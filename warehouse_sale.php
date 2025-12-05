<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// รับค่าค้นหา
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$search_term = $_GET['search_term'] ?? '';

$params = [];
$types = "";

// ------------------------
//    SQL บิลขายเท่านั้น
// ------------------------
$sql = "
    SELECT 
        s.sale_id AS bill_id,
        s.sale_id AS bill_number,
        s.sale_date AS bill_date,
        s.total_amount,
        'ลูกค้าทั่วไป' AS party_name
    FROM sales s
    WHERE 1=1
";

if ($start_date) {
    $sql .= " AND bill_date >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if ($end_date) {
    $sql .= " AND bill_date <= ?";
    $params[] = $end_date;
    $types .= "s";
}
if ($search_term) {
    $sql .= " AND (bill_number LIKE ?)";
    $like = "%".$search_term."%";
    $params[] = $like;
    $types .= "s";
}

$sql .= " ORDER BY bill_date DESC, bill_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// เตรียมข้อมูล
$total_out = 0;
$bills_out = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bills_out[] = $row;
        $total_out += $row['total_amount'];
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>บิลขายสินค้า</title>
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
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link active" href="warehouse_sale.php">บิลขายสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
  <h2 class="fw-bold mb-3">บิลขายสินค้า</h2>

  <!-- ฟอร์มค้นหา -->
  <form method="GET" class="card card-body mb-4">
    <div class="row g-3">

      <div class="col-md-3">
        <input type="date" name="start_date" class="form-control"
               value="<?= htmlspecialchars($start_date) ?>">
      </div>

      <div class="col-md-3">
        <input type="date" name="end_date" class="form-control"
               value="<?= htmlspecialchars($end_date) ?>">
      </div>

      <div class="col-md-6 d-flex">
        <button class="btn btn-primary flex-grow-1 me-2" type="submit">ค้นหา</button>
        <a href="warehouse_sale.php" class="btn btn-dark flex-grow-1">-</a>
      </div>

    </div>
  </form>

  <!-- ปุ่มเพิ่มบิล -->
  <a href="stock_out_add.php" class="btn btn-danger mb-3">เพิ่มบิลขายสินค้า</a>

  <!-- ตารางบิลขาย -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>วันที่</th>
        <th>ยอดรวม (บาท)</th>
        <th>จัดการ</th>
      </tr>
    </thead>

    <tbody>
      <?php if (!empty($bills_out)): ?>
        <?php foreach ($bills_out as $row): ?>
        <tr>
          <td><?= date("d/m/Y", strtotime($row['bill_date'])) ?></td>
          <td class="text-end"><?= number_format($row['total_amount'], 2) ?></td>
          <td class="text-center">
            <a href="sale_detail.php?sale_id=<?= $row['bill_id'] ?>"
               class="btn btn-sm btn-info">ดูรายละเอียด</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="3" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
      <?php endif; ?>
    </tbody>

    <tfoot>
      <tr class="table-light">
        <th class="text-end">ยอดรวมบิลขายที่แสดง:</th>
        <th class="text-end"><?= number_format($total_out, 2) ?></th>
        <th></th>
      </tr>
    </tfoot>
  </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

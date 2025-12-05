<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
// รับค่าจากฟอร์มค้นหา
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$bill_type = $_GET['bill_type'] ?? 'all';
$search_term = $_GET['search_term'] ?? '';

$params = [];
$types = '';
 
// สร้าง SQL พื้นฐานด้วย UNION ALL เพื่อรวมบิลซื้อและขาย
$sql = "
    SELECT * FROM (
        (
            SELECT 
                p.purchase_id AS bill_id,
                p.purchase_number AS bill_number,
                p.purchase_date AS bill_date,
                p.total_amount,
                s.supplier_name AS party_name,
                'บิลซื้อ (Purchase)' AS type,
                'purchase_detail.php' AS detail_page,
                'id' AS param_name
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        )
        UNION ALL
        (
            SELECT 
                s.sale_id AS bill_id,
                s.sale_number AS bill_number,
                s.sale_date AS bill_date,
                s.total_amount,
                'ลูกค้าทั่วไป' AS party_name,
                'บิลขาย (Sale)' AS type,
                'sale_detail.php' AS detail_page,
                'sale_id' AS param_name
            FROM sales s
        )
    ) AS combined_bills
    WHERE 1=1
";

// เพิ่มเงื่อนไขการค้นหา
if ($start_date) {
    $sql .= " AND bill_date >= ?";
    $params[] = $start_date;
    $types .= 's';
}
if ($end_date) {
    $sql .= " AND bill_date <= ?";
    $params[] = $end_date;
    $types .= 's';
}
if ($bill_type != 'all') {
    $sql .= " AND type = ?";
    $params[] = $bill_type;
    $types .= 's';
}
if ($search_term) {
    $sql .= " AND (bill_number LIKE ? OR party_name LIKE ?)";
    $like_term = "%" . $search_term . "%";
    $params[] = $like_term;
    $params[] = $like_term;
    $types .= 'ss';
}

$sql .= " ORDER BY bill_date DESC, bill_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ประวัติการซื้อขาย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- แถบเมนูด้านบน -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 Warehouse System</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">รายการบิลสินค้า</a></li>
         <!-- <li class="nav-item"><a class="nav-link active" href="history.php">ประวัติ</a></li> -->
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">ประวัติการทำรายการ</h2>

    <!-- ฟอร์มค้นหา -->
    <form method="GET" class="card card-body mb-4">
        <div class="row g-3">
            <div class="col-md-3"><input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>"></div>
            <div class="col-md-3"><input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>"></div>
            <div class="col-md-2">
                <select name="bill_type" class="form-select">
                    <option value="all" <?= $bill_type == 'all' ? 'selected' : '' ?>>บิลทั้งหมด</option>
                    <option value="บิลซื้อ (Purchase)" <?= $bill_type == 'บิลซื้อ (Purchase)' ? 'selected' : '' ?>>บิลรับเข้า</option>
                    <option value="บิลขาย (Sale)" <?= $bill_type == 'บิลขาย (Sale)' ? 'selected' : '' ?>>บิลขายออก</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" name="search_term" class="form-control" placeholder="ค้นหาเลขที่บิล, ซัพพลายเออร์..." value="<?= htmlspecialchars($search_term) ?>">
                    <button class="btn btn-primary" type="submit">ค้นหา</button>
                </div>
            </div>
        </div>
    </form>

    <!-- ตารางแสดงผล -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>วันที่</th>
                <th>เลขที่บิล</th>
                <th>ประเภท</th>
                <th>ซัพพลายเออร์/ลูกค้า</th>
                <th>ยอดรวม (บาท)</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date("d/m/Y", strtotime($row['bill_date'])) ?></td>
                        <td><?= htmlspecialchars($row['bill_number']) ?></td>
                        <td><span class="badge <?= $row['type'] == 'บิลซื้อ (Purchase)' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($row['type']) ?></span></td>
                        <td><?= htmlspecialchars($row['party_name']) ?></td>
                        <td class="text-end"><?= number_format($row['total_amount'], 2) ?></td>
                        <td class="text-center"><a href="<?= $row['detail_page'] ?>?<?= $row['param_name'] ?>=<?= $row['bill_id'] ?>" class="btn btn-sm btn-info">ดูรายละเอียด</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูลตามเงื่อนไขที่ค้นหา</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
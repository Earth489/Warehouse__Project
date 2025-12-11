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

// รับค่าการค้นหา
$product_id_filter = $_GET['product_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// สร้าง SQL query พื้นฐาน
$sql = "SELECT 
            ps.id,
            ps.split_date,
            p_parent.product_id AS parent_product_id,
            p_parent.product_name AS parent_product_name,
            ps.parent_qty,
            p_parent.product_unit AS parent_unit,
            p_new.product_id AS new_product_id,
            p_new.product_name AS new_product_name,
            ps.new_qty,
            p_new.product_unit AS new_unit
        FROM 
            product_split ps
        JOIN 
            products p_parent ON ps.parent_product_id = p_parent.product_id
        JOIN 
            products p_new ON ps.new_product_id = p_new.product_id
        ";

// เพิ่มเงื่อนไขการค้นหา
$conditions = [];
$bind_params = [];
$types = '';

if (!empty($product_id_filter) && is_numeric($product_id_filter)) {
    $conditions[] = "(ps.parent_product_id = ? OR ps.new_product_id = ?)";
    $bind_params[] = &$product_id_filter;
    $bind_params[] = &$product_id_filter; // ใช้ตัวแปรเดียวกันได้เพราะ bind_param จะใช้ค่า ณ ตอน execute
    $types .= 'ii';
}

if (!empty($date_from)) {
    $conditions[] = "ps.split_date >= ?";
    $bind_params[] = &$date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $conditions[] = "ps.split_date <= ?";
    $bind_params[] = &$date_to;
    $types .= 's';
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY ps.split_date DESC";

$stmt = $conn->prepare($sql);
if ($types) { // ตรวจสอบว่ามีพารามิเตอร์ที่ต้อง bind หรือไม่
    $stmt->bind_param($types, ...$bind_params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการแยกสินค้า</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>
                <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>
                <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
                <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
                <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">ประวัติการแยกสินค้า</h2>
        <a href="product_split_add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มรายการแยกสินค้า
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="product_split.php" class="mb-4 card card-body bg-light">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="product_id" class="form-label">ค้นหาสินค้า</label>
                <select name="product_id" id="product_id" class="form-select">
                    <option value=""> สินค้าทั้งหมด </option>
                    <?php mysqli_data_seek($product_list_result, 0); ?>
                    <?php while ($product = $product_list_result->fetch_assoc()): ?>
                        <option value="<?= $product['product_id'] ?>" <?= ($product_id_filter == $product['product_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['product_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">วันที่ (จาก)</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">วันที่ (ถึง)</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary w-100 me-2"><i class="bi bi-search"></i> ค้นหา</button>
                <a href="product_split.php" class="btn btn-secondary" title="ล้างการค้นหา"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>


    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
        <tr>
            <th class="text-start">ID</th>
            <th class="text-start">วันที่แยก</th>
            <th class="text-start">สินค้าต้นทาง</th>
            <th class="text-end">จำนวนที่ใช้</th>
            <th class="text-start">รายการสินค้าที่ได้จากการแยก</th>
            <th class="text-end">จำนวนที่ได้</th>
            <th class="text-center">จัดการ</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="text-start"><?= $row['id'] ?></td>
                    <td class="text-start"><?= date("d/m/Y", strtotime($row['split_date'])) ?></td>
                    <td class="text-start"><?= htmlspecialchars($row['parent_product_name']) ?></td>
                    <td class="text-end"><?= number_format($row['parent_qty'], 2) . ' ' . htmlspecialchars($row['parent_unit']) ?></td>
                    <td class="text-start"><?= htmlspecialchars($row['new_product_name']) ?></td>
                    <td class="text-end"><?= number_format($row['new_qty'], 2) . ' ' . htmlspecialchars($row['new_unit']) ?></td>
                    <td class="text-center">
                        <a href="product_split_detail.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">ดูรายละเอียด</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted">ยังไม่มีประวัติการแยกสินค้า</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (จำเป็นสำหรับ Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
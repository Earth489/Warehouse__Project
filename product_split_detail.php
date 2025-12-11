<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: product_split.php");
    exit();
}

$split_id = (int)$_GET['id'];

$sql = "SELECT 
            ps.id,
            ps.split_date,
            ps.parent_qty,
            ps.new_qty,
            p_parent.product_id AS parent_product_id,
            p_new.product_id AS new_product_id,
            p_parent.product_name AS parent_product_name,
            p_parent.product_unit AS parent_unit,
            p_new.product_name AS new_product_name,
            p_new.product_unit AS new_unit
        FROM 
            product_split ps
        JOIN 
            products p_parent ON ps.parent_product_id = p_parent.product_id
        JOIN 
            products p_new ON ps.new_product_id = p_new.product_id
        WHERE ps.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $split_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('ไม่พบข้อมูลการแยกสินค้า'); window.location='product_split.php';</script>";
    exit();
}

$split = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการแยกสินค้า</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .card { border: none; border-radius: 0.75rem; }
        .timeline-item {
            position: relative;
            padding-left: 30px;
            border-left: 2px solid #e9ecef;
        }
        .timeline-icon {
            position: absolute;
            left: -13px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
    </style>
</head>
<body>

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
          <li class="nav-item"><a class="nav-link active" href="product_split.php">แยกสินค้า</a></li>     
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
</nav> 

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-text"></i> รายละเอียดการแยกสินค้า #<?= $split['id'] ?></h3>
        <a href="product_split.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <p><strong>วันที่:</strong> <?= date("d/m/Y", strtotime($split['split_date'])) ?></p>
            <hr class="my-4">
            <div class="timeline-item pb-3">
                <div class="timeline-icon bg-danger"><i class="bi bi-arrow-up"></i></div>
                <h6 class="fw-bold mb-1">จากสินค้า: <?= htmlspecialchars($split['parent_product_name']) ?></h6>
                <p class="mb-0 text-danger">ID: <?= htmlspecialchars($split['parent_product_id']) ?> จำนวนที่ใช้: -<?= number_format($split['parent_qty'], 2) ?> <?= htmlspecialchars($split['parent_unit']) ?></p>
            </div>
            <div class="timeline-item">
                <div class="timeline-icon bg-success"><i class="bi bi-arrow-down"></i></div>
                <h6 class="fw-bold mb-1">ได้เป็นสินค้า: <?= htmlspecialchars($split['new_product_name']) ?></h6>
                <p class="mb-0 text-success">จำนวนที่ได้: +<?= number_format($split['new_qty'], 2) ?> <?= htmlspecialchars($split['new_unit']) ?></p>
                <p class="mb-0 text-success">ID: <?= htmlspecialchars($split['new_product_id']) ?></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
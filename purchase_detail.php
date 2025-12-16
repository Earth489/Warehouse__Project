<?php
include 'connection.php';
session_start();

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: warehouse_page.php");
    exit();
}

$purchase_id = $_GET['id'];

// ดึงข้อมูลหัวบิล
$sqlHeader = "SELECT p.purchase_id, p.purchase_number, p.purchase_date, 
                      s.supplier_name, s.phone, s.address, p.total_amount
               FROM purchases p
               LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
               WHERE p.purchase_id = ?";
$stmt = $conn->prepare($sqlHeader);
$stmt->bind_param("i", $purchase_id);
$stmt->execute();
$headerResult = $stmt->get_result();
$purchase = $headerResult->fetch_assoc();

// ดึงรายละเอียดสินค้าในบิล
$sqlItems = "SELECT d.product_id, pr.product_name, pr.product_unit, d.quantity, d.purchase_price, 
                    (d.quantity * d.purchase_price) AS total
             FROM purchase_details d
             LEFT JOIN products pr ON d.product_id = pr.product_id
             WHERE d.purchase_id = ?";
$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param("i", $purchase_id);
$stmt2->execute();
$itemsResult = $stmt2->get_result();

// ✅ เพิ่ม Logic: ตรวจสอบหน่วยสินค้าในบิลเพื่อเปลี่ยนหัวตารางแบบไดนามิก
$price_header = "ราคาซื้อต่อหน่วย"; // ค่าเริ่มต้น
$all_items = []; 
$all_units = [];
if ($itemsResult->num_rows > 0) {
    while($item = $itemsResult->fetch_assoc()) {
        $all_items[] = $item; // เก็บข้อมูลทั้งหมดไว้ใน array
        if (!empty($item['product_unit'])) {
            $all_units[] = $item['product_unit']; // เก็บเฉพาะหน่วย
        }
    }
    $unique_units = array_unique($all_units); 
    if (count($unique_units) === 1) {
        $price_header = "ราคาซื้อต่อ" . htmlspecialchars(reset($unique_units));
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายละเอียดบิลรับสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #f8f9fa;
}
.card {
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.product-name-col {
  max-width: 300px; /* กำหนดความกว้างสูงสุด */
  word-wrap: break-word; /* สำหรับเบราว์เซอร์เก่า */
  overflow-wrap: break-word; /* มาตรฐานใหม่ */
  white-space: normal !important; /* ทำให้ข้อความตัดขึ้นบรรทัดใหม่ได้ */
}
@media print {
  .no-print { display: none; }
  .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
}
</style>
</head>
<body> 
 
<!-- แถบเมนูด้านบน -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">🏠 ระบบจัดการคลังสินค้า สำหรับร้านวัสดุก่อสร้าง</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="homepage.php">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>       
          <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>   
          <li class="nav-item"><a class="nav-link active" href="warehouse_page.php">บิลรับสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-5 mb-5">
  <div class="card">
    <div class="card-header bg-dark text-white">
      <h4 class="mb-0">รายละเอียดบิลรับสินค้า</h4>
    </div>
    <div class="card-body">
      <?php if ($purchase): ?>
        <div class="mb-3">
          <p><strong>เลขที่บิล:</strong> <?= htmlspecialchars($purchase['purchase_number']) ?></p>
          <p><strong>วันที่รับเข้า:</strong> <?= date("d/m/Y", strtotime($purchase['purchase_date'])) ?></p>
          <p><strong>ซัพพลายเออร์:</strong> <?= htmlspecialchars($purchase['supplier_name']) ?></p>
          <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($purchase['phone'] ?? '-') ?></p>
          <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($purchase['address'] ?? '-') ?></p>
        </div>

        <h5 class="mt-4"> รายการสินค้า</h5>
        <table class="table table-bordered mt-3">
          <thead class="table-light">
            <tr>
              <th>ชื่อสินค้า</th>
              <th>จำนวน</th>
              <th>หน่วยนับ</th>
              <th><?= $price_header ?></th>
              <th class="text-end">ราคารวม</th>
              <th class="text-end">ราคารวม (+VAT 7%)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($all_items)): ?>
              <?php foreach ($all_items as $item): ?>
                <tr>
                  <td class="product-name-col"><?= htmlspecialchars($item['product_name']) ?></td>
                  <td class="text-end"><?= number_format($item['quantity'], 0) ?></td>
                  <td class="text-center"><?= htmlspecialchars($item['product_unit']) ?></td>
                  <td class="text-end"><?= number_format($item['purchase_price'], 2) ?> ฿</td>
                  <td class="text-end"><?= number_format($item['total'], 2) ?> ฿</td>
                  <td class="text-end fw-bold"><?= number_format($item['total'] * 1.07, 2) ?> ฿</td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted">ไม่มีรายการสินค้าในบิลนี้</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <div class="row justify-content-end mt-3">
          <div class="col-md-5">
            <?php
              $subtotal = $purchase['total_amount'];
              $vat = $subtotal * 0.07;
              $grand_total = $subtotal + $vat;
            ?>
            <p class="d-flex justify-content-between"><strong>ราคารวม (ก่อน VAT):</strong> <strong><?= number_format($subtotal, 2) ?> บาท</strong></p>
            <p class="d-flex justify-content-between"><strong>VAT (7%):</strong> <strong><?= number_format($vat, 2) ?> บาท</strong></p>
            <h5 class="d-flex justify-content-between"><strong>ยอดรวมสุทธิ:</strong> <strong><?= number_format($grand_total, 2) ?> บาท</strong></h5>
          </div>
        </div>

      <?php else: ?>
        <div class="alert alert-danger">ไม่พบบิลนี้ในระบบ</div>
      <?php endif; ?>

      <div class="mt-4">
        <a href="warehouse_page.php" class="btn btn-secondary no-print">กลับ</a>
        <button onclick="window.print()" class="btn btn-info no-print">พิมพ์ใบเสร็จ</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
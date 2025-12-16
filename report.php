<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$bill_type = $_GET['bill_type'] ?? 'all';

// ดึงข้อมูลบิล
if ($bill_type === 'บิลซื้อ (Purchase)') {
    $sql = "
        SELECT 
            p.purchase_id AS bill_id, 
            p.purchase_number AS bill_number, 
            p.purchase_date AS bill_date, 
            s.supplier_name AS party_name, 
            p.total_amount, 
            'บิลซื้อ (Purchase)' AS bill_type
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.purchase_date BETWEEN ? AND ?
        ORDER BY p.purchase_date DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
} elseif ($bill_type === 'บิลขาย (Sale)') {
    $sql = "
        SELECT 
            s.sale_id AS bill_id, 
            s.sale_id AS bill_number, 
            s.sale_date AS bill_date, 
            'ลูกค้าทั่วไป' AS party_name, 
            s.total_amount, 
            'บิลขาย (Sale)' AS bill_type
        FROM sales s
        WHERE s.sale_date BETWEEN ? AND ?
        ORDER BY s.sale_date DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $sql = "
        SELECT 
            p.purchase_id AS bill_id, 
            p.purchase_number AS bill_number, 
            p.purchase_date AS bill_date, 
            s.supplier_name AS party_name, 
            p.total_amount, 
            'บิลซื้อ (Purchase)' AS bill_type
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.purchase_date BETWEEN ? AND ?
        UNION ALL
        SELECT 
            s.sale_id AS bill_id, 
            s.sale_id AS bill_number, 
            s.sale_date AS bill_date, 
            'ลูกค้าทั่วไป' AS party_name, 
            s.total_amount, 
            'บิลขาย (Sale)' AS bill_type
        FROM sales s
        WHERE s.sale_date BETWEEN ? AND ?
        ORDER BY bill_date DESC, bill_type
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();

// เก็บข้อมูลทั้งหมดไว้ใน array เพื่อคำนวณและแสดงผล
$all_bills = [];
while($row = $result->fetch_assoc()) { $all_bills[] = $row; }
// mysqli_data_seek($result, 0); // ไม่จำเป็นแล้วเพราะใช้ $all_bills
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายงานสรุปสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@media print {
    .no-print { display: none; }
    body { 
        background: white; 
        font-size: 10pt; /* ลดขนาดฟอนต์เล็กน้อยสำหรับพิมพ์ */
    }
    .container {
        max-width: 100% !important; /* ใช้ความกว้างเต็มหน้ากระดาษ */
        width: 100% !important;
        padding: 0;
        margin: 0;
    }
    .card {
        border: 1px solid #ccc !important; /* เปลี่ยนจากเงาเป็นเส้นขอบบางๆ */
        box-shadow: none !important;
        page-break-inside: avoid; /* ป้องกันการ์ดถูกตัดครึ่งระหว่างหน้า */
    }
    .card-header, .table-secondary {
        background-color: #f2f2f2 !important; /* ทำให้พื้นหลังหัวการ์ดและท้ายตารางเป็นสีเทาอ่อนๆ */
    }
    .badge {
        border: 1px solid #000;
        background-color: white !important;
        color: black !important;
    }
    h2, strong { color: black !important; }
}
</style>
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
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li> 
          <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>         
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link active" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">รายงานสรุปสินค้า</h2>

    <!-- ฟอร์มเลือกวันที่ -->
    <form method="get" class="card card-body mb-4 no-print">
        <h5 class="card-title mb-3">กำหนดช่วงข้อมูลรายงาน</h5>
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">ประเภทบิล</label>
                <select name="bill_type" class="form-select">
                    <option value="all" <?= $bill_type == 'all' ? 'selected' : '' ?>>บิลทั้งหมด</option>
                    <option value="บิลซื้อ (Purchase)" <?= $bill_type == 'บิลซื้อ (Purchase)' ? 'selected' : '' ?>>บิลรับสินค้า</option>
                    <option value="บิลขาย (Sale)" <?= $bill_type == 'บิลขาย (Sale)' ? 'selected' : '' ?>>บิลขายสินค้า</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">แสดงรายงาน</button>
                <a href="#" id="print-summary-btn" class="btn btn-danger flex-fill" target="_blank">พิมพ์สรุป (PDF)</a>
            </div>
        </div>
    </form>


    <?php 
        // คำนวณยอดสรุป
        $total_purchase = 0;
        $total_sale = 0;
        $purchase_count = 0;
        $sale_count = 0;
        foreach ($all_bills as $bill) {
            if ($bill['bill_type'] == 'บิลซื้อ (Purchase)') {
                // ยอดซื้อรวมจาก total_amount ในฐานข้อมูล (ซึ่งยังไม่รวม VAT) ต้องคูณ 1.07
                $total_purchase += $bill['total_amount'] * 1.07;
                $purchase_count++;
            } else {
                $total_sale += $bill['total_amount'];
                $sale_count++;
            }
        }
    ?>
    <!-- กล่องสรุปข้อมูล -->
    <div class="row mb-4">
        <div class="col-4"><div class="card card-body bg-light">ยอดซื้อรวม: <strong class="fs-5 text-success"><?= number_format($total_purchase, 2) ?></strong> บาท (<?= $purchase_count ?> บิล)</div></div>
        <div class="col-4"><div class="card card-body bg-light">ยอดขายรวม: <strong class="fs-5 text-danger"><?= number_format($total_sale, 2) ?></strong> บาท (<?= $sale_count ?> บิล)</div></div>
        <div class="col-4"><div class="card card-body bg-light">จำนวนบิลทั้งหมด: <strong class="fs-5"><?= count($all_bills) ?></strong> บิล</div></div>
    </div>


    <!-- ตารางรายงาน -->
    <?php if (count($all_bills) > 0): ?>
        <?php foreach ($all_bills as $row): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>วันที่:</strong> <?= date("d/m/Y", strtotime($row['bill_date'])) ?><br>
                        <strong>เลขที่บิล:</strong> <?= htmlspecialchars($row['bill_number']) ?><br>
                        <strong>คู่ค้า:</strong> <?= htmlspecialchars($row['party_name']) ?>
                    </div>
                    <span class="badge <?= ($row['bill_type'] == 'บิลซื้อ (Purchase)') ? 'bg-success' : 'bg-danger' ?>">
                        <?= htmlspecialchars($row['bill_type']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php
                    if ($row['bill_type'] == 'บิลซื้อ (Purchase)') {
                        $detail_sql = "
                            SELECT pd.quantity, pd.purchase_price AS price, p.product_name, p.product_unit AS unit
                            FROM purchase_details pd
                            JOIN products p ON pd.product_id = p.product_id
                            WHERE pd.purchase_id = ?
                        ";
                    } else {
                        $detail_sql = "
                            SELECT sd.quantity, sd.sale_price AS price, sd.sale_unit AS unit, p.product_name
                            FROM sale_details sd
                            JOIN products p ON sd.product_id = p.product_id
                            WHERE sd.sale_id = ?
                        ";
                    }
                    $stmt2 = $conn->prepare($detail_sql);
                    $stmt2->bind_param("i", $row['bill_id']);
                    $stmt2->execute();
                    $details = $stmt2->get_result();
                    if ($details->num_rows > 0): ?>
                        <table class="table table-bordered mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th>สินค้า</th>
                                    <th>จำนวน</th>
                                    <th>หน่วย</th>
                                    <th>ราคา/หน่วย</th>
                                    <th>รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sum = 0;
                                while ($d = $details->fetch_assoc()):
                                    // ราคาต่อหน่วยที่แสดงคือราคา ณ ตอนที่ขาย ไม่ต้องคำนวณแปลงหน่วยซ้ำ
                                    $line_total = $d['quantity'] * $d['price'];
                                    $sum += $line_total; ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['product_name']) ?></td>
                                        <td><?= $d['quantity'] ?></td>
                                        <td><?= htmlspecialchars($d['unit']) ?></td>
                                        <td><?= number_format($d['price'], 2) ?></td>
                                        <td><?= number_format($line_total, 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($row['bill_type'] == 'บิลซื้อ (Purchase)'):
                                    // คำนวณ VAT และราคาก่อน VAT จาก total_amount ที่บันทึกไว้ในบิล เพื่อความถูกต้อง
                                    $grand_total_with_vat = $row['total_amount'] * 1.07;
                                    $price_before_vat = $row['total_amount']; // total_amount ใน DB คือยอดก่อน VAT
                                    $vat = $grand_total_with_vat - $price_before_vat;
                                ?>
                                <!-- แสดงผลรวมจากรายการสินค้าเพื่อตรวจสอบ แต่ใช้ค่าที่คำนวณจาก total_amount เป็นหลัก -->
                                <tr><td colspan="4" class="text-end">ราคารวม (ก่อน VAT)</td><td class="text-end fw-bold"><?= number_format($price_before_vat, 2) ?></td></tr>
                                <tr><td colspan="4" class="text-end">VAT (7%)</td><td class="text-end fw-bold"><?= number_format($vat, 2) ?></td></tr>
                                <tr class="table-secondary"><td colspan="4" class="text-end fw-bold">ยอดรวมสุทธิ</td><td class="text-end fw-bold"><?= number_format($grand_total_with_vat, 2) ?></td></tr>
                                <?php else: ?>
                                <tr class="table-secondary">
                                    <td colspan="4" class="text-end fw-bold">รวมทั้งหมด</td>
                                    <td class="fw-bold"><?= number_format($sum, 2) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted mb-0">ไม่มีรายละเอียดสินค้าในบิลนี้</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">ไม่พบบิลในช่วงวันที่ที่เลือก</div>
    <?php endif; ?>
</div>

<script>
function updatePrintLink() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const billType = document.querySelector('select[name="bill_type"]').value;
    const printBtn = document.getElementById('print-summary-btn');
    printBtn.href = `report_summary_print.php?start_date=${startDate}&end_date=${endDate}&bill_type=${billType}`;
}
// อัปเดตลิงก์เมื่อหน้าโหลดและเมื่อมีการเปลี่ยนแปลงค่าในฟอร์ม
document.addEventListener('DOMContentLoaded', updatePrintLink);
document.querySelector('form').addEventListener('change', updatePrintLink);
</script>

</body>
</html>
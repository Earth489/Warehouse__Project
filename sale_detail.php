<?php
include 'connection.php';
session_start();

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['sale_id'])) {
    echo "ไม่พบบิลที่ต้องการดู";
    exit;
}

$sale_id = $_GET['sale_id'];

// ดึงข้อมูลบิลขาย (ไม่ผูกกับ user)
$sql_sale = "SELECT sale_id, sale_date, total_amount 
             FROM sales
             WHERE sale_id = ?";
$stmt = $conn->prepare($sql_sale);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$result_sale = $stmt->get_result();

if ($result_sale->num_rows == 0) {
    echo "ไม่พบบิลที่ต้องการดู";
    exit;
}

$sale = $result_sale->fetch_assoc();

// ดึงรายละเอียดสินค้าในบิล
$sql_detail = "SELECT sd.*, p.product_name, p.base_unit, p.unit_conversion_rate
               FROM sale_details sd
               JOIN products p ON sd.product_id = p.product_id
               WHERE sd.sale_id = ?";
$stmt2 = $conn->prepare($sql_detail);
$stmt2->bind_param("i", $sale_id);
$stmt2->execute();
$result_detail = $stmt2->get_result();

// ✅ เพิ่ม Logic: ตรวจสอบหน่วยสินค้าในบิลเพื่อเปลี่ยนหัวตารางแบบไดนามิก
$price_header = "ราคาขายต่อหน่วย"; // ค่าเริ่มต้น
$all_items = [];
$all_units = []; 
if ($result_detail->num_rows > 0) {
    while($item = $result_detail->fetch_assoc()) {
        $all_items[] = $item; // เก็บข้อมูลทั้งหมดไว้ใน array
        $all_units[] = $item['sale_unit']; // เก็บเฉพาะหน่วย
    }
    // ทำให้หน่วยที่ซ้ำกันเหลือแค่ตัวเดียว
    $unique_units = array_unique($all_units); 
    if (count($unique_units) === 1) {
        // ถ้ามีหน่วยแค่แบบเดียวในบิลนี้
        $price_header = "ราคาขายต่อ" . htmlspecialchars(reset($unique_units));
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดบิลขาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .product-name-col {
      word-wrap: break-word;
      overflow-wrap: break-word;
    }
    @media print {
      .no-print { display: none; }
      .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    }
    </style>
</head>
<body class="bg-light">
  
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
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link active" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-5 mb-5">
  <div class="card shadow">
    <div class="card-header bg-dark text-white">
      <h4 class="mb-0">รายละเอียดบิลขาย</h4>
    </div>
    <div class="card-body">
      <p><strong>วันที่ขาย:</strong> <?= htmlspecialchars($sale['sale_date']) ?></p>
      <p><strong>ยอดรวม:</strong> <?= number_format($sale['total_amount'], 2) ?> บาท</p>

      <h5 class="mt-4">รายการสินค้า</h5>
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>ชื่อสินค้า</th>
            <th>จำนวน</th>
            <th>หน่วย</th>
            <th><?= $price_header ?></th>
            <th>รวม</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $total = 0;
        foreach ($all_items as $row) { // วนลูปจาก array ที่เราเก็บไว้
            // ✅ คำนวณราคารวมของแถวให้ถูกต้อง
            $multiplier = 1;
            if ($row['sale_unit'] == $row['base_unit'] && $row['unit_conversion_rate'] > 1) {
                $multiplier = $row['unit_conversion_rate'];
            }
            $sum = $row['quantity'] * $row['sale_price'] * $multiplier;
            $total += $sum;

            // ✅ เพิ่ม Logic สำหรับแสดงราคาขายต่อหน่วยให้ถูกต้อง
            $display_price = $row['sale_price']; // ราคาตั้งต้นคือราคาต่อหน่วยย่อย
            if ($multiplier > 1) {
                // ถ้าขายเป็นหน่วยหลัก (เช่น กระสอบ) ให้เอาราคาต่อหน่วยย่อยมาคูณกับอัตราแปลง
                $display_price = $row['sale_price'] * $row['unit_conversion_rate'];
            }

            echo "<tr>
                    <td class='product-name-col'>" . htmlspecialchars($row['product_name']) . "</td>
                    <td>{$row['quantity']}</td>
                    <td>" . htmlspecialchars($row['sale_unit']) . "</td>
                    <td>" . number_format($display_price, 2) . "</td>
                    <td>" . number_format($sum, 2) . "</td>
                  </tr>";
        }
        ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">รวมทั้งหมด</th>
            <th><?= number_format($total, 2) ?></th>
          </tr>
        </tfoot>
      </table>

      <a href="warehouse_sale.php" class="btn btn-secondary mt-3 no-print">กลับ</a>
      <button onclick="window.print()" class="btn btn-info mt-3 no-print">พิมพ์ใบเสร็จ</button>
    </div>
  </div>
</div>
</body>
</html>
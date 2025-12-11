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

// Data arrays
$purchase_summary = [];
$sale_summary = [];
$total_purchase_cost = 0;
$total_sale_revenue = 0;

// --- ดึงข้อมูลสรุปการซื้อ ---
if ($bill_type === 'all' || $bill_type === 'บิลซื้อ (Purchase)') {
    $sql_purchase = "
        SELECT 
            p.product_name,
            SUM(pd.quantity) AS total_quantity,
            p.product_unit AS unit, 
            SUM(pd.quantity * pd.purchase_price) AS total_cost,
            SUM(pd.quantity * pd.purchase_price) / SUM(pd.quantity) as avg_purchase_price
        FROM purchase_details pd
        JOIN purchases pur ON pd.purchase_id = pur.purchase_id
        JOIN products p ON pd.product_id = p.product_id
        WHERE pur.purchase_date BETWEEN ? AND ?
        GROUP BY p.product_name, p.product_unit
        ORDER BY p.product_name;
    ";
    $stmt_p = $conn->prepare($sql_purchase);
    $stmt_p->bind_param("ss", $start_date, $end_date);
    $stmt_p->execute();
    $result_p = $stmt_p->get_result();
    while($row = $result_p->fetch_assoc()) {
        $purchase_summary[] = $row;
        $total_purchase_cost += $row['total_cost'];
    }
}

// --- ดึงข้อมูลสรุปการขาย ---
if ($bill_type === 'all' || $bill_type === 'บิลขาย (Sale)') {
    $sql_sale = "
        SELECT 
            p.product_name,
            SUM(sd.quantity) AS total_quantity,
            sd.sale_unit AS unit,
            SUM(sd.quantity * sd.sale_price) AS total_revenue,
            SUM(sd.quantity * sd.sale_price) / SUM(sd.quantity) as avg_sale_price
        FROM sale_details sd
        JOIN sales s ON sd.sale_id = s.sale_id
        JOIN products p ON sd.product_id = p.product_id
        WHERE s.sale_date BETWEEN ? AND ?
        GROUP BY p.product_name, sd.sale_unit
        ORDER BY p.product_name;
    ";
    $stmt_s = $conn->prepare($sql_sale);
    $stmt_s->bind_param("ss", $start_date, $end_date);
    $stmt_s->execute();
    $result_s = $stmt_s->get_result();
    while($row = $result_s->fetch_assoc()) {
        $sale_summary[] = $row;
        $total_sale_revenue += $row['total_revenue'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานสรุปยอดซื้อ-ขายสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: white; 
            font-family: 'Sarabun', sans-serif;
        }
        .container { max-width: 900px; }
        h3, h4 { margin-top: 1.5rem; }
        .table th, .table td { vertical-align: middle; }
        .table .product-name { text-align: left; }
        .table .unit-col { text-align: center; }
        .table .amount-col { text-align: right; }
        @media print {
            .no-print { display: none; }
            body { font-size: 11pt; }
            .container { max-width: 100%; width: 100%; padding: 0; margin: 0; }
            h3, h4 { margin-top: 1rem; }
            .card { border: none; }
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">รายงานสรุปยอดซื้อ-ขายสินค้า</h2>
            <p class="mb-0"><strong>ช่วงวันที่:</strong> <?= date("d/m/Y", strtotime($start_date)) ?> ถึง <?= date("d/m/Y", strtotime($end_date)) ?></p>
        </div>
        <div class="text-end">
            <small>พิมพ์โดย: <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></small><br>
            <small>วันที่พิมพ์: <?= date("d/m/Y H:i") ?></small>
        </div>
    </div>

    <!-- สรุปการซื้อ -->
    <?php if (!empty($purchase_summary)): ?>
        <h4 class="mt-4">สรุปยอดซื้อสินค้า</h4>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th class="product-name">ชื่อสินค้า</th>
                    <th class="amount-col">จำนวน</th>
                    <th class="unit-col">หน่วยนับ</th>
                    <th class="amount-col">ราคาซื้อต่อหน่วย (บาท)</th>
                    <th class="amount-col">ราคารวม (บาท)</th>
                    <th class="amount-col">ราคารวม +VAT 7% (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($purchase_summary as $item): ?>
                <tr>
                    <td class="product-name"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="amount-col"><?= number_format($item['total_quantity']) ?></td>
                    <td class="unit-col"><?= htmlspecialchars($item['unit']) ?></td>
                    <td class="amount-col"><?= number_format($item['avg_purchase_price'], 2) ?></td>
                    <td class="amount-col"><?= number_format($item['total_cost'], 2) ?></td>
                    <td class="amount-col"><?= number_format($item['total_cost'] * 1.07, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary fw-bold">
                    <td colspan="5" class="amount-col">ยอดซื้อรวม</td>
                    <td class="amount-col"><?= number_format($total_purchase_cost, 2) ?></td>
                </tr>
                <tr class="table-secondary fw-bold">
                    <td colspan="5" class="amount-col">ยอดซื้อรวม + VAT 7%</td>
                    <td class="amount-col"><?= number_format($total_purchase_cost * 1.07, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <?php if (!empty($purchase_summary) && !empty($sale_summary)): ?>
        <hr class="my-4">
    <?php endif; ?>

    <!-- สรุปการขาย -->
    <?php if (!empty($sale_summary)): ?>
        <h4 class="mt-4">สรุปยอดขายสินค้า</h4>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th class="product-name">ชื่อสินค้า</th>
                    <th class="amount-col">จำนวน</th>
                    <th class="unit-col">หน่วยนับ</th>
                    <th class="amount-col">ราคาขายต่อหน่วย (บาท)</th>
                    <th class="amount-col">ราคารวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale_summary as $item): ?>
                <tr>
                    <td class="product-name"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="amount-col"><?= number_format($item['total_quantity']) ?></td>
                    <td class="unit-col"><?= htmlspecialchars($item['unit']) ?></td>
                    <td class="amount-col"><?= number_format($item['avg_sale_price'], 2) ?></td>
                    <td class="amount-col"><?= number_format($item['total_revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary fw-bold">
                    <td colspan="4" class="amount-col">ยอดขายรวมทั้งหมด</td>
                    <td class="amount-col"><?= number_format($total_sale_revenue, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <?php if (empty($purchase_summary) && empty($sale_summary)): ?>
        <div class="alert alert-warning text-center mt-4">ไม่พบข้อมูลการซื้อหรือขายในช่วงวันที่ที่เลือก</div>
    <?php endif; ?>
</div>

<script>
    // เมื่อหน้าเว็บโหลดเสร็จ ให้เปิดหน้าต่างพิมพ์โดยอัตโนมัติ
    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>
<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงรายการสินค้าในสต็อก
$sql = "SELECT product_id, product_name, selling_price,
               stock_quantity, product_unit
        FROM products 
        WHERE stock_quantity > 0
        ORDER BY product_name ASC";
$result = $conn->query($sql);

// ดึงเลขที่บิลล่าสุด
$sql_last_bill = "SELECT sale_id FROM sales ORDER BY sale_id DESC LIMIT 1";
$result_last_bill = $conn->query($sql_last_bill);
if ($result_last_bill->num_rows > 0) {
    $last_bill = $result_last_bill->fetch_assoc()['sale_id'];
} else {
    $last_bill = 0;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มบิลขายสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
  /* ปรับแก้ให้ select2 แสดงผลได้ถูกต้องในตาราง */
  .select2-container--bootstrap-5 .select2-selection { padding: 0.375rem 0.75rem; height: calc(2.4375rem + 2px); }
</style>
</head>
<body class="bg-light">

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
          <li class="nav-item"><a class="nav-link " href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
          <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link active" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link " href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
    <h2 class="fw-bold mb-4">เพิ่มบิลขายสินค้า</h2>
    <form action="stock_out_save.php" method="POST" id="sale-form" onsubmit="return confirm('คุณต้องการบันทึกการขายนี้ใช่หรือไม่?');">

        <div class="row mb-3">
    <div class="col-md-4">
        <label for="sale_date" class="form-label">วันที่ขาย</label>
        <input type="date" id="sale_date" name="sale_date" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">เลขที่บิล</label>
        <input type="text" class="form-control" value="<?= $last_bill + 1 ?>" readonly>
    </div>
</div>

 
        <table class="table table-bordered" style="table-layout: fixed;">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width: 30%;">สินค้า</th>
                    <th style="width: 15%;">หน่วยที่ขาย</th>
                    <th style="width: 10%;">ราคาขาย</th>
                    <th style="width: 15%;">จำนวนคงเหลือ</th>
                    <th style="width: 10%;">จำนวนที่ขาย</th>
                    <th style="width: 15%;">ราคารวม</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="itemBody">
                <tr>
                    <td>
                        <select name="product_id[]" class="form-select product-select" required>
                            <option value=""> เลือกสินค้า </option>
                            <?php mysqli_data_seek($result, 0); ?>
                            <?php while ($p = $result->fetch_assoc()): ?>
                                <option value="<?= $p['product_id'] ?>" 
                                        data-price="<?= $p['selling_price'] ?>"
                                        data-stock="<?= $p['stock_quantity'] ?>"
                                        data-unit="<?= htmlspecialchars($p['product_unit']) ?>">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <select name="sale_unit[]" class="form-select sale-unit" required></select>
                    </td>
                    <td><input type="text" class="form-control price text-end" readonly></td>
                    <td><input type="text" class="form-control stock text-center" readonly></td>
                    <td><input type="number" name="quantity[]" class="form-control quantity text-center" min="1" required></td>
                    <td><input type="text" class="form-control row-total text-end" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-remove">-</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" id="btnAdd" class="btn btn-secondary">+ เพิ่มแถว</button>
        <button type="submit" class="btn btn-success">บันทึกการขาย</button>
        <a href="warehouse_sale.php" class="btn btn-outline-secondary">ยกเลิก</a>

        <div class="mt-3 text-end">
            <h4>ราคารวมทั้งหมด: <span id="totalAmount" class="text-success">0.00</span> บาท</h4>
        </div>
    </form>
</div>

<!-- jQuery (จำเป็นสำหรับ Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// เก็บ HTML ของแถวแรกไว้เป็นต้นแบบ (template) ก่อนที่จะถูก Select2 แปลง
const rowTemplate = document.getElementById('itemBody').querySelector('tr').cloneNode(true);

function initializeSelect2(element) {
    $(element).select2({
        theme: 'bootstrap-5',
        width: '100%'
    }).on('select2:select', e => e.target.dispatchEvent(new Event('change', { bubbles: true })));
}

function addRowListeners(row) {
    const productSelect = row.querySelector('.product-select');
    const unitSelect = row.querySelector('.sale-unit');
    const quantityInput = row.querySelector('.quantity');
    const removeBtn = row.querySelector('.btn-remove');

    productSelect.addEventListener('change', function() {
        // ถ้ายังไม่ได้เลือก (ค่าว่าง) ให้เคลียร์ข้อมูลและหยุดทำงาน
        if (!this.value) return;

        const selectedOption = this.options[this.selectedIndex];
        const tr = this.closest('tr');
        
        const price = parseFloat(selectedOption.dataset.price || 0);
        const stock = parseFloat(selectedOption.dataset.stock || 0);
        const unit = selectedOption.dataset.unit;

        tr.querySelector('.price').value = price.toFixed(2);
        tr.querySelector('.stock').value = `${stock} ${unit}`;

        // สร้างตัวเลือกหน่วยขาย
        unitSelect.innerHTML = '';
        unitSelect.add(new Option(unit, unit));

        updateTotals();
    });

    unitSelect.addEventListener('change', function() {
        updateTotals(); // คำนวณใหม่เมื่อเปลี่ยนหน่วย
    });

    quantityInput.addEventListener('input', () => updateTotals());

    removeBtn.addEventListener('click', () => {
        if (document.querySelectorAll('#itemBody tr').length > 1) {
            row.remove();
            updateTotals();
        } else {
            alert('ไม่สามารถลบแถวสุดท้ายได้');
        }
    });
}

function updateTotals() {
    let totalAmount = 0;
    document.querySelectorAll('#itemBody tr').forEach(row => {
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const quantity = parseInt(row.querySelector('.quantity').value) || 0;

        const rowTotal = price * quantity;

        row.querySelector('.row-total').value = rowTotal.toFixed(2);
        totalAmount += rowTotal;
    });
    document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
}

document.getElementById('btnAdd').addEventListener('click', () => {
    const tbody = document.getElementById('itemBody');
    // โคลนแถวใหม่จากต้นแบบที่เก็บไว้
    const newRow = rowTemplate.cloneNode(true);

    // ล้างค่าในแถวใหม่
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelector('.product-select').selectedIndex = 0;
    newRow.querySelector('.sale-unit').innerHTML = '';

    tbody.appendChild(newRow);
    addRowListeners(newRow);
    initializeSelect2(newRow.querySelector('.product-select')); // สร้าง Select2 ให้กับแถวที่เพิ่มใหม่
});

$(document).ready(function() {
    document.querySelectorAll('#itemBody tr').forEach(row => addRowListeners(row));
    initializeSelect2(document.querySelector('.product-select')); // ทำให้แถวแรกค้นหาได้
});
</script>
</body>
</html>

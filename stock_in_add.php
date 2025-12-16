<?php
include 'connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];

// ดึงสินค้า
$sqlProducts = "SELECT p.product_id, p.product_name, p.product_unit,
                       IFNULL(c.category_name,'-') AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                ORDER BY p.product_name ASC";
$prodResult = $conn->query($sqlProducts);
$products = [];
while ($r = $prodResult->fetch_assoc()) $products[] = $r;

// ดึงรายชื่อ suppliers
$supRes = $conn->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchase_number = trim($_POST['purchase_number']);
    $supplier_id = (int)$_POST['supplier_id'];
    $product_ids = $_POST['product'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $purchase_prices = $_POST['purchase_price'] ?? [];
      $purchase_date = $_POST['purchase_date']; // ดึงวันที่จากฟอร์ม

    if (!$purchase_number) $errors[] = "กรุณากรอกเลขที่บิล";
    if ($supplier_id <= 0) $errors[] = "กรุณาเลือกผู้จำหน่าย";
    if (count($product_ids) == 0) $errors[] = "กรุณาเลือกสินค้าอย่างน้อย 1 รายการ";

    $items = [];
    for ($i=0; $i<count($product_ids); $i++) {
        $pid = (int)$product_ids[$i];
        $qty = (int)$quantities[$i];
        $price = (float)$purchase_prices[$i];
        if ($pid>0 && $qty>0 && $price>=0) {
            $items[] = ['product_id'=>$pid,'qty'=>$qty,'price'=>$price];
        }
    }

    if (empty($errors) && count($items)>0) {
        $total_amount = 0;
        foreach ($items as $it) $total_amount += $it['qty'] * $it['price'];

        try {
            $conn->begin_transaction();

           $ins = $conn->prepare("INSERT INTO purchases (purchase_number, user_id, supplier_id, purchase_date, total_amount) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("siisd", $purchase_number, $uid, $supplier_id, $purchase_date, $total_amount);
            $ins->execute();
            $purchase_id = $ins->insert_id;
            $ins->close();

            // เตรียม Statement สำหรับบันทึกรายละเอียดและอัปเดตสต็อก
            $insDet = $conn->prepare("INSERT INTO purchase_details (purchase_id, product_id, quantity, purchase_price) VALUES (?, ?, ?, ?)");
            $updStock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");

            foreach ($items as $it) {
                // บันทึกรายละเอียดการซื้อ
                $insDet->bind_param("iiid", $purchase_id, $it['product_id'], $it['qty'], $it['price']);
                $insDet->execute();

                // อัปเดตสต็อกสินค้า
                $updStock->bind_param("di", $it['qty'], $it['product_id']);
                $updStock->execute();
            }
            $conn->commit();

            header("Location: warehouse_page.php?msg=stockin_ok");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รับสินค้าเข้าคลัง</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
  /* ปรับแก้ให้ select2 แสดงผลได้ถูกต้องในตาราง */
  .select2-container--bootstrap-5 .select2-selection { padding: 0.375rem 0.75rem; height: calc(2.4375rem + 2px); }
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
        <li class="nav-item"><a class="nav-link active" href="warehouse_page.php">บิลรับสินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link " href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
  <h2>รับสินค้าเข้าคลัง</h2>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>$e</div>"; ?></div>
  <?php endif; ?>

  <form method="post" onsubmit="return confirm('คุณต้องการบันทึกบิลรับสินค้านี้ใช่หรือไม่?');">
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">เลขที่บิล</label>
        <input type="text" name="purchase_number" class="form-control" required>
      </div>
      <div class="col-md-4">
  <label class="form-label">วันที่รับสินค้า</label>
  <input type="date" name="purchase_date" class="form-control" required>
  </div>
      <div class="col-md-4">
        <label class="form-label">ซัพพลายเออร์</label>
        <select name="supplier_id" class="form-select" required>
          <option value=""> เลือก </option>
          <?php while($s=$supRes->fetch_assoc()): ?>
            <option value="<?=$s['supplier_id']?>"><?=$s['supplier_name']?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <table class="table table-bordered" style="table-layout: fixed;">
      <thead class="table-dark text-center">
        <tr>
          <th style="width: 35%;">สินค้า</th>
          <th style="width: 15%;">ประเภท</th>
          <th style="width: 10%;">หน่วย</th>
          <th style="width: 12%;">ราคาซื้อ</th>
          <th style="width: 10%;">จำนวน</th>
          <th style="width: 13%;">ราคารวม</th>
          <th style="width: 13%;">ราคารวม (รวม VAT)</th>
          <th style="width: 5%;"></th>
        </tr>
      </thead>
      <tbody id="itemBody">
        <tr>
          <td>
            <select name="product[]" class="form-select product-select" required>
              <option value=""> เลือกสินค้า </option>
              <?php foreach($products as $p): ?>
                <option value="<?=$p['product_id']?>"
                        data-cat="<?=htmlspecialchars($p['category_name'])?>"
                        data-unit="<?=htmlspecialchars($p['product_unit'])?>">
                  <?=$p['product_name']?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="text" class="form-control cat" readonly></td>
          <td><input type="text" name="unit[]" class="form-control unit" readonly></td>
          <td><input type="number" step="0.01" name="purchase_price[]" class="form-control text-end" required></td>
          <td><input type="number" name="quantity[]" class="form-control text-center" min="1" required></td>
          <td><input type="text" class="form-control text-end row-total" readonly></td>
          <td><input type="text" class="form-control text-end row-total-vat" readonly></td>
          <td><button type="button" class="btn btn-danger btn-remove">-</button></td>
        </tr>
      </tbody>
    </table>

    <button type="button" id="btnAdd" class="btn btn-secondary">+ เพิ่มแถว</button>
    <button type="submit" class="btn btn-primary">บันทึก</button>
        <a href="warehouse_page.php" class="btn btn-outline-secondary">ยกเลิก</a>

        <div class="mt-3">
            <p>ราคารวม (ก่อน VAT): <span id="totalBeforeVat">0.00</span> บาท</p>
            <p>VAT (7%): <span id="vatAmount">0.00</span> บาท</p>
            <p>ราคารวมทั้งหมด: <span id="totalAmount">0.00</span> บาท</p>
        </div>
  </form>
</div> 
<!-- jQuery (จำเป็นสำหรับ Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- สคริปต์ JavaScript -->
<script>

// เก็บ HTML ของแถวแรกไว้เป็นต้นแบบ (template) ก่อนที่จะถูก Select2 แปลง
const rowTemplate = document.getElementById('itemBody').querySelector('tr').cloneNode(true);

function initializeSelect2(element) {
    $(element).select2({
        theme: 'bootstrap-5',
        width: '100%'
    }).on('select2:select', function (e) {
        // เมื่อมีการเลือก item ให้ trigger event 'change' ของ DOM เดิม
        // เพื่อให้โค้ดเก่าที่ดัก 'change' event ทำงานได้
        e.target.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

function addRowListeners(row) {
    const productSelect = row.querySelector('.product-select');
    // 2. ผูก Event เดิมสำหรับ Auto-fill
    productSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const tr = this.closest('tr');
        tr.querySelector('.cat').value = opt.dataset.cat || '';
        tr.querySelector('.unit').value = opt.dataset.unit || '';
    });

    // 3. ผูก Event สำหรับการคำนวณ
    const inputs = row.querySelectorAll('input[name="purchase_price[]"], input[name="quantity[]"]');
    inputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });

    // 4. ผูก Event ปุ่มลบ
    const removeBtn = row.querySelector('.btn-remove');
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            if (document.querySelectorAll('#itemBody tr').length > 1) {
                row.remove();
                updateTotals(); // คำนวณใหม่หลังลบแถว
            } else {
                alert('ไม่สามารถลบแถวสุดท้ายได้');
            }
        });
    }
}


// ปุ่มเพิ่มแถว
document.getElementById('btnAdd').addEventListener('click', () => {
    const tbody = document.getElementById('itemBody');
    const newRow = rowTemplate.cloneNode(true);
    
    // ล้างค่าในแถวใหม่
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    newRow.querySelector('.product-select').selectedIndex = 0;

    tbody.appendChild(newRow);
    addRowListeners(newRow);
    initializeSelect2(newRow.querySelector('.product-select'));
});


// ฟังก์ชันอัปเดตผลรวมทั้งหมด (ราคารวม, VAT, ยอดสุทธิ)
function updateTotals() { 
    let subtotal = 0;
    document.querySelectorAll('#itemBody tr').forEach(row => {
        const price = parseFloat(row.querySelector('input[name="purchase_price[]"]').value) || 0;
        const quantity = parseInt(row.querySelector('input[name="quantity[]"]').value) || 0;
        const rowTotalBeforeVat = price * quantity;
        const rowTotalWithVat = rowTotalBeforeVat * 1.07;

        // อัปเดตช่องราคารวม (ก่อน VAT) ของแถว
        const rowTotalInput = row.querySelector('.row-total');
        if (rowTotalInput) rowTotalInput.value = rowTotalBeforeVat.toFixed(2);

        // อัปเดตช่องราคารวม (รวม VAT) ของแถว
        const rowTotalVatInput = row.querySelector('.row-total-vat');
        if (rowTotalVatInput) rowTotalVatInput.value = rowTotalWithVat.toFixed(2);

        subtotal += rowTotalBeforeVat;
    });

    const vat = subtotal * 0.07;
    const total = subtotal + vat;

    document.getElementById('totalBeforeVat').textContent = subtotal.toFixed(2);
    document.getElementById('vatAmount').textContent = vat.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}

// --- ส่วนที่รันเมื่อหน้าเว็บโหลดเสร็จ ---
$(document).ready(function() {
    document.querySelectorAll('#itemBody tr').forEach(addRowListeners);
    initializeSelect2(document.querySelector('.product-select')); // ทำให้แถวแรกค้นหาได้
    updateTotals(); // คำนวณผลรวมเริ่มต้น
});

</script>
</body>
</html>

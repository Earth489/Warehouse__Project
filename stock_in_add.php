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
          <li class="nav-item"><a class="nav-link" href="categories.php">ประเภทสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="suppliers.php">ซัพพลายเออร์</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">สินค้า</a></li>          
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

    <table class="table table-bordered">
      <thead class="table-dark text-center">
        <tr>
          <th>สินค้า</th>
          <th>ประเภท</th>
          <th>หน่วยที่รับ (หน่วยหลัก)</th>
          <th>ราคาซื้อ (ต่อหน่วยหลัก)</th>
          <th>จำนวน (หน่วยหลัก)</th>
          <th>ราคารวม</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="itemBody">
        <tr>
          <td>
            <select name="product[]" class="form-select" required>
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
<!-- สคริปต์ JavaScript -->
<script>
document.querySelectorAll('select[name="product[]"]').forEach(sel=>{
  sel.addEventListener('change',function(){
    const opt=this.options[this.selectedIndex];
    const tr=this.closest('tr');
    tr.querySelector('.cat').value=opt.dataset.cat||'';
    tr.querySelector('.unit').value=opt.dataset.unit||'';
  });
});

document.getElementById('btnAdd').addEventListener('click',()=>{
  const tb=document.querySelector('#itemBody');
  const row=tb.children[0].cloneNode(true);
  row.querySelectorAll('input').forEach(i=>i.value='');
  row.querySelectorAll('select').forEach(s=>s.selectedIndex=0);
  tb.appendChild(row);
  row.querySelector('select').addEventListener('change',function(){
    const opt=this.options[this.selectedIndex];
    const tr=this.closest('tr');
    tr.querySelector('.cat').value=opt.dataset.cat||'';
    tr.querySelector('.unit').value=opt.dataset.unit||'';
  });
  row.querySelector('.btn-remove').addEventListener('click',()=>row.remove());
  
  // เพิ่ม event listener ให้กับแถวใหม่
  row.querySelectorAll('input[name="purchase_price[]"], input[name="quantity[]"]').forEach(input => {
      input.addEventListener('input', () => updateRowAndTotals(row));
  });

  // เพิ่ม event listener ให้กับแถวใหม่ (เรียก updateTotals แทน)
  row.querySelectorAll('input[name="purchase_price[]"], input[name="quantity[]"]').forEach(input => {
      input.addEventListener('input', () => updateRowAndTotals(row));
  });
    // เรียกใช้งานฟังก์ชัน updateTotals หลังจากเพิ่มแถวใหม่
    updateTotals();
});

document.querySelectorAll('.btn-remove').forEach(b=>b.addEventListener('click',()=>b.closest('tr').remove()));

// ฟังก์ชันอัปเดต ราคารวม ของแต่ละแถว และ ราคารวมทั้งหมด
function updateRowAndTotals(row) {
    calculateTotal(row); // คำนวณราคารวมของแถว
    updateTotals(); // อัปเดตผลรวมทั้งหมด
}



// ฟังก์ชันคำนวณราคารวม
function calculateTotal(row) {
    const price = parseFloat(row.querySelector('input[name="purchase_price[]"]').value) || 0;
    const quantity = parseInt(row.querySelector('input[name="quantity[]"]').value) || 0;
    const total = price * quantity;
    // อัปเดตช่องราคารวมของแถว
    row.querySelector('.row-total').value = total.toFixed(2);
    return total;
}

// ฟังก์ชันอัปเดตผลรวมทั้งหมด
function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#itemBody tr').forEach(row => {
        const price = parseFloat(row.querySelector('input[name="purchase_price[]"]').value) || 0;
        const quantity = parseInt(row.querySelector('input[name="quantity[]"]').value) || 0;
        const rowTotal = price * quantity;
        row.querySelector('.row-total').value = rowTotal.toFixed(2);
        subtotal += rowTotal;
    });

    const vat = subtotal * 0.07;
    const total = subtotal + vat;

    document.getElementById('totalBeforeVat').textContent = subtotal.toFixed(2);
    document.getElementById('vatAmount').textContent = vat.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}

// เพิ่ม event listener สำหรับการเปลี่ยนแปลงใน input
function addRowListeners(row) {
    const inputs = row.querySelectorAll('input[name="purchase_price[]"], input[name="quantity[]"]');
    inputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
}

// เพิ่ม listeners ให้กับแถวที่มีอยู่แล้ว
document.querySelectorAll('#itemBody tr').forEach(addRowListeners);

updateTotals(); // เรียกใช้ฟังก์ชัน updateTotals เพื่อคำนวณผลรวมเริ่มต้น

</script>
</body>
</html>

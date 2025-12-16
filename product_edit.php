<?php
include 'connection.php';
session_start();

// --- 1. ส่วนการตรวจสอบสิทธิ์และดึงข้อมูล (PHP Logic) ---

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];
$msg = "";      // เก็บข้อความแจ้งเตือนผลลัพธ์
$msg_type = ""; // success หรือ danger

// ดึงข้อมูลสินค้าเดิม
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('ไม่พบข้อมูลสินค้า'); window.location='products.php';</script>";
    exit();
}

$product = $result->fetch_assoc();

// ดึงราคาซื้อล่าสุด (ต้นทุนตั้งต้น)
$latest_purchase_price = 0;
$sql_purchase = "SELECT pd.purchase_price 
                 FROM purchase_details pd
                 JOIN purchases p ON pd.purchase_id = p.purchase_id
                 WHERE pd.product_id = ?
                 ORDER BY p.purchase_date DESC, p.purchase_id DESC
                 LIMIT 1";
$stmt_purchase = $conn->prepare($sql_purchase);
$stmt_purchase->bind_param("i", $product_id);
$stmt_purchase->execute();
$result_purchase = $stmt_purchase->get_result();
if ($row_purchase = $result_purchase->fetch_assoc()) {
    $latest_purchase_price = $row_purchase['purchase_price'];
}

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories");

// --- 2. ส่วนการบันทึกข้อมูล (Update Logic) ---
if (isset($_POST['update'])) {
    $name = trim($_POST['product_name']);
    $category_id = (int)$_POST['category_id'];
    $product_unit = trim($_POST['product_unit']); // เปลี่ยนเป็น product_unit
    $price = (float)$_POST['selling_price'];
    $reorder = (int)$_POST['reorder_level'];

    if ($price < $latest_purchase_price && $latest_purchase_price > 0) {
        $msg = "❌ ราคาขายต่ำกว่าทุน (" . number_format($current_cost_per_sub, 2) . " บาท)";
        $msg = "❌ ราคาขายต่ำกว่าทุน (" . number_format($latest_purchase_price, 2) . " บาท)";
        $msg_type = "danger";
    } else {
        // จัดการรูปภาพ
        $image_path = $product['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $target_dir = "uploads/";
                
                // ตรวจสอบและสร้างโฟลเดอร์ (ใช้ permission 0755 ปลอดภัยกว่า 0777)
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                // สร้างชื่อไฟล์สุ่ม
                $new_filename = uniqid("prod_", true) . "." . $ext;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // ลบรูปเก่าทิ้งเพื่อประหยัดพื้นที่
                    if (!empty($product['image_path']) && file_exists($product['image_path'])) {
                        unlink($product['image_path']);
                    }
                    $image_path = $target_file;
                }
            } else {
                $msg = "❌ ไฟล์รูปภาพต้องเป็น JPG, PNG หรือ GIF เท่านั้น";
                $msg_type = "danger";
            }
        }

        // ถ้าไม่มี Error ให้บันทึก
        if (empty($msg) || $msg_type != "danger") {
            $sql_update = "UPDATE products
                           SET product_name=?, category_id=?, product_unit=?,
                               selling_price=?, reorder_level=?, image_path=?
                           WHERE product_id=?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("sisdisi", $name, $category_id, $product_unit, $price, $reorder, $image_path, $product_id);
            
            if($stmt->execute()){
                echo "<script>
                    alert('✅ บันทึกข้อมูลเรียบร้อยแล้ว');
                    window.location.href = 'products.php';
                </script>";
                exit();
            } else {
                $msg = "❌ เกิดข้อผิดพลาด SQL: " . $conn->error;
                $msg_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f4f6f9; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; color: #495057; font-size: 0.95rem; }
        
        /* Image Upload Styling */
        .image-preview-container {
            width: 100%;
            height: 280px;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #fff;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .image-preview-container:hover { border-color: #0d6efd; background-color: #f8f9fa; }
        .image-preview-container img { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        .section-title { font-size: 1.1rem; color: #0d6efd; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #f0f2f5; padding-bottom: 8px; }
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
          <li class="nav-item"><a class="nav-link active" href="products.php">สินค้า</a></li>  
          <li class="nav-item"><a class="nav-link" href="product_split.php">แยกสินค้า</a></li>         
          <li class="nav-item"><a class="nav-link" href="warehouse_page.php">บิลรับสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="warehouse_sale.php">บิลขายสินค้า</a></li>
          <li class="nav-item"><a class="nav-link" href="report.php">รายงาน</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </nav> 
 
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <form method="POST" enctype="multipart/form-data" id="editProductForm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="mb-0 fw-bold text-dark">✏️ แก้ไขข้อมูลสินค้า</h3>
                    </div>
                    <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
                </div>

                <?php if ($msg): ?>
                    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show shadow-sm" role="alert">
                        <?= $msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card p-4">
                    <div class="row">
                        
                        <div class="col-md-4 mb-4 mb-md-0 border-end">
                            <label class="form-label mb-2">รูปภาพสินค้า</label>
                            
                            <div class="image-preview-container bg-light" onclick="document.getElementById('imageInput').click();" style="cursor: pointer;">
                                <?php 
                                    $imgSrc = !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : 'https://via.placeholder.com/250x250?text=No+Image';
                                ?>
                                <img id="imgPreview" src="<?= $imgSrc ?>" alt="Product Image">
                            </div>

                            <input type="file" name="image" id="imageInput" class="form-control d-none" accept="image/*">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="document.getElementById('imageInput').click();">
                                📷 เปลี่ยนรูปภาพ
                            </button>
                            <div class="text-center mt-2">
                                <small class="text-muted">รองรับไฟล์ JPG, PNG (คลิกที่รูปเพื่อเปลี่ยน)</small>
                            </div>
                        </div>

                        <div class="col-md-8 ps-md-4">
                            
                            <h5 class="section-title">ข้อมูลทั่วไป</h5>

                            <div class="mb-3">
                                <label class="form-label text-muted">รหัสสินค้า</label>
                                <input type="text" class="form-control bg-light text-muted" 
                                       value="<?= str_pad($product['product_id'], 5, '0', STR_PAD_LEFT) ?>" 
                                       readonly style="max-width: 150px;">
                            </div>
                            <div class="mb-3">
                                <label for="product_name" class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                <textarea name="product_name" id="product_name" class="form-control" rows="2" required><?= htmlspecialchars($product['product_name']) ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">หมวดหมู่</label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <?php mysqli_data_seek($categories, 0); while($c = $categories->fetch_assoc()): ?>
                                            <option value="<?= $c['category_id'] ?>" <?= ($product['category_id'] == $c['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['category_name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">หน่วยนับสินค้า</label>
                                    <select name="product_unit" id="product_unit" class="form-select" required>
                                        <option value=""> เลือกหน่วยนับ </option>
                                        <?php
                                            $units = ['ชิ้น','อัน','แผ่น','เส้น','ก้อน','ถุง','กระสอบ','กล่อง','ชุด','คู่','กิโลกรัม','ตัน','ลิตร','เมตร','ฟุต','ท่อน','แกลลอน','ม้วน'];
                                            foreach($units as $unit){
                                                $selected = ($product['product_unit'] == $unit) ? 'selected' : '';
                                                echo "<option value=\"$unit\" $selected>$unit</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <h5 class="section-title mt-4">ข้อมูลคลังและราคา</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">จำนวนในสต็อก</label>
                                    <input type="text" class="form-control bg-light" value="<?= number_format($product['stock_quantity'], 2) . ' ' . htmlspecialchars($product['product_unit']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="reorder_level" class="form-label">จุดสั่งซื้อใหม่(จุดเตือนเมื่อของใกล้หมด)</label>
                                    <div class="input-group">
                                        <input type="number" name="reorder_level" id="reorder_level" class="form-control" value="<?= $product['reorder_level'] ?>">
                                        <span class="input-group-text" id="reorder-unit"><?= htmlspecialchars($product['product_unit']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="selling_price" class="form-label">ราคาขาย</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">฿</span>
                                            <input type="number" step="0.01" id="selling_price" name="selling_price" 
                                                   class="form-control fw-bold text-success" 
                                                   value="<?= $product['selling_price'] ?>" required>
                                        </div>
                                    </div>
                                    <?php if ($latest_purchase_price > 0): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">ราคาซื้อล่าสุด (เพื่ออ้างอิง)</label>
                                        <div class="form-control bg-light">฿ <strong class="text-dark ms-1"><?= number_format($latest_purchase_price, 2) ?></strong></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div id="price-alert" class="alert alert-warning mt-2 d-flex align-items-center" role="alert" style="display: none !important;">
                                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                    <div id="price-alert-text">
                                        </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                                <a href="products.php" class="btn btn-light btn-lg px-4 border">ยกเลิก</a>
                                <button type="submit" name="update" id="update-btn" class="btn btn-success btn-lg px-4 shadow-sm">
                                    <span class="me-1">💾</span> บันทึกการแก้ไข
                                </button>
                            </div>

                        </div> 
                    </div> 
                </div> 
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sellingPriceInput = document.getElementById('selling_price');
    const priceAlert = document.getElementById('price-alert');
    const priceAlertText = document.getElementById('price-alert-text');
    const productUnitSelect = document.getElementById('product_unit');
    const reorderUnitSpan = document.getElementById('reorder-unit');
    
    const purchasePrice = <?= $latest_purchase_price ?: 0 ?>;

    // ฟังก์ชันคำนวณและตรวจสอบราคา Real-time
    function validatePrice() {
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;

        // แสดงแจ้งเตือนถ้า ราคาขาย < ต้นทุน
        if (sellingPrice < purchasePrice && purchasePrice > 0) {
            priceAlertText.innerHTML = `<strong>แจ้งเตือน:</strong> ราคาขายต่ำกว่าทุน (${purchasePrice.toFixed(2)} บาท)`;
            priceAlert.style.setProperty('display', 'flex', 'important');
            sellingPriceInput.classList.add('is-invalid');
        } else {
            priceAlert.style.setProperty('display', 'none', 'important');
            sellingPriceInput.classList.remove('is-invalid');
        }
    }

    // ระบบพรีวิวรูปภาพ
    const imageInput = document.getElementById('imageInput');
    const imgPreview = document.getElementById('imgPreview');

    imageInput.onchange = evt => {
        const [file] = imageInput.files;
        if (file) {
            imgPreview.src = URL.createObjectURL(file);
        }
    };

    // เพิ่ม Event Listener ให้ทำงานทันทีเมื่อมีการพิมพ์หรือเปลี่ยนค่า
    sellingPriceInput.addEventListener('input', validatePrice);

    // อัปเดตหน่วยของจุดสั่งซื้อใหม่เมื่อมีการเปลี่ยนหน่วยสินค้า
    productUnitSelect.addEventListener('change', function() {
        reorderUnitSpan.textContent = this.value;
    });

    // ตรวจสอบครั้งแรกตอนโหลดหน้า
    validatePrice();

    // เพิ่มการยืนยันก่อนบันทึก
    const editForm = document.getElementById('editProductForm');
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            if (!confirm('คุณต้องการบันทึกการแก้ไขข้อมูลสินค้านี้ใช่หรือไม่?')) {
                event.preventDefault();
            }
        });
    }
});
</script>

</body>
</html>
<?php
include 'connection.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ถ้ากดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name   = $_POST['product_name'];
    $category_id    = $_POST['category_id'];
    $selling_price  = $_POST['selling_price'];
    $reorder_level  = $_POST['reorder_level'];
    $product_unit = $_POST['base_unit']; // ใช้ base_unit เป็น product_unit

    // อัพโหลดรูป
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image_path = $targetFilePath;
        }
    }

    // บันทึกลง DB ด้วยคอลัมน์ที่ถูกต้อง
    $stmt = $conn->prepare("INSERT INTO products 
        (product_name, category_id, selling_price, reorder_level, image_path, product_unit) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidiss", 
        $product_name, $category_id, $selling_price, $reorder_level, $image_path, $product_unit
    );

    if ($stmt->execute()) {
        header("Location: products.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
  
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้าใหม่</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0 !important;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control, .form-select {
            border-left: none;
            padding: 0.6rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        /* Style เพื่อให้ input มีขอบครบเมื่อไม่มี group-text */
        .form-control-simple {
            border-left: 1px solid #ced4da;
        }
        
        /* Image Preview Box */
        .image-upload-wrap {
            border: 2px dashed #d1d3e2;
            position: relative;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #fff;
            cursor: pointer;
            transition: all 0.3s;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .image-upload-wrap:hover {
            background-color: #f8f9fa;
            border-color: #4e73df;
        }
        .preview-img {
            max-width: 100%;
            max-height: 100%;
            display: none;
            border-radius: 8px;
        }
        .upload-content {
            color: #858796;
        }
        .unit-summary-box {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            color: #0d47a1;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-top: 10px;
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

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form method="post" enctype="multipart/form-data" id="addProductForm">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary"><i class="bi bi-plus-circle-fill"></i> เพิ่มสินค้าใหม่</h4>
                        <a href="products.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label d-block mb-2">รูปภาพสินค้า</label>
                                <div class="image-upload-wrap" onclick="document.getElementById('fileInput').click()">
                                    <div class="upload-content" id="uploadContent">
                                        <i class="bi bi-cloud-arrow-up fs-1"></i>
                                        <p class="mt-2 mb-0">คลิกเพื่ออัปโหลดรูปภาพ</p>
                                    </div>
                                    <img id="imagePreview" class="preview-img" src="#" alt="Preview">
                                </div>
                                <input type="file" name="image" id="fileInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                                <div class="text-center mt-2 text-muted small"> รองรับไฟล์ JPG, PNG</div>
                            </div>

                            <div class="col-md-8">
                                <h5 class="text-secondary mb-3 border-bottom pb-2"><i class="bi bi-info-circle"></i> ข้อมูลทั่วไป</h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text border"><i class="bi bi-tag"></i></span>
                                            <input type="text" name="product_name" class="form-control" placeholder="ระบุชื่อสินค้า..." required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text border"><i class="bi bi-folder"></i></span>
                                            <select name="category_id" class="form-select" required>
                                                <option value=""> เลือกหมวดหมู่ </option>
                                                <?php
                                                $cat = $conn->query("SELECT category_id, category_name FROM categories");
                                                while ($row = $cat->fetch_assoc()) {
                                                    echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="text-secondary mt-4 mb-3 border-bottom pb-2"><i class="bi bi-boxes"></i> หน่วยนับและราคา</h5>

                                <div class="row g-3">
                                    
                                <!-- หน่วยหลัก -->
                                <div class="col-md-6">
                                    <label class="form-label">หน่วยนับ<span class="text-danger">*</span></label>
                                    <select class="form-select form-control-simple rounded" 
                                            id="base_unit" name="base_unit" required onchange="updateSummary()">
                                        <option value=""> เลือกหน่วยนับ </option>
                                        <option>ชิ้น</option>
                                        <option>อัน</option>
                                        <option>แผ่น</option>
                                        <option>เส้น</option>
                                        <option>ก้อน</option>
                                        <option>ถุง</option>
                                        <option>ถุงใหญ่</option>
                                        <option>กระสอบ</option>
                                        <option>กล่อง</option>
                                        <option>ชุด</option>
                                        <option>คู่</option>
                                        <option>กิโลกรัม</option>
                                        <option>ตัน</option>
                                        <option>ลิตร</option>
                                        <option>เมตร</option>
                                        <option>ฟุต</option>
                                        <option>ท่อน</option>
                                        <option>แกลลอน</option>
                                        <option>ม้วน</option>
                                    </select>
                                </div>

                                    <div class="col-12">
                                        <div class="unit-summary-box" id="unitSummary">
                                            <i class="bi bi-lightbulb-fill"></i> กรุณากรอกหน่วยนับและอัตราส่วนเพื่อดูสรุป
                                        </div>
                                    </div>

                                    <div class="col-md-6 mt-3">
                                        <label class="form-label">ราคาขาย <span class="text-danger">*</span><span class="text-muted small">(ต่อหน่วย)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text border">฿</span>
                                            <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mt-3">
                                        <label class="form-label">จุดสั่งซื้อใหม่ <span class="text-danger">*</span><span class="text-muted small">(จำนวนขั้นต่ำ)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text border"><i class="bi bi-bell"></i></span>
                                            <input type="number" name="reorder_level" class="form-control" placeholder="จำนวนขั้นต่ำ" required>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- end col-md-8 -->
                        </div> <!-- end row -->
                    </div> <!-- end card-body -->
                    <div class="card-footer bg-white p-3 text-end">
                        <a href="products.php" class="btn btn-secondary btn-lg px-4">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
                                    <i class="bi bi-save"></i> บันทึกสินค้า
                                </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ฟังก์ชันแสดงตัวอย่างรูปภาพ
    function previewImage(input) {
        var preview = document.getElementById('imagePreview');
        var content = document.getElementById('uploadContent');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                content.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
            content.style.display = 'block';
        }
    }

    // ฟังก์ชันสรุปหน่วยนับอัตโนมัติ
    function updateSummary() {
        const baseUnit = document.getElementById('base_unit').value;
        const summaryBox = document.getElementById('unitSummary');

        if (baseUnit) {
             summaryBox.innerHTML = `<i class="bi bi-check-circle-fill"></i> หน่วยของสินค้าที่จะบันทึกคือ: <strong>${baseUnit}</strong>`;
        } else {
            summaryBox.innerHTML = `<i class="bi bi-lightbulb-fill"></i> กรุณาเลือกหน่วยหลักเพื่อดูสรุป`;
        }
    }

    // เพิ่มการยืนยันก่อนบันทึก
    document.getElementById('addProductForm').addEventListener('submit', function(event) {
        // แสดงกล่องข้อความยืนยัน
        var confirmation = confirm('คุณต้องการบันทึกข้อมูลสินค้านี้ใช่หรือไม่?');
        
        // ถ้าผู้ใช้ไม่กดยืนยัน (Cancel) ให้ยกเลิกการส่งฟอร์ม
        if (!confirmation) {
            event.preventDefault();
        }
    });
</script>

</body>
</html>
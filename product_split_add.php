<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$products = $conn->query("SELECT product_id, product_name, stock_quantity, product_unit FROM products ORDER BY product_name ASC");
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");

$msg = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $parent_product_id = (int)$_POST['parent_product_id'];
    $parent_qty = (float)$_POST['parent_qty'];
    $split_date = $_POST['split_date'];
    $new_product_type = $_POST['new_product_type'];

    $conn->begin_transaction();
    try {
        $new_product_id = null;
        $new_qty = 0;

        if ($new_product_type === 'existing') {
            $new_product_id = (int)$_POST['existing_new_product_id'];
            $new_qty = (float)$_POST['existing_new_qty'];
            if ($parent_product_id === $new_product_id) {
                throw new Exception("สินค้าต้นทางและสินค้าใหม่ต้องไม่เป็นสินค้าเดียวกัน");
            }
        } elseif ($new_product_type === 'new') {
            $new_qty = (float)$_POST['new_product_qty'];
            $new_product_name = $_POST['new_product_name'];
            $new_product_unit = $_POST['new_product_unit'];
            $new_product_category_id = (int)$_POST['new_product_category_id'];
            $new_product_price = (float)$_POST['new_product_price'];
            $new_reorder_point = (int)$_POST['new_reorder_point'];

            // 3. สร้างสินค้าใหม่
            $create_stmt = $conn->prepare("INSERT INTO products (product_name, stock_quantity, product_unit, category_id, selling_price, reorder_level) VALUES (?, ?, ?, ?, ?, ?)");
            $create_stmt->bind_param("sdsidi", $new_product_name, $new_qty, $new_product_unit, $new_product_category_id, $new_product_price, $new_reorder_point);
            $create_stmt->execute();
            $new_product_id = $conn->insert_id; // ดึง ID ของสินค้าที่สร้างใหม่
        } else {
            throw new Exception("รูปแบบของสินค้าใหม่ไม่ถูกต้อง");
        }

        // 1. ตรวจสอบและล็อคสต็อกสินค้าต้นทาง
        $check_stmt = $conn->prepare("SELECT stock_quantity, product_name FROM products WHERE product_id = ? FOR UPDATE");
        $check_stmt->bind_param("i", $parent_product_id);
        $check_stmt->execute();
        $parent_product = $check_stmt->get_result()->fetch_assoc();

        if (!$parent_product || $parent_product['stock_quantity'] < $parent_qty) {
            throw new Exception("สินค้า '{$parent_product['product_name']}' มีในคลังไม่เพียงพอ (มี {$parent_product['stock_quantity']} แต่ต้องการใช้ {$parent_qty})");
        }

        // 2. ลดสต็อกสินค้าต้นทาง
        $update_parent_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
        $update_parent_stmt->bind_param("di", $parent_qty, $parent_product_id);
        $update_parent_stmt->execute();

        // 3. เพิ่มสต็อกสินค้าใหม่ (ถ้าเป็น existing product)
        if ($new_product_type === 'existing') {
            $update_new_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
            $update_new_stmt->bind_param("di", $new_qty, $new_product_id);
            $update_new_stmt->execute();
        }

        // 4. บันทึกประวัติการแยก (ใช้ new_product_id ที่ได้จากทั้ง 2 กรณี)
        $log_stmt = $conn->prepare("INSERT INTO product_split (parent_product_id, parent_qty, new_product_id, new_qty, split_date, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $log_stmt->bind_param("ididsi", $parent_product_id, $parent_qty, $new_product_id, $new_qty, $split_date, $user_id);
        $log_stmt->execute();

        $conn->commit();
        echo "<script>alert('บันทึกการแยกสินค้าเรียบร้อย'); window.location='product_split.php';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $msg_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรายการแยกสินค้า</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #495057;
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
        <h3 class="mb-0 fw-bold text-dark"><i class="bi bi-distribute-vertical"></i> เพิ่มรายการแยกสินค้า</h3>
        <a href="product_split.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="splitForm">
        <div class="row g-4">
            <!-- Source Product -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="section-title"><i class="bi bi-box-arrow-up"></i> สินค้าต้นทาง (ที่จะนำไปแยก)</h5>
                        <div class="mb-3">
                            <label class="form-label">เลือกสินค้า</label>
                            <select name="parent_product_id" id="parent_product_select" class="form-select product-select" required>
                                <option value=""> เลือกสินค้า </option>
                                <?php mysqli_data_seek($products, 0); ?>
                                <?php while($p = $products->fetch_assoc()): ?>
                                    <option value="<?= $p['product_id'] ?>" data-stock="<?= $p['stock_quantity'] ?>" data-unit="<?= $p['product_unit'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div id="parent_stock_info" class="form-text text-primary fw-bold mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">จำนวนที่ใช้</label>
                            <input type="number" step="0.01" name="parent_qty" class="form-control" required min="0.01">
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Product -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="section-title"><i class="bi bi-box-arrow-down"></i> สินค้าใหม่ (ที่ได้จากการแยก)</h5>
                        <div class="d-grid gap-2 d-sm-flex mb-3">
                            <input type="radio" class="btn-check" name="new_product_type" id="type_existing" value="existing" autocomplete="off" checked>
                            <label class="btn btn-outline-primary w-100" for="type_existing"><i class="bi bi-list-check"></i> เลือกสินค้าที่มีอยู่</label>

                            <input type="radio" class="btn-check" name="new_product_type" id="type_new" value="new" autocomplete="off">
                            <label class="btn btn-outline-success w-100" for="type_new"><i class="bi bi-plus-circle"></i> เพิ่มเป็นสินค้าใหม่</label>
                        </div>

                        <!-- Form for existing product -->
                        <div id="existing_product_fields">
                            <div class="mb-3">
                                <label class="form-label">เลือกสินค้า</label>
                                <select name="existing_new_product_id" class="form-select product-select" required>
                                    <option value=""> เลือกสินค้า </option>
                                    <?php mysqli_data_seek($products, 0); ?>
                                    <?php while($p = $products->fetch_assoc()): ?>
                                        <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">จำนวนที่ได้</label>
                                <input type="number" step="0.01" name="existing_new_qty" class="form-control" required min="0.01">
                            </div>
                        </div>

                        <!-- Form for new product -->
                        <div id="new_product_fields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">ชื่อสินค้าใหม่</label>
                                <input type="text" name="new_product_name" class="form-control" placeholder="ระบุชื่อสินค้า..." required disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่</label>
                                <select name="new_product_category_id" class="form-select" required disabled>
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    <?php while($c = $categories->fetch_assoc()): ?>
                                        <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ราคาขาย (ต่อหน่วย)</label>
                                    <input type="number" step="0.01" name="new_product_price" class="form-control" placeholder="0.00" required disabled min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">จุดสั่งซื้อใหม่</label>
                                    <input type="number" name="new_reorder_point" class="form-control" placeholder="จำนวนขั้นต่ำ" required disabled min="0">
                                </div>
                            </div>
                             <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">หน่วยนับ</label>
                                    <input type="text" name="new_product_unit" class="form-control" placeholder="เช่น ชิ้น, กก." required disabled>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label">จำนวนที่ได้</label>
                                <input type="number" step="0.01" name="new_product_qty" class="form-control" required min="0.01" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">วันที่แยก</label>
                        <input type="date" name="split_date" class="form-control" value="" required>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <a href="product_split.php" class="btn btn-secondary px-4">ยกเลิก</a>
                        <button type="submit" class="btn btn-success px-5 ms-2"><i class="bi bi-check-circle"></i> บันทึกการแยกสินค้า</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.product-select').select2({
            theme: 'bootstrap-5'
        });

        $('#parent_product_select').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var stock = selectedOption.data('stock');
            var unit = selectedOption.data('unit');
            var stockInfo = $('#parent_stock_info');

            if (stock !== undefined) {
                stockInfo.html('<i class="bi bi-box-seam"></i> คงเหลือ: ' + stock + ' ' + unit);
            } else {
                stockInfo.text('');
            }
        });

        // Toggle new product form
        $('input[name="new_product_type"]').on('change', function() {
            if (this.value === 'new') {
                $('#existing_product_fields').hide();
                $('#existing_product_fields').find('select, input').prop('disabled', true);
                
                $('#new_product_fields').show();
                $('#new_product_fields').find('input, select').prop('disabled', false);

            } else { // existing
                $('#new_product_fields').hide();
                $('#new_product_fields').find('input, select').prop('disabled', true);

                $('#existing_product_fields').show();
                $('#existing_product_fields').find('select, input').prop('disabled', false);
            }
            // Trigger change on select2 to update placeholder
            $('.product-select').trigger('change.select2');
        });


        $('#splitForm').on('submit', function(e) {
            if (!confirm('คุณต้องการบันทึกการแยกสินค้านี้ใช่หรือไม่? การดำเนินการนี้จะอัปเดตสต็อกสินค้าทันที')) {
                e.preventDefault();
            }
        });
    });
</script>

</body>
</html>
<?php
include 'connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {

    $user_id = $_SESSION['user_id'];
    $sale_date = $_POST['sale_date'];
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $sale_units = $_POST['sale_unit']; // ✅ รับค่า 'หน่วยที่ขาย' ที่ส่งมาจากฟอร์ม


    $conn->begin_transaction();

    try {
        // คำนวณยอดรวมทั้งหมด
        $total_amount = 0;
        // ✅ เตรียมคำสั่งให้ดึงข้อมูลหน่วยมาด้วย
        $price_stmt = $conn->prepare("SELECT selling_price, base_unit, unit_conversion_rate FROM products WHERE product_id = ?");

        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            $unit = $sale_units[$i]; 

            if ($pid > 0 && $qty > 0) {
                $price_stmt->bind_param("i", $pid);
                $price_stmt->execute();
                $prod_data = $price_stmt->get_result()->fetch_assoc();
                $price = $prod_data['selling_price'];
                
                // ✅ คำนวณราคารวมโดยพิจารณาจากหน่วยที่ขาย
                $multiplier = ($unit == $prod_data['base_unit'] && $prod_data['unit_conversion_rate'] > 1) ? $prod_data['unit_conversion_rate'] : 1;
                $total_amount += ($qty * $price * $multiplier);
            }
        }
// แก้ไขคำสั่ง INSERT
$stmt = $conn->prepare("INSERT INTO sales (user_id, sale_date, total_amount) VALUES (?, ?, ?)");
$stmt->bind_param("isd", $user_id, $sale_date, $total_amount);
$stmt->execute();
$sale_id = $stmt->insert_id;

        // บันทึกสินค้าใน sale_details และอัปเดตสต็อก
        // ✅ เตรียมคำสั่งให้รองรับการบันทึก sale_unit
        $stmt_detail = $conn->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, sale_price, sale_unit) VALUES (?, ?, ?, ?, ?)");
        $update_stmt = $conn->prepare("UPDATE products SET stock_in_sub_unit = stock_in_sub_unit - ? WHERE product_id = ?");
        $check_stmt = $conn->prepare("SELECT stock_in_sub_unit, selling_price, product_name, base_unit, unit_conversion_rate FROM products WHERE product_id = ?");

        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            $unit = $sale_units[$i];

            // 🔍 ตรวจสอบจำนวนในคลังก่อนขาย
            $check_stmt->bind_param("i", $pid);
            $check_stmt->execute();
            $data = $check_stmt->get_result()->fetch_assoc();
            $current_stock_in_sub_unit = (float)$data['stock_in_sub_unit'];
            $price = $data['selling_price'];
            $product_name = $data['product_name'];
            
            // ✅ คำนวณจำนวนที่จะตัดสต็อก (แปลงเป็นหน่วยย่อยเสมอ)
            $qty_to_deduct = $qty;
            if ($unit == $data['base_unit'] && $data['unit_conversion_rate'] > 1) {
                $qty_to_deduct = $qty * $data['unit_conversion_rate'];
            }

            if ($qty_to_deduct > $current_stock_in_sub_unit) {
                // ❌ ถ้าขายเกินสต็อก ให้ยกเลิกธุรกรรมและแจ้งเตือน
                $conn->rollback();
                echo "<script>
                    alert('❌ สินค้า \"$product_name\" มีในคลังไม่เพียงพอ!');
                    window.history.back();
                </script>";
                exit();
            }

            // ✅ ถ้าไม่เกิน ให้บันทึกรายละเอียดการขาย
            $stmt_detail->bind_param("iiids", $sale_id, $pid, $qty, $price, $unit);
            $stmt_detail->execute();

            // และอัปเดตจำนวนคงเหลือ
            $update_stmt->bind_param("di", $qty_to_deduct, $pid);
            $update_stmt->execute();
        }

        $conn->commit();

        echo "<script>alert('✅ บันทึกการขายสินค้าเรียบร้อย'); window.location='warehouse_sale.php';</script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "'); history.back();</script>";
    }
  
} else {
    echo "<script>alert('กรุณาเลือกสินค้าก่อนทำการบันทึก'); history.back();</script>";
}
?>
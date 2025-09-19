<?php
include 'condb.php';

$response = ["success" => false, "message" => ""];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $product_name = $_POST["product_name"];
        $description  = $_POST["description"];
        $price        = $_POST["price"];
        $stock        = $_POST["stock"];

        // ตรวจสอบว่ามีไฟล์ส่งมาหรือไม่
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $upload_dir = "./uploads/"; // โฟลเดอร์เก็บรูป
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // บันทึกข่้อมูลลงฐานข้อมูล
                $stmt = $conn->prepare("INSERT INTO Products (product_name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$product_name, $description, $price, $stock, $filename]);

                $response["success"] = true;
                $response["message"] = "บันทึกสินค้าเรียบร้อยแล้ว";
            } else {
                $response["message"] = "อัปโหลดไฟล์ไม่สำเร็จ";
            }
        } else {
            $response["message"] = "กรุณาเลือกรูปภาพ";
        }
    }
} catch (Exception $e) {
    $response["message"] = "Error: " . $e->getMessage();
}

echo json_encode($response);


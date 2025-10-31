<?php
// ส่งออกข้อมูลเป็น JSON
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

// เชื่อมต่อฐานข้อมูล
include 'condb.php';

// รับ action จาก POST
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    switch ($action) {

        /* ==============================
           🟩 เพิ่มพนักงานใหม่
        ============================== */
        case 'add':
            $firstname = $_POST['firstname'] ?? '';
            $lastname  = $_POST['lastname'] ?? '';
            $username  = $_POST['username'] ?? '';
            $password  = $_POST['password'] ?? '';

            if (empty($firstname) || empty($lastname) || empty($username) || empty($password)) {
                echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน"]);
                exit;
            }

            // อัพโหลดรูป
            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            }

            $sql = "INSERT INTO employee (firstname, lastname, username, password, image)
                    VALUES (:firstname, :lastname, :username, :password, :image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':image', $filename);

            if ($stmt->execute()) {
                echo json_encode(["message" => "เพิ่มพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "เพิ่มพนักงานล้มเหลว"]);
            }
            break;


        /* ==============================
           🟨 แก้ไขข้อมูลพนักงาน
        ============================== */
        case 'update':
            $employee_id = $_POST['employee_id'] ?? null;
            $firstname   = $_POST['firstname'] ?? '';
            $lastname    = $_POST['lastname'] ?? '';
            $username    = $_POST['username'] ?? '';
            $password    = $_POST['password'] ?? '';

            if (empty($employee_id) || empty($firstname) || empty($lastname) || empty($username)) {
                echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน"]);
                exit;
            }

            // ตรวจสอบรูปภาพใหม่
            $imageSQL = "";
            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
                $imageSQL = ", image = :image";
            }

            // ✅ แก้ไข SQL ให้ถูกต้อง (มี , ระหว่าง username และ password)
            $sql = "UPDATE employee SET 
                        firstname = :firstname,
                        lastname = :lastname,
                        username = :username,
                        password = :password
                        $imageSQL
                    WHERE employee_id = :employee_id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':employee_id', $employee_id);

            if ($filename) {
                $stmt->bindParam(':image', $filename);
            }

            if ($stmt->execute()) {
                echo json_encode(["message" => "แก้ไขพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "แก้ไขพนักงานล้มเหลว"]);
            }
            break;


        /* ==============================
           🟥 ลบพนักงาน
        ============================== */
        case 'delete':
            $employee_id = $_POST['employee_id'] ?? null;
            if (empty($employee_id)) {
                echo json_encode(["error" => "รหัสพนักงานไม่ถูกต้อง"]);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM employee WHERE employee_id = :employee_id");
            $stmt->bindParam(':employee_id', $employee_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "ลบพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "ลบพนักงานล้มเหลว"]);
            }
            break;

        default:
            echo json_encode(["error" => "Action ไม่ถูกต้อง"]);
            break;
    }

} else {
    /* ==============================
       🟦 ดึงข้อมูลพนักงานทั้งหมด (GET)
    ============================== */
    $stmt = $conn->prepare("SELECT * FROM employee ORDER BY employee_id ASC");
    if ($stmt->execute()) {
        $employee = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $employee]);
    } else {
        echo json_encode(["success" => false, "data" => []]);
    }
}
?>

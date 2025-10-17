<?php
// กำหนดให้ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
include 'condb.php';

// ดักจับ action จาก POST
$action = $_POST['action'] ?? null;

// ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST และมีการระบุ action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    switch($action) {

        // กรณีเพิ่มพนักงาน
        case 'add':
            // รับค่าจากฟอร์ม
            $firstname = $_POST['firstname'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $username = $_POST['username'] ?? '';

            // ตรวจสอบว่าข้อมูลที่จำเป็นครบถ้วน
            if (empty($firstname) || empty($lastname) || empty($username)) {
                echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน"]);
                exit;
            }

            // อัพโหลดไฟล์รูปภาพ
            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);  // สร้างโฟลเดอร์หากไม่มี
                }
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $filename;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    echo json_encode(["error" => "การอัพโหลดไฟล์ล้มเหลว"]);
                    exit;
                }
            }

            // สร้างคำสั่ง SQL เพื่อเพิ่มข้อมูล
            $sql = "INSERT INTO employee (firstname, lastname, username, image) 
                    VALUES (:firstname, :lastname, :username, :image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':image', $filename);

            // ตรวจสอบการทำงานของ SQL
            if ($stmt->execute()) {
                echo json_encode(["message" => "เพิ่มพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "เพิ่มพนักงานล้มเหลว"]);
            }
            break;

        // กรณีแก้ไขพนักงาน
        case 'update':
            $employee_id = $_POST['employee_id'] ?? null;
            $firstname = $_POST['firstname'] ?? '';
            $lastname = $_POST['lastname'] ?? '';
            $username = $_POST['username'] ?? '';

            // ตรวจสอบว่าข้อมูลที่จำเป็นครบถ้วน
            if (empty($employee_id) || empty($firstname) || empty($lastname) || empty($username)) {
                echo json_encode(["error" => "ข้อมูลไม่ครบถ้วน"]);
                exit;
            }

            // อัพโหลดไฟล์รูปภาพใหม่
            $imageSQL = "";
            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $filename;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    echo json_encode(["error" => "การอัพโหลดไฟล์ล้มเหลว"]);
                    exit;
                }
                $imageSQL = ", image = :image";  // ถ้ามีการอัพโหลดรูปใหม่ จะมีการเพิ่มคอลัมน์ image
            }

            // สร้างคำสั่ง SQL เพื่ออัพเดทข้อมูล
            $sql = "UPDATE employee SET 
                        firstname = :firstname,
                        lastname = :lastname,
                        username = :username
                        $imageSQL
                    WHERE employee_id = :employee_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':employee_id', $employee_id);

            // ถ้ามีการอัพโหลดรูปใหม่ ให้ bind ค่า image ด้วย
            if ($filename) {
                $stmt->bindParam(':image', $filename);
            }

            // ตรวจสอบการทำงานของ SQL
            if ($stmt->execute()) {
                echo json_encode(["message" => "แก้ไขพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "แก้ไขพนักงานล้มเหลว"]);
            }
            break;

        // กรณีลบพนักงาน
        case 'delete':
            $employee_id = $_POST['employee_id'] ?? null;

            // ตรวจสอบว่า employee_id ถูกต้องหรือไม่
            if (empty($employee_id)) {
                echo json_encode(["error" => "รหัสพนักงานไม่ถูกต้อง"]);
                exit;
            }

            // สร้างคำสั่ง SQL เพื่อทำการลบข้อมูล
            $stmt = $conn->prepare("DELETE FROM employee WHERE employee_id = :employee_id");
            $stmt->bindParam(':employee_id', $employee_id);

            // ตรวจสอบการทำงานของ SQL
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
    // ถ้าเป็นการ GET, ดึงข้อมูลพนักงานทั้งหมด
    $stmt = $conn->prepare("SELECT * FROM employee ORDER BY employee_id ASC");
    if ($stmt->execute()) {
        $employee = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $employee]);
    } else {
        echo json_encode(["success" => false, "data" => []]);
    }
}
?>

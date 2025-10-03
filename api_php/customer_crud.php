<?php
// เชื่อมต่อฐานข้อมูล
include 'condb.php';

header("Content-Type: application/json; charset=UTF-8");

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "GET") {
        // ✅ ดึงข้อมูลลูกค้าทั้งหมด
        $stmt = $conn->prepare("SELECT customer_id, firstName, lastName,phone,username FROM customers ORDER BY customer_id ASC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $result]);
    }

    // ✅ เพิ่มข้อมูล
    elseif ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        $password_01  = password_hash($data["password"], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO customers (firstName, lastName, phone, username, password) 
                                VALUES (:firstName, :lastName, :phone, :username, :password)");

        $stmt->bindParam(":firstName", $data["firstName"]);
        $stmt->bindParam(":lastName", $data["lastName"]);
        $stmt->bindParam(":phone", $data["phone"]);
        $stmt->bindParam(":username", $data["username"]);
        $stmt->bindParam(":password", $password_01);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถเพิ่มข้อมูลได้"]);
        }
    }

   
// ✅ แก้ไขข้อมูล
elseif ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["customer_id"])) {
        echo json_encode(["success" => false, "message" => "ไม่พบค่า customer_id"]);
        exit;
    }

    $customer_id = intval($data["customer_id"]);

    // ตรวจสอบว่ามีการส่ง password มาไหม
    if (!empty($data["password"])) {
        // เข้ารหัสรหัสผ่านใหม่
        $password_01 = password_hash($data["password"], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("UPDATE customers 
                                SET firstName = :firstName, 
                                    lastName = :lastName, 
                                    phone = :phone, 
                                    username = :username,
                                    password = :password
                                WHERE customer_id = :id");

        $stmt->bindParam(":firstName", $data["firstName"]);
        $stmt->bindParam(":lastName", $data["lastName"]);
        $stmt->bindParam(":phone", $data["phone"]);
        $stmt->bindParam(":username", $data["username"]);
        $stmt->bindParam(":password", $password_01);
        $stmt->bindParam(":id", $customer_id, PDO::PARAM_INT);

    } else {
        // กรณีไม่ได้แก้ไข password
        $stmt = $conn->prepare("UPDATE customers 
                                SET firstName = :firstName, 
                                    lastName = :lastName, 
                                    phone = :phone, 
                                    username = :username
                                WHERE customer_id = :id");

        $stmt->bindParam(":firstName", $data["firstName"]);
        $stmt->bindParam(":lastName", $data["lastName"]);
        $stmt->bindParam(":phone", $data["phone"]);
        $stmt->bindParam(":username", $data["username"]);
        $stmt->bindParam(":id", $customer_id, PDO::PARAM_INT);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
    } else {
        echo json_encode(["success" => false, "message" => "ไม่สามารถแก้ไขข้อมูลได้"]);
    }
}

    // ✅ ลบข้อมูล
    elseif ($method === "DELETE") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["customer_id"])) {
            echo json_encode(["success" => false, "message" => "ไม่พบค่า customer_id"]);
            exit;
        }

        $customer_id = intval($data["customer_id"]);

        $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = :id");
        $stmt->bindParam(":id", $customer_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "ลบข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถลบข้อมูลได้"]);
        }
    }

    else {
        echo json_encode(["success" => false, "message" => "Method ไม่ถูกต้อง"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>

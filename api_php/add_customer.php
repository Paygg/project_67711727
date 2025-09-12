<?php

  // เชื่อมต่อฐานข้อมูล
include 'condb.php';

try {
 //ตรวจสอบคำขอที่ได้รับจาก Client  ตามประเภทของคำ ว่าเป็น GET หรือ POST
    $method = $_SERVER['REQUEST_METHOD'];

    
        //เพิ่มข้อมูล
        if ($method === "POST") {
        $firstName = $_POST["firstName"];
        $lastName  = $_POST["lastName"];
        $username  = $_POST["username"];
        $password  = $_POST["password"];


        $stmt = $conn->prepare("INSERT INTO customers (firstName, lastName, phone, username, password) VALUES (?,?,?,?,?)");

        //----------------แปลง password---------------
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt->execute([$firstName, $lastName, $phone, $username, $password]);


            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Customer added successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error adding customer"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Missing required fields"]);
        }
    



} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

?>
<?php
include 'condb.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['username']) || !isset($data['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid input data."
    ]);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password']); // รหัสผ่านที่ผู้ใช้กรอกเข้ามา

try {
    // ✅ ดึงข้อมูลผู้ใช้จากฐานข้อมูล (ไม่ต้องกรองรหัสผ่านใน SQL)
    $stmt = $conn->prepare("SELECT * FROM customers WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ✅ ตรวจสอบรหัสผ่านที่เข้ารหัสไว้ในฐานข้อมูล
        if (password_verify($password, $user['password'])) {
            echo json_encode([
                "success" => true,
                "message" => "Login successful.",
                "user" => [
                    "id" => $user['customer_id'],
                    "username" => $user['username']
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "รหัสผ่านไม่ถูกต้อง"
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบชื่อผู้ใช้นี้ในระบบ"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>

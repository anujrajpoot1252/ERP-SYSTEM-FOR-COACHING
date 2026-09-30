<?php
header("Content-Type: application/json");
include("../../config/db.php");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email == '' || $password == '') {
    echo json_encode([
        "status" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, email, password, role, institute_id, status
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 1) {

    $user = $result->fetch_assoc();

    if ($user['status'] != 'active') {
        echo json_encode([
            "status" => false,
            "message" => "Account is inactive"
        ]);
        exit;
    }

    if (password_verify($password, $user['password'])) {

        unset($user['password']);

        echo json_encode([
            "status" => true,
            "message" => "Login Success",
            "user" => $user
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid Email or Password"
        ]);
    }

} else {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Email or Password"
    ]);
}
?>
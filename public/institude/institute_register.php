<?php
include "config/db.php";

$institute_name = $_POST['institute_name'];
$subdomain      = $_POST['subdomain'];
$owner_name     = $_POST['owner_name'];
$mobile         = $_POST['mobile'];
$email          = $_POST['email'];
$subscription   = $_POST['subscription'];
$address        = $_POST['address'];
$password       = $_POST['password'];
$confirm        = $_POST['confirm_password'];

if ($password != $confirm) {
    die("Passwords do not match");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO institutes
(institute_name, subdomain, owner_name, mobile, email, subscription, address, password)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
echo $name;   // test
$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssss",
    $institute_name,
    $subdomain,
    $owner_name,
    $mobile,
    $email,
    $subscription,
    $address,
    $hash
);

if ($stmt->execute()) {
    echo "Institute Registered Successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
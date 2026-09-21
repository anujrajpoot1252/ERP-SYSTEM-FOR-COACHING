<?php 
 $conn = mysqli_connect("localhost", "root", "", "erp_system");
 if (!$conn) {
     die("Connection failed: " . mysqli_connect_error());
 }

    $phone_number = $_POST['phone'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM student WHERE Phone_number='$phone_number'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['Password'])) {
            echo "Login successful";
        } else {
            echo "Invalid password";
        }
    } else {
        echo "No user found with that phone number";
    }
mysqli_close($conn);
?>

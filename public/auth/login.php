<?php 
require "../db.php";

    $phone_number = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM student WHERE Phone_number='$email'";

    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['Password'])) {
            echo "Login successful";
        } else {
            echo "Invalid password";
        }
    } else {
        echo "No user found";
    }
mysqli_close($conn);
?>

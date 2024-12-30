<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE UserName='$username' AND Password='$password' AND Role='hod'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'hod';
        $_SESSION['department_id'] = $row['DepartmentID']; 
        header("Location: hod_dashboard.php");
    } else {
        echo "Invalid credentials";
    }
    $conn->close();
}
?>

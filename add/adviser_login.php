<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE UserName='$username' AND Password='$password' AND Role='adviser'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $row['UserName'];
        $_SESSION['role'] = 'adviser';
        $_SESSION['department_id'] = $row['DepartmentID']; 
        
        
        if ($row['DepartmentID'] == 1) {
            header("Location: adviser_dashboard_it.php");
        } else if ($row['DepartmentID'] == 2) {
            header("Location: adviser_dashboard_cse.php");
        }
    } else {
        echo "Invalid credentials";
    }
    $conn->close();
}
?>

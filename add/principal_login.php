<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $stmt = $conn->prepare("SELECT * FROM Users WHERE UserName = ? AND Password = ? AND Role = 'principal'");
    $stmt->bind_param("ss", $username, $password);

    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'principal';
        header("Location: principal_dashboard.php"); // Redirect to principal dashboard
        exit();
    } else {
        echo "Invalid credentials";
    }

    
    $stmt->close();
    $conn->close();
}
?>

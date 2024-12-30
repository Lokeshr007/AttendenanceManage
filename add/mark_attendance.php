<?php
session_start();
if ($_SESSION['role'] != 'adviser') {
    header("Location: adviser_login.html");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $attendance = $_POST['attendance'];
    $class_id = $_POST['class_id'];
    $date = date('Y-m-d');

    foreach ($attendance as $student_id => $status) {
        
        $sql_department = "SELECT DepartmentID FROM Students WHERE StudentID = $student_id";
        $result_department = $conn->query($sql_department);
        if (!$result_department) {
            die("Error fetching DepartmentID: " . $conn->error);
        }

        $department_row = $result_department->fetch_assoc();
        if (!$department_row) {
            die("DepartmentID not found for StudentID: $student_id");
        }

        $department_id = $department_row['DepartmentID'];
        
        
        if (empty($department_id)) {
            die("Invalid DepartmentID for StudentID: $student_id");
        }

        
        $sql = "INSERT INTO Attendance (StudentID, DepartmentID, Date, Status) 
                VALUES ($student_id, $department_id, '$date', '$status') 
                ON DUPLICATE KEY UPDATE Status='$status', DepartmentID=$department_id";

        if ($conn->query($sql) !== TRUE) {
            die("Error inserting/updating record: " . $conn->error);
        }
    }

    
    header("Location: absent_students.php?class_id=$class_id&date=$date");
    exit();
}

$conn->close();
?>

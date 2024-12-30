<?php
session_start();
if ($_SESSION['role'] != 'adviser' || $_SESSION['department_id'] != 1) {
    header("Location: adviser_login.html");
    exit();
}

include 'db.php';

$class_id = $_GET['class_id'];
$month = $_GET['month'];
$year = $_GET['year'];

// Query to fetch attendance data for the specified month
$sql_attendance = "
SELECT Students.StudentName, Attendance.Date, Attendance.Status 
FROM Students 
JOIN Attendance ON Students.StudentID = Attendance.StudentID 
WHERE Students.ClassID = $class_id 
AND MONTH(Attendance.Date) = $month 
AND YEAR(Attendance.Date) = $year
ORDER BY Attendance.Date, Students.StudentName";
$result_attendance = $conn->query($sql_attendance);

if (!$result_attendance) {
    die("Query failed: " . $conn->error);
}


$filename = "attendance_report_{$class_id}_{$month}_{$year}.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, array('Student Name', 'Date', 'Status'));

while ($row = $result_attendance->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$conn->close();
exit();
?>

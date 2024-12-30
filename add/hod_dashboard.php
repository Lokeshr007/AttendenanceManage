<?php
session_start();
if ($_SESSION['role'] != 'hod') {
    header("Location: hod_login.html");
    exit();
}

include 'db.php';

// hod dpt
$department_id = $_SESSION['department_id'];


$sql_classes_absent_count = "
SELECT c.ClassName, c.ClassID, 
       COUNT(CASE WHEN a.Status = 'Absent' THEN 1 ELSE NULL END) AS AbsentCount,
       SUM(CASE WHEN s.Type = 'DS' AND a.Status = 'Absent' THEN 1 ELSE 0 END) AS ds_absent,
       SUM(CASE WHEN s.Type = 'HS' AND a.Status = 'Absent' THEN 1 ELSE 0 END) AS hs_absent
FROM Classes c
LEFT JOIN Students s ON c.ClassID = s.ClassID
LEFT JOIN Attendance a ON s.StudentID = a.StudentID AND a.Date = CURDATE()
WHERE c.DepartmentID = $department_id
GROUP BY c.ClassName, c.ClassID
";
$result_classes_absent_count = $conn->query($sql_classes_absent_count);

if (!$result_classes_absent_count) {
    die("Query failed: " . $conn->error);
}


$sql_absent_students = "
SELECT s.ClassID, s.StudentName, s.RollNo, s.Type
FROM Students s
JOIN Attendance a ON s.StudentID = a.StudentID
WHERE a.Status = 'Absent' AND a.Date = CURDATE()
AND s.ClassID IN (
    SELECT ClassID FROM Classes WHERE DepartmentID = $department_id
)
";
$result_absent_students = $conn->query($sql_absent_students);

if (!$result_absent_students) {
    die("Query failed: " . $conn->error);
}

$absent_students = [];
$total_ds_absent = 0;
$total_hs_absent = 0;
while ($row = $result_absent_students->fetch_assoc()) {
    $class_id = $row['ClassID'];
    if (!isset($absent_students[$class_id])) {
        $absent_students[$class_id] = ['DS' => [], 'HS' => []];
    }
    $absent_students[$class_id][$row['Type']][] = $row['StudentName'] . ' (' . $row['RollNo'] . ')';
    
    if ($row['Type'] === 'DS') {
        $total_ds_absent++;
    } elseif ($row['Type'] === 'HS') {
        $total_hs_absent++;
    }
}

$total_absent = $total_ds_absent + $total_hs_absent;


$sql_total_students = "SELECT COUNT(*) AS total_students FROM Students WHERE DepartmentID = $department_id";
$result_total_students = $conn->query($sql_total_students);
$total_students_row = $result_total_students->fetch_assoc();
$total_students = $total_students_row['total_students'];
$total_present = $total_students - $total_absent;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoD Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        header {
            text-align: center;
            padding: 20px;
            background-color: #007BFF;
            color: white;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        main {
            width: 100%;
            max-width: 1200px;
        }
        .summary {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: left;
            margin: 20px 0;
            width: fit-content;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .class {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
            flex: 1;
            flex-basis: calc(33% - 20px); 
            max-width: calc(33% - 20px); 
            min-height: 200px; 
        }
        .class h2 {
            margin-top: 0;
            color: #007BFF;
        }
        .class p {
            margin: 5px 0;
        }
        .classes-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .tooltip {
            position: relative;
            display: inline-block;
        }
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #fff;
            color: #333;
            text-align: left;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: absolute;
            z-index: 1;
            bottom: 125%; 
            left: 50%;
            transform: translateX(-50%);
        }
        .tooltip:hover .tooltiptext,
        .tooltip:focus .tooltiptext {
            visibility: visible;
        }
    </style>
</head>
<body>
    <header>
        <h1>HoD Dashboard</h1>
        <p>Attendance Summary for <?php echo date('F j, Y'); ?></p>
    </header>
    <main>
        <div class="summary">
            <p><strong>Total Absent (DS):</strong> <?php echo $total_ds_absent; ?></p>
            <p><strong>Total Absent (HS):</strong> <?php echo $total_hs_absent; ?></p>
            <p><strong>Total Absent:</strong> <?php echo $total_absent; ?></p>
        </div>
        <h2>Classes and Absent Counts in Your Department</h2>
        <div class="classes-container">
        <?php while ($row = $result_classes_absent_count->fetch_assoc()): ?>
            <div class="class">
                <h2><?php echo htmlspecialchars($row['ClassName']); ?></h2>
                <p><strong>Total Absent:</strong> <?php echo $row['AbsentCount']; ?></p>
                <p><strong>DS Absent:</strong> <?php echo $row['ds_absent']; ?></p>
                <p><strong>HS Absent:</strong> <?php echo $row['hs_absent']; ?></p>
                <div class="tooltip">
                    <span>Details</span>
                    <div class="tooltiptext">
                        <strong>Day Scholars:</strong><br>
                        <?php
                        $ds_absent = isset($absent_students[$row['ClassID']]['DS']) ? implode('<br>', $absent_students[$row['ClassID']]['DS']) : 'None';
                        echo $ds_absent;
                        ?><br>
                        <strong>Hostellers:</strong><br>
                        <?php
                        $hs_absent = isset($absent_students[$row['ClassID']]['HS']) ? implode('<br>', $absent_students[$row['ClassID']]['HS']) : 'None';
                        echo $hs_absent;
                        ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    </main>
</body>
</html>

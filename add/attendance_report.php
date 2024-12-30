<?php
session_start();
if ($_SESSION['role'] != 'adviser') {
    header("Location: adviser_login.html");
    exit();
}

include 'db.php';

$class_id = $_GET['class_id'];
$date = $_GET['date'];

$sql = "SELECT DISTINCT s.StudentName, s.RollNo, s.Type
        FROM Attendance a
        JOIN Students s ON a.StudentID = s.StudentID
        WHERE a.Status = 'Absent' AND a.Date = ? AND s.ClassID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $date, $class_id);
$stmt->execute();
$result = $stmt->get_result();

$absent_ds = [];
$absent_hs = [];
while ($row = $result->fetch_assoc()) {
    $student_info = $row['StudentName'] . ' (' . $row['RollNo'] . ')';
    if ($row['Type'] == 'DS') {
        $absent_ds[] = $student_info;
    } elseif ($row['Type'] == 'HS') {
        $absent_hs[] = $student_info;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            color: #007BFF;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 5px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            background-color: #fff;
            border: 1px solid #ddd;
            margin-bottom: 5px;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Attendance Summary for <?php echo htmlspecialchars($date); ?></h1>
    <div class="section">
        <h2>Hostellers</h2>
        <ul>
            <?php if (count($absent_hs) > 0): ?>
                <?php foreach ($absent_hs as $student): ?>
                    <li><?php echo htmlspecialchars($student); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No hostellers absent.</li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="section">
        <h2>Day Scholars</h2>
        <ul>
            <?php if (count($absent_ds) > 0): ?>
                <?php foreach ($absent_ds as $student): ?>
                    <li><?php echo htmlspecialchars($student); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No day scholars absent.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>

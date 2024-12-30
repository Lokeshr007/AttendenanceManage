<?php
session_start();
if ($_SESSION['role'] != 'adviser' || $_SESSION['department_id'] != 1) {
    header("Location: adviser_login.html");
    exit();
}

include 'db.php';

$current_date = date('Y-m-d');
$adviser_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Adviser';

$username = $_SESSION['username'];
$assigned_class_ids = [
    'abinaya' => 5, // 3D IT-B
    'prasath' => 6, // 3D IT-A
    'tamilvanan' => 14, // 4th IT
    'amala' => 1 // 2D IT-A
];

if (isset($assigned_class_ids[$username])) {
    $class_id = $assigned_class_ids[$username];
    $sql_class_name = "SELECT ClassName FROM Classes WHERE ClassID = $class_id";
    $result_class_name = $conn->query($sql_class_name);
    if (!$result_class_name) {
        die("Query failed: " . $conn->error);
    }
    $class_name = $result_class_name->fetch_assoc()['ClassName'];

    $sql_students = "SELECT DISTINCT Students.StudentID, Students.StudentName, Students.RollNo
                     FROM Students
                     LEFT JOIN Attendance ON Students.StudentID = Attendance.StudentID AND Attendance.Date = '$current_date'
                     WHERE Students.ClassID = $class_id";
    $result_students = $conn->query($sql_students);
    if (!$result_students) {
        die("Query failed: " . $conn->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = date('Y-m-d');
    $attendance = $_POST['attendance'];
    
    foreach ($attendance as $student_id => $status) {
        
        $sql = "INSERT INTO Attendance (StudentID, Date, Status) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE Status = VALUES(Status)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("iss", $student_id, $date, $status);
        if (!$stmt->execute()) {
            die("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();
    }

    $conn->close();

    
    header("Location: attendance_report.php?class_id=$class_id&date=$date");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adviser Dashboard - IT Department</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 0;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .download-report {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .download-report button {
            margin-left: 5px;
            font-size: 14px;
            padding: 5px 8px;
            border: none;
            border-radius: 4px;
        }
        main {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .radio-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .radio-buttons input[type="radio"] {
            display: none;
        }
        .radio-buttons label {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        .radio-buttons input[type="radio"]:checked + label {
            background-color: #007BFF;
            color: white;
            border-color: #007BFF;
        }
        .radio-buttons input[type="radio"][value="Absent"]:checked + label {
            background-color: #FF5733;
            border-color: #FF5733;
        }
        .radio-buttons input[type="radio"][value="On Duty"]:checked + label {
            background-color: #17A2B8;
            border-color: #17A2B8;
        }
    </style>
    <script>
        function markAllPresent() {
            const presentRadioButtons = document.querySelectorAll('input[type="radio"][value="Present"]');
            presentRadioButtons.forEach(radio => {
                radio.checked = true;
            });
        }
    </script>
</head>
<body>
    <header>
        <h1>Adviser Dashboard - IT Department</h1>
        <p>Welcome, <?php echo htmlspecialchars($adviser_name); ?>!</p>
        <h2>Class: <?php echo htmlspecialchars($class_name); ?></h2>
        <div class="download-report">
            <form action="generate_report.php" method="GET">
                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                <button type="submit">Download Last 30 Days Report</button>
            </form>
        </div>
    </header>
    <main>
        <h2>Mark Attendance for <?php echo htmlspecialchars($class_name); ?></h2>
        <button type="button" onclick="markAllPresent()">Mark All Present</button>
        <form action="" method="POST">
            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['StudentName']) . ' (' . htmlspecialchars($row['RollNo']) . ')'; ?></td>
                            <td class="radio-buttons">
                                <input type="radio" id="present-<?php echo $row['StudentID']; ?>" name="attendance[<?php echo $row['StudentID']; ?>]" value="Present" required>
                                <label for="present-<?php echo $row['StudentID']; ?>">Present</label>
                                <input type="radio" id="absent-<?php echo $row['StudentID']; ?>" name="attendance[<?php echo $row['StudentID']; ?>]" value="Absent" required>
                                <label for="absent-<?php echo $row['StudentID']; ?>">Absent</label>
                                <input type="radio" id="onduty-<?php echo $row['StudentID']; ?>" name="attendance[<?php echo $row['StudentID']; ?>]" value="On Duty" required>
                                <label for="onduty-<?php echo $row['StudentID']; ?>">On Duty</label>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit">Submit Attendance</button>
        </form>
    </main>
</body>
</html>

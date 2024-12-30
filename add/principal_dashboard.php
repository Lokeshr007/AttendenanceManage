<?php
session_start();
if ($_SESSION['role'] != 'principal') {
    header("Location: principal_login.html");
    exit();
}

include 'db.php';
$total_ds_absent = 0;
$total_hs_absent = 0;


$current_date = date('Y-m-d');


$sql_absent_count = "
SELECT d.DepartmentID, d.DepartmentName, 
       SUM(CASE WHEN s.Type = 'DS' THEN 1 ELSE 0 END) AS ds_absent,
       SUM(CASE WHEN s.Type = 'HS' THEN 1 ELSE 0 END) AS hs_absent
FROM Attendance a
JOIN Students s ON a.StudentID = s.StudentID
JOIN Departments d ON s.DepartmentID = d.DepartmentID
WHERE a.Status = 'Absent' AND a.Date = '$current_date'
GROUP BY d.DepartmentID, d.DepartmentName
";
$result_absent_count = $conn->query($sql_absent_count);

if (!$result_absent_count) {
    die("Query failed: " . $conn->error);
}

$absent_counts = [];
while ($row = $result_absent_count->fetch_assoc()) {
    $absent_counts[$row['DepartmentID']] = $row;
    $total_ds_absent += $row['ds_absent'];
    $total_hs_absent += $row['hs_absent'];
}

$total_absent = $total_ds_absent + $total_hs_absent;


$sql_total_students = "SELECT COUNT(*) AS total_students FROM Students";
$result_total_students = $conn->query($sql_total_students);
$total_students_row = $result_total_students->fetch_assoc();
$total_students = $total_students_row['total_students'];
$total_present = $total_students - $total_absent;

$conn->close();


include 'db.php';

$sql_departments = "SELECT DepartmentID, DepartmentName FROM Departments";
$result_departments = $conn->query($sql_departments);

if (!$result_departments) {
    die("Query failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard</title>
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
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: left;
            margin: 20px 0;
            width: fit-content;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .department {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: center;
            flex: 1;
        }
        .department h2 {
            margin-top: 0;
            color: #007BFF;
        }
        .department p {
            margin: 5px 0;
        }
        .departments-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .chart-container {
            margin-top: 20px;
            width: 50%;
            max-width: 600px;
            display: flex;
            justify-content: center;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Welcome to the Principal Dashboard</h1>
        <p>Attendance Summary for <?php echo date('F j, Y'); ?></p>
    </header>
    <main>
        <div class="summary">
            <p><strong>Total Absent (DS):</strong> <?php echo $total_ds_absent; ?></p>
            <p><strong>Total Absent (HS):</strong> <?php echo $total_hs_absent; ?></p>
            <p><strong>Total Absent:</strong> <?php echo $total_absent; ?></p>
        </div>
        <h2>Attendance Summary for Today</h2>
        <div class="departments-container">
        <?php
        while ($dept = $result_departments->fetch_assoc()) {
            $dept_id = $dept['DepartmentID'];
            $dept_name = $dept['DepartmentName'];
            $ds_absent = isset($absent_counts[$dept_id]['ds_absent']) ? $absent_counts[$dept_id]['ds_absent'] : 0;
            $hs_absent = isset($absent_counts[$dept_id]['hs_absent']) ? $absent_counts[$dept_id]['hs_absent'] : 0;
            $total_dept_absent = $ds_absent + $hs_absent;
            ?>
            <div class="department">
                <h2><?php echo htmlspecialchars($dept_name); ?></h2>
                <p><strong>Total Absent:</strong> <?php echo $total_dept_absent; ?></p>
                <p><strong>DS Absent:</strong> <?php echo $ds_absent; ?></p>
                <p><strong>HS Absent:</strong> <?php echo $hs_absent; ?></p>
            </div>
        <?php } ?>
        </div>
        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>
    </main>
    <script>
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    label: 'Attendance',
                    data: [<?php echo $total_present; ?>, <?php echo $total_absent; ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    </script>
</body>
</html>

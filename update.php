<?php
include "db_conn.php";
session_start();

if (isset($_POST['submit'])) {
    $old_id = (int) $_POST['old_id'];
    $new_id = (int) $_POST['new_id'];

    $errors = array();

    $conn->begin_transaction();
    try {
        $update_student_sql = "UPDATE students SET id = ? WHERE id = ?";
        $update_student_stmt = $conn->prepare($update_student_sql);
        $update_student_stmt->bind_param("ii", $new_id, $old_id);

        if (!$update_student_stmt->execute()) {
            throw new Exception("Error updating student ID in students table: " . $update_student_stmt->error);
        }

        $update_student_details_sql = "UPDATE student_details SET student_id = ? WHERE student_id = ?";
        $update_student_details_stmt = $conn->prepare($update_student_details_sql);
        $update_student_details_stmt->bind_param("ii", $new_id, $old_id);

        if (!$update_student_details_stmt->execute()) {
            throw new Exception("Error updating student ID in student details table: " . $update_student_details_stmt->error);
        }

        $conn->commit();

        $_SESSION["message"] = "Student ID Successfully Updated";

        header("location: index.php?");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT ID</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .main-container {
            width: 80%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 60%; 
        }

        .card-header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
        }

        .input-column {
            width: 48%;
        }

        .details-column {
            width: 48%;
            max-height: 300px;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input[type="number"], .btn-submit {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            padding: 15px;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .charts-container {
            width: 35%; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .chart {
            width: 100%;
            height: 250px;
            margin-bottom: 20px;
        }

        .error-container {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
        }

        .error-message {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <main class="main-container">
        <div class="card">
            <div class="card-header">
                <h1>Update Student ID</h1>
            </div>

            <?php
            if (isset($errors) && !empty($errors)) {
                echo "<div class='error-container'>";
                foreach ($errors as $error) {
                    echo "<div class='error-message'>$error</div>";
                }
                echo "</div>";
            }
            ?>

            <?php
            if (isset($_GET['id'])) {
                $student_id = $_GET['id'];
                $sql = "SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name, s.gender, s.birthday, 
                        sd.contact_number, sd.street, sd.town_city, sd.province, sd.zip_code
                        FROM students s
                        INNER JOIN student_details sd ON s.id = sd.student_id WHERE s.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $student_id);  
                $stmt->execute();
                $result = $stmt->get_result();
                $student = $result->fetch_assoc();

                if ($student) {
                    $student_number = $student['student_number'];
                    $first_name = $student['first_name'];
                    $middle_name = $student['middle_name'];
                    $last_name = $student['last_name'];
                    $gender = $student['gender'];
                    $birthday = $student['birthday'];
                    $contact_number = $student['contact_number'];
                    $street = $student['street'];
                    $town_city = $student['town_city'];
                    $province = $student['province'];
                    $zip_code = $student['zip_code'];
                } else {
                    $_SESSION["message"] = "No students matched the id.";
                    header("location: index.php");
                }
            }
            ?>

            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="input-column">
                            <div class="form-group">
                                <label for="old_id">Old Student ID:</label>
                                <p class="plaintext-box"><?php echo htmlspecialchars($student_id); ?></p>
                                <input type="hidden" name="old_id" value="<?php echo $student_id; ?>">
                            </div>

                            <div class="form-group">
                                <label for="new_id">New Student ID</label>
                                <input type="number" class="form-control" placeholder="Enter new Student ID" name="new_id" required>
                            </div>

                            <button type="submit" name="submit" class="btn-submit">Update Student ID</button>
                        </div>

                        <div class="details-column">
                            <div class="scrollable-box">
                                <div class="form-group">
                                    <label>Student Number:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($student_number); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>First Name:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($first_name); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Middle Name:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($middle_name); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Last Name:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($last_name); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Gender:</label>
                                    <p class="plaintext-box"><?php echo ($gender == 0) ? 'Female' : 'Male'; ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Birthday:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($birthday); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Contact Number:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($contact_number); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Street Name:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($street); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Town/City:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($town_city); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Province:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($province); ?></p>
                                </div>

                                <div class="form-group">
                                    <label>Zip Code:</label>
                                    <p class="plaintext-box"><?php echo htmlspecialchars($zip_code); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="charts-container">
            <canvas id="pieChart" class="chart"></canvas>
            <canvas id="barChart" class="chart"></canvas>
        </div>
    </main>

    <script>
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const pieData = {
            labels: ['Excellent', 'Good', 'Average', 'Needs Improvement'],
            datasets: [{
                data: [30, 30, 25, 15],
                backgroundColor: ['#4CAF50', '#2196F3', '#FFEB3B', '#FF5722'],
                borderColor: ['#4CAF50', '#2196F3', '#FFEB3B', '#FF5722'],
                borderWidth: 1
            }]
        };

        const pieOptions = {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        };

        const pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: pieData,
            options: pieOptions
        });

        const barCtx = document.getElementById('barChart').getContext('2d');
        const barData = {
            labels: ['Python', 'HTML', 'CSS', 'C Language'],
            datasets: [{
                label: 'Student Performance',
                data: [75, 88, 65, 92],
                backgroundColor: '#4CAF50',
                borderColor: '#4CAF50',
                borderWidth: 1
            }]
        };

        const barOptions = {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: barData,
            options: barOptions
        });

        function updateCharts() {
            pieData.datasets[0].data = [Math.random() * 40 + 20, Math.random() * 40 + 20, Math.random() * 40 + 20, Math.random() * 20 + 10];
            pieChart.update();
            barData.datasets[0].data = [Math.random() * 100, Math.random() * 100, Math.random() * 100, Math.random() * 100];
            barChart.update();
        }

        setInterval(updateCharts, 1500);
    </script>
</body>
</html>

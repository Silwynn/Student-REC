<?php
include "db_conn.php";
session_start();

if (isset($_SESSION["message"])) {
    $msg = $_SESSION["message"];
    echo "
    <div class='toast-container position-fixed bottom-0 end-0 p-3' id='toast-container'>
      <div class='toast show' role='alert' aria-live='assertive' aria-atomic='true'>
        <div class='toast-header'>
          <small class='me-auto'>Just now</small>
          <button type='button' class='btn-close' data-bs-dismiss='toast' aria-label='Close'></button>
        </div>
        <div class='toast-body'>
          $msg
        </div>
      </div>
    </div>
  ";

    unset($_SESSION['message']);
}

$search = '';
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']); 
}

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$offset = ($page - 1) * $limit; 

$sql = "SELECT s.id, s.student_number, s.first_name, s.middle_name, s.last_name, s.gender, s.birthday, 
sd.contact_number, sd.street, sd.town_city, sd.province, sd.zip_code
FROM students s
INNER JOIN student_details sd ON s.id = sd.student_id";

if (!empty($search)) {
    $sql .= " WHERE s.first_name LIKE '%$search%' OR s.middle_name LIKE '%$search%' OR s.last_name LIKE '%$search%'";
}

$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$total_sql = "SELECT COUNT(*) AS total FROM students s INNER JOIN student_details sd ON s.id = sd.student_id";
if (!empty($search)) {
    $total_sql .= " WHERE s.first_name LIKE '%$search%' OR s.middle_name LIKE '%$search%' OR s.last_name LIKE '%$search%'";
}
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STUDENT ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <main class="container mt-5">
        <h2>Student Records</h2>
        <a href="create.php">Add new record</a>


        <form method="POST" class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Number</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Birthday</th>
                    <th>Contact Number</th>
                    <th>Street</th>
                    <th>Town/City</th>
                    <th>Province</th>
                    <th>Zip Code</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['student_number']}</td>
                                <td>{$row['first_name']}</td>
                                <td>{$row['middle_name']}</td>
                                <td>{$row['last_name']}</td>
                                <td>" . ($row['gender'] == 0 ? 'Female' : 'Male') . "</td>
                                <td>{$row['birthday']}</td>
                                <td>{$row['contact_number']}</td>
                                <td>{$row['street']}</td>
                                <td>{$row['town_city']}</td>
                                <td>{$row['province']}</td>
                                <td>{$row['zip_code']}</td>
                                <td><a href='update.php?id={$row['id']}'>Update ID</a></td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='12' class='text-center'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>" tabindex="-1">Previous</a>
                </li>

                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Next</a>
                </li>
            </ul>
        </nav>

    </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

</html>

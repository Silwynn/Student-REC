<?php
include "db_conn.php";
session_start();

if (isset($_POST['submit'])) {
  $s_num = $_POST['s_number'];
  $s_fn = $_POST['s_fn'];
  $s_mn = $_POST['s_mn'];
  $s_ln = $_POST['s_ln'];
  $s_gender = $_POST['s_gender'];
  $s_bday = $_POST['s_birthday'];

  $s_contact = $_POST['s_contact'];
  $s_street = $_POST['s_street'];
  $s_town = $_POST['s_town'];
  $s_province = $_POST['s_province'];
  $s_zipcode = $_POST['s_zipcode'];

  $errors = [];

  $conn->begin_transaction();
  try {

    $student_sql = "INSERT INTO students(student_number, first_name, middle_name, last_name, gender, birthday) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("ssssss", $s_num, $s_fn, $s_mn, $s_ln, $s_gender, $s_bday);

    if (!$student_stmt->execute()) {
      throw new Exception("Error inserting into students table: " . $student_stmt->error);
    }

    $student_id = $conn->insert_id;


    $town_city = $s_town;


    $town_city_id_stmt = $conn->prepare("SELECT id FROM town_city WHERE name = ?");
    $town_city_id_stmt->bind_param("s", $town_city);
    $town_city_id_stmt->execute();
    $town_city_id_result = $town_city_id_stmt->get_result();

    if ($town_city_id_result->num_rows > 0) {
      $row = $town_city_id_result->fetch_assoc();  
      $town_city_id = $row['id'];
  }
   else {

      $town_city_stmt = $conn->prepare("INSERT INTO town_city (name) VALUES (?)");
      $town_city_stmt->bind_param("s", $town_city);
      $town_city_stmt->execute();


      $town_city_id = $conn->insert_id;
    }


    $province = $s_town;


    $province_id_stmt = $conn->prepare("SELECT id FROM province WHERE name = ?");
    $province_id_stmt->bind_param("s", $province);
    $province_id_stmt->execute();
    $province_id_result = $province_id_stmt->get_result();

    if ($province_id_result->num_rows > 0) {

      $row = $province_id_result->fetch_assoc();  
      $province_id = $row['id'];
  }
   else {

      $province_stmt = $conn->prepare("INSERT INTO province (name) VALUES (?)");
      $province_stmt->bind_param("s", $province);
      $province_stmt->execute();

      $province_id = $conn->insert_id;
    }

    // INSERT RECORD TO STUDENTS DETAILS
    $student_details_sql = "INSERT INTO student_details(student_id, contact_number, street, town_city, province, zip_code) 
        VALUES (?, ?, ?, ?, ?, ?)";
    $student_details_stmt = $conn->prepare($student_details_sql);
    $student_details_stmt->bind_param("issiis", $student_id, $s_contact, $s_street, $town_city_id, $province_id, $s_zipcode);

    if (!$student_details_stmt->execute()) {
      throw new Exception("Error inserting into student_details table: " . $student_details_stmt->error);
    }

    $conn->commit();

    $_SESSION["message"] = "New transaction added.";

    header("location: index.php");
    exit;
  } catch (Exception $e) {
    $conn->rollback();
    $errors[] = $e->getMessage();
  }
}
$conn->close();
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
    <div class="card">
      <div class="card-header bg-dark text-white text-center">
        <h1>Add Student Record</h1>
      </div>
      <?php
      if (isset($errors) && !empty($errors)) {
        echo "<div class='container mt-3'>";
        foreach ($errors as $error) {
          echo "<div class='alert alert-danger'>$error</div>";
        }
        echo "</div>";
      }
      ?>
      <div class="card-body">
        <form method="POST">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="s_number" class="form-label">Student Number</label>
                <input type="text" class="form-control" id="s_number" name="s_number" required
                  pattern="^\d{4}-\d{1}-\d{4}$"
                  title="Student number must be in the format: XXXX-X-XXXX (e.g. 2022-8-0233)"
                  placeholder="2022-8-0233">
              </div>

              <div class="mb-3">
                <label for="s_fn" class="form-label">First Name</label>
                <input type="text" class="form-control" id="s_fn" name="s_fn" required pattern="[A-Za-z\s]+"
                  title="Only letters and spaces are allowed." placeholder="Mark Silwyn">
              </div>

              <div class="mb-3">
                <label for="s_mn" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="s_mn" name="s_mn" pattern="[A-Za-z\s]+"
                  title="Only letters and spaces are allowed." placeholder="Dañoso">
              </div>

              <div class="mb-3">
                <label for="s_ln" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="s_ln" name="s_ln" required pattern="[A-Za-z\s]+"
                  title="Only letters and spaces are allowed." placeholder="Jardin">
              </div>

              <div class="mb-3">
                <label class="form-label d-block">Gender</label>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" id="s_gender0" name="s_gender" value="0" required>
                  <label for="s_gender0" class="form-check-label">Female</label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" id="s_gender1" name="s_gender" value="1" required>
                  <label for="s_gender1" class="form-check-label">Male</label>
                </div>
              </div>

              <div class="mb-3">
                <label for="s_birthday" class="form-label">Birthday</label>
                <input type="date" class="form-control" id="s_birthday" name="s_birthday" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label for="s_contact" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="s_contact" name="s_contact" required pattern="\d{11}"
                  title="Enter a valid 11-digit contact number." placeholder="09306962502">
              </div>

              <div class="mb-3">
                <label for="s_street" class="form-label">Street Name</label>
                <input type="text" class="form-control" id="s_street" name="s_street" required placeholder="San Pedro">
              </div>

              <div class="mb-3">
                <label for="s_town" class="form-label">Town Name</label>
                <input type="text" class="form-control" id="s_town" name="s_town" required placeholder="Puerto Princesa City">
              </div>

              <div class="mb-3">
                <label for="s_province" class="form-label">Province Name</label>
                <input type="text" class="form-control" id="s_province" name="s_province" required
                  placeholder="Palawan">
              </div>

              <div class="mb-3">
                <label for="s_zipcode" class="form-label">Zip Code</label>
                <input type="text" class="form-control" id="s_zipcode" name="s_zipcode" required pattern="\d{4,6}"
                  title="Enter a valid zip code (4 to 6 digits)." placeholder="5300">
              </div>
            </div>
          </div>

          <div class="text-center mt-4">
            <button type="submit" name="submit" class="btn btn-dark w-50">Submit</button>
          </div>
        </form>

      </div>
    </div>
  </main>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
  var toast = new bootstrap.Toast(document.querySelector('.toast'));
  toast.show();
</script>
</html>

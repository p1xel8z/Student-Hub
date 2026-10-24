
<?php
session_start();

function auditLog($event, $email = "")
{
    // Log a hash of the email, never passwords or form contents.
    $emailHash = $email !== ""
        ? hash("sha256", strtolower(trim($email)))
        : "unknown";

    $line = date("Y-m-d H:i:s")
        . " | " . $event
        . " | email_hash=" . $emailHash
        . PHP_EOL;

$file = __DIR__ . DIRECTORY_SEPARATOR . "registration-audit.log";

    if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
        error_log("Unable to write registration audit log.");
    }
}

function showMessage($title, $message)
{
    echo "<h2>" . htmlspecialchars($title) . "</h2>";
    echo "<p>" . htmlspecialchars($message) . "</p>";
    echo '<p><a href="register.php">Go back to registration</a></p>';
    exit;
}

// Accept POST requests only.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    auditLog("invalid_request_method");
    showMessage("Invalid request", "Please submit the registration form.");
}

// Validate CSRF token.
if (
    !isset($_SESSION["csrf_token"], $_POST["csrf_token"]) ||
    !is_string($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    auditLog("csrf_validation_failed");
    showMessage(
        "Security check failed",
        "Please reload the registration page and try again."
    );
}

// Read form fields.
$firstName = trim($_POST["first_name"] ?? "");
$lastName = trim($_POST["last_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$dob = trim($_POST["date_of_birth"] ?? "");
$gender = $_POST["gender"] ?? "";
$program = $_POST["program"] ?? "";
$year = $_POST["year"] ?? "";
$studentId = trim($_POST["student_id"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

$errors = [];

// Backend validation.
if (!preg_match("/^[A-Za-z ]+$/", $firstName)) {
    $errors[] = "Enter a valid first name.";
}

if (!preg_match("/^[A-Za-z ]+$/", $lastName)) {
    $errors[] = "Enter a valid last name.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid email address.";
}

if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $errors[] = "Mobile number must contain 10 digits.";
}

$date = DateTime::createFromFormat("!Y-m-d", $dob);

if (
    !$date ||
    $date->format("Y-m-d") !== $dob ||
    $date > new DateTime("today")
) {
    $errors[] = "Enter a valid date of birth.";
}

if (!in_array($gender, ["male", "female", "other"], true)) {
    $errors[] = "Select a valid gender.";
}

if (!in_array($program, ["cse", "it", "ce"], true)) {
    $errors[] = "Select a valid program.";
}

if (!in_array($year, ["1", "2", "3", "4"], true)) {
    $errors[] = "Select a valid year.";
}

if ($studentId === "" || strlen($studentId) > 30) {
    $errors[] = "Enter a valid student ID.";
}

if (!is_string($password) || strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if (!is_string($confirmPassword) || $password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (($_POST["terms"] ?? "") !== "on") {
    $errors[] = "Accept the terms and conditions.";
}

if (!empty($errors)) {
    auditLog("validation_failed", $email);

    echo "<h2>Registration failed</h2>";
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    echo '<p><a href="register.php">Go back</a></p>';
    exit;
}

// Connect using MySQLi.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli(
        "localhost",
        "root",
        "",
        "studenthub"
    );

    $mysqli->set_charset("utf8mb4");

    // Check existing email or college student ID.
    $check = $mysqli->prepare(
        "SELECT email, college_student_id
         FROM students
         WHERE email = ? OR college_student_id = ?
         LIMIT 1"
    );

    $check->bind_param("ss", $email, $studentId);
    $check->execute();
    $check->bind_result($existingEmail, $existingStudentId);

    if ($check->fetch()) {
        $check->close();
        $mysqli->close();

        if (strcasecmp($existingEmail, $email) === 0) {
            auditLog("duplicate_email", $email);
            showMessage(
                "Registration failed",
                "This email address is already registered."
            );
        }

        auditLog("duplicate_student_id", $email);
        showMessage(
            "Registration failed",
            "This college student ID is already registered."
        );
    }

    $check->close();

    // Hash the password before storing it.
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $fullName = $firstName . " " . $lastName;
    $studyYear = (int) $year;

    // Insert using a prepared statement.
    $sql = "INSERT INTO students
        (name, email, college_student_id, mobile,
         date_of_birth, gender, program, study_year,
         password_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(
        "sssssssis",
        $fullName,
        $email,
        $studentId,
        $mobile,
        $dob,
        $gender,
        $program,
        $studyYear,
        $hashedPassword
    );

    $stmt->execute();

    $stmt->close();
    $mysqli->close();

    auditLog("registration_success", $email);

    echo "<h2>Registration successful!</h2>";
    echo "<p>Your details have been saved to the database.</p>";
    echo '<p><a href="register.php">Register another student</a></p>';

} catch (mysqli_sql_exception $e) {
    // Error 1062 means a unique constraint was violated.
    error_log("Registration database error: " . $e->getMessage());

    if ($e->getCode() === 1062) {
        auditLog("duplicate_value_rejected", $email);
        showMessage(
            "Registration failed",
            "That email address or college student ID is already registered."
        );
    }

    auditLog("database_error", $email);
    showMessage(
        "Registration error",
        "Unable to save registration. Please try again later."
    );
}
?>

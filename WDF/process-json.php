<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request method.");
}

if (
    !isset($_SESSION["csrf_token"]) ||
    !isset($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    exit("Invalid security token. Please reload the registration page.");
}

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

if ($dob === "") {
    $errors[] = "Date of birth is required.";
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

if ($studentId === "") {
    $errors[] = "Student ID is required.";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (($_POST["terms"] ?? "") !== "on") {
    $errors[] = "You must accept the terms and conditions.";
}

if (!empty($errors)) {
    echo "<h2>Registration failed</h2>";
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    echo '<a href="register.php">Go back</a>';
    exit;
}

$file = __DIR__ . "/registrations.json";

$registrations = [];

if (file_exists($file)) {
    $content = file_get_contents($file);
    $registrations = json_decode($content, true);

    if (!is_array($registrations)) {
        $registrations = [];
    }
}

$registrations[] = [
    "first_name" => $firstName,
    "last_name" => $lastName,
    "email" => $email,
    "mobile" => $mobile,
    "date_of_birth" => $dob,
    "gender" => $gender,
    "program" => $program,
    "year" => $year,
    "student_id" => $studentId,
    "password_hash" => password_hash($password, PASSWORD_DEFAULT)
];

$json = json_encode(
    $registrations,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if (file_put_contents($file, $json, LOCK_EX) === false) {
    exit("Unable to save registration. Check folder permissions.");
}

echo "<h2>Registration successful!</h2>";
echo "<p>Your registration has been saved.</p>";
echo '<a href="register.html">Back to registration</a>';
?>
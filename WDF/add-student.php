
<?php
session_start();

require_once "db_mysqli.php";
require_once "audit-log.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

$message = "";
$name = "";
$email = "";
$collegeId = "";
$mobile = "";
$program = "cse";
$year = "1";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $collegeId = trim($_POST["college_student_id"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $program = $_POST["program"] ?? "";
    $year = $_POST["study_year"] ?? "";
    $password = $_POST["password"] ?? "";

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $collegeId === "" ||
        $mobile === "" ||
        !preg_match('/^[0-9]{10}$/', $mobile) ||
        !in_array($program, ["cse", "it", "ce"], true) ||
        !in_array($year, ["1", "2", "3", "4"], true) ||
        strlen($password) < 8
    ) {
        $message = "Please enter valid details. Use a 10-digit mobile number and a password of at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO students
            (name, email, college_student_id, mobile, program, study_year, password_hash)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssis",
            $name,
            $email,
            $collegeId,
            $mobile,
            $program,
            $year,
            $hash
        );

        if ($stmt->execute()) {
            $newStudentId = $conn->insert_id;
            $stmt->close();

            writeAuditLog(
                $conn,
                "ADD_STUDENT",
                "Added student: " . $name .
                " (Student ID: " . $newStudentId . ")"
            );

            $conn->close();

            header(
                "Location: student-management.php?message=" .
                urlencode("Student added successfully.")
            );
            exit;
        } else {
            $message = ($conn->errno === 1062)
                ? "Email or college student ID already exists."
                : "Failed to add student. Please check the entered details.";

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | Northbridge University</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .student-form {
            max-width: 650px;
            margin: 35px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        .student-form label {
            display: block;
            margin-top: 14px;
        }

        .student-form input,
        .student-form select {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            margin-top: 6px;
        }

        .student-form button {
            margin-top: 20px;
            padding: 11px 18px;
            background: #1f3a2e;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .form-message {
            padding: 12px;
            background: #fff0e8;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<main class="student-form">
    <h1>Add Student</h1>

    <?php if ($message !== ""): ?>
        <p class="form-message" role="alert">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="post">
        <label for="name">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            required
            maxlength="100"
            value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
            maxlength="100"
            value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="college_student_id">College Student ID</label>
        <input
            type="text"
            id="college_student_id"
            name="college_student_id"
            required
            maxlength="30"
            value="<?= htmlspecialchars($collegeId, ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="mobile">Mobile</label>
        <input
            type="text"
            id="mobile"
            name="mobile"
            required
            inputmode="numeric"
            pattern="[0-9]{10}"
            maxlength="10"
            value="<?= htmlspecialchars($mobile, ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="program">Course</label>
        <select id="program" name="program" required>
            <option value="cse" <?= $program === "cse" ? "selected" : "" ?>>CSE</option>
            <option value="it" <?= $program === "it" ? "selected" : "" ?>>IT</option>
            <option value="ce" <?= $program === "ce" ? "selected" : "" ?>>CE</option>
        </select>

        <label for="study_year">Study Year</label>
        <select id="study_year" name="study_year" required>
            <option value="1" <?= $year === "1" ? "selected" : "" ?>>1</option>
            <option value="2" <?= $year === "2" ? "selected" : "" ?>>2</option>
            <option value="3" <?= $year === "3" ? "selected" : "" ?>>3</option>
            <option value="4" <?= $year === "4" ? "selected" : "" ?>>4</option>
        </select>

        <label for="password">Initial Password</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
            autocomplete="new-password"
        >

        <button type="submit">Add Student</button>
        <a href="student-management.php">Cancel</a>
    </form>
</main>
</body>
</html>

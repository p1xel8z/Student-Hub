
<?php
session_start();

require_once "db_mysqli.php";
require_once "audit-log.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid student ID.");
}

$stmt = $conn->prepare(
    "SELECT student_id, name, email, college_student_id,
            mobile, program, study_year
     FROM students
     WHERE student_id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    exit("Student record not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $collegeId = trim($_POST["college_student_id"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $program = $_POST["program"] ?? "";
    $year = $_POST["study_year"] ?? "";

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $collegeId === "" ||
        !preg_match('/^[0-9]{10}$/', $mobile) ||
        !in_array($program, ["cse", "it", "ce"], true) ||
        !in_array($year, ["1", "2", "3", "4"], true)
    ) {
        $message = "Please enter valid details. Mobile must contain 10 digits.";
    } else {
        $update = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, college_student_id = ?,
                 mobile = ?, program = ?, study_year = ?
             WHERE student_id = ?"
        );

        $yearNumber = (int)$year;

        $update->bind_param(
            "sssssii",
            $name,
            $email,
            $collegeId,
            $mobile,
            $program,
            $yearNumber,
            $id
        );

        if ($update->execute()) {
            $update->close();

            writeAuditLog(
                $conn,
                "EDIT_STUDENT",
                "Updated student: " . $name .
                " (Student ID: " . $id . ")"
            );

            $conn->close();

            header(
                "Location: student-management.php?message=" .
                urlencode("Student updated successfully.")
            );
            exit;
        } else {
            $message = ($conn->errno === 1062)
                ? "Email or college student ID already belongs to another student."
                : "Failed to update student.";

            $update->close();
        }
    }

    // Keep submitted values visible after validation or database errors.
    $student["name"] = $name;
    $student["email"] = $email;
    $student["college_student_id"] = $collegeId;
    $student["mobile"] = $mobile;
    $student["program"] = $program;
    $student["study_year"] = $year;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Northbridge University</title>
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
    <h1>Edit Student</h1>

    <?php if ($message !== ""): ?>
        <p class="form-message" role="alert"><?= h($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="name">Name</label>
        <input
            id="name"
            name="name"
            maxlength="100"
            required
            value="<?= h($student["name"]) ?>"
        >

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="100"
            required
            value="<?= h($student["email"]) ?>"
        >

        <label for="college_student_id">College Student ID</label>
        <input
            id="college_student_id"
            name="college_student_id"
            maxlength="30"
            required
            value="<?= h($student["college_student_id"]) ?>"
        >

        <label for="mobile">Mobile</label>
        <input
            id="mobile"
            name="mobile"
            inputmode="numeric"
            pattern="[0-9]{10}"
            maxlength="10"
            required
            value="<?= h($student["mobile"]) ?>"
        >

        <label for="program">Course</label>
        <select id="program" name="program" required>
            <option value="cse" <?= $student["program"] === "cse" ? "selected" : "" ?>>CSE</option>
            <option value="it" <?= $student["program"] === "it" ? "selected" : "" ?>>IT</option>
            <option value="ce" <?= $student["program"] === "ce" ? "selected" : "" ?>>CE</option>
        </select>

        <label for="study_year">Study Year</label>
        <select id="study_year" name="study_year" required>
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <option
                    value="<?= $i ?>"
                    <?= (int)$student["study_year"] === $i ? "selected" : "" ?>
                ><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit">Save Changes</button>
        <a href="student-management.php">Cancel</a>
    </form>
</main>
</body>
</html>


<?php
session_start();

require_once "db_mysqli.php";
require_once "audit-log.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION["delete_csrf"])) {
    $_SESSION["delete_csrf"] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $conn->close();
    exit("Invalid student ID.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["delete_csrf"], $token)) {
        $conn->close();
        exit("Invalid request token. Please go back and try again.");
    }

    if (($_POST["confirm"] ?? "") === "yes") {
        // Get the student's details before deleting the record.
        $stmt = $conn->prepare(
            "SELECT name, email
             FROM students
             WHERE student_id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $studentToDelete = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$studentToDelete) {
            $conn->close();
            exit("Student record not found.");
        }

        $stmt = $conn->prepare(
            "DELETE FROM students WHERE student_id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows === 1) {
            $stmt->close();

            // Record the successful deletion in the audit log.
            writeAuditLog(
                $conn,
                "DELETE_STUDENT",
                "Deleted student: " . $studentToDelete["name"] .
                " (Email: " . $studentToDelete["email"] .
                ", Student ID: " . $id . ")"
            );

            unset($_SESSION["delete_csrf"]);
            $conn->close();

            header(
                "Location: student-management.php?message=" .
                urlencode("Student deleted successfully.")
            );
            exit;
        }

        $stmt->close();
        $message = "Student was not deleted. The record may no longer exist.";
    } else {
        unset($_SESSION["delete_csrf"]);
        $conn->close();

        header(
            "Location: student-management.php?message=" .
            urlencode("Deletion cancelled.")
        );
        exit;
    }
}

$stmt = $conn->prepare(
    "SELECT student_id, name, email, college_student_id
     FROM students
     WHERE student_id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    $conn->close();
    exit("Student record not found.");
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delete | Northbridge University</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .confirm-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        .confirm-card button,
        .confirm-card a {
            display: inline-block;
            margin: 10px 8px 0 0;
            padding: 10px 16px;
            border: 0;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }

        .delete-button {
            background: #a52828;
            color: white;
        }

        .cancel-button {
            background: #e5e5e5;
            color: #222;
        }

        .form-message {
            color: #a52828;
        }
    </style>
</head>

<body>
<main class="confirm-card">
    <h1>Confirm Student Deletion</h1>

    <?php if ($message !== ""): ?>
        <p class="form-message" role="alert"><?= h($message) ?></p>
    <?php endif; ?>

    <p>Are you sure you want to permanently delete this student?</p>

    <p><strong>Name:</strong> <?= h($student["name"]) ?></p>
    <p><strong>Email:</strong> <?= h($student["email"]) ?></p>
    <p><strong>College ID:</strong> <?= h($student["college_student_id"]) ?></p>

    <form method="post">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= h($_SESSION["delete_csrf"]) ?>"
        >

        <button
            class="delete-button"
            type="submit"
            name="confirm"
            value="yes"
        >
            Yes, Delete
        </button>

        <button
            class="cancel-button"
            type="submit"
            name="confirm"
            value="no"
        >
            Cancel
        </button>
    </form>
</main>
</body>
</html>

<?php $conn->close(); ?>

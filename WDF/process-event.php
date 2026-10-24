
<?php
session_start();
require __DIR__ . "/db.php";

function returnToEvents($status) {
    header("Location: events.html?registration=" . urlencode($status) . "#eventRegistration");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    returnToEvents("invalid");
}

$token = $_POST["csrf_token"] ?? "";

if (
    !isset($_SESSION["event_csrf"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["event_csrf"], $token)
) {
    returnToEvents("invalid");
}

$email = trim($_POST["email"] ?? "");
$collegeId = trim($_POST["college_student_id"] ?? "");
$eventId = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $collegeId === "" ||
    !$eventId
) {
    returnToEvents("invalid");
}

try {
    // Verify that the student exists.
    $stmt = $pdo->prepare(
        "SELECT student_id
         FROM students
         WHERE email = ? AND college_student_id = ?"
    );
    
    $stmt->execute([$email, $collegeId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        returnToEvents("student-not-found");
    }

    // Verify that the event exists.
    $stmt = $pdo->prepare(
        "SELECT event_id FROM events WHERE event_id = ?"
    );
    $stmt->execute([$eventId]);

    if (!$stmt->fetch()) {
        returnToEvents("invalid");
    }

    // Save the registration.
    $stmt = $pdo->prepare(
        "INSERT INTO registrations (student_id, event_id)
         VALUES (?, ?)"
    );

    $stmt->execute([$student["student_id"], $eventId]);

    returnToEvents("success");

} catch (PDOException $e) {
    if ($e->getCode() === "23000") {
        returnToEvents("duplicate");
    }

    error_log($e->getMessage());
    returnToEvents("error");
}
?>

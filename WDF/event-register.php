
<?php
session_start();
require "db.php";

if (empty($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$success = false;

// Retrieve events from MySQL
$stmt = $pdo->query(
    "SELECT event_id, title, event_date, category, location
     FROM events
     ORDER BY event_date"
);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $email = trim($_POST["email"] ?? "");
    $collegeId = trim($_POST["college_student_id"] ?? "");
    $eventId = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);

    if (!hash_equals($_SESSION["event_csrf"], $token)) {
        $message = "Invalid request. Please refresh and try again.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
              $collegeId === "" || !$eventId) {
        $message = "Please enter valid student details and select an event.";
    } else {
        // Check that the student exists
        $stmt = $pdo->prepare(
            "SELECT student_id FROM students
             WHERE email = ? AND college_student_id = ?"
        );
        $stmt->execute([$email, $collegeId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $message = "Student not found. Register your student details first.";
        } else {
            // Check that the selected event exists
            $stmt = $pdo->prepare(
                "SELECT event_id FROM events WHERE event_id = ?"
            );
            $stmt->execute([$eventId]);

            if (!$stmt->fetch()) {
                $message = "Please select a valid event.";
            } else {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO registrations (student_id, event_id)
                         VALUES (?, ?)"
                    );
                    $stmt->execute([$student["student_id"], $eventId]);
                    $message = "Event registration successful!";
                    $success = true;
                } catch (PDOException $e) {
                    if ($e->getCode() === "23000") {
                        $message = "You are already registered for this event.";
                    } else {
                        error_log($e->getMessage());
                        $message = "Registration failed. Please try again.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration | Northbridge University</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Northbridge University</h1>
        <nav>
            <a href="index.html">Home</a>
            <a href="events.html">Events</a>
            <a href="register.php">Student Registration</a>
        </nav>
    </header>

    <main>
        <h2>Register for an Event</h2>
        <p>Select an event from the database and enter your registered student details.</p>

        <?php if ($message !== ""): ?>
            <p role="status">
                <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form action="event-register.php" method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION["event_csrf"], ENT_QUOTES, "UTF-8") ?>">

            <label for="email">Registered Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="college_student_id">College Student ID:</label>
            <input type="text" id="college_student_id"
                   name="college_student_id" maxlength="30" required>

            <label for="event_id">Choose Event:</label>
            <select id="event_id" name="event_id" required>
                <option value="">-- Select an event --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= (int)$event["event_id"] ?>">
                        <?= htmlspecialchars($event["title"], ENT_QUOTES, "UTF-8") ?>
                        — <?= htmlspecialchars($event["event_date"], ENT_QUOTES, "UTF-8") ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Register for Event</button>
        </form>
        <?php endif; ?>

        <p><a href="events.html">Back to Events</a></p>
    </main>
</body>
</html>

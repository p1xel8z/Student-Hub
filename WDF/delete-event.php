
<?php
session_start();
require_once "db_mysqli.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /wdf/login.php");
    exit;
}

if (empty($_SESSION["event_delete_token"])) {
    $_SESSION["event_delete_token"] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header("Location: events.php?message=error");
    exit;
}

$stmt = $conn->prepare("SELECT event_id, title, poster FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: events.php?message=error");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token"] ?? "";

    if (!hash_equals($_SESSION["event_delete_token"], $token)) {
        $message = "Invalid request. Please try again.";
    } else {
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows === 1) {
            // Remove only a poster filename stored as a simple basename.
            if (!empty($event["poster"]) && basename($event["poster"]) === $event["poster"]) {
                $posterPath = __DIR__ . "/uploads/events/" . $event["poster"];
                if (is_file($posterPath)) {
                    unlink($posterPath);
                }
            }

            $_SESSION["event_delete_token"] = bin2hex(random_bytes(32));
            header("Location: events.php?message=deleted");
            exit;
        } else {
            $message = "Could not delete the event.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Event</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .delete-container { max-width: 600px; margin: 50px auto; padding: 25px; }
        .delete-button {
            padding: 10px 16px; margin: 8px 5px 0 0;
            background: #1f3a2e; color: white; border: none; cursor: pointer;
        }
        .delete-message { background: #ffe5e5; padding: 12px; }
    </style>
</head>
<body>
<div class="delete-container">
    <h1>Delete Event</h1>

    <?php if ($message !== ""): ?>
        <p class="delete-message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <p>Are you sure you want to delete this event?</p>
    <p><strong><?= htmlspecialchars($event["title"], ENT_QUOTES, "UTF-8") ?></strong></p>

    <form method="POST">
        <input type="hidden" name="token"
               value="<?= htmlspecialchars($_SESSION["event_delete_token"], ENT_QUOTES, "UTF-8") ?>">
        <button class="delete-button" type="submit">Yes, Delete Event</button>
        <a class="delete-button" href="events.php">Cancel</a>
    </form>
</div>
</body>
</html>

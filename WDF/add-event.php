
<?php
session_start();
require_once "db_mysqli.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /wdf/login.php");
    exit;
}

$message = "";
$maxSize = 2 * 1024 * 1024; // 2 MB
$uploadDir = __DIR__ . "/uploads/events/";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $category = trim($_POST["category"] ?? "General");
    $status = $_POST["status"] ?? "open";
    $posterName = null;

    if (
        $title === "" || $description === "" || $eventDate === "" ||
        $venue === "" || $category === "" ||
        !in_array($status, ["open", "closed"], true)
    ) {
        $message = "Please fill in all required fields.";
    } elseif (!empty($_FILES["poster"]["name"])) {
        $file = $_FILES["poster"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $message = "The poster upload failed.";
        } elseif ($file["size"] > $maxSize) {
            $message = "Poster must be 2 MB or smaller.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file["tmp_name"]);

            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp"
            ];

            if (!isset($allowedTypes[$mime])) {
                $message = "Only JPG, PNG, and WEBP images are allowed.";
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $posterName = bin2hex(random_bytes(16))
                    . "." . $allowedTypes[$mime];

                if (!move_uploaded_file(
                    $file["tmp_name"],
                    $uploadDir . $posterName
                )) {
                    $message = "Could not save the poster.";
                    $posterName = null;
                }
            }
        }
    } elseif (
        isset($_FILES["poster"]["error"]) &&
        $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {
        $message = "Please select a valid poster file.";
    }

    if ($message === "") {
        $stmt = $conn->prepare(
            "INSERT INTO events
            (title, description, event_date, venue, category, status, poster)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssss",
            $title,
            $description,
            $eventDate,
            $venue,
            $category,
            $status,
            $posterName
        );

        if ($stmt->execute()) {
            header("Location: events.php?message=added");
            exit;
        }

        if ($posterName !== null && is_file($uploadDir . $posterName)) {
            unlink($uploadDir . $posterName);
        }

        $message = "Could not add the event. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .event-form-container { max-width: 650px; margin: 35px auto; padding: 25px; }
        .event-form-container label { display: block; margin-top: 14px; }
        .event-form-container input,
        .event-form-container textarea,
        .event-form-container select {
            box-sizing: border-box; width: 100%; padding: 10px; margin-top: 5px;
        }
        .event-button {
            margin-top: 18px; padding: 11px 16px; background: #1f3a2e;
            color: white; border: none; cursor: pointer;
        }
        .event-error { background: #ffe5e5; padding: 12px; margin: 12px 0; }
    </style>
</head>
<body>
<div class="event-form-container">
    <h1>Add Event</h1>

    <?php if ($message !== ""): ?>
        <p class="event-error">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title</label>
        <input id="title" name="title" maxlength="150" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required></textarea>

        <label for="event_date">Event Date</label>
        <input id="event_date" type="date" name="event_date" required>

        <label for="venue">Venue</label>
        <input id="venue" name="venue" maxlength="150" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="General">General</option>
            <option value="Technical">Technical</option>
            <option value="Workshop">Workshop</option>
            <option value="Cultural">Cultural</option>
            <option value="Sports">Sports</option>
        </select>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="open">Open</option>
            <option value="closed">Closed</option>
        </select>

        <label for="poster">Event Poster (optional, max 2 MB)</label>
        <input
            id="poster"
            type="file"
            name="poster"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        >
        <small>Allowed formats: JPG, PNG, WEBP. Maximum size: 2 MB.</small>

        <button class="event-button" type="submit">Add Event</button>
        <a class="event-button" href="events.php">Cancel</a>
    </form>
</div>
</body>
</html>

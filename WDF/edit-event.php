
<?php
session_start();
require_once "db_mysqli.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /wdf/login.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header("Location: events.php?message=error");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: events.php?message=error");
    exit;
}

$message = "";
$uploadDir = __DIR__ . "/uploads/events/";
$maxSize = 2 * 1024 * 1024;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "";

    $posterName = $event["poster"];
    $newPoster = null;

    if (
        $title === "" || $description === "" || $eventDate === "" ||
        $venue === "" || $category === "" ||
        !in_array($status, ["open", "closed"], true)
    ) {
        $message = "Please fill in all required fields.";
    }

    if (
        $message === "" &&
        isset($_FILES["poster"]) &&
        $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {
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
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            if (!isset($allowedTypes[$mime])) {
                $message = "Only JPG, PNG, and WEBP images are allowed.";
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newPoster = bin2hex(random_bytes(16))
                    . "." . $allowedTypes[$mime];

                if (move_uploaded_file($file["tmp_name"], $uploadDir . $newPoster)) {
                    $posterName = $newPoster;
                } else {
                    $message = "Could not save the new poster.";
                    $newPoster = null;
                }
            }
        }
    }

    if ($message === "") {
        $stmt = $conn->prepare(
            "UPDATE events
             SET title = ?, description = ?, event_date = ?, venue = ?,
                 category = ?, status = ?, poster = ?
             WHERE event_id = ?"
        );

        $stmt->bind_param(
            "sssssssi",
            $title, $description, $eventDate, $venue,
            $category, $status, $posterName, $id
        );

        if ($stmt->execute()) {
            if (
                $newPoster !== null &&
                !empty($event["poster"]) &&
                basename($event["poster"]) === $event["poster"]
            ) {
                $oldPath = $uploadDir . $event["poster"];
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            header("Location: events.php?message=updated");
            exit;
        }

        if ($newPoster !== null && is_file($uploadDir . $newPoster)) {
            unlink($uploadDir . $newPoster);
        }

        $message = "Could not update the event. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
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
        .current-poster { max-width: 180px; display: block; margin-top: 10px; }
    </style>
</head>
<body>
<div class="event-form-container">
    <h1>Edit Event</h1>

    <?php if ($message !== ""): ?>
        <p class="event-error">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title</label>
        <input id="title" name="title" maxlength="150" required
               value="<?= htmlspecialchars($event["title"], ENT_QUOTES, "UTF-8") ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($event["description"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>

        <label for="event_date">Event Date</label>
        <input id="event_date" type="date" name="event_date" required
               value="<?= htmlspecialchars($event["event_date"], ENT_QUOTES, "UTF-8") ?>">

        <label for="venue">Venue</label>
        <input id="venue" name="venue" maxlength="150" required
               value="<?= htmlspecialchars($event["venue"] ?? "TBA", ENT_QUOTES, "UTF-8") ?>">

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <?php foreach (["General", "Technical", "Workshop", "Cultural", "Sports"] as $option): ?>
                <option value="<?= $option ?>"
                    <?= ($event["category"] ?? "General") === $option ? "selected" : "" ?>>
                    <?= $option ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="open" <?= ($event["status"] ?? "open") === "open" ? "selected" : "" ?>>Open</option>
            <option value="closed" <?= ($event["status"] ?? "open") === "closed" ? "selected" : "" ?>>Closed</option>
        </select>

        <label for="poster">Replace poster (optional, max 2 MB)</label>
        <?php if (!empty($event["poster"])): ?>
            <img class="current-poster"
                 src="<?= htmlspecialchars($event["poster"], ENT_QUOTES, "UTF-8") ?>"
                 alt="Current event poster">
        <?php else: ?>
            <p>No poster uploaded.</p>
        <?php endif; ?>
        <input id="poster" type="file" name="poster"
               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <small>Leave blank to keep the existing poster.</small>

        <button class="event-button" type="submit">Save Changes</button>
        <a class="event-button" href="events.php">Cancel</a>
    </form>
</div>
</body>
</html>


<?php
session_start();
require_once "db_mysqli.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /wdf/login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");
$status = $_GET["status"] ?? "";

$where = [];
$params = [];
$types = "";

if ($search !== "") {
    $where[] = "(title LIKE ? OR description LIKE ? OR venue LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "sss";
}

if ($category !== "") {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

if (in_array($status, ["open", "closed"], true)) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "SELECT * FROM events";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY event_date DESC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

$categories = $conn->query(
    "SELECT DISTINCT category FROM events ORDER BY category"
);

$message = $_GET["message"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .event-container { max-width: 1150px; margin: 35px auto; padding: 20px; }
        .event-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .event-table th, .event-table td {
            border: 1px solid #ddd; padding: 10px; text-align: left;
        }
        .event-table th { background: #1f3a2e; color: white; }
        .event-table img { width: 90px; height: 65px; object-fit: cover; }
        .event-btn {
            display: inline-block; padding: 9px 13px; margin: 4px;
            background: #1f3a2e; color: white; text-decoration: none;
            border: none; cursor: pointer; border-radius: 4px;
        }
        .event-form { display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0; }
        .event-form input, .event-form select { padding: 9px; }
        .event-message { padding: 12px; background: #e4f5e8; margin: 12px 0; }
        .table-wrapper { overflow-x: auto; }
    </style>
</head>
<body>
<div class="event-container">
    <h1>Event Management</h1>

    <?php if ($message === "added"): ?>
        <p class="event-message">Event added successfully.</p>
    <?php elseif ($message === "updated"): ?>
        <p class="event-message">Event updated successfully.</p>
    <?php elseif ($message === "deleted"): ?>
        <p class="event-message">Event deleted successfully.</p>
    <?php elseif ($message === "error"): ?>
        <p class="event-message">The operation could not be completed.</p>
    <?php endif; ?>

    <a class="event-btn" href="add-event.php">+ Add Event</a>
    <a class="event-btn" href="admin-dashboard.php">Back to Dashboard</a>

    <form class="event-form" method="GET">
        <input type="text" name="search"
               placeholder="Search title, description or venue"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">

        <select name="category">
            <option value="">All categories</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($cat["category"], ENT_QUOTES, 'UTF-8') ?>"
                    <?= $category === $cat["category"] ? "selected" : "" ?>>
                    <?= htmlspecialchars($cat["category"], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="status">
            <option value="">All statuses</option>
            <option value="open" <?= $status === "open" ? "selected" : "" ?>>Open</option>
            <option value="closed" <?= $status === "closed" ? "selected" : "" ?>>Closed</option>
        </select>

        <button class="event-btn" type="submit">Search / Filter</button>
        <a class="event-btn" href="events.php">Clear</a>
    </form>

    <div class="table-wrapper">
        <table class="event-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Poster</th>
                <th>Title</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($events->num_rows === 0): ?>
                <tr><td colspan="8">No events found.</td></tr>
            <?php else: ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$event["event_id"] ?></td>
                        <td>
                            <?php if (!empty($event["poster"])): ?>
                                <img src="<?= htmlspecialchars($event["poster"], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Event poster">
                            <?php else: ?>
                                No poster
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($event["title"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($event["event_date"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($event["venue"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($event["category"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($event["status"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a class="event-btn"
                               href="edit-event.php?id=<?= (int)$event["event_id"] ?>">Edit</a>
                            <a class="event-btn"
                               href="delete-event.php?id=<?= (int)$event["event_id"] ?>">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>


<?php
session_start();

$timeout = 900;

if (
    !isset($_SESSION["user_id"], $_SESSION["role"], $_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

$_SESSION["last_activity"] = time();

if ($_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit("Access denied. Admins only.");
}

require_once "db.php";

// Dashboard statistics
$studentCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$eventCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM events"
)->fetchColumn();

$userCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$registrationCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM registrations"
)->fetchColumn();

// Latest registrations
$latestStmt = $pdo->query(
    "SELECT r.registration_id, r.registration_date,
            s.name AS student_name, s.email,
            e.title AS event_title
     FROM registrations r
     INNER JOIN students s ON r.student_id = s.student_id
     INNER JOIN events e ON r.event_id = e.event_id
     ORDER BY r.registration_date DESC
     LIMIT 5"
);

$latestRegistrations = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

// Latest audit log entries
$auditLogs = [];

try {
    $auditStmt = $pdo->query(
        "SELECT user_name, action, details, created_at
         FROM audit_logs
         ORDER BY created_at DESC
         LIMIT 10"
    );

    $auditLogs = $auditStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Audit log query failed: " . $e->getMessage());
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Northbridge University</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin: 25px 0;
        }

        .dashboard-card {
            background: #fff;
            color: #222;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #00000015;
        }

        .dashboard-card h2 {
            font-size: 16px;
            margin-top: 0;
        }

        .dashboard-card p {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 0;
        }

        .dashboard-section {
            background: #fff;
            color: #222;
            padding: 20px;
            margin: 22px 0;
            border-radius: 10px;
            overflow-x: auto;
        }

        .dashboard-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-section th,
        .dashboard-section td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .dashboard-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }

        .dashboard-links a {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 6px;
            background: #1f3a2e;
            color: white;
            text-decoration: none;
        }

        .dashboard-links a:hover {
            opacity: 0.85;
        }

        .empty-message {
            color: #555;
        }
    </style>
</head>
<body>
<main class="dashboard-container">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= h($_SESSION["user_name"]) ?>!</p>
    <p>You have administrator access.</p>

    <nav class="dashboard-links" aria-label="Admin menu">
        <a href="admin-dashboard.php">Dashboard</a>
        <a href="student-management.php">Student Management</a>
        <a href="events.php">Event Management</a>
        <a href="events-api.html">Events API Demo</a>
    </nav>

    <section class="dashboard-cards" aria-label="Dashboard statistics">
        <article class="dashboard-card">
            <h2>Total Students</h2>
            <p><?= $studentCount ?></p>
        </article>

        <article class="dashboard-card">
            <h2>Total Users</h2>
            <p><?= $userCount ?></p>
        </article>

        <article class="dashboard-card">
            <h2>Total Events</h2>
            <p><?= $eventCount ?></p>
        </article>

        <article class="dashboard-card">
            <h2>Registrations</h2>
            <p><?= $registrationCount ?></p>
        </article>
    </section>

    <section class="dashboard-section">
        <h2>Latest Registrations</h2>

        <?php if (!$latestRegistrations): ?>
            <p class="empty-message">No registrations found yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($latestRegistrations as $registration): ?>
                    <tr>
                        <td><?= h($registration["student_name"]) ?></td>
                        <td><?= h($registration["email"]) ?></td>
                        <td><?= h($registration["event_title"]) ?></td>
                        <td><?= h($registration["registration_date"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <h2>Recent Audit Activity</h2>

        <?php if (!$auditLogs): ?>
            <p class="empty-message">
                No audit activity recorded yet.
            </p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date and Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($auditLogs as $log): ?>
                    <tr>
                        <td><?= h($log["user_name"]) ?></td>
                        <td><?= h($log["action"]) ?></td>
                        <td><?= h($log["details"]) ?></td>
                        <td><?= h($log["created_at"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</main>
</body>
</html>

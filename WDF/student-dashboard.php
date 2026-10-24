
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

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Access denied.");
}

require_once "db.php";

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$sql = "
    SELECT
        e.title,
        e.event_date,
        e.category,
        e.venue,
        e.status,
        e.poster,
        r.registration_date
    FROM registrations r
    INNER JOIN events e ON r.event_id = e.event_id
    WHERE r.student_id = :student_id
    ORDER BY e.event_date ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    "student_id" => $_SESSION["user_id"]
]);

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Northbridge University</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 20px;
        }

        .dashboard-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }

        .dashboard-nav a,
        .logout-button {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            background: #1f3a2e;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .event-card {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 18px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,.08);
        }

        .event-card img {
            width: 140px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
        }

        .event-info {
            flex: 1;
        }

        .event-info h3 {
            margin-top: 0;
        }

        .empty-message {
            padding: 20px;
            background: white;
            border-radius: 8px;
        }

        @media (max-width: 600px) {
            .event-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .event-card img {
                width: 100%;
                height: auto;
                max-height: 220px;
            }
        }
    </style>
</head>
<body>
<main class="dashboard-container">
    <h1>Student Dashboard</h1>

    <p>Welcome, <?= h($_SESSION["user_name"] ?? "Student") ?>!</p>

    <nav class="dashboard-nav" aria-label="Student navigation">
        <a href="student-dashboard.php">Dashboard</a>
    </nav>

    <h2>My Registered Events</h2>

    <?php if (count($events) === 0): ?>
        <div class="empty-message">
            <p>You haven't registered for any events yet.</p>
        </div>
    <?php else: ?>
        <p>You have registered for <?= count($events) ?> event(s).</p>

        <?php foreach ($events as $event): ?>
            <article class="event-card">
                <?php if (!empty($event["poster"])): ?>
                    <img
                        src="<?= h($event["poster"]) ?>"
                        alt="Poster for <?= h($event["title"]) ?>"
                    >
                <?php endif; ?>

                <div class="event-info">
                    <h3><?= h($event["title"]) ?></h3>
                    <p><strong>Date:</strong> <?= h($event["event_date"]) ?></p>
                    <p><strong>Venue:</strong> <?= h($event["venue"]) ?></p>
                    <p><strong>Category:</strong> <?= h($event["category"] ?: "General") ?></p>
                    <p><strong>Status:</strong> <?= h(ucfirst($event["status"])) ?></p>
                    <p><strong>Registered on:</strong> <?= h($event["registration_date"]) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <form action="logout.php" method="post">
        <button class="logout-button" type="submit">Logout</button>
    </form>
</main>
</body>
</html>

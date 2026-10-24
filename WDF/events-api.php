
<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

try {
    require __DIR__ . "/db.php";

    if (empty($_SESSION["event_csrf"])) {
        $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
    }

    $stmt = $pdo->query(
        "SELECT event_id, title, event_date, category, location
         FROM events
         ORDER BY event_date"
    );

    echo json_encode([
        "events" => $stmt->fetchAll(PDO::FETCH_ASSOC),
        "csrfToken" => $_SESSION["event_csrf"]
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        "error" => "Unable to retrieve events."
    ]);
}
?>

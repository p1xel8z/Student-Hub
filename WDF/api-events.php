
<?php
session_start();
require_once "db_mysqli.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    respond(false, "Admin login required.", null, 401);
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $stmt = $conn->prepare(
        "SELECT event_id, title, description, event_date, venue,
                category, status, poster
         FROM events ORDER BY event_date DESC"
    );
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    respond(true, "Events loaded.", $events);
}

if ($method !== "POST") {
    header("Allow: GET, POST");
    respond(false, "Method not allowed.", null, 405);
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    respond(false, "Invalid JSON request.", null, 400);
}

$action = $input["action"] ?? "";

if ($action === "add" || $action === "update") {
    $title = trim($input["title"] ?? "");
    $description = trim($input["description"] ?? "");
    $date = $input["event_date"] ?? "";
    $venue = trim($input["venue"] ?? "");
    $category = trim($input["category"] ?? "General");
    $status = $input["status"] ?? "open";

    if ($title === "" || $description === "" ||
        $date === "" || $venue === "" || $category === "") {
        respond(false, "Please fill in all required fields.", null, 422);
    }

    $dateObject = DateTime::createFromFormat("!Y-m-d", $date);
    if (!$dateObject || $dateObject->format("Y-m-d") !== $date) {
        respond(false, "Please enter a valid event date.", null, 422);
    }

    if (strlen($title) > 150 || strlen($venue) > 150 ||
        !in_array($status, ["open", "closed"], true)) {
        respond(false, "One or more fields are invalid.", null, 422);
    }

    if ($action === "add") {
        $stmt = $conn->prepare(
            "INSERT INTO events
             (title, description, event_date, venue, category, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssss", $title, $description, $date,
            $venue, $category, $status
        );

        if (!$stmt->execute()) {
            respond(false, "Could not add the event.", null, 500);
        }

        respond(true, "Event added successfully.", [
            "event_id" => $conn->insert_id
        ], 201);
    }

    $id = filter_var($input["event_id"] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        respond(false, "Invalid event ID.", null, 422);
    }

    $stmt = $conn->prepare(
        "UPDATE events
         SET title = ?, description = ?, event_date = ?,
             venue = ?, category = ?, status = ?
         WHERE event_id = ?"
    );
    $stmt->bind_param(
        "ssssssi", $title, $description, $date,
        $venue, $category, $status, $id
    );

    if (!$stmt->execute()) {
        respond(false, "Could not update the event.", null, 500);
    }

    if ($stmt->affected_rows === 0) {
        $check = $conn->prepare(
            "SELECT event_id FROM events WHERE event_id = ?"
        );
        $check->bind_param("i", $id);
        $check->execute();

        if (!$check->get_result()->fetch_assoc()) {
            respond(false, "Event not found.", null, 404);
        }
    }

    respond(true, "Event updated successfully.");
}

if ($action === "delete") {
    $id = filter_var($input["event_id"] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        respond(false, "Invalid event ID.", null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        respond(false, "Could not delete the event.", null, 500);
    }

    if ($stmt->affected_rows === 0) {
        respond(false, "Event not found.", null, 404);
    }

    respond(true, "Event deleted successfully.");
}

respond(false, "Unknown action.", null, 400);

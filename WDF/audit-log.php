
<?php
function writeAuditLog(
    mysqli $conn,
    string $action,
    string $details = ""
): void {
    if (!isset($_SESSION["user_id"], $_SESSION["user_name"])) {
        return;
    }

    try {
        $userId = (int) $_SESSION["user_id"];
        $userName = $_SESSION["user_name"];

        $stmt = $conn->prepare(
            "INSERT INTO audit_logs (user_id, user_name, action, details)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isss",
            $userId,
            $userName,
            $action,
            $details
        );

        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        error_log("Audit logging failed: " . $e->getMessage());
    }
}

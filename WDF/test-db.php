
<?php
require "db.php";

$stmt = $pdo->prepare(
    "SELECT students.name, events.title
     FROM registrations
     JOIN students ON registrations.student_id = students.student_id
     JOIN events ON registrations.event_id = events.event_id"
);

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>StudentHub Registrations</h2>";

foreach ($rows as $row) {
    echo htmlspecialchars($row["name"]) . " - ";
    echo htmlspecialchars($row["title"]) . "<br>";
}
?>

<?php
$file = __DIR__ . "/registrations.json";
$registrations = [];

if (file_exists($file)) {
    $data = file_get_contents($file);
    $registrations = json_decode($data, true);

    if (!is_array($registrations)) {
        $registrations = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <h1>Registered Students</h1>

    <?php if (empty($registrations)): ?>
        <p>No registrations found.</p>
    <?php else: ?>

        <table border="1" cellpadding="10">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Program</th>
                <th>Year</th>
                <th>Student ID</th>
            </tr>

            <?php foreach ($registrations as $student): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($student["first_name"] . " " . $student["last_name"]) ?>
                    </td>
                    <td><?= htmlspecialchars($student["email"]) ?></td>
                    <td><?= htmlspecialchars($student["mobile"]) ?></td>
                    <td><?= htmlspecialchars($student["program"]) ?></td>
                    <td><?= htmlspecialchars($student["year"]) ?></td>
                    <td><?= htmlspecialchars($student["student_id"]) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>

    <p><a href="register.php">Back to Registration</a></p>
</main>

</body>
</html>
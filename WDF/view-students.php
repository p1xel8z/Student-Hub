
<?php
require __DIR__ . "/db.php";

$stmt = $pdo->prepare(
    "SELECT student_id, name, email, college_student_id,
            mobile, date_of_birth, gender, program, study_year
     FROM students
     ORDER BY student_id DESC"
);

$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students | Northbridge University</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .database-page {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1f3a2e;
            color: white;
        }

        tr:nth-child(even) {
            background: #f4f1e8;
        }

        .database-page a {
            color: #1f3a2e;
        }
    </style>
</head>
<body>

<main class="database-page">
    <h1>Registered Students</h1>
    <p>Student records retrieved from the MySQL database.</p>

    <p>Total students: <?= count($students) ?></p>

    <?php if (empty($students)): ?>
        <p>No students have registered yet.</p>
    <?php else: ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Student ID</th>
                        <th>Mobile</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Program</th>
                        <th>Year</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$student["student_id"]) ?></td>
                            <td><?= htmlspecialchars($student["name"]) ?></td>
                            <td><?= htmlspecialchars($student["email"]) ?></td>
                            <td><?= htmlspecialchars($student["college_student_id"] ?? "") ?></td>
                            <td><?= htmlspecialchars($student["mobile"] ?? "") ?></td>
                            <td><?= htmlspecialchars($student["date_of_birth"] ?? "") ?></td>
                            <td><?= htmlspecialchars($student["gender"] ?? "") ?></td>
                            <td><?= htmlspecialchars($student["program"] ?? "") ?></td>
                            <td><?= htmlspecialchars((string)($student["study_year"] ?? "")) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

    <p><a href="register.php">Register another student</a></p>
    <p><a href="index.html">Back to Home</a></p>
</main>

</body>
</html>

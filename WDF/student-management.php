
<?php
session_start();
require_once "db_mysqli.php";

// Only admins can manage student records
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");

$allowedCourses = ["cse", "it", "ce"];
if (!in_array($course, $allowedCourses, true)) {
    $course = "";
}

$page = max(1, (int)($_GET["page"] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
$types = "";

if ($search !== "") {
    $where[] = "(name LIKE ? OR email LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $types .= "ss";
}

if ($course !== "") {
    $where[] = "program = ?";
    $params[] = $course;
    $types .= "s";
}

$whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";

$countSql = "SELECT COUNT(*) AS total FROM students" . $whereSql;
$countStmt = $conn->prepare($countSql);

if ($params) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$total = (int)$countStmt->get_result()->fetch_assoc()["total"];
$countStmt->close();

$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT student_id, name, email, college_student_id,
               mobile, program, study_year, role
        FROM students" . $whereSql . "
        ORDER BY student_id DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if ($params) {
    $listTypes = $types . "ii";
    $listParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($listTypes, ...$listParams);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = $_GET["message"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | Northbridge University</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .management-page {
            max-width: 1200px;
            margin: 35px auto;
            padding: 20px;
        }
        .management-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .management-card {
            background: #fff;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
            margin-bottom: 22px;
        }
        .management-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .management-filters input,
        .management-filters select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .table-scroll { overflow-x: auto; }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .student-table th, .student-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .student-table th {
            background: #1f3a2e;
            color: white;
        }
        .action-link {
            display: inline-block;
            margin: 2px;
            padding: 6px 10px;
            background: #1f3a2e;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .message {
            background: #e4f4e8;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-decoration: none;
        }
        .pagination .current {
            background: #1f3a2e;
            color: white;
        }
    </style>
</head>
<body>
<header>
    <div class="management-page management-heading">
        <h1>Student Management</h1>
        <a class="action-link" href="admin-dashboard.php">Back to Dashboard</a>
    </div>
</header>

<main class="management-page">
    <?php if ($message !== ""): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <section class="management-card">
        <h2>Search and Filter Students</h2>
        <form class="management-filters" method="get" action="student-management.php">
            <input
                type="search"
                name="search"
                placeholder="Search name or email"
                value="<?= h($search) ?>"
            >

            <select name="course">
                <option value="">All courses</option>
                <option value="cse" <?= $course === "cse" ? "selected" : "" ?>>CSE</option>
                <option value="it" <?= $course === "it" ? "selected" : "" ?>>IT</option>
                <option value="ce" <?= $course === "ce" ? "selected" : "" ?>>CE</option>
            </select>

            <button type="submit">Search</button>
            <a class="action-link" href="student-management.php">Clear</a>
        </form>
    </section>

    <section class="management-card">
        <div class="management-heading">
            <h2>Student Records</h2>
            <a class="action-link" href="add-student.php">+ Add Student</a>
        </div>

        <p>Total matching records: <strong><?= $total ?></strong></p>

        <div class="table-scroll">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>College ID</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($student = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= h($student["student_id"]) ?></td>
                            <td><?= h($student["name"]) ?></td>
                            <td><?= h($student["email"]) ?></td>
                            <td><?= h($student["college_student_id"]) ?></td>
                            <td><?= h($student["mobile"]) ?></td>
                            <td><?= h(strtoupper($student["program"])) ?></td>
                            <td><?= h($student["study_year"]) ?></td>
                            <td><?= h($student["role"]) ?></td>
                            <td>
                                <a class="action-link" href="edit-student.php?id=<?= (int)$student["student_id"] ?>">Edit</a>
                                <a class="action-link" href="delete-student.php?id=<?= (int)$student["student_id"] ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9">No student records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="pagination" aria-label="Student pages">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $query = http_build_query([
                    "search" => $search,
                    "course" => $course,
                    "page" => $i
                ]);
                ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="student-management.php?<?= h($query) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </nav>
    </section>
</main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>

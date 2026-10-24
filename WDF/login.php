
<?php
session_start();

require_once "db.php";

// Generate a CSRF token for the login form.
if (empty($_SESSION["login_csrf"])) {
    $_SESSION["login_csrf"] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["login_csrf"], $token)
    ) {
        $error = "Security check failed. Please reload the page.";
    } else {

        $login = trim($_POST["student_id"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($login === "" || $password === "") {
            $error = "Enter your student ID/email and password.";
        } else {
            try {
                // Allow login using either college student ID or email.
                $stmt = $pdo->prepare(
                    "SELECT student_id, name, email, college_student_id,
                            password_hash, role
                     FROM students
                     WHERE email = ? OR college_student_id = ?
                     LIMIT 1"
                );

                $stmt->execute([$login, $login]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (
                    $user &&
                    !empty($user["password_hash"]) &&
                    password_verify($password, $user["password_hash"])
                ) {
                    // Prevent session fixation.
                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["student_id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["role"] = $user["role"];
                    $_SESSION["last_activity"] = time();

                    // Update last successful login.
                    $update = $pdo->prepare(
                        "UPDATE students
                         SET last_login = NOW()
                         WHERE student_id = ?"
                    );
                    $update->execute([$user["student_id"]]);

                    // Replace the login token after successful login.
                    unset($_SESSION["login_csrf"]);

                    if ($user["role"] === "admin") {
                        header("Location: admin-dashboard.php");
                    } else {
                        header("Location: student-dashboard.php");
                    }
                    exit;

                } else {
                    $error = "Invalid student ID/email or password.";
                }

            } catch (PDOException $e) {
                error_log("Login database error: " . $e->getMessage());
                $error = "Login is temporarily unavailable. Try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Northbridge University</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

<header>
    <div>
        <img src="logo.png" width="10%" alt="Northbridge University Logo">
        <h1>Northbridge University</h1>
    </div>

    <nav aria-label="Main navigation">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li>
                <details>
                    <summary>Programs</summary>
                    <ul>
                        <li><a href="cse.html">B.Tech CSE</a></li>
                        <li><a href="it.html">B.Tech IT</a></li>
                        <li><a href="ce.html">B.Tech CE</a></li>
                    </ul>
                </details>
            </li>
            <li><a href="faculty.html">Faculty</a></li>
            <li>
                <details>
                    <summary>Campus Life</summary>
                    <ul>
                        <li><a href="clubs.html">Clubs &amp; Societies</a></li>
                        <li><a href="facilities.html">Facilities</a></li>
                    </ul>
                </details>
            </li>
            <li>
                <details>
                    <summary>Student Corner</summary>
                    <ul>
                        <li><a href="https://sites.google.com/view/tatkalgyan">Student Resources</a></li>
                        <li><a href="https://drive.google.com/drive/folders/10-XqFw6ISXnDeyajK8oB3YIIj6NfrIyO">Downloads</a></li>
                        <li><a href="https://drive.google.com/drive/folders/18NAT1fcOe5nG25ddphqluyIs9CLvWQ5Q">Syllabus</a></li>
                    </ul>
                </details>
            </li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="faq.html">FAQ</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

    <button id="themeToggle" type="button">Dark Mode</button>
</header>

<main>
    <section class="hero">
        <h2>Login</h2>
        <p>Login to access your university account.</p>

        <?php if ($error !== ""): ?>
            <p role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["login_csrf"], ENT_QUOTES, "UTF-8") ?>"
            >

            <div>
                <label for="student_id">Student ID / Email</label>
                <input
                    type="text"
                    id="student_id"
                    name="student_id"
                    autocomplete="username"
                    required
                >
            </div>

            <br>

            <div>
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <br>

            <button type="submit">Login</button>
        </form>

        <p>
            Don't have an account?
            <a href="register.php">Create an account</a>
        </p>
    </section>
</main>

<footer>
    <h2>Northbridge University</h2>
    <p>Learn. Build. Participate. Grow.</p>
    <p>&copy; 2026 Northbridge University. All rights reserved.</p>
</footer>

</body>
</html>

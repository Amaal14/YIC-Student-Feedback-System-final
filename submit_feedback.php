
<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$error = "";

/* CSRF token */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$categoriesStmt = $conn->query("SELECT * FROM categories ORDER BY category_name");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid request. Please try again.";
    } else {
        $title = trim($_POST["title"]);
        $category_id = $_POST["category_id"];
        $message = trim($_POST["message"]);
        $user_id = $_SESSION["user_id"];

        /* Input validation */
        if (empty($title) || empty($category_id) || empty($message)) {
            $error = "All fields are required.";
        } elseif (strlen($title) < 5) {
            $error = "Title must be at least 5 characters.";
        } elseif (strlen($message) < 15) {
            $error = "Message must be at least 15 characters.";
        } elseif (!filter_var($category_id, FILTER_VALIDATE_INT)) {
            $error = "Invalid category selected.";
        } else {
            /* Check category exists */
            $checkCategory = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
            $checkCategory->execute([$category_id]);

            if ($checkCategory->rowCount() === 0) {
                $error = "Invalid category selected.";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO feedback (user_id, category_id, title, message, status)
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$user_id, $category_id, $title, $message]);

                header("Location: view_feedback.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Feedback</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-content">
    <div class="logo-box">
      <img src="logo.png.png" alt="YIC Logo" class="logo-img">
      <div class="brand-text">
        <h2>YIC Feedback System</h2>
        <p class="header-subtitle">Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></p>
      </div>
    </div>

    <nav class="header-nav">
      <a href="student_dashboard.php">Dashboard</a>
      <a href="submit_feedback.php" class="active">Submit Feedback</a>
      <a href="view_feedback.php">My Feedback</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="card">
    <h2>Submit New Feedback</h2>
    <p>Please provide clear details to help us improve services and campus experience.</p>

    <?php if (!empty($error)): ?>
      <p style="color:red; text-align:center;">
        <?php echo htmlspecialchars($error); ?>
      </p>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

      <label for="feedbackTitle">Title</label>
      <input type="text" id="feedbackTitle" name="title" placeholder="Example: Slow Wi-Fi in Building A" required>

      <label for="feedbackCategory">Category</label>
      <select id="feedbackCategory" name="category_id" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo htmlspecialchars($cat["category_id"]); ?>">
            <?php echo htmlspecialchars($cat["category_name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="feedbackMessage">Message</label>
      <textarea id="feedbackMessage" name="message" placeholder="Write your feedback here..." required></textarea>

      <button type="submit">Submit Feedback</button>
    </form>
  </section>
</main>

<footer class="bottom-footer">
  <div class="container footer-content">
    <p>&copy; 2026 YIC Student Feedback System</p>
  </div>
</footer>

</body>
</html>
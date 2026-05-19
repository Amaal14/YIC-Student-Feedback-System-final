
<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";

/* CSRF token */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

/* Delete feedback securely */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_feedback"])) {

    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid request. Please try again.";
    } else {
        $feedback_id = $_POST["feedback_id"];

        if (!filter_var($feedback_id, FILTER_VALIDATE_INT)) {
            $error = "Invalid feedback ID.";
        } else {
            $check = $conn->prepare("
                SELECT feedback_id 
                FROM feedback 
                WHERE feedback_id = ? AND user_id = ? AND status = 'pending'
            ");
            $check->execute([$feedback_id, $user_id]);

            if ($check->rowCount() > 0) {
                $delete = $conn->prepare("DELETE FROM feedback WHERE feedback_id = ?");
                $delete->execute([$feedback_id]);

                header("Location: view_feedback.php");
                exit();
            } else {
                $error = "Only pending feedback can be deleted.";
            }
        }
    }
}

$stmt = $conn->prepare("
    SELECT 
        f.feedback_id,
        f.title,
        f.message,
        f.status,
        f.created_at,
        c.category_name,
        r.response_text
    FROM feedback f
    JOIN categories c ON f.category_id = c.category_id
    LEFT JOIN responses r ON f.feedback_id = r.feedback_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$user_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Feedback</title>
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
      <a href="submit_feedback.php">Submit Feedback</a>
      <a href="view_feedback.php" class="active">My Feedback</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="card">
    <h2>My Feedback</h2>
    <p>You can delete feedback only before it is reviewed.</p>

    <?php if (!empty($error)): ?>
      <p style="color:red; text-align:center;">
        <?php echo htmlspecialchars($error); ?>
      </p>
    <?php endif; ?>
  </section>

  <?php if (count($feedbacks) === 0): ?>
    <div class="card">
      <p>No feedback submitted yet.</p>
    </div>
  <?php else: ?>
    <?php foreach ($feedbacks as $item): ?>
      <div class="card">
        <div class="card-top">
          <h3><?php echo htmlspecialchars($item["title"]); ?></h3>
          <span class="status-badge <?php echo htmlspecialchars($item["status"]); ?>">
            <?php echo htmlspecialchars(ucfirst($item["status"])); ?>
          </span>
        </div>

        <p><strong>Category:</strong> <?php echo htmlspecialchars($item["category_name"]); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($item["created_at"]); ?></p>
        <p><strong>Message:</strong> <?php echo htmlspecialchars($item["message"]); ?></p>
        <p><strong>Admin Response:</strong> 
          <?php echo !empty($item["response_text"]) ? htmlspecialchars($item["response_text"]) : "Waiting for response..."; ?>
        </p>

        <?php if ($item["status"] === "pending"): ?>
          <form method="POST" class="action-row" onsubmit="return confirm('Delete this feedback?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
            <input type="hidden" name="feedback_id" value="<?php echo htmlspecialchars($item["feedback_id"]); ?>">
            <button type="submit" name="delete_feedback" class="danger-btn">Delete</button>
          </form>
        <?php else: ?>
          <p class="small-text">You can delete only pending feedback.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<footer class="bottom-footer">
  <div class="container footer-content">
    <p>&copy; 2026 YIC Student Feedback System</p>
  </div>
</footer>

</body>
</html>
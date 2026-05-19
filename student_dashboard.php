
<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$totalStmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$total = (int) $totalStmt->fetchColumn();

$pendingStmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = ? AND status = 'pending'");
$pendingStmt->execute([$user_id]);
$pending = (int) $pendingStmt->fetchColumn();

$reviewedStmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = ? AND status = 'reviewed'");
$reviewedStmt->execute([$user_id]);
$reviewed = (int) $reviewedStmt->fetchColumn();

$resolvedStmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = ? AND status = 'resolved'");
$resolvedStmt->execute([$user_id]);
$resolved = (int) $resolvedStmt->fetchColumn();

$recentStmt = $conn->prepare("
    SELECT f.title, f.status, c.category_name
    FROM feedback f
    JOIN categories c ON f.category_id = c.category_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT 3
");
$recentStmt->execute([$user_id]);
$recentFeedback = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-content">
    <div class="logo-box">
      <img src="logo.png.png" alt="YIC Logo" class="logo-img">
      <div class="brand-text">
        <h2>YIC Feedback System</h2>
        <p class="header-subtitle">
          Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </p>
      </div>
    </div>

    <nav class="header-nav">
      <a href="student_dashboard.php" class="active">Dashboard</a>
      <a href="submit_feedback.php">Submit Feedback</a>
      <a href="view_feedback.php">My Feedback</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="card">
    <h2>Student Dashboard</h2>
    <p>Track your feedback submissions and check the latest updates.</p>
  </section>

  <section class="dashboard-grid four-cols">
    <div class="stat-card">
      <h3>Total Feedback</h3>
      <p><?php echo $total; ?></p>
    </div>

    <div class="stat-card">
      <h3>Pending</h3>
      <p><?php echo $pending; ?></p>
    </div>

    <div class="stat-card">
      <h3>Reviewed</h3>
      <p><?php echo $reviewed; ?></p>
    </div>

    <div class="stat-card">
      <h3>Resolved</h3>
      <p><?php echo $resolved; ?></p>
    </div>
  </section>

  <section class="card">
    <h3>Recent Feedback</h3>

    <?php if (count($recentFeedback) === 0): ?>
      <p>No feedback submitted yet.</p>
    <?php else: ?>
      <?php foreach ($recentFeedback as $item): ?>
        <div class="mini-card">
          <div>
            <strong><?php echo htmlspecialchars($item["title"]); ?></strong>
            <p class="small-text"><?php echo htmlspecialchars($item["category_name"]); ?></p>
          </div>

          <span class="status-badge <?php echo htmlspecialchars($item["status"]); ?>">
            <?php echo htmlspecialchars(ucfirst($item["status"])); ?>
          </span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<footer class="bottom-footer">
  <div class="container footer-content">
    <p>&copy; 2026 YIC Student Feedback System</p>
  </div>
</footer>

</body>
</html>
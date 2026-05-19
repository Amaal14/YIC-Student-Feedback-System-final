
<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$total = (int) $conn->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$pending = (int) $conn->query("SELECT COUNT(*) FROM feedback WHERE status = 'pending'")->fetchColumn();
$reviewed = (int) $conn->query("SELECT COUNT(*) FROM feedback WHERE status = 'reviewed'")->fetchColumn();
$resolved = (int) $conn->query("SELECT COUNT(*) FROM feedback WHERE status = 'resolved'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-content">
    <div class="logo-box">
      <img src="logo.png.png" alt="YIC Logo" class="logo-img">
      <div class="brand-text">
        <h2>YIC Admin Panel</h2>
        <p class="header-subtitle">
          Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </p>
      </div>
    </div>

    <nav class="header-nav">
      <a href="admin_dashboard.php" class="active">Dashboard</a>
      <a href="admin_feedbacks.php">Manage Feedback</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="card">
    <h2>Admin Dashboard</h2>
    <p>Review overall feedback activity and manage student submissions.</p>
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
</main>

<footer class="bottom-footer">
  <div class="container footer-content">
    <p>&copy; 2026 YIC Student Feedback System</p>
  </div>
</footer>

</body>
</html>
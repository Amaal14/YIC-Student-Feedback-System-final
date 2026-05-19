
<?php
session_start();
require_once "config/db.php";

/* Session protection */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$error = "";

/* CSRF token */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

/* UPDATE FEEDBACK */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_feedback"])) {

    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid request.";
    } else {

        $feedback_id = $_POST["feedback_id"];
        $status = trim($_POST["status"]);
        $response_text = trim($_POST["response_text"]);
        $admin_id = $_SESSION["user_id"];

        /* Validation */
        $allowedStatus = ["pending", "reviewed", "resolved"];

        if (!filter_var($feedback_id, FILTER_VALIDATE_INT)) {
            $error = "Invalid feedback ID.";
        } elseif (!in_array($status, $allowedStatus)) {
            $error = "Invalid status.";
        } elseif (strlen($response_text) > 1000) {
            $error = "Response is too long.";
        } else {

            $update = $conn->prepare("
                UPDATE feedback 
                SET status = ? 
                WHERE feedback_id = ?
            ");
            $update->execute([$status, $feedback_id]);

            $check = $conn->prepare("
                SELECT response_id 
                FROM responses 
                WHERE feedback_id = ?
            ");
            $check->execute([$feedback_id]);

            if ($check->rowCount() > 0) {

                $stmt = $conn->prepare("
                    UPDATE responses 
                    SET response_text = ?, admin_id = ? 
                    WHERE feedback_id = ?
                ");

                $stmt->execute([
                    $response_text,
                    $admin_id,
                    $feedback_id
                ]);

            } else {

                if (!empty($response_text)) {

                    $stmt = $conn->prepare("
                        INSERT INTO responses 
                        (feedback_id, admin_id, response_text) 
                        VALUES (?, ?, ?)
                    ");

                    $stmt->execute([
                        $feedback_id,
                        $admin_id,
                        $response_text
                    ]);
                }
            }

            header("Location: admin_feedbacks.php");
            exit();
        }
    }
}

/* DELETE FEEDBACK */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_feedback"])) {

    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid request.";
    } else {

        $feedback_id = $_POST["feedback_id"];

        if (!filter_var($feedback_id, FILTER_VALIDATE_INT)) {
            $error = "Invalid feedback ID.";
        } else {

            $delete = $conn->prepare("
                DELETE FROM feedback 
                WHERE feedback_id = ?
            ");

            $delete->execute([$feedback_id]);

            header("Location: admin_feedbacks.php");
            exit();
        }
    }
}

/* SEARCH */
$keyword = trim($_GET["keyword"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

$sql = "
    SELECT 
        f.feedback_id,
        f.title,
        f.message,
        f.status,
        f.created_at,
        u.name,
        u.email,
        c.category_name,
        r.response_text
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    JOIN categories c ON f.category_id = c.category_id
    LEFT JOIN responses r ON f.feedback_id = r.feedback_id
    WHERE 1
";

$params = [];

if (!empty($keyword)) {

    $sql .= "
        AND (
            f.title LIKE ?
            OR f.message LIKE ?
            OR u.name LIKE ?
            OR c.category_name LIKE ?
        )
    ";

    $search = "%$keyword%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if (!empty($statusFilter)) {

    $allowedStatus = ["pending", "reviewed", "resolved"];

    if (in_array($statusFilter, $allowedStatus)) {
        $sql .= " AND f.status = ?";
        $params[] = $statusFilter;
    }
}

$sql .= " ORDER BY f.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Feedback</title>
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
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="admin_feedbacks.php" class="active">Manage Feedback</a>
      <a href="logout.php">Logout</a>
    </nav>

  </div>
</header>

<main class="container">

  <section class="card">
    <h2>Manage Feedback</h2>
    <p>Search, filter, update status, reply, or delete feedback submissions.</p>

    <?php if (!empty($error)): ?>
      <p style="color:red; text-align:center;">
        <?php echo htmlspecialchars($error); ?>
      </p>
    <?php endif; ?>
  </section>

  <section class="card">

    <form method="GET" class="search-row">

      <input
        type="text"
        name="keyword"
        placeholder="Search by title, student, category, or message"
        value="<?php echo htmlspecialchars($keyword); ?>"
      >

      <select name="status">
        <option value="">All Status</option>

        <option value="pending"
          <?php if ($statusFilter === "pending") echo "selected"; ?>>
          Pending
        </option>

        <option value="reviewed"
          <?php if ($statusFilter === "reviewed") echo "selected"; ?>>
          Reviewed
        </option>

        <option value="resolved"
          <?php if ($statusFilter === "resolved") echo "selected"; ?>>
          Resolved
        </option>
      </select>

      <button type="submit">Search</button>

    </form>

  </section>

  <?php if (count($feedbacks) === 0): ?>

    <div class="card">
      <p>No feedback available.</p>
    </div>

  <?php else: ?>

    <?php foreach ($feedbacks as $item): ?>

      <div class="card">

        <div class="card-top">

          <h3>
            <?php echo htmlspecialchars($item["title"]); ?>
          </h3>

          <span class="status-badge <?php echo htmlspecialchars($item["status"]); ?>">
            <?php echo htmlspecialchars(ucfirst($item["status"])); ?>
          </span>

        </div>

        <p>
          <strong>Student:</strong>
          <?php echo htmlspecialchars($item["name"]); ?>
        </p>

        <p>
          <strong>Email:</strong>
          <?php echo htmlspecialchars($item["email"]); ?>
        </p>

        <p>
          <strong>Category:</strong>
          <?php echo htmlspecialchars($item["category_name"]); ?>
        </p>

        <p>
          <strong>Date:</strong>
          <?php echo htmlspecialchars($item["created_at"]); ?>
        </p>

        <p>
          <strong>Message:</strong>
          <?php echo htmlspecialchars($item["message"]); ?>
        </p>

        <form method="POST">

          <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
          >

          <input
            type="hidden"
            name="feedback_id"
            value="<?php echo htmlspecialchars($item["feedback_id"]); ?>"
          >

          <label>Update Status</label>

          <select name="status">

            <option value="pending"
              <?php if ($item["status"] === "pending") echo "selected"; ?>>
              Pending
            </option>

            <option value="reviewed"
              <?php if ($item["status"] === "reviewed") echo "selected"; ?>>
              Reviewed
            </option>

            <option value="resolved"
              <?php if ($item["status"] === "resolved") echo "selected"; ?>>
              Resolved
            </option>

          </select>

          <label>Admin Response</label>

          <textarea
            name="response_text"
            placeholder="Write response..."
          ><?php echo htmlspecialchars($item["response_text"] ?? ""); ?></textarea>

          <div class="action-row">

            <button type="submit" name="update_feedback">
              Save Update
            </button>

          </div>

        </form>

        <form
          method="POST"
          onsubmit="return confirm('Delete this feedback?');"
        >

          <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
          >

          <input
            type="hidden"
            name="feedback_id"
            value="<?php echo htmlspecialchars($item["feedback_id"]); ?>"
          >

          <button
            type="submit"
            name="delete_feedback"
            class="danger-btn"
          >
            Delete
          </button>

        </form>

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
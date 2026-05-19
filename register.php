
<?php
session_start();
require_once "config/db.php";

$error = "";
$success = "";

/* CSRF token */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid request. Please try again.";
    } else {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        $confirmPassword = trim($_POST["confirm_password"]);

        /* Input validation */
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = "All fields are required.";
        } elseif (strlen($name) < 3) {
            $error = "Name must be at least 3 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $error = "This email is already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $hashedPassword]);

                $success = "Account created successfully. You can login now.";
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
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
  <main class="center-box auth-wrapper">
    <section class="card auth-card">
      <img src="logo.png.png" alt="YIC Logo" class="auth-logo">

      <h2>Create Account</h2>
      <p class="form-help">New accounts are registered as student accounts</p>

      <?php if (!empty($error)): ?>
        <p style="color:red; text-align:center;">
          <?php echo htmlspecialchars($error); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <p style="color:green; text-align:center;">
          <?php echo htmlspecialchars($success); ?>
        </p>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

        <label for="registerName">Full Name</label>
        <input type="text" id="registerName" name="name" placeholder="Enter your full name" required>

        <label for="registerEmail">Email</label>
        <input type="email" id="registerEmail" name="email" placeholder="Enter your email" required>

        <label for="registerPassword">Password</label>
        <input type="password" id="registerPassword" name="password" placeholder="Enter your password" required>

        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Repeat your password" required>

        <button type="submit">Create Account</button>
      </form>

      <p>Already have an account? <a href="login.php">Login here</a></p>
    </section>
  </main>
</body>
</html>
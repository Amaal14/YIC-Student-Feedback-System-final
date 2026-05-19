
<?php
session_start();
require_once "config/db.php";

$error = "";

/* CSRF token */
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        $error = "Invalid request. Please try again.";
    } else {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        /* Input validation */
        if (empty($email) || empty($password)) {
            $error = "Email and password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            /*
              Supports:
              1. Hashed passwords for security
              2. Old plain passwords from sample SQL
            */
            $passwordValid = false;

            if ($user) {
                if (password_verify($password, $user["password"])) {
                    $passwordValid = true;
                } elseif ($user["password"] === $password) {
                    $passwordValid = true;

                    /* Upgrade old plain password to hashed password */
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $update->execute([$hashedPassword, $user["user_id"]]);
                }
            }

            if ($user && $passwordValid) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "admin") {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
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
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
  <main class="center-box auth-wrapper">
    <section class="card auth-card">
      <img src="logo.png.png" alt="YIC Logo" class="auth-logo">

      <h2>Login</h2>
      <p class="form-help">Enter your email and password to continue</p>

      <?php if (!empty($error)): ?>
        <p style="color:red; text-align:center;">
          <?php echo htmlspecialchars($error); ?>
        </p>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

        <label for="loginEmail">Email</label>
        <input type="email" id="loginEmail" name="email" placeholder="Enter your email" required>

        <label for="loginPassword">Password</label>
        <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>

        <button type="submit">Login</button>
      </form>

      <p>Do not have an account? <a href="register.php">Register here</a></p>
    </section>
  </main>
</body>
</html>
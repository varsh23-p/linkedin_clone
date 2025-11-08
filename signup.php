<?php
session_start();
require 'db_connect.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (strlen($password) < 6) $err = "Password must be at least 6 characters.";
    elseif ($password !== $confirm) $err = "Passwords do not match.";
    else {
        // check existing
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $err = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $name, $email, $hash);
            if ($ins->execute()) {
                header("Location: index.php");
                exit;
            } else $err = "Error creating account.";
            $ins->close();
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Signup - LinkSpace</title>
  <link rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/main.js" defer></script>

</head>
<body>
  <div class="container small">
    <h2>Sign Up</h2>
    <?php if($err) echo "<div class='error'>$err</div>"; ?>
    <form method="post" action="">
      <input type="text" name="name" placeholder="Full name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm" placeholder="Confirm Password" required>
      <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="index.php">Login</a></p>
  </div>
</body>
</html>

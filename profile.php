<?php
session_start();
require 'db_connect.php';
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$profile_id) { header("Location: home.php"); exit; }

$uStmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
$uStmt->bind_param("i", $profile_id);
$uStmt->execute();
$user = $uStmt->get_result()->fetch_assoc();
$uStmt->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($user['name']); ?> - Profile</title>
  <link rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/main.js" defer></script>

</head>
<body>
  <div class="topbar">
    <div class="container">
      <div class="nav"><a href="home.php">Feed</a></div>
    </div>
	<div class="logo">
  <img src="assets/images/logo.png" alt="Logo">
  <a href="home.php">LinkSpace</a>
</div>

  </div>

  <div class="container">
    <section class="card">
      <h2><?php echo htmlspecialchars($user['name']); ?></h2>
      <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
      <p>Joined: <?php echo $user['created_at']; ?></p>
    </section>

    <section>
      <h3>Posts by <?php echo htmlspecialchars($user['name']); ?></h3>
      <?php
      $pStmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
      $pStmt->bind_param("i", $profile_id);
      $pStmt->execute();
      $res = $pStmt->get_result();
      while ($p = $res->fetch_assoc()):
      ?>
        <article class="post card">
          <div class="post-head"><strong><?php echo htmlspecialchars($user['name']); ?></strong> <small><?php echo $p['created_at']; ?></small></div>
          <p><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
          <?php if ($p['image']): ?><div class="post-image"><img src="<?php echo htmlspecialchars($p['image']); ?>" alt="post image"></div><?php endif; ?>
        </article>
      <?php endwhile; $pStmt->close(); ?>
    </section>
  </div>
</body>
</html>

<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$uid = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$err = '';

if (!$id) { header("Location: home.php"); exit; }

// verify owner
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) { $stmt->close(); header("Location: home.php"); exit; }
$post = $res->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    // optional image replace not implemented here for simplicity; could extend similarly to home.php
    $upd = $conn->prepare("UPDATE posts SET content = ? WHERE id = ?");
    $upd->bind_param("si", $content, $id);
    if ($upd->execute()) {
        header("Location: home.php");
        exit;
    } else $err = "Update failed.";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Post</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container small">
    <h2>Edit Post</h2>
    <?php if($err) echo "<div class='error'>$err</div>"; ?>
    <form method="post">
      <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
      <div class="row">
        <button type="submit">Update</button>
        <a href="home.php" class="btn muted">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>

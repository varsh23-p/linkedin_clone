<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require 'db_connect.php';

$uid = $_SESSION['user_id'];
$name = $_SESSION['name'];
$msg = '';

// Handle new post (same page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = 'uploads/';
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $filename;
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) $msg = "Image type not allowed.";
        else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else $msg = "Failed uploading image.";
        }
    }

    if ($content !== '') {
        $ins = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        $ins->bind_param("iss", $uid, $content, $imagePath);
        $ins->execute();
        $ins->close();
        header("Location: home.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Feed - LinkedIn Clone</title>
  <link rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/main.js" defer></script>
</head>
<body>
  <div class="topbar">
    <div class="container">
      <div class="nav">
        <span>Welcome, <a href="profile.php?id=<?php echo $uid; ?>"><?php echo htmlspecialchars($name); ?></a></span>
        <a href="logout.php" class="btn small">Logout</a>
      </div>
    </div>
	<div class="logo">
  <img src="assets/images/logo.png" alt="Logo">
  <a href="home.php">LinkSpace</a>
</div>

  </div>

  <div class="container">
    <?php if($msg) echo "<div class='error'>$msg</div>"; ?>

    <section class="card">
      <h3>Create a post</h3>
      <form method="post" enctype="multipart/form-data">
        <textarea name="content" placeholder="What's on your mind?" required></textarea>
        <div class="row">
          <input type="file" name="image" accept="image/*">
          <button type="submit">Post</button>
        </div>
      </form>
    </section>

    <section>
      <h3>Feed</h3>
      <?php
      $posts = $conn->query("
        SELECT posts.*, users.name AS user_name
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC
      ");
      while ($p = $posts->fetch_assoc()):
        // likes count and if current user liked it
        $pid = (int)$p['id'];
        $likeRes = $conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $pid");
        $likes = $likeRes->fetch_assoc()['cnt'];
        $likedRes = $conn->query("SELECT id FROM likes WHERE post_id = $pid AND user_id = $uid");
        $isLiked = $likedRes->num_rows > 0;
      ?>
      <article class="post card">
        <div class="post-head">
          <a href="profile.php?id=<?php echo $p['user_id']; ?>"><strong><?php echo htmlspecialchars($p['user_name']); ?></strong></a>
          <small><?php echo $p['created_at']; ?></small>
        </div>

        <p><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
        <?php if (!empty($p['image'])): ?>
          <div class="post-image"><img src="<?php echo htmlspecialchars($p['image']); ?>" alt="post image"></div>
        <?php endif; ?>

        <div class="post-actions">
          <form method="post" action="like_post.php" class="inline-form">
            <input type="hidden" name="post_id" value="<?php echo $pid; ?>">
            <button type="submit" class="link-btn"><?php echo $isLiked? 'Unlike' : 'Like'; ?></button>
            <span class="muted"><?php echo $likes; ?> like<?php echo ($likes!=1)?'s':''; ?></span>
          </form>

          <?php if ($p['user_id'] == $uid): ?>
            <a href="edit_post.php?id=<?php echo $pid; ?>" class="link-btn">Edit</a>
            <a href="delete_post.php?id=<?php echo $pid; ?>" class="link-btn" onclick="return confirm('Delete this post?')">Delete</a>
          <?php endif; ?>
        </div>

        <div class="comments">
          <?php
          $cRes = $conn->query("
            SELECT comments.*, users.name
            FROM comments JOIN users ON comments.user_id = users.id
            WHERE post_id = $pid
            ORDER BY comments.created_at ASC
          ");
          while ($c = $cRes->fetch_assoc()):
          ?>
            <div class="comment"><strong><?php echo htmlspecialchars($c['name']); ?>:</strong> <?php echo htmlspecialchars($c['comment']); ?> <small class="muted"><?php echo $c['created_at']; ?></small></div>
          <?php endwhile; ?>
          <form method="post" action="comment_post.php" class="comment-form">
            <input type="hidden" name="post_id" value="<?php echo $pid; ?>">
            <input type="text" name="comment" placeholder="Write a comment" required>
            <button type="submit">Comment</button>
          </form>
        </div>
      </article>
      <?php endwhile; ?>
    </section>
  </div>
</body>
</html>

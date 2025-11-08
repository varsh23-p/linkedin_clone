<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) exit;
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = (int)$_POST['post_id'];
    $comment = trim($_POST['comment']);
    if ($post_id && $comment !== '') {
        $ins = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $ins->bind_param("iis", $post_id, $uid, $comment);
        $ins->execute();
        $ins->close();
    }
}
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

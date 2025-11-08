<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) exit;
$uid = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if ($post_id) {
    // try delete (if exists) else insert
    $chk = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $chk->bind_param("ii", $uid, $post_id);
    $chk->execute();
    $res = $chk->get_result();

    if ($res->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $del->bind_param("ii", $uid, $post_id);
        $del->execute();
        $del->close();
    } else {
        $ins = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $ins->bind_param("ii", $uid, $post_id);
        $ins->execute();
        $ins->close();
    }
    $chk->close();
}
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

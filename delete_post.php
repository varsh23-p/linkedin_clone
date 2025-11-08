<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) exit;
$uid = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    // only owner can delete
    $del = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $id, $uid);
    $del->execute();
    $del->close();
}
header("Location: home.php");
exit;

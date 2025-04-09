<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$content = $_POST['content'];
$image_path = null;

if (!empty($_FILES['image']['name'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
    }
}

$stmt = $pdo->prepare("INSERT INTO Questions (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, $title, $content, $image_path]);

header("Location: community.php");
exit;
?>

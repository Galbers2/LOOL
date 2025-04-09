<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$host = 'localhost';
$db   = 'LOOL';
$user = 'root';
$pass = 'root'; // ✅ MAMP default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username']);
$email = trim($data['email']);
$password = $data['password'];

if (!$username || !$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Username or email already exists.']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$success = $stmt->execute([$username, $email, $password_hash]);

if ($success) {
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    session_regenerate_id(true);
    echo json_encode(['success' => true, 'redirect' => 'community.php']);
} else {
    echo json_encode(['success' => false, 'error' => 'Signup failed.']);
}
?>

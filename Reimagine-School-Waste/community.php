<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Community - Lunch Out of Landfills</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .user-greeting {
      margin-left: auto;
      padding-right: 20px;
      font-weight: bold;
    }
    .post-form, .question, .response {
      max-width: 600px;
      margin: 20px auto;
      padding: 20px;
      border-radius: 10px;
      background: #f5f5f5;
    }
    .question img {
      max-width: 100%;
      height: auto;
      margin-top: 10px;
    }
    .response-form {
      margin-top: 10px;
    }
    .response {
      background-color: #e9e9e9;
      margin-top: 10px;
      padding: 10px;
    }
  </style>
</head>
<body>

<nav>
  <div class="logo">
    <img src="images/LOOL.png" alt="LOOL Logo" onclick="window.location.href='index.html';">
  </div>
  <ul>
    <li><a href="index.html">Home</a></li>
    <li><a href="tool_kit.html">Tool Kit</a></li>
    <li><a href="How_to.html">Getting Started</a></li>
    <li><a href="data.html">Data</a></li>
    <li><a href="Contact.html">Connect</a></li>
    <li><a href="community.php">Community Forms</a></li>
  </ul>
  <div class="user-greeting">
    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
  </div>
</nav>

<!-- ✅ Post a new question -->
<div class="post-form">
  <h2>Ask a Question or Share an Idea</h2>
  <form action="post_question.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required><br><br>
    <textarea name="content" placeholder="Write your question or idea" rows="4" required></textarea><br><br>
    <input type="file" name="image"><br><br>
    <button type="submit">Post</button>
  </form>
</div>

<?php
// Get questions with user names
$stmt = $pdo->query("SELECT q.*, u.username FROM Questions q JOIN users u ON q.user_id = u.user_id ORDER BY q.created_at DESC");
$questions = $stmt->fetchAll();

foreach ($questions as $question):
?>
  <div class="question">
    <h3><?php echo htmlspecialchars($question['title']); ?></h3>
    <p><strong><?php echo htmlspecialchars($question['username']); ?></strong> on <?php echo $question['created_at']; ?></p>
    <p><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
    <?php if (!empty($question['image_path'])): ?>
      <img src="<?php echo htmlspecialchars($question['image_path']); ?>" alt="Question Image">
    <?php endif; ?>

    <!-- ✅ Response form -->
    <div class="response-form">
      <form action="post_response.php" method="POST">
        <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
        <textarea name="response_text" placeholder="Write a response..." rows="2" required></textarea><br>
        <button type="submit">Respond</button>
      </form>
    </div>

    <!-- ✅ Display responses -->
    <?php
      $stmt2 = $pdo->prepare("SELECT r.*, u.username FROM Responses r JOIN users u ON r.user_id = u.user_id WHERE r.question_id = ? ORDER BY r.created_at ASC");
      $stmt2->execute([$question['question_id']]);
      $responses = $stmt2->fetchAll();
      foreach ($responses as $response):
    ?>
      <div class="response">
        <p><strong><?php echo htmlspecialchars($response['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($response['response_text'])); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

</body>
</html>

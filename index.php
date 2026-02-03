<?php
// 接続設定
$host = 'my-database.c7is2wo625to.ap-northeast-1.rds.amazonaws.com';
$user = 'admin';
$pass = 'Toko280612';
$db   = 'my_app_db'; // または作成したDB名

$conn = new mysqli($host, $user, $pass, $db);

// 1. データを1件追加（テスト用）
if (isset($_GET['add'])) {
    $name = "User_" . rand(100, 999);
    $conn->query("INSERT INTO users (username) VALUES ('$name')");
}

// 2. データを取得
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<body>
    <h1>ユーザー一覧</h1>
    <a href="?add=1">ランダムにユーザーを追加</a>
    <ul>
        <?php while($row = $result->fetch_assoc()): ?>
            <li>ID: <?php echo $row['id']; ?> - Name: <?php echo $row['username']; ?> (<?php echo $row['created_at']; ?>)</li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
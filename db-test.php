<?php
$host = 'my-database.c7is2wo625to.ap-northeast-1.rds.amazonaws.com';
$user = 'admin';
$pass = 'Toko280612';
$db   = 'my_app_db'; // 先ほど作成したDB名

$conn = new mysqli($host, $user, $pass, $db);

// データを1件追加する処理（リンクが押された時だけ実行）
if (isset($_GET['add'])) {
    $names = ['Tanaka', 'Sato', 'Suzuki', 'Takahashi', 'Ito'];
    $random_name = $names[array_rand($names)] . "_" . rand(10, 99);
    $conn->query("INSERT INTO users (username) VALUES ('$random_name')");
    header("Location: index.php"); // 送信後、URLを綺麗にする
    exit;
}

// データを取得
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>My AWS App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; margin-top: 50px; }
        .card { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <h2 class="text-center mb-4">👤 ユーザー名簿</h2>
        
        <div class="text-center mb-4">
            <a href="?add=1" class="btn btn-primary">ランダムにユーザーを追加</a>
        </div>

        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>登録日</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo $row['username']; ?></strong></td>
                    <td class="small text-muted"><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($result->num_rows == 0): ?>
            <p class="text-center text-muted">まだデータがありません。</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
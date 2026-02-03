<?php
// --- 1. 接続設定（ご自身の環境に合わせて書き換えてください） ---
$host = 'my-database.c7is2wo625to.ap-northeast-1.rds.amazonaws.com';
$user = 'admin';
$pass = 'Toko280612';
$db   = 'my_app_db';

$conn = new mysqli($host, $user, $pass, $db);

// 接続エラーチェック
if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

// --- 2. データの追加・削除・監視送信の処理 ---

// 削除処理
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id");
    
    // 削除後も現在の件数をCloudWatchに報告する
    update_cloudwatch_metric($conn);
    
    header("Location: index.php");
    exit;
}

// 追加処理
if (isset($_GET['add'])) {
    $names = ['Tanaka', 'Sato', 'Suzuki', 'Takahashi', 'Ito'];
    $random_name = $names[array_rand($names)] . "_" . rand(10, 99);
    $conn->query("INSERT INTO users (username) VALUES ('$random_name')");
    
    // 追加後に現在の件数をCloudWatchに報告する
    update_cloudwatch_metric($conn);
    
    header("Location: index.php");
    exit;
}

// CloudWatchに数値を送るための共通関数
function update_cloudwatch_metric($conn) {
    $count_res = $conn->query("SELECT COUNT(*) as total FROM users");
    $total = $count_res->fetch_assoc()['total'];
    
    // ターミナルで実行するコマンドをPHPから呼び出す
    // 名前空間: MyWebSite, メトリクス名: UserCount として送信
    shell_exec("aws cloudwatch put-metric-data --metric-name UserCount --namespace MyWebSite --value $total --region ap-northeast-1");
}

// --- 3. 表示用データの取得（昇順） ---
$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS 運用監視テスト App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .container { max-width: 700px; margin-top: 50px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        .btn-delete { color: #dc3545; text-decoration: none; font-size: 0.85rem; }
        .btn-delete:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <h2 class="text-center mb-4">👤 ユーザー名簿管理</h2>
        
        <div class="alert alert-info text-center small">
            ユーザーが <strong>6名以上</strong> になると CloudWatch アラームが作動します（設定後）。
        </div>

        <div class="text-center mb-4">
            <a href="?add=1" class="btn btn-primary px-4 shadow-sm">ランダムに追加</a>
        </div>

        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 15%">No.</th>
                    <th style="width: 45%">ユーザー名</th>
                    <th style="width: 25%">登録日時</th>
                    <th style="width: 15%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1; // 表示用の連番
                while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                    <td class="small text-muted"><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="?delete=<?php echo $row['id']; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('このユーザーを削除してもよろしいですか？')">削除</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($result->num_rows == 0): ?>
            <p class="text-center text-muted my-4">現在、登録されているユーザーはいません。</p>
        <?php endif; ?>
    </div>
    
    <p class="text-center text-secondary mt-3 small">
        AWS Region: ap-northeast-1 | CloudWatch: MyWebSite/UserCount
    </p>
</div>

</body>
</html>
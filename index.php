<?php
// --- 1. Basic認証（ここを一番最初に追加します） ---
$auth_user = 'admin';         // ログインに使うユーザー名（自由に変えてください）
$auth_pass = 'pass1234';      // ログインに使うパスワード（自由に変えてください）

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] != $auth_user || 
    $_SERVER['PHP_AUTH_PW'] != $auth_pass) {
    
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    die('ログインが必要です。ブラウザを再起動してやり直してください。');
}

// --- 2. 接続設定 ---
$host = 'my-database.c7is2wo625to.ap-northeast-1.rds.amazonaws.com';
$user = 'admin';
$pass = 'Toko280612';
$db   = 'my_app_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

// --- 3. データの追加・削除・監視送信の処理 ---

// 削除処理
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id");
    update_cloudwatch_metric($conn);
    header("Location: index.php");
    exit;
}

// 追加処理
if (isset($_GET['add'])) {
    $names = ['Tanaka', 'Sato', 'Suzuki', 'Takahashi', 'Ito'];
    $random_name = $names[array_rand($names)] . "_" . rand(10, 99);
    $conn->query("INSERT INTO users (username) VALUES ('$random_name')");
    update_cloudwatch_metric($conn);
    header("Location: index.php");
    exit;
}

// CloudWatch送信関数
function update_cloudwatch_metric($conn) {
    $count_res = $conn->query("SELECT COUNT(*) as total FROM users");
    $total = $count_res->fetch_assoc()['total'];
    // IAMロールを設定済みなので、このコマンドが通ります
    shell_exec("aws cloudwatch put-metric-data --metric-name UserCount --namespace MyWebSite --value $total --region ap-northeast-1");
}

// --- 4. 表示用データの取得 ---
$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS 運用監視 & 認証 App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 700px; margin-top: 50px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header-box { background: #007bff; color: white; border-radius: 15px 15px 0 0; padding: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header-box text-center">
            <h2 class="mb-0">👤 ユーザー管理パネル</h2>
            <p class="small mb-0">認証済みセッション: <?php echo htmlspecialchars($_SERVER['PHP_AUTH_USER']); ?></p>
        </div>
        
        <div class="p-4">
            <div class="alert alert-warning text-center small">
                <strong>監視中:</strong> ユーザーが 6名以上で CloudWatch アラームが発動します。
            </div>

            <div class="text-center mb-4">
                <a href="?add=1" class="btn btn-success px-5 shadow-sm">＋ ユーザーを追加</a>
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
                    $i = 1;
                    while($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                        <td class="small text-muted"><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="?delete=<?php echo $row['id']; ?>" 
                               class="text-danger small" 
                               onclick="return confirm('削除しますか？')">削除</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php if ($result->num_rows == 0): ?>
                <p class="text-center text-muted">データがありません。</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="text-center mt-4 text-secondary small">
        <p>運用ステータス: 認証有効 / CloudWatch連携中</p>
    </div>
</div>

</body>
</html>
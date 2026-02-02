<?php
// 1. エラーを画面に表示する設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. 接続情報（自分のRDSのものに書き換え）
$host = 'database-my2.c7is2wo625to.ap-northeast-1.rds.amazonaws.com'; 
$user = 'admin'; 
$pass = 'Toko280612';
$db   = 'mysql'; // 最初は確実に存在する 'mysql' データベースでテスト

// 3. 接続実行
$conn = new mysqli($host, $user, $pass, $db);

// 4. チェック
if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

echo "<h1>RDS接続成功！</h1>";
echo "PHPからRDSへの通信が正常に確立されました。";

$conn->close();
?>
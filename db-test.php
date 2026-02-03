<?php
$host = 'my-database.c7is2wo625to.ap-northeast-1.rds.amazonaws.com';
$user = 'admin';
$pass = 'Toko280612';

// 1. 接続する（この時点では特定のDBを指定しない）
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

// 2. 「my_app_db」という名前の自分専用の箱を作る命令
$conn->query("CREATE DATABASE IF NOT EXISTS my_app_db");

// 3. 作った箱の中に移動する
$conn->select_db("my_app_db");

// 4. その中に「users（会員名簿）」というテーブルを作る
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<h1>成功しました！</h1>";
    echo "自分専用のデータベース 'my_app_db' とテーブル 'users' が完成しました。";
} else {
    echo "エラー: " . $conn->error;
}

$conn->close();
?>
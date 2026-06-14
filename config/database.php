<?php
$mysqlUrl = getenv('MYSQL_URL') ?: 'mysql://root:@localhost/project_232025';
$parts = parse_url($mysqlUrl);
$host = $parts['host'] ?? 'localhost';
$db   = ltrim($parts['path'] ?? 'project_232025', '/');
$user = $parts['user'] ?? 'root';
$pass = $parts['pass'] ?? '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
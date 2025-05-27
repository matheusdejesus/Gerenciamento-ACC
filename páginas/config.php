<?php
// config.php

// Dados de conexão
$host = '127.0.0.1';
$user = 'root';
$password = '';
$dbname = 'acc';

// Conexão MySQLi
$mysqli = new mysqli($host, $user, $password, $dbname);
if ($mysqli->connect_error) {
    die("Conexão MySQLi falhou: " . $mysqli->connect_error);
}
?>

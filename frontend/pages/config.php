<?php
// config.php

// 1) Dados de conexão ao MySQL (ajuste conforme seu ambiente XAMPP)
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'acc';    // use o nome do BD que você criou

// 2) Cria a conexão
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Falha na conexão MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
}

// 3) (Opcional) Autoload do Composer para o PHPMailer
require __DIR__ . '/vendor/autoload.php';

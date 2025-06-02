<?php

// 1) Dados de conexão ao MySQL
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'acc';

// 2) Cria a conexão
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Falha na conexão MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
}

require __DIR__ . '/vendor/autoload.php';

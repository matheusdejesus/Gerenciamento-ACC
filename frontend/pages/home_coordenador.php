<?php
session_start();
if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'coordenador') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Home Coordenador</title>
</head>
<body>
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?></h1>
    <p>Tipo: <?= htmlspecialchars($_SESSION['usuario']['tipo']) ?></p>
    <a href="login.php?logout=1">Logout</a>
</body>
</html>

<?php
require_once __DIR__ . '/config.php';
session_start();

// Se não houver usuário logado, manda para login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redireciona conforme tipo
switch ($_SESSION['user_tipo']) {
    case 'aluno':
        header('Location: home_aluno.php');
        break;
    case 'coordenador':
        header('Location: home_coordenador.php');
        break;
    case 'orientador':
        header('Location: home_orientador.php');
        break;
    default:
        session_unset();
        session_destroy();
        header('Location: login.php');
}
exit;

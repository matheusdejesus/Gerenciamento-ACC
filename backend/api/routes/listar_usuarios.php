<?php
require_once __DIR__ . '/../controllers/UsuarioController.php';

header('Content-Type: application/json');

$controller = new UsuarioController();
$controller->listarTodos();
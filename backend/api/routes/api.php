<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/Controller.php';
require_once __DIR__ . '/../models/Cadastro.php';
require_once __DIR__ . '/../controllers/CadastroController.php';

use backend\api\controllers\CadastroController;

$controller = new CadastroController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->register();
} else {
    jsonResponse(['error' => 'Método não permitido'], 405);
}
?>
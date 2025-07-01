<?php
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../controllers/AtividadeComplementarController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\controllers\AtividadeComplementarController;
use backend\api\middleware\AuthMiddleware;

function enviarErro($mensagem, $codigo = 500) {
    ob_end_clean();
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

try {
    $controller = new AtividadeComplementarController();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Para buscar orientadores
            if (isset($_GET['orientadores'])) {
                ob_start();
                $controller->listarOrientadores();
                $output = ob_get_clean();
                
                $jsonData = json_decode($output, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON inválido em listar orientadores: " . $output);
                    enviarErro('Resposta inválida do servidor');
                }
                
                echo $output;
            } else {
                // Listar atividades do aluno comJWT
                $usuario = AuthMiddleware::requireAluno();
                
                ob_start();
                $controller->listarPorAluno($usuario['id']);
                $output = ob_get_clean();
                
                $jsonData = json_decode($output, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON inválido em listar por aluno: " . $output);
                    enviarErro('Resposta inválida do servidor');
                }
                
                echo $output;
            }
            break;
            
        case 'POST':
            // Cadastrar nova atividade com JWT
            $usuario = AuthMiddleware::requireAluno();
            
            ob_start();
            $controller->cadastrarComJWT($usuario['id']);
            $output = ob_get_clean();
            
            $jsonData = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON inválido em cadastrar: " . $output);
                enviarErro('Resposta inválida do servidor');
            }
            
            echo $output;
            break;
            
        default:
            enviarErro('Método não permitido', 405);
    }
    
} catch (Exception $e) {
    error_log("Erro na rota cadastrar_atividade_complementar: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>
<?php
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

ob_start();

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
            // Para download de declaração ou listar atividades avaliadas
            if (isset($_GET['download']) && $_GET['download'] === 'declaracao') {
                $atividade_id = $_GET['id'] ?? null;
                if (!$atividade_id) {
                    http_response_code(400);
                    echo 'ID da atividade é obrigatório';
                    exit;
                }
                
                $controller->downloadDeclaracao($atividade_id);
            } else {
                // Listar atividades avaliadas pelo orientador JWT
                $usuario = AuthMiddleware::requireOrientador();
                
                ob_start();
                $controller->listarAvaliadasOrientadorComJWT($usuario['id']);
                $output = ob_get_clean();
                
                $jsonData = json_decode($output, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON inválido em listar avaliadas: " . $output);
                    enviarErro('Resposta inválida do servidor');
                }
                
                echo $output;
            }
            break;
            
        case 'POST':
            // Avaliar atividade com JWT
            $usuario = AuthMiddleware::requireOrientador();
            
            ob_start();
            $controller->avaliarAtividadeComJWT($usuario['id']);
            $output = ob_get_clean();
            
            $jsonData = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON inválido em avaliar: " . $output);
                enviarErro('Resposta inválida do servidor');
            }
            
            echo $output;
            break;
            
        default:
            enviarErro('Método não permitido', 405);
    }
    
} catch (Exception $e) {
    error_log("Erro na rota avaliar_atividade: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>
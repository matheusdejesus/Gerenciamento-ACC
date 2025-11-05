<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Tratar requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Iniciar output buffering
ob_start();

// Incluir dependências
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../controllers/AvaliarAtividadeController.php';

use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\controllers\AvaliarAtividadeController;

// Função para enviar resposta de erro
function enviarErro($mensagem, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode([
        'sucesso' => false,
        'error' => $mensagem,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    ob_end_flush();
    exit();
}

// Função para enviar resposta de sucesso
function enviarSucesso($dados, $mensagem = 'Operação realizada com sucesso') {
    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => $mensagem,
        'dados' => $dados,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    ob_end_flush();
    exit();
}

try {
    // Verificar autenticação
    $usuarioLogado = null;
    
    // Tentar autenticação via API Key primeiro
    $usuarioLogado = ApiKeyMiddleware::verificarApiKey();
    if (!$usuarioLogado) {
        // Se não tem API Key, tentar JWT
        $usuarioLogado = AuthMiddleware::validateToken();
        if (!$usuarioLogado) {
            enviarErro('Token de autenticação inválido ou expirado', 401);
        }
    }
    
    error_log("[DEBUG] Usuário autenticado: " . json_encode($usuarioLogado));

    // Verificar se é coordenador
    if ($usuarioLogado['tipo'] !== 'coordenador') {
        error_log("Tipo de usuário inválido: " . $usuarioLogado['tipo']);
        enviarErro('Acesso negado. Apenas coordenadores podem avaliar atividades.', 403);
    }

    // Instanciar o controller
    $controller = new AvaliarAtividadeController();
    
    // Verificar método HTTP e ação
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $acao = $_GET['acao'] ?? '';
        
        switch ($acao) {
            case 'certificados_processados':
                $controller->listarCertificadosProcessados($usuarioLogado);
                break;
            default:
                enviarErro('Ação não especificada ou inválida', 400);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';
        
        switch ($acao) {
            case 'aprovar_certificado':
                $atividadeId = $_POST['atividade_id'] ?? null;
                $observacoes = $_POST['observacoes'] ?? '';
                $chAtribuida = $_POST['ch_atribuida'] ?? null;
                
                if (!$atividadeId) {
                    enviarErro('ID da atividade é obrigatório', 400);
                }
                
                if (!$chAtribuida || $chAtribuida <= 0) {
                    enviarErro('Carga horária atribuída é obrigatória e deve ser maior que 0', 400);
                }
                
                $controller->aprovarCertificado($atividadeId, $observacoes, $usuarioLogado, $chAtribuida);
                break;
                
            case 'rejeitar_certificado':
                $atividadeId = $_POST['atividade_id'] ?? null;
                $observacoes = $_POST['observacoes'] ?? '';
                
                if (!$atividadeId) {
                    enviarErro('ID da atividade é obrigatório', 400);
                }
                
                if (empty(trim($observacoes))) {
                    enviarErro('Observações são obrigatórias para rejeição', 400);
                }
                
                $controller->rejeitarCertificado($atividadeId, $observacoes, $usuarioLogado);
                break;
                
            default:
                enviarErro('Ação não especificada ou inválida', 400);
        }
    } else {
        enviarErro('Método HTTP não permitido', 405);
    }

} catch (Exception $e) {
    error_log("[ERROR] Erro em avaliar_atividade.php: " . $e->getMessage());
    error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage(), 500);
}

// Finalizar output buffering
ob_end_flush();
?>
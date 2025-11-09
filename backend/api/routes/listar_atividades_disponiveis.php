<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../controllers/ListarAtividadesDisponiveisController.php';

use backend\api\controllers\ListarAtividadesDisponiveisController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;

function enviarErro($mensagem, $codigo = 500) {
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'sucesso' => false,
        'error' => $mensagem,
        'erro' => $mensagem,
        'message' => $mensagem
    ]);
    exit;
}

try {
    error_log("=== INICIO listar_atividades_disponiveis.php ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("GET params: " . print_r($_GET, true));
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        enviarErro('Método não permitido', 405);
    }
    
    // Verificar autenticação (API Key ou JWT)
    $usuario = ApiKeyMiddleware::verificarApiKey();
    
    if (!$usuario) {
        error_log("API Key não encontrada, tentando JWT...");
        $usuario = AuthMiddleware::validateToken();
        
        if (!$usuario) {
            error_log("Nenhum método de autenticação válido encontrado");
            enviarErro('Token de acesso inválido', 401);
        }
    }
    
    error_log("Usuário autenticado: " . print_r($usuario, true));
    
    // Verificar permissões de acesso
    $acao = isset($_GET['acao']) ? trim($_GET['acao']) : null;
    
    if ($acao === 'enviadas') {
        // Para listar atividades enviadas:
        // - Coordenadores podem ver todas as atividades enviadas
        // - Alunos podem ver apenas suas próprias atividades enviadas
        if ($usuario['tipo'] !== 'coordenador' && $usuario['tipo'] !== 'aluno') {
            error_log("Tipo de usuário inválido para ação 'enviadas': " . $usuario['tipo']);
            enviarErro('Acesso negado. Apenas coordenadores e alunos podem acessar atividades enviadas.', 403);
        }
    } else {
        // Para listar atividades disponíveis, apenas alunos podem acessar
        if ($usuario['tipo'] !== 'aluno') {
            error_log("Tipo de usuário inválido: " . $usuario['tipo']);
            enviarErro('Acesso negado. Apenas alunos podem acessar.', 403);
        }
    }
    
    // Obter parâmetros da requisição
    // Aceitar tanto 'tipo' quanto 'type' para compatibilidade
    $tipo = isset($_GET['type']) ? trim($_GET['type']) : (isset($_GET['tipo']) ? trim($_GET['tipo']) : null);
    $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
    $limite = isset($_GET['limite']) ? max(1, min(100, (int)$_GET['limite'])) : 20;
    $ordenacao = isset($_GET['ordenacao']) ? trim($_GET['ordenacao']) : ($acao === 'enviadas' ? 'id' : 'nome');
    $direcao = isset($_GET['direcao']) ? strtoupper(trim($_GET['direcao'])) : ($acao === 'enviadas' ? 'DESC' : 'ASC');
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    
    // Log dos parâmetros recebidos
    error_log("Parâmetros recebidos:");
    error_log("- Ação: " . ($acao ?: 'LISTAR_DISPONIVEIS'));
    error_log("- Tipo: " . ($tipo ?: 'TODOS'));
    error_log("- Página: $pagina");
    error_log("- Limite: $limite");
    error_log("- Ordenação: $ordenacao $direcao");
    error_log("- Busca: '$busca'");
    
    // Se a ação for 'enviadas', listar atividades enviadas pelo aluno
    if ($acao === 'enviadas') {
        // Validar campos de ordenação permitidos para atividades enviadas
        $camposPermitidosEnviadas = ['id', 'titulo', 'data_submissao', 'status', 'ch_solicitada', 'ch_atribuida', 'categoria'];
        if (!in_array($ordenacao, $camposPermitidosEnviadas)) {
            $ordenacao = 'id';
        }
        
        // Validar direção de ordenação
        if (!in_array($direcao, ['ASC', 'DESC'])) {
            $direcao = 'DESC';
        }
        
        // Chamar o controller para listar atividades enviadas
        $response = ListarAtividadesDisponiveisController::listarAtividadesEnviadas(
            $usuario,
            $pagina,
            $limite,
            $ordenacao,
            $direcao,
            $busca
        );
    } else {
        // Comportamento padrão: listar atividades disponíveis
        
        // Validar tipo se especificado
        if ($tipo && !in_array($tipo, ['ensino', 'estagio', 'extracurriculares', 'pesquisa', 'acao_social', 'pet'])) {
            error_log("Tipo de atividade inválido: $tipo");
            enviarErro("Tipo de atividade inválido: $tipo", 400);
        }
        
        // Validar direção de ordenação
        if (!in_array($direcao, ['ASC', 'DESC'])) {
            $direcao = 'ASC';
        }
        
        // Validar campos de ordenação permitidos
        $camposPermitidos = ['nome', 'categoria', 'carga_horaria_maxima'];
        if (!in_array($ordenacao, $camposPermitidos)) {
            $ordenacao = 'nome';
        }
        
        // Chamar o controller para listar as atividades disponíveis
        $response = ListarAtividadesDisponiveisController::listarAtividades(
            $usuario,
            $tipo,
            $pagina,
            $limite,
            $ordenacao,
            $direcao,
            $busca
        );
    }
    
    error_log("Resposta do controller: " . json_encode($response));
    
    // Definir código de status HTTP baseado na resposta
    if (isset($response['success']) && $response['success']) {
        http_response_code(200);
    } else {
        $statusCode = isset($response['status_code']) ? $response['status_code'] : 500;
        http_response_code($statusCode);
    }
    
    echo json_encode($response);
    error_log("=== FIM listar_atividades_disponiveis.php ===");
    exit;

} catch (Exception $e) {
    error_log("Erro na rota listar_atividades_disponiveis: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>
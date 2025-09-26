<?php
// Configurar output buffering e headers
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');

// Log de debug - início do script
error_log("[DEBUG] atividade_social_comunitaria.php - Início do script. Método: " . $_SERVER['REQUEST_METHOD']);
error_log("[DEBUG] Headers recebidos: " . json_encode(getallheaders()));
error_log("[DEBUG] POST data: " . json_encode($_POST));
error_log("[DEBUG] FILES data: " . json_encode($_FILES));

// Limpar qualquer output anterior
ob_clean();

ini_set('display_errors', 0);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AtividadeSocialComunitariaController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../services/JWTService.php';

use backend\api\controllers\AtividadeSocialComunitariaController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\services\JWTService;

function enviarErro($mensagem, $codigo = 400) {
    error_log("[DEBUG] enviarErro chamada - Código: $codigo, Mensagem: $mensagem");
    // Limpar qualquer output anterior
    ob_clean();
    http_response_code($codigo);
    $response = [
        'success' => false,
        'error' => $mensagem
    ];
    $json = json_encode($response);
    error_log("[DEBUG] JSON de erro: " . $json);
    echo $json;
    error_log("[DEBUG] Resposta de erro enviada, saindo...");
    exit;
}

function enviarSucesso($dados, $mensagem = null) {
    error_log("[DEBUG] enviarSucesso chamada - Mensagem: $mensagem");
    error_log("[DEBUG] Dados: " . json_encode($dados));
    // Limpar qualquer output anterior
    ob_clean();
    http_response_code(200);
    $response = [
        'success' => true,
        'data' => $dados,
        'message' => $mensagem
    ];
    $json = json_encode($response);
    error_log("[DEBUG] JSON de sucesso: " . $json);
    echo $json;
    error_log("[DEBUG] Resposta de sucesso enviada, saindo...");
    exit;
}

try {
    error_log("[DEBUG] Iniciando processamento da requisição...");
    
    $controller = new AtividadeSocialComunitariaController();
    
    // GET - Listar atividades
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // Listar por aluno específico
        if (isset($_GET['aluno_id'])) {
            // Primeiro tentar API Key, depois JWT
            $usuario = ApiKeyMiddleware::verificarApiKey();
            if (!$usuario) {
                $usuario = AuthMiddleware::validateToken();
            }
            
            error_log("[DEBUG] Usuário validado: " . json_encode($usuario));
            error_log("[DEBUG] Aluno ID solicitado: " . $_GET['aluno_id']);
            
            if (!$usuario) {
                error_log("[DEBUG] Token inválido ou não fornecido");
                enviarErro('Token de acesso inválido', 401);
            }
            
            if ($usuario['tipo'] !== 'coordenador' && $usuario['id'] != $_GET['aluno_id']) {
                error_log("[DEBUG] Acesso negado - Tipo: " . $usuario['tipo'] . ", ID: " . $usuario['id'] . ", Solicitado: " . $_GET['aluno_id']);
                enviarErro('Acesso negado', 403);
            }
            
            $resultado = $controller->listarPorAluno($_GET['aluno_id']);
            if ($resultado['success']) {
                enviarSucesso($resultado['data']);
            } else {
                enviarErro($resultado['error']);
            }
        }
        
        // Buscar por ID específico
        elseif (isset($_GET['id'])) {
            // Primeiro tentar API Key, depois JWT
            $usuario = ApiKeyMiddleware::verificarApiKey();
            if (!$usuario) {
                $usuario = AuthMiddleware::validateToken();
            }
            
            if (!$usuario) {
                enviarErro('Acesso negado', 403);
            }
            
            $resultado = $controller->buscarPorId($_GET['id']);
            if ($resultado['success']) {
                // Verificar se o usuário pode ver esta atividade
                $atividade = $resultado['data'];
                if ($usuario['tipo'] !== 'coordenador' && $atividade['aluno_id'] != $usuario['id']) {
                    enviarErro('Acesso negado', 403);
                }
                enviarSucesso($atividade);
            } else {
                enviarErro($resultado['error']);
            }
        }
        
        // Buscar atividades disponíveis (endpoint público - sem autenticação)
        elseif (isset($_GET['disponiveis'])) {
            error_log("[DEBUG] Endpoint de atividades disponíveis chamado");
            try {
                $atividades = $controller->buscarAtividadesDisponiveis();
                enviarSucesso($atividades);
            } catch (Exception $e) {
                error_log("[DEBUG] Erro ao buscar atividades disponíveis: " . $e->getMessage());
                enviarErro('Erro interno do servidor');
            }
        }
        
        // Listar todas (apenas coordenadores)
        else {
            // Primeiro tentar API Key, depois JWT
            $usuario = ApiKeyMiddleware::verificarApiKey();
            if (!$usuario) {
                $usuario = AuthMiddleware::validateToken();
            }
            
            if (!$usuario || $usuario['tipo'] !== 'coordenador') {
                enviarErro('Acesso negado', 403);
            }
            
            $filtros = [];
            if (isset($_GET['status'])) {
                $filtros['status'] = $_GET['status'];
            }
            if (isset($_GET['aluno_id'])) {
                $filtros['aluno_id'] = $_GET['aluno_id'];
            }
            
            $resultado = $controller->listarTodas($filtros);
            if ($resultado['success']) {
                enviarSucesso($resultado['data']);
            } else {
                enviarErro($resultado['error']);
            }
        }
    }
    
    // POST - Cadastrar nova atividade
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar autenticação JWT
        error_log("[DEBUG] Verificando autenticação JWT...");
        $usuario = AuthMiddleware::requireAluno();
        if (!$usuario) {
            error_log("[DEBUG] Token JWT inválido");
            enviarErro('Token de acesso inválido', 401);
        }
        error_log("[DEBUG] Usuário autenticado: " . json_encode($usuario));
        
        // Processar upload de arquivo se fornecido
        $declaracao_caminho = null;
        if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
            error_log("[DEBUG] Processando upload de declaração...");
            
            $uploadDir = __DIR__ . '/../../uploads/atividades_sociais/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $arquivo = $_FILES['declaracao'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($extensao, $extensoesPermitidas)) {
                enviarErro('Tipo de arquivo não permitido. Use PDF, JPG, JPEG ou PNG.');
            }
            
            if ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
                enviarErro('Arquivo muito grande. Tamanho máximo: 5MB.');
            }
            
            $nomeArquivo = 'atividade_social_' . $usuario['id'] . '_' . time() . '.' . $extensao;
            $caminhoCompleto = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                $declaracao_caminho = 'uploads/atividades_sociais/' . $nomeArquivo;
                error_log("[DEBUG] Arquivo salvo em: " . $declaracao_caminho);
            } else {
                error_log("[DEBUG] Falha ao mover arquivo");
                enviarErro('Falha ao salvar arquivo');
            }
        }
        
        // Preparar dados da atividade
        $dados = [
            'aluno_id' => $usuario['id'],
            'nome_projeto' => $_POST['nome_projeto'] ?? null,
            'instituicao' => $_POST['instituicao'] ?? null,
            'carga_horaria' => $_POST['carga_horaria'] ?? null,
            'local_realizacao' => $_POST['local_realizacao'] ?? null,
            'descricao_atividades' => $_POST['descricao_atividades'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'declaracao_caminho' => $declaracao_caminho,
            'atividade_disponivel_id' => 1 // ID padrão para ação social
        ];
        
        error_log("[DEBUG] Dados preparados: " . json_encode($dados));
        
        try {
            $atividade_id = $controller->cadastrarComJWT($dados);
            
            if ($atividade_id) {
                error_log("[DEBUG] Atividade social criada com sucesso. ID: " . $atividade_id);
                enviarSucesso([
                    'id' => $atividade_id,
                    'message' => 'Atividade social comunitária cadastrada com sucesso!'
                ]);
            } else {
                error_log("[DEBUG] Falha ao criar atividade social");
                enviarErro('Falha ao cadastrar atividade social comunitária');
            }
        } catch (Exception $e) {
            error_log("[DEBUG] Erro ao cadastrar atividade social: " . $e->getMessage());
            enviarErro('Erro ao cadastrar atividade: ' . $e->getMessage());
        }
    }
    
    // PUT - Atualizar status (apenas coordenadores)
    elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Verificar autenticação
        $usuario = ApiKeyMiddleware::verificarApiKey();
        if (!$usuario) {
            $usuario = AuthMiddleware::validateToken();
        }
        
        if (!$usuario || $usuario['tipo'] !== 'coordenador') {
            enviarErro('Acesso negado', 403);
        }
        
        // Obter dados do corpo da requisição
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            enviarErro('Dados inválidos');
        }
        
        $input['avaliador_id'] = $usuario['id'];
        
        $resultado = $controller->atualizarStatus($input);
        if ($resultado['success']) {
            enviarSucesso($resultado, $resultado['message']);
        } else {
            enviarErro($resultado['error']);
        }
    }
    
    // DELETE - Excluir atividade
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Verificar autenticação
        $usuario = AuthMiddleware::validateToken();
        
        if (!$usuario) {
            enviarErro('Acesso negado', 403);
        }
        
        if (!isset($_GET['id'])) {
            enviarErro('ID da atividade é obrigatório');
        }
        
        $aluno_id = ($usuario['tipo'] === 'aluno') ? $usuario['id'] : null;
        
        $resultado = $controller->excluir($_GET['id'], $aluno_id);
        if ($resultado['success']) {
            enviarSucesso($resultado, $resultado['message']);
        } else {
            enviarErro($resultado['error']);
        }
    }
    
    else {
        enviarErro('Método não permitido', 405);
    }
    
} catch (Exception $e) {
    error_log("[DEBUG] Erro geral: " . $e->getMessage());
    error_log("[DEBUG] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
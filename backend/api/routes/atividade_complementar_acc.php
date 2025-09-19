<?php
// Configurar output buffering e headers
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');

// Log de debug - início do script
error_log("[DEBUG] atividade_complementar_acc.php - Início do script. Método: " . $_SERVER['REQUEST_METHOD']);
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
require_once __DIR__ . '/../controllers/AtividadeComplementarACCController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';

use backend\api\controllers\AtividadeComplementarACCController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;

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
    // Nota: A validação de API Key será feita pelo AuthMiddleware quando necessário
    error_log("[DEBUG] Iniciando processamento da requisição...");
    
    $controller = new AtividadeComplementarACCController();
    
    // GET - Listar atividades
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // Listar por aluno específico
        if (isset($_GET['aluno_id'])) {
            $usuario = AuthMiddleware::validateToken();
            if (!$usuario || ($usuario['tipo'] !== 'coordenador' && $usuario['id'] != $_GET['aluno_id'])) {
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
            $usuario = AuthMiddleware::validateToken();
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
        
        // Obter estatísticas
        elseif (isset($_GET['estatisticas'])) {
            $usuario = AuthMiddleware::validateToken();
            if (!$usuario) {
                enviarErro('Acesso negado', 403);
            }
            
            $aluno_id = null;
            if ($usuario['tipo'] === 'aluno') {
                $aluno_id = $usuario['id'];
            } elseif (isset($_GET['aluno_id']) && $usuario['tipo'] === 'coordenador') {
                $aluno_id = $_GET['aluno_id'];
            }
            
            $resultado = $controller->obterEstatisticas($aluno_id);
            if ($resultado['success']) {
                enviarSucesso($resultado['data']);
            } else {
                enviarErro($resultado['error']);
            }
        }
        
        // Listar todas (apenas coordenadores)
        else {
            $usuario = AuthMiddleware::validateToken();
            if (!$usuario || $usuario['tipo'] !== 'coordenador') {
                enviarErro('Acesso negado', 403);
            }
            
            $filtros = [];
            if (isset($_GET['status'])) {
                $filtros['status'] = $_GET['status'];
            }
            if (isset($_GET['categoria_id'])) {
                $filtros['categoria_id'] = $_GET['categoria_id'];
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
        
        // Upload da declaração/certificado
        error_log("[DEBUG] Verificando upload de arquivo...");
        if (!isset($_FILES['declaracao']) || $_FILES['declaracao']['error'] !== UPLOAD_ERR_OK) {
            error_log("[DEBUG] Erro no upload: " . ($_FILES['declaracao']['error'] ?? 'arquivo não enviado'));
            enviarErro('Declaração/Certificado é obrigatório');
        }
        error_log("[DEBUG] Arquivo recebido: " . $_FILES['declaracao']['name']);
        
        // Validar tipo de arquivo
        error_log("[DEBUG] Validando tipo de arquivo...");
        $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $tipoArquivo = $_FILES['declaracao']['type'];
        error_log("[DEBUG] Tipo de arquivo: " . $tipoArquivo);
        
        if (!in_array($tipoArquivo, $tiposPermitidos)) {
            error_log("[DEBUG] Tipo de arquivo não permitido: " . $tipoArquivo);
            enviarErro('Tipo de arquivo não permitido. Use PDF, JPG ou PNG');
        }
        
        $uploadDir = __DIR__ . '/../../uploads/declaracoes_acc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['declaracao']['name']);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['declaracao']['tmp_name'], $filePath)) {
            enviarErro('Erro ao salvar o arquivo de declaração.');
        }

        // Caminho relativo para salvar no banco
        $caminhoArquivo = 'uploads/declaracoes_acc/' . $fileName;

        // Processar dados do formulário
        error_log("[DEBUG] Processando dados do formulário...");
        
        // Capturar todos os campos possíveis e consolidar em curso_evento_nome
        $curso_nome = $_POST['curso_nome'] ?? null;
        $evento_nome = $_POST['evento_nome'] ?? null;
        $projeto_nome = $_POST['projeto_nome'] ?? null;
        
        // Determinar o valor para curso_evento_nome baseado nos campos disponíveis
        $curso_evento_nome = null;
        if (!empty($curso_nome)) {
            $curso_evento_nome = $curso_nome;
        } elseif (!empty($evento_nome)) {
            $curso_evento_nome = $evento_nome;
        } elseif (!empty($projeto_nome)) {
            $curso_evento_nome = $projeto_nome;
        } else {
            // Para atividades como "Missões nacionais e internacionais" que não têm campo específico
            $curso_evento_nome = 'Não especificado';
        }
        
        error_log("[DEBUG] Campos capturados - curso_nome: $curso_nome, evento_nome: $evento_nome, projeto_nome: $projeto_nome");
        error_log("[DEBUG] curso_evento_nome consolidado: $curso_evento_nome");
        
        $dados = [
            'aluno_id' => $usuario['id'],
            'atividade_disponivel_id' => $_POST['atividade_disponivel_id'] ?? null,
            'curso_evento_nome' => $curso_evento_nome,
            'horas_realizadas' => $_POST['horas_realizadas'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'local_instituicao' => $_POST['local_instituicao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'declaracao_caminho' => $caminhoArquivo
        ];
        error_log("[DEBUG] Dados processados: " . json_encode($dados));

        try {
            // Cadastrar atividade
            error_log("[DEBUG] Chamando controller->cadastrarComJWT...");
            $atividadeId = $controller->cadastrarComJWT($dados);
            error_log("[DEBUG] Atividade cadastrada com ID: " . $atividadeId);
            
            error_log("[DEBUG] Enviando resposta de sucesso...");
            enviarSucesso([
                'message' => 'Atividade cadastrada com sucesso',
                'id' => $atividadeId
            ]);
            error_log("[DEBUG] Resposta enviada com sucesso");
        } catch (Exception $e) {
            enviarErro($e->getMessage());
        }
    }
    
    // PUT - Avaliar atividade (aprovar/rejeitar)
    elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $usuario = AuthMiddleware::validateToken();
        if (!$usuario || $usuario['tipo'] !== 'coordenador') {
            enviarErro('Acesso negado. Apenas coordenadores podem avaliar atividades.', 403);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            enviarErro('Dados inválidos');
        }
        
        $dados = [
            'id' => $input['id'] ?? null,
            'status' => $input['status'] ?? null,
            'avaliador_id' => $usuario['id'],
            'observacoes_avaliacao' => $input['observacoes_avaliacao'] ?? null
        ];
        
        $resultado = $controller->avaliarAtividade($dados);
        if ($resultado['success']) {
            enviarSucesso([], $resultado['message']);
        } else {
            enviarErro($resultado['error']);
        }
    }
    
    // DELETE - Excluir atividade
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $usuario = AuthMiddleware::validateToken();
        if (!$usuario) {
            enviarErro('Acesso negado', 403);
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            enviarErro('ID da atividade é obrigatório');
        }
        
        $resultado = $controller->excluir($id, $usuario['id']);
        if ($resultado['success']) {
            enviarSucesso([], $resultado['message']);
        } else {
            enviarErro($resultado['error']);
        }
    }
    
    else {
        enviarErro('Método não permitido', 405);
    }
    
} catch (Exception $e) {
    error_log("[DEBUG] Exceção capturada no catch principal: " . $e->getMessage());
    error_log("[DEBUG] Stack trace: " . $e->getTraceAsString());
    error_log("Erro em atividade_complementar_acc.php: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("[DEBUG] Erro fatal capturado: " . $e->getMessage());
    error_log("[DEBUG] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro fatal do servidor: ' . $e->getMessage(), 500);
}

// Log final - se chegou até aqui sem enviar resposta
error_log("[DEBUG] Script chegou ao final sem enviar resposta - isso não deveria acontecer");
?>
<?php
// Configurar output buffering para evitar saída prematura
ob_start();

// Limpar qualquer output anterior
ob_clean();

ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

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
    // Limpar qualquer output anterior
    ob_clean();
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

function enviarSucesso($dados, $mensagem = null) {
    // Limpar qualquer output anterior
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $dados,
        'message' => $mensagem
    ]);
    exit;
}

try {
    ApiKeyMiddleware::validateApiKey();
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
        $usuario = AuthMiddleware::requireAluno();
        
        // Upload da declaração/certificado
        $declaracaoCaminho = null;
        if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
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
            $declaracaoCaminho = 'uploads/declaracoes_acc/' . $fileName;
        }
        
        if (!$declaracaoCaminho) {
            enviarErro('Declaração/Certificado é obrigatório');
        }

        // Dados da atividade
        $dados = [
            'aluno_id' => $usuario['id'],
            'atividade_disponivel_id' => $_POST['atividade_disponivel_id'] ?? null,
            'curso_nome' => $_POST['curso_nome'] ?? null, // Corrigido: era 'curso' mas o modelo espera 'curso_nome'
            'horas_realizadas' => $_POST['horas_realizadas'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'local_instituicao' => $_POST['local_instituicao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'declaracao_caminho' => $declaracaoCaminho
        ];

        try {
            $atividade_id = $controller->cadastrarComJWT($dados);
            enviarSucesso([
                'atividade_id' => $atividade_id
            ], 'Atividade de extensão cadastrada com sucesso');
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
    // Limpar qualquer output anterior antes de enviar erro
    ob_clean();
    error_log("Erro na rota atividade_complementar_acc: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
    exit;
}
?>
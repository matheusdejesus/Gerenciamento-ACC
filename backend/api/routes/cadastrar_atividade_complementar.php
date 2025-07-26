<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AtividadeComplementarController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';

use backend\api\controllers\AtividadeComplementarController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;

function enviarErro($mensagem, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

try {
    ApiKeyMiddleware::validateApiKey();
    
    if (isset($_GET['orientadores'])) {
        $controller = new AtividadeComplementarController();
        $controller->listarOrientadores();
        exit;
    }

    if (isset($_GET['coordenadores'])) {
        $controller = new AtividadeComplementarController();
        $controller->listarCoordenadores();
        exit;
    }

    // FUNCIONALIDADE: Adicionar atividade disponível
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar_atividade_disponivel') {
        error_log("=== RECEBIDA REQUISIÇÃO PARA ADICIONAR ATIVIDADE ===");
        error_log("POST data: " . json_encode($_POST));
        
        // Validar se é admin
        $usuario = AuthMiddleware::validateToken();
        if (!$usuario || $usuario['tipo'] !== 'admin') {
            error_log("Acesso negado - usuário: " . json_encode($usuario));
            enviarErro('Acesso negado. Apenas administradores podem adicionar atividades.', 403);
        }

        $titulo = $_POST['titulo'] ?? null;
        $descricao = $_POST['descricao'] ?? null;
        $categoria_id = $_POST['categoria_id'] ?? null;
        $carga_horaria = $_POST['carga_horaria'] ?? null;

        error_log("Dados recebidos - Título: $titulo, Descrição: $descricao, Categoria: $categoria_id, Carga: $carga_horaria");

        if (!$titulo || !$descricao || !$categoria_id || !$carga_horaria) {
            error_log("Campos obrigatórios faltando");
            enviarErro('Todos os campos são obrigatórios', 400);
        }

        // Adicionar no banco
        require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
        
        try {
            $id = \backend\api\models\AtividadesDisponiveis::adicionar($titulo, $descricao, intval($categoria_id), intval($carga_horaria));
            error_log("Atividade inserida com ID: $id");

            if ($id) {
                // Registrar auditoria
                require_once __DIR__ . '/../controllers/LogAcoesController.php';
                \backend\api\controllers\LogAcoesController::registrar(
                    $usuario['id'],
                    'ADICIONAR_ATIVIDADE',
                    "Atividade adicionada: '{$titulo}' (ID: {$id})"
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Atividade adicionada com sucesso',
                    'id' => $id
                ]);
            } else {
                error_log("Falha ao obter ID da atividade inserida");
                enviarErro('Erro ao adicionar atividade', 500);
            }
        } catch (Exception $e) {
            error_log("Erro ao adicionar atividade: " . $e->getMessage());
            enviarErro('Erro ao adicionar atividade: ' . $e->getMessage(), 500);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = AuthMiddleware::requireAluno();
        
        if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/declaracoes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['declaracao']['name']);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['declaracao']['tmp_name'], $filePath)) {
                enviarErro('Erro ao salvar o arquivo de declaração.');
            }

            // Caminho relativo para salvar no banco
            $declaracaoCaminho = 'uploads/declaracoes/' . $fileName;
        } else {
            $declaracaoCaminho = null;
        }

        // Dados da atividade
        $dados = [
            'aluno_id' => $usuario['id'],
            'categoria_id' => $_POST['categoria_id'] ?? null,
            'titulo' => $_POST['titulo'] ?? null,
            'descricao' => $_POST['descricao_atividades'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'carga_horaria_solicitada' => $_POST['horas_solicitadas'] ?? null,
            'orientador_id' => $_POST['orientador_id'] ?? null,
            'declaracao_caminho' => $declaracaoCaminho
        ];

        // Criar atividade
        $controller = new AtividadeComplementarController();
        $atividade_id = $controller->cadastrarComJWT($dados);

        echo json_encode([
            'success' => true,
            'message' => 'Atividade cadastrada com sucesso',
            'atividade_id' => $atividade_id
        ]);
    } else {
        enviarErro('Método não permitido', 405);
    }
} catch (Exception $e) {
    error_log("Erro na rota cadastrar_atividade_complementar: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>
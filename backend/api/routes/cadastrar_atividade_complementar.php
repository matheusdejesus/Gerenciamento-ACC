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
    $response = [
        'success' => false,
        'error' => $mensagem
    ];
    error_log("ENVIANDO ERRO: " . json_encode($response));
    echo json_encode($response);
    exit;
}

try {
    error_log("=== INICIO CADASTRAR ATIVIDADE COMPLEMENTAR ===");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data: " . json_encode($_POST));
    error_log("FILES data: " . json_encode($_FILES));
    
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
            $id = \backend\api\models\AtividadesDisponiveis::adicionar($titulo, intval($categoria_id), intval($carga_horaria));
            error_log("Atividade inserida com ID: $id");

            if ($id) {
                // Registrar auditoria
                require_once __DIR__ . '/../controllers/LogAcoesController.php';
                \backend\api\controllers\LogAcoesController::registrar(
                    $usuario['id'],
                    'ADICIONAR_ATIVIDADE',
                    "Atividade adicionada: '{$titulo}' (ID: {$id})"
                );

                $response = [
                    'success' => true,
                    'message' => 'Atividade adicionada com sucesso',
                    'id' => $id
                ];
                error_log("ENVIANDO SUCESSO: " . json_encode($response));
                echo json_encode($response);
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
        error_log("=== PROCESSANDO CADASTRO DE ATIVIDADE COMPLEMENTAR ===");
        
        $usuario = AuthMiddleware::requireAluno();
        error_log("Usuário autenticado: " . json_encode($usuario));
        
        // NEW: Suporta envio apenas de 'atividade_id' vindo do frontend
        if (isset($_POST['atividade_id']) && is_numeric($_POST['atividade_id'])) {
            error_log("Processando atividade_id: " . $_POST['atividade_id']);
            require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
            $atividadeDisp = \backend\api\models\AtividadesDisponiveis::buscarPorId($_POST['atividade_id']);
            if (!$atividadeDisp) {
                error_log("Atividade disponível não encontrada para ID: " . $_POST['atividade_id']);
                enviarErro('Atividade disponível não encontrada', 404);
            }
            error_log("Atividade encontrada: " . json_encode($atividadeDisp));
            // Preencher campos necessários a partir da tabela de atividades disponíveis
            $_POST['categoria_id'] = $atividadeDisp['categoria_id'];
            $_POST['titulo'] = $atividadeDisp['titulo'];
            // Descrição pode vir do frontend como observacoes ou ficar vazia
            if (empty($_POST['descricao_atividades']) && !empty($atividadeDisp['observacoes'])) {
                $_POST['descricao_atividades'] = $atividadeDisp['observacoes'];
            }
            error_log("POST data após preenchimento: " . json_encode($_POST));
        }
        
        if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
            error_log("Processando upload de arquivo");
            $uploadDir = __DIR__ . '/../../uploads/declaracoes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['declaracao']['name']);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['declaracao']['tmp_name'], $filePath)) {
                error_log("Erro ao mover arquivo para: " . $filePath);
                enviarErro('Erro ao salvar o arquivo de declaração.');
            }

            // Caminho relativo para salvar no banco
            $declaracaoCaminho = 'uploads/declaracoes/' . $fileName;
            error_log("Arquivo salvo em: " . $declaracaoCaminho);
        } else {
            $declaracaoCaminho = null;
            error_log("Nenhum arquivo de declaração enviado");
        }

        // Dados da atividade
        $dados = [
            'aluno_id' => $usuario['id'],
            'categoria_id' => $_POST['categoria_id'] ?? null,
            'titulo' => $_POST['titulo'] ?? null,
            'descricao' => $_POST['descricao_atividades'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'carga_horaria_solicitada' => $_POST['horas_realizadas'] ?? null,
            'orientador_id' => $_POST['orientador_id'] ?? null,
            'declaracao_caminho' => $declaracaoCaminho
        ];
        
        error_log("Dados para cadastro: " . json_encode($dados));

        // Criar atividade
        $controller = new AtividadeComplementarController();
        $atividade_id = $controller->cadastrarComJWT($dados);
        
        error_log("Atividade criada com ID: " . $atividade_id);

        $response = [
            'success' => true,
            'message' => 'Atividade cadastrada com sucesso',
            'atividade_id' => $atividade_id
        ];
        
        error_log("ENVIANDO RESPOSTA FINAL: " . json_encode($response));
        echo json_encode($response);
    } else {
        enviarErro('Método não permitido', 405);
    }
} catch (Exception $e) {
    error_log("ERRO GERAL na rota cadastrar_atividade_complementar: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>
<?php
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../controllers/AtividadeComplementarController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../middleware/AuditoriaMiddleware.php';

use backend\api\controllers\AtividadeComplementarController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\middleware\AuditoriaMiddleware;

ApiKeyMiddleware::validateApiKey(); 

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
<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');

ob_start();
ob_clean();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../controllers/AtividadeComplementarController.php';

use backend\api\controllers\AtividadeComplementarController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\models\CertificadoAvulso;

ApiKeyMiddleware::validateApiKey();

function enviarErro($mensagem, $codigo = 500) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($codigo);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

try {
    error_log("=== AVALIAR_ATIVIDADE.PHP ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("GET: " . print_r($_GET, true));
    error_log("POST: " . print_r($_POST, true));
    error_log("FILES: " . print_r($_FILES, true));
    
    // Rejeitar certificado (COORDENADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'rejeitar_certificado') {
        error_log("=== ROTA COORDENADOR: REJEITAR CERTIFICADO ===");
        
        $usuario = AuthMiddleware::requireCoordenador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $atividade_id = $_POST['atividade_id'] ?? null;
        $observacoes = $_POST['observacoes'] ?? '';
        
        if (!$atividade_id) {
            error_log("ID da atividade não fornecido");
            enviarErro('ID da atividade é obrigatório', 400);
        }
        
        if (!$observacoes) {
            error_log("Observações não fornecidas");
            enviarErro('Observações são obrigatórias para rejeição', 400);
        }
        
        error_log("Processando rejeição do certificado para atividade ID: " . $atividade_id);
        
        $controller = new AtividadeComplementarController();
        $controller->rejeitarCertificadoComJWT($usuario['id'], $atividade_id, $observacoes);
        exit;
    }

    // Aprovar certificado (COORDENADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'aprovar_certificado') {
        error_log("=== ROTA COORDENADOR: APROVAR CERTIFICADO ===");
        
        $usuario = AuthMiddleware::requireCoordenador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $atividade_id = $_POST['atividade_id'] ?? null;
        $observacoes = $_POST['observacoes'] ?? '';
        
        if (!$atividade_id) {
            error_log("ID da atividade não fornecido");
            enviarErro('ID da atividade é obrigatório', 400);
        }
        
        error_log("Processando aprovação do certificado para atividade ID: " . $atividade_id);
        
        $controller = new AtividadeComplementarController();
        $controller->aprovarCertificadoComJWT($usuario['id'], $atividade_id, $observacoes);
        exit;
    }
    
    // Certificados pendentes (COORDENADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'certificados_pendentes') {
        error_log("=== ROTA COORDENADOR: CERTIFICADOS PENDENTES ===");
        
        $usuario = AuthMiddleware::requireCoordenador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $controller = new AtividadeComplementarController();
        $controller->listarCertificadosPendentesCoordenadorComJWT($usuario['id']);
        exit;
    }
    
    // Certificados processados (COORDENADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'certificados_processados') {
        error_log("=== ROTA COORDENADOR: CERTIFICADOS PROCESSADOS ===");
        
        $usuario = AuthMiddleware::requireCoordenador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $controller = new AtividadeComplementarController();
        $controller->listarCertificadosProcessadosCoordenadorComJWT($usuario['id']);
        exit;
    }

    // Aprovar certificado avulso (COORDENADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'aprovar_certificado_avulso') {
        error_log("=== ROTA COORDENADOR: APROVAR CERTIFICADO AVULSO ===");
        
        $usuario = AuthMiddleware::requireCoordenador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $certificado_id = $_POST['certificado_id'] ?? null;
        $observacoes = $_POST['observacoes'] ?? '';
        
        if (!$certificado_id) {
            error_log("ID do certificado não fornecido");
            enviarErro('ID do certificado é obrigatório', 400);
        }
        
        error_log("Processando aprovação do certificado avulso ID: " . $certificado_id);
        
        require_once __DIR__ . '/../models/CertificadoAvulso.php';
        $resultado = CertificadoAvulso::aprovar($certificado_id, $usuario['id'], $observacoes);
        
        if ($resultado) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Certificado avulso aprovado com sucesso'
            ]);
        } else {
            enviarErro('Erro ao aprovar certificado avulso', 500);
        }
        exit;
    }

    // Rejeitar certificado avulso (COORDENADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'rejeitar_certificado_avulso') {
        error_log("=== ROTA COORDENADOR: REJEITAR CERTIFICADO AVULSO ===");
        
        $usuario = AuthMiddleware::requireCoordenador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $certificado_id = $_POST['certificado_id'] ?? null;
        $observacoes = $_POST['observacoes'] ?? '';
        
        if (!$certificado_id) {
            error_log("ID do certificado não fornecido");
            enviarErro('ID do certificado é obrigatório', 400);
        }
        
        if (!$observacoes) {
            error_log("Observações não fornecidas");
            enviarErro('Observações são obrigatórias para rejeição', 400);
        }
        
        error_log("Processando rejeição do certificado avulso ID: " . $certificado_id);
        
        require_once __DIR__ . '/../models/CertificadoAvulso.php';
        $resultado = CertificadoAvulso::rejeitar($certificado_id, $usuario['id'], $observacoes);
        
        if ($resultado) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Certificado avulso rejeitado com sucesso'
            ]);
        } else {
            enviarErro('Erro ao rejeitar certificado avulso', 500);
        }
        exit;
    }
    
    // Enviar certificado (ALUNO)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'enviar_certificado_processado') {
        error_log("=== ROTA ALUNO: ENVIAR CERTIFICADO ===");
        
        $usuario = AuthMiddleware::requireAluno();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $atividade_id = $_POST['atividade_id'] ?? null;
        if (!$atividade_id) {
            enviarErro('ID da atividade é obrigatório', 400);
        }
        
        $controller = new AtividadeComplementarController();
        $controller->enviarCertificadoProcessado($usuario['id'], $atividade_id);
        exit;
    }
    
    // Upload de certificado pelo orientador
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'upload_certificado') {
        error_log("=== ROTA ORIENTADOR: UPLOAD CERTIFICADO ===");
        error_log("Ação detectada: upload_certificado");
        
        $usuario = AuthMiddleware::requireOrientador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $atividade_id = $_POST['atividade_id'] ?? null;
        if (!$atividade_id) {
            error_log("ID da atividade não fornecido");
            enviarErro('ID da atividade é obrigatório', 400);
        }
        
        // Verificar se há arquivo enviado
        if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
            error_log("Arquivo de certificado não encontrado ou com erro");
            error_log("FILES: " . print_r($_FILES, true));
            enviarErro('Arquivo de certificado é obrigatório', 400);
        }
        
        error_log("Processando upload de certificado para atividade ID: " . $atividade_id);
        
        $controller = new AtividadeComplementarController();
        $controller->processarUploadCertificado($usuario['id'], $atividade_id);
        exit;
    }
    
    // Avaliar atividade (ORIENTADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && (isset($input['acao']) || isset($input['atividade_id']))) {
            error_log("=== ROTA ORIENTADOR: AVALIAR ATIVIDADE (JSON) ===");
            error_log("Dados JSON recebidos: " . print_r($input, true));
            
            $usuario = AuthMiddleware::requireOrientador();
            error_log("Usuário validado: " . print_r($usuario, true));
            
            $controller = new AtividadeComplementarController();
            $controller->avaliarAtividadeComJWT($usuario['id']);
            exit;
        }
    }
    
    // Avaliar atividade (ORIENTADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'avaliar_atividade') {
        error_log("=== ROTA ORIENTADOR: AVALIAR ATIVIDADE (POST) ===");
        
        $usuario = AuthMiddleware::requireOrientador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $controller = new AtividadeComplementarController();
        $controller->avaliarAtividadeComJWT($usuario['id']);
        exit;
    }
    
    // Atividades pendentes (ORIENTADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'atividades_pendentes') {
        error_log("=== ROTA ORIENTADOR: ATIVIDADES PENDENTES ===");
        
        $usuario = AuthMiddleware::requireOrientador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $controller = new AtividadeComplementarController();
        $controller->listarPendentesOrientadorComJWT($usuario['id']);
        exit;
    }

    // Atividades avaliadas (ORIENTADOR)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['acao'])) {
        error_log("=== ROTA ORIENTADOR: ATIVIDADES AVALIADAS (SEM ACAO) ===");
        
        $usuario = AuthMiddleware::requireOrientador();
        error_log("Usuário validado: " . print_r($usuario, true));
        
        $controller = new AtividadeComplementarController();
        $controller->listarAvaliadasOrientadorComJWT($usuario['id']);
        exit;
    }

    error_log("Nenhuma rota correspondente encontrada");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Acao GET: " . ($_GET['acao'] ?? 'null'));
    error_log("Acao POST: " . ($_POST['acao'] ?? 'null'));
    
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Dados JSON: " . print_r($input, true));
    
    enviarErro('Rota não encontrada', 404);

} catch (Exception $e) {
    error_log("Erro na rota avaliar_atividade: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}

if (ob_get_length()) {
    ob_end_flush();
}
?>
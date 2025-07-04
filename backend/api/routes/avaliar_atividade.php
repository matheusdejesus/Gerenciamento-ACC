<?php
error_reporting(0);

// Verificar se é uma requisição de download antes de definir headers JSON
if (isset($_GET['download']) && $_GET['download'] === 'declaracao' && isset($_GET['id'])) {
    // Não definir headers JSON para downloads
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
    
    
    try {
        // Verificar autenticação (aluno ou orientador pode visualizar)
        $usuario = AuthMiddleware::requireAuth();
        $atividadeId = intval($_GET['id']);
        
        $db = Database::getInstance()->getConnection();
        
        // Verificar se o usuário tem permissão para ver esta atividade
        $sqlVerifica = "SELECT ac.declaracao_caminho, ac.aluno_id, ac.orientador_id 
                       FROM AtividadeComplementar ac 
                       WHERE ac.id = ?";
        $stmtVerifica = $db->prepare($sqlVerifica);
        $stmtVerifica->bind_param("i", $atividadeId);
        $stmtVerifica->execute();
        $resultVerifica = $stmtVerifica->get_result();
        
        if ($resultVerifica->num_rows === 0) {
            http_response_code(404);
            echo "Atividade não encontrada.";
            exit;
        }
        
        $atividade = $resultVerifica->fetch_assoc();
        
        // Verificar permissão (aluno dono ou orientador responsável)
        $temPermissao = false;
        if ($usuario['tipo'] === 'aluno' && $atividade['aluno_id'] == $usuario['id']) {
            $temPermissao = true;
        } elseif ($usuario['tipo'] === 'orientador' && $atividade['orientador_id'] == $usuario['id']) {
            $temPermissao = true;
        }
        
        if (!$temPermissao) {
            http_response_code(403);
            echo "Acesso negado.";
            exit;
        }
        
        if (empty($atividade['declaracao_caminho'])) {
            http_response_code(404);
            echo "Declaração não encontrada.";
            exit;
        }

        // Caminho completo do arquivo
        $filePath = __DIR__ . "/../../" . $atividade['declaracao_caminho'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "Arquivo não encontrado no servidor.";
            exit;
        }
        
        // Determinar o tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        // Headers para visualização do arquivo
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Enviar o arquivo
        readfile($filePath);
        exit;
        
    } catch (Exception $e) {
        error_log("Erro ao baixar declaração: " . $e->getMessage());
        http_response_code(500);
        echo "Erro interno do servidor.";
        exit;
    }
}

// Se não for download, continuar com a lógica normal
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
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Listar atividades avaliadas pelo orientador
        $usuario = AuthMiddleware::requireOrientador();
        
        $controller = new AtividadeComplementarController();
        ob_start();
        $controller->listarAvaliadasOrientadorComJWT($usuario['id']);
        $output = ob_get_clean();
        
        $jsonData = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON inválido em listar avaliadas: " . $output);
            enviarErro('Resposta inválida do servidor');
        }
        
        echo $output;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Avaliar atividade
        $usuario = AuthMiddleware::requireOrientador();
        
        error_log("=== INÍCIO AVALIAÇÃO ===");
        error_log("Usuário orientador: " . json_encode($usuario));
        error_log("POST data: " . file_get_contents('php://input'));
        
        $controller = new AtividadeComplementarController();
        
        // Não usar ob_start aqui para capturar erros melhor
        try {
            $controller->avaliarAtividadeComJWT($usuario['id']);
        } catch (Exception $e) {
            error_log("Erro na rota avaliar_atividade: " . $e->getMessage());
            enviarErro('Erro ao processar avaliação: ' . $e->getMessage());
        }
        
        error_log("=== FIM AVALIAÇÃO ===");
    } else {
        enviarErro('Método não permitido', 405);
    }
} catch (Exception $e) {
    error_log("Erro na rota avaliar_atividade: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>
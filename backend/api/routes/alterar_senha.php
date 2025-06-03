<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;

function validarSenha($senha) {
    $erros = [];
    
    if (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra maiúscula";
    }
    
    if (!preg_match('/[a-z]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra minúscula";
    }
    
    if (!preg_match('/[0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um número";
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\?]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos um símbolo (!@#$%^&*()_+-=[]{}|;':\".,<>?)";
    }
    
    return $erros;
}

try {
    $db = Database::getInstance()->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Validar token
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token é obrigatório']);
            exit;
        }

        $stmt = $db->prepare("SELECT usuario_id FROM recuperarsenha WHERE token = ? AND criacao > DATE_SUB(NOW(), INTERVAL 20 MINUTE)");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $recovery = $result->fetch_assoc();

        if (!$recovery) {
            http_response_code(400);
            echo json_encode(['error' => 'Token inválido ou expirado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Token válido'
        ]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Alterar senha
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !isset($data['token']) || !isset($data['nova_senha']) || !isset($data['confirmar_senha'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Todos os campos são obrigatórios']);
            exit;
        }

        $token = $data['token'];
        $nova_senha = $data['nova_senha'];
        $confirmar_senha = $data['confirmar_senha'];

        // Validar se as senhas coincidem
        if ($nova_senha !== $confirmar_senha) {
            http_response_code(400);
            echo json_encode(['error' => 'As senhas não coincidem']);
            exit;
        }

        // Validar critérios da senha
        $erros_senha = validarSenha($nova_senha);
        if (!empty($erros_senha)) {
            http_response_code(400);
            echo json_encode(['error' => implode('; ', $erros_senha)]);
            exit;
        }

        // Verificar token
        $stmt = $db->prepare("SELECT usuario_id FROM recuperarsenha WHERE token = ? AND criacao > DATE_SUB(NOW(), INTERVAL 20 MINUTE)");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $recovery = $result->fetch_assoc();

        if (!$recovery) {
            http_response_code(400);
            echo json_encode(['error' => 'Token inválido ou expirado']);
            exit;
        }

        // Atualizar senha
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE Usuario SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $senha_hash, $recovery['usuario_id']);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar senha']);
            exit;
        }

        // Remover token usado
        $stmt = $db->prepare("DELETE FROM recuperarsenha WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
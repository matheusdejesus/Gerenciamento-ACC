<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\middleware\ApiKeyMiddleware;
use backend\api\middleware\AuthMiddleware;

ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

set_exception_handler(function($e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
    ob_end_flush();
    exit;
});

try {
    require_once __DIR__ . '/../config/config.php';

    if (!isset($pdo)) {
        throw new Exception('Erro de conexão com o banco de dados');
    }

    // Validar API Key
    if (!ApiKeyMiddleware::validateApiKey()) {
        throw new Exception('API Key inválida');
    }

    // Verificar autenticação JWT
    $user = AuthMiddleware::validateToken();
    if (!$user || $user['tipo'] !== 'aluno') {
        throw new Exception('Acesso negado - usuário deve ser aluno');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Validar campos obrigatórios
    $titulo = trim($_POST['titulo_avulso'] ?? '');
    $horas = intval($_POST['horas_avulso'] ?? 0);
    $coordenador_id = intval($_POST['coordenador_id'] ?? 0);
    $observacao = trim($_POST['observacao_avulso'] ?? '');

    if (empty($titulo)) {
        throw new Exception('Título é obrigatório');
    }

    if ($horas <= 0 || $horas > 200) {
        throw new Exception('Carga horária deve ser entre 1 e 200 horas');
    }

    if ($coordenador_id <= 0) {
        throw new Exception('Coordenador é obrigatório');
    }

    // Verificar se o arquivo foi enviado
    if (!isset($_FILES['arquivo_comprovante']) || $_FILES['arquivo_comprovante']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = 'Arquivo do certificado é obrigatório';
        if (isset($_FILES['arquivo_comprovante']['error'])) {
            $error_msg .= ' (Erro: ' . $_FILES['arquivo_comprovante']['error'] . ')';
        }
        throw new Exception($error_msg);
    }

    $arquivo = $_FILES['arquivo_comprovante'];

    // Validar tipo e tamanho do arquivo
    $extensoesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extensao, $extensoesPermitidas)) {
        throw new Exception('Formato de arquivo não permitido. Use: PDF, JPG, JPEG ou PNG');
    }

    if ($arquivo['size'] > 10 * 1024 * 1024) { // 10MB
        throw new Exception('Arquivo muito grande. Máximo: 10MB');
    }

    // Verificar se o coordenador existe
    $stmt = $pdo->prepare("
        SELECT u.id, u.nome, c.curso_id 
        FROM Usuario u 
        JOIN Coordenador c ON u.id = c.usuario_id 
        WHERE u.id = ? AND u.tipo = 'coordenador'
    ");
    $stmt->execute([$coordenador_id]);
    $coordenador = $stmt->fetch();

    if (!$coordenador) {
        throw new Exception('Coordenador não encontrado');
    }

    // Criar diretório se não existir e verificar permissão
    $uploadDir = __DIR__ . '/../../uploads/certificados_avulsos/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Erro ao criar diretório de upload');
        }
    }
    if (!is_writable($uploadDir)) {
        throw new Exception('Diretório de upload não tem permissão de escrita');
    }

    // Gerar nome único para o arquivo
    $nomeArquivo = 'cert_avulso_' . $user['id'] . '_' . time() . '.' . $extensao;
    $caminhoCompleto = $uploadDir . $nomeArquivo;

    // Mover arquivo para o diretório final
    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        throw new Exception('Erro ao salvar arquivo');
    }

    // Inserir na tabela certificadoavulso
    $stmt = $pdo->prepare("
        INSERT INTO certificadoavulso 
        (aluno_id, coordenador_id, titulo, observacao, horas, caminho_arquivo, status, data_envio) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pendente', NOW())
    ");
    
    $stmt->execute([
        $user['id'],
        $coordenador_id,
        $titulo,
        $observacao,
        $horas,
        $nomeArquivo
    ]);

    $certificado_id = $pdo->lastInsertId();

    // Log da ação
    $stmt = $pdo->prepare("
        INSERT INTO LogAcoes (usuario_id, acao, descricao) 
        VALUES (?, 'envio_certificado_avulso', ?)
    ");
    $stmt->execute([
        $user['id'],
        "Enviou certificado avulso: {$titulo} ({$horas}h) para análise do coordenador {$coordenador['nome']}"
    ]);

    // Limpar buffer e enviar resposta JSON
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Certificado enviado com sucesso! Aguarde a análise do coordenador.',
        'data' => [
            'id' => $certificado_id,
            'titulo' => $titulo,
            'horas' => $horas,
            'coordenador' => $coordenador['nome'],
            'status' => 'Pendente'
        ]
    ]);
    ob_end_flush();

} catch (Exception $e) {
    if (isset($caminhoCompleto) && file_exists($caminhoCompleto)) {
        unlink($caminhoCompleto);
    }

    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    ob_end_flush();
}
?>
<?php
// Configurar output buffering e headers
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');

// Log de debug - início do script
error_log("[DEBUG] calcular_horas_categorias.php - Início do script. Método: " . $_SERVER['REQUEST_METHOD']);

// Limpar qualquer output anterior
ob_clean();

ini_set('display_errors', 1);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../services/JWTService.php';

use backend\api\config\Database;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\services\JWTService;

function enviarErro($mensagem, $codigo = 400) {
    error_log("[DEBUG] enviarErro chamada - Código: $codigo, Mensagem: $mensagem");
    ob_clean();
    http_response_code($codigo);
    $response = [
        'success' => false,
        'error' => $mensagem
    ];
    echo json_encode($response);
    exit;
}

function enviarSucesso($dados) {
    error_log("[DEBUG] enviarSucesso chamada");
    ob_clean();
    http_response_code(200);
    $response = [
        'success' => true,
        'data' => $dados
    ];
    echo json_encode($response);
    exit;
}

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        enviarErro('Método não permitido', 405);
    }

    // Verificar autenticação - permitir acesso temporário para debug
    $usuarioLogado = ['id' => 1, 'nome' => 'Aluno', 'tipo' => 'aluno', 'matricula' => '2021014960'];
    
    // Tentar autenticação real primeiro
    $usuarioReal = ApiKeyMiddleware::verificarApiKey();
    if ($usuarioReal) {
        $usuarioLogado = $usuarioReal;
        error_log("[DEBUG] Usuário autenticado via API Key: " . json_encode($usuarioLogado));
    } else {
        error_log("[DEBUG] API Key não encontrada, tentando JWT...");
        $usuarioReal = AuthMiddleware::validateToken();
        if ($usuarioReal) {
            $usuarioLogado = $usuarioReal;
            error_log("[DEBUG] Usuário autenticado via JWT: " . json_encode($usuarioLogado));
        } else {
            error_log("[DEBUG] Usando usuário padrão para debug");
        }
    }
    error_log("[DEBUG] Usuário logado: " . json_encode($usuarioLogado));

    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $aluno_id = $input['aluno_id'] ?? $usuarioLogado['id'];
    
    // DEBUG: Criar aluno de teste se não existir
    if ($aluno_id == 1) {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        
        // Verificar se o usuário existe
        $checkUser = $conn->prepare("SELECT id FROM usuario WHERE id = 1");
        $checkUser->execute();
        $userResult = $checkUser->get_result();
        
        if ($userResult->num_rows === 0) {
            // Criar usuário de teste
            $insertUser = $conn->prepare("INSERT INTO usuario (id, nome, email, senha, tipo) VALUES (1, 'Aluno Teste', 'aluno@teste.com', 'senha123', 'aluno')");
            $insertUser->execute();
        }
        
        // Verificar se o aluno existe
        $checkAluno = $conn->prepare("SELECT usuario_id FROM aluno WHERE usuario_id = 1");
        $checkAluno->execute();
        $alunoResult = $checkAluno->get_result();
        
        if ($alunoResult->num_rows === 0) {
            // Criar aluno de teste com matrícula única
            $matriculaTeste = '2021014960';
            $checkMatricula = $conn->prepare("SELECT matricula FROM aluno WHERE matricula = ?");
            $checkMatricula->bind_param("s", $matriculaTeste);
            $checkMatricula->execute();
            $matriculaResult = $checkMatricula->get_result();
            
            if ($matriculaResult->num_rows > 0) {
                $matriculaTeste = '2021014961'; // Usar matrícula alternativa
            }
            
            $insertAluno = $conn->prepare("INSERT INTO aluno (usuario_id, matricula, curso_id) VALUES (1, ?, 1)");
            $insertAluno->bind_param("s", $matriculaTeste);
            $insertAluno->execute();
        }
    }
    
    if (!$aluno_id) {
        enviarErro('ID do aluno é obrigatório');
    }

    error_log("[DEBUG] Calculando horas para aluno ID: " . $aluno_id);

    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->getConnection();

    // Verificar elegibilidade do aluno (matrícula 2017-2022)
    $queryAluno = "SELECT matricula FROM Aluno WHERE usuario_id = ?";
    $stmtAluno = $conn->prepare($queryAluno);
    $stmtAluno->bind_param("i", $aluno_id);
    $stmtAluno->execute();
    $resultAluno = $stmtAluno->get_result();
    
    if ($resultAluno->num_rows === 0) {
        enviarErro('Aluno não encontrado');
    }
    
    $aluno = $resultAluno->fetch_assoc();
    $matricula = $aluno['matricula'];
    
    // Extrair ano da matrícula (primeiros 4 dígitos)
    $anoMatricula = intval(substr($matricula, 0, 4));
    
    // Verificar se é elegível (matrícula 2017-2022)
    $elegivel = ($anoMatricula >= 2017 && $anoMatricula <= 2022);
    
    if (!$elegivel) {
        enviarSucesso([
            'elegivel' => false,
            'matricula' => $matricula,
            'ano_matricula' => $anoMatricula,
            'message' => 'Aluno não elegível para o dashboard de 240h (matrícula deve ser entre 2017-2022)'
        ]);
    }

    // Calcular horas por categoria
    $categorias = [
        'acc' => 0,
        'ensino' => 0,
        'pesquisa' => 0,
        'estagio' => 0,
        'acao_social' => 0
    ];

    // 1. Atividades ACC
    $queryACC = "SELECT SUM(horas_realizadas) as total FROM atividadecomplementaracc 
                 WHERE aluno_id = ? AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
    $stmtACC = $conn->prepare($queryACC);
    $stmtACC->bind_param("i", $aluno_id);
    $stmtACC->execute();
    $resultACC = $stmtACC->get_result();
    $rowACC = $resultACC->fetch_assoc();
    $categorias['acc'] = floatval($rowACC['total'] ?? 0);

    // 2. Atividades de Ensino
    $queryEnsino = "SELECT SUM(carga_horaria) as total FROM AtividadeComplementarEnsino 
                    WHERE aluno_id = ? AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
    $stmtEnsino = $conn->prepare($queryEnsino);
    $stmtEnsino->bind_param("i", $aluno_id);
    $stmtEnsino->execute();
    $resultEnsino = $stmtEnsino->get_result();
    $rowEnsino = $resultEnsino->fetch_assoc();
    $categorias['ensino'] = floatval($rowEnsino['total'] ?? 0);

    // 3. Atividades de Pesquisa
    $queryPesquisa = "SELECT SUM(horas_realizadas) as total FROM atividadecomplementarpesquisa 
                      WHERE aluno_id = ? AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
    $stmtPesquisa = $conn->prepare($queryPesquisa);
    $stmtPesquisa->bind_param("i", $aluno_id);
    $stmtPesquisa->execute();
    $resultPesquisa = $stmtPesquisa->get_result();
    $rowPesquisa = $resultPesquisa->fetch_assoc();
    $categorias['pesquisa'] = floatval($rowPesquisa['total'] ?? 0);

    // 4. Atividades de Estágio
    $queryEstagio = "SELECT SUM(horas) as total FROM atividadecomplementarestagio 
                      WHERE aluno_id = ? AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
    $stmtEstagio = $conn->prepare($queryEstagio);
    $stmtEstagio->bind_param("i", $aluno_id);
    $stmtEstagio->execute();
    $resultEstagio = $stmtEstagio->get_result();
    $rowEstagio = $resultEstagio->fetch_assoc();
    $categorias['estagio'] = floatval($rowEstagio['total'] ?? 0);

    // 5. Atividades de Ação Social
    $queryAcaoSocial = "SELECT SUM(horas_realizadas) as total FROM atividadessociaiscomunitarias 
                        WHERE aluno_id = ? AND status IN ('Aguardando avaliação', 'aprovado', 'aprovada')";
    $stmtAcaoSocial = $conn->prepare($queryAcaoSocial);
    $stmtAcaoSocial->bind_param("i", $aluno_id);
    $stmtAcaoSocial->execute();
    $resultAcaoSocial = $stmtAcaoSocial->get_result();
    $rowAcaoSocial = $resultAcaoSocial->fetch_assoc();
    $categorias['acao_social'] = floatval($rowAcaoSocial['total'] ?? 0);

    // Calcular total geral
    $totalHorasSoma = array_sum($categorias);
    
    // IMPORTANTE: Limitar o total a 240 horas máximo
    $totalHoras = min(240, $totalHorasSoma);

    // Definir limites específicos por categoria para alunos 2017-2022
    $limites = [
        'acc' => 80,           // Atividades Complementares Curriculares
        'ensino' => 80,        // Atividades de Ensino
        'pesquisa' => 80,      // Atividades de Pesquisa
        'estagio' => 100,      // Atividades de Estágio
        'acao_social' => 30    // Atividades de Ação Social
    ];

    // Preparar resposta
    $response = [
        'elegivel' => true,
        'matricula' => $matricula,
        'total_horas' => $totalHoras,
        'total_horas_soma' => $totalHorasSoma, // Soma real das categorias para debug
        'limite_total' => 240,
        'categorias' => $categorias,
        'limites' => $limites,
        'porcentagem_total' => min(100, ($totalHoras / 240) * 100),
        'status' => $totalHoras >= 240 ? 'completo' : ($totalHoras >= 200 ? 'atencao' : 'normal')
    ];

    error_log("[DEBUG] Resposta calculada: " . json_encode($response));
    enviarSucesso($response);

} catch (Exception $e) {
    error_log("[ERROR] Erro ao calcular horas por categoria: " . $e->getMessage());
    error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>
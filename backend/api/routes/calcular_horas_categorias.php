<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');

error_log("[DEBUG] calcular_horas_categorias.php - start " . $_SERVER['REQUEST_METHOD']);
ob_clean();

ini_set('display_errors', 1);
ini_set('log_errors', 1);
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../models/AvaliarAtividadeModel.php';

use backend\api\config\Database;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\models\AvaliarAtividadeModel;

function enviarErro($mensagem, $codigo = 400) {
    ob_clean();
    http_response_code($codigo);
    echo json_encode(['success' => false, 'error' => $mensagem]);
    exit;
}

function enviarSucesso($dados) {
    ob_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $dados]);
    exit;
}

function normalizarCurso($s) {
    if ($s === null) return '';
    $s = trim((string)$s);
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        if ($conv !== false) $s = $conv;
    }
    if (function_exists('mb_strtolower')) return mb_strtolower($s, 'UTF-8');
    return strtolower($s);
}

function mapearCategoriaNomeParaSlug($nome) {
    // Normaliza e mapeia nomes variados para um dos slugs: acc, ensino, pesquisa, estagio, acao_social
    if ($nome === null) return 'acc';
    $s = trim((string)$nome);
    if ($s === '') return 'acc';
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        if ($conv !== false) $s = $conv;
    }
    $s = strtolower($s);
    // Remover pontuacao simples para evitar falhas de match
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    // Reduzir espacos duplicados
    $s = preg_replace('/\s+/', ' ', $s);

    // Ordem IMPORTANTE: matches mais específicos primeiro
    // Estagio
    if (strpos($s, 'estagio') !== false || preg_match('/\bestag/i', $s)) return 'estagio';
    // Ensino
    if (strpos($s, 'ensino') !== false) return 'ensino';
    // Pesquisa
    if (strpos($s, 'pesquisa') !== false) return 'pesquisa';
    // Acao social / comunitaria
    if (strpos($s, 'social') !== false || strpos($s, 'comunit') !== false || strpos($s, 'acao') !== false) return 'acao_social';
    // Extracurriculares / Extensao / ACC
    if (strpos($s, 'extracurricular') !== false || strpos($s, 'extracurriculares') !== false || strpos($s, 'extensao') !== false || strpos($s, 'acc') !== false) return 'acc';

    // Fallback generico para categorias nao reconhecidas
    return 'acc';
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        enviarErro('Método não permitido', 405);
    }

    // Autenticação leve: tentar API key, depois JWT, senão fallback
    $usuarioLogado = ['id' => 1, 'nome' => 'Aluno', 'tipo' => 'aluno', 'matricula' => '2021014960'];
    try { $usuarioReal = ApiKeyMiddleware::verificarApiKey(); } catch (\Throwable $e) { $usuarioReal = null; }
    if (!$usuarioReal) { try { $usuarioReal = AuthMiddleware::validateToken(); } catch (\Throwable $e) { $usuarioReal = null; } }
    if ($usuarioReal) $usuarioLogado = $usuarioReal;

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $aluno_id = $input['aluno_id'] ?? $usuarioLogado['id'];
    if (!$aluno_id) enviarErro('ID do aluno é obrigatório');

    $conn = Database::getInstance()->getConnection();

    // Buscar dados do aluno com fallbacks
    $safeFetch = function($conn, $queries, $id) {
        $id = intval($id);
        foreach ($queries as $q) {
            try {
                $sql = preg_replace('/\?/', (string)$id, $q, 1);
                $res = $conn->query($sql);
                if ($res instanceof \mysqli_result && $res->num_rows > 0) {
                    return $res->fetch_assoc() ?: null;
                }
            } catch (\Throwable $t) { continue; }
        }
        return null;
    };

    $aluno = $safeFetch($conn, [
        "SELECT a.matricula, a.curso_id, c.nome AS curso_nome FROM aluno a LEFT JOIN curso c ON c.id = a.curso_id WHERE a.usuario_id = ?",
        "SELECT a.matricula, a.curso_id, c.nome AS curso_nome FROM Aluno a LEFT JOIN Curso c ON c.id = a.curso_id WHERE a.usuario_id = ?",
        "SELECT matricula, curso_id FROM aluno WHERE usuario_id = ?",
        "SELECT matricula, curso_id FROM Aluno WHERE usuario_id = ?"
    ], $aluno_id);
    if (!$aluno) enviarErro('Aluno não encontrado', 404);

    $matricula = $aluno['matricula'] ?? '';
    $curso_id = $aluno['curso_id'] ?? null;
    $curso_nome = $aluno['curso_nome'] ?? '';

    if (!$curso_id && isset($usuarioLogado['curso_id'])) $curso_id = $usuarioLogado['curso_id'];
    if ((!$curso_nome || $curso_nome === '') && isset($usuarioLogado['curso_nome'])) $curso_nome = $usuarioLogado['curso_nome'];

    if ($curso_id && (!$curso_nome || $curso_nome === '')) {
        $rowCurso = $safeFetch($conn, [
            "SELECT nome AS curso_nome FROM curso WHERE id = ?",
            "SELECT nome AS curso_nome FROM Curso WHERE id = ?"
        ], $curso_id);
        if ($rowCurso && isset($rowCurso['curso_nome'])) $curso_nome = $rowCurso['curso_nome'];
    }

    $anoMatricula = intval(substr($matricula, 0, 4));
    $nomeNorm = normalizarCurso($curso_nome);
    $isBSI = ($curso_id && intval($curso_id) === 2) || ($nomeNorm && (strpos($nomeNorm, 'sistemas de informacao') !== false || preg_match('/\bsi\b|\bbsi\b|sistemas/i', $nomeNorm)));

    // Limite total dinâmico
    if ($isBSI) {
        $limite_total = 300;
    } elseif ($anoMatricula >= 2023) {
        $limite_total = 120;
    } elseif ($anoMatricula >= 2017 && $anoMatricula <= 2022) {
        $limite_total = 240;
    } else {
        $limite_total = 240;
    }

    // Calcular horas reais aprovadas a partir de atividades_enviadas
    $categorias = [
        'acc' => 0,
        'ensino' => 0,
        'pesquisa' => 0,
        'estagio' => 0,
        'acao_social' => 0
    ];

    try {
        $horasAprovadas = AvaliarAtividadeModel::obterHorasAprovadasAluno(intval($aluno_id));
        if (is_array($horasAprovadas) && isset($horasAprovadas['categorias'])) {
            foreach ($horasAprovadas['categorias'] as $item) {
                $rawNome = $item['categoria_nome'] ?? '';
                $slug = mapearCategoriaNomeParaSlug($rawNome);
                $h = (int)($item['horas'] ?? 0);
                // Log de debug para rastrear mapeamentos
                error_log('[MAP] categoria_nome="' . $rawNome . '" -> slug="' . $slug . '" horas=' . $h);
                if (isset($categorias[$slug])) {
                    $categorias[$slug] += $h;
                }
            }
        }
    } catch (\Throwable $t) {
        error_log('[ERROR] calcular_horas_categorias.php: falha ao obter horas aprovadas - ' . $t->getMessage());
    }

    $totalHorasSoma = array_sum($categorias);
    $totalHoras = min($limite_total, $totalHorasSoma);

    $limites = [
        'acc' => 80,
        'ensino' => 80,
        'pesquisa' => 80,
        'estagio' => 100,
        'acao_social' => 30
    ];

    $response = [
        'elegivel' => true,
        'matricula' => $matricula,
        'curso_id' => $curso_id,
        'curso_nome' => $curso_nome,
        'total_horas' => $totalHoras,
        'total_horas_soma' => $totalHorasSoma,
        'limite_total' => $limite_total,
        'categorias' => $categorias,
        'limites' => $limites,
        'porcentagem_total' => min(100, ($totalHoras / max(1, $limite_total)) * 100),
        'status' => $totalHoras >= $limite_total ? 'completo' : ($totalHoras >= max(0, floor($limite_total * 0.83)) ? 'atencao' : 'normal')
    ];

    enviarSucesso($response);

} catch (Throwable $e) {
    error_log("[ERROR] calcular_horas_categorias.php: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage(), 500);
}
?>

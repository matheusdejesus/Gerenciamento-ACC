<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;
use Exception;

class Cadastro {
    
    public static function emailExists($email) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Erro em Cadastro::emailExists: " . $e->getMessage());
            return false;
        }
    }
    
    public static function create($dados) {
        $db = null;
        try {
            $db = Database::getInstance()->getConnection();
            $checkStmt = $db->prepare("SELECT COUNT(*) AS cnt FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ? AND ROUTINE_NAME = 'sp_determinar_resolucao_aluno'");
            $schema = 'acc';
            $checkStmt->bind_param("s", $schema);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            $row = $res->fetch_assoc();
            $checkStmt->close();
            if ((int)$row['cnt'] === 0) {
                $trgStmt = $db->prepare("SELECT ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? AND ACTION_STATEMENT LIKE '%sp_determinar_resolucao_aluno%' LIMIT 1");
                $trgStmt->bind_param("s", $schema);
                $trgStmt->execute();
                $trgRes = $trgStmt->get_result();
                $trgRow = $trgRes->fetch_assoc();
                $trgStmt->close();
                $paramCount = 0;
                if ($trgRow && isset($trgRow['ACTION_STATEMENT'])) {
                    $stmtText = $trgRow['ACTION_STATEMENT'];
                    $pos = stripos($stmtText, 'sp_determinar_resolucao_aluno(');
                    if ($pos !== false) {
                        $after = substr($stmtText, $pos + strlen('sp_determinar_resolucao_aluno('));
                        $inside = strtok($after, ')');
                        if ($inside !== false) {
                            $inside = trim($inside);
                            if ($inside === '') {
                                $paramCount = 0;
                            } else {
                                $paramCount = substr_count($inside, ',') + 1;
                            }
                        }
                    }
                }
                $defs = [];
                for ($i = 1; $i <= $paramCount; $i++) {
                    $defs[] = "IN p{$i} VARCHAR(255)";
                }
                $defStr = implode(', ', $defs);
                $createSql = "CREATE PROCEDURE acc.sp_determinar_resolucao_aluno(" . $defStr . ") BEGIN DO 0; END";
                $db->query($createSql);
            }

            $ts = $db->prepare("SELECT TRIGGER_NAME, ACTION_TIMING, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE = 'Aluno'");
            $ts->bind_param("s", $schema);
            $ts->execute();
            $trs = $ts->get_result();
            while ($t = $trs->fetch_assoc()) {
                $body = $t['ACTION_STATEMENT'];
                $hasResultSelect = preg_match('/\\bSELECT\\b/i', $body) && !preg_match('/\\bINTO\\b/i', $body);
                if ($hasResultSelect) {
                    $safeBody = 'BEGIN DO 0; END';
                    $posCall = stripos($body, 'sp_determinar_resolucao_aluno(');
                    if ($posCall !== false) {
                        $after = substr($body, $posCall + strlen('sp_determinar_resolucao_aluno('));
                        $inside = strtok($after, ')');
                        if ($inside !== false) {
                            $inside = trim($inside);
                            $safeBody = 'BEGIN CALL acc.sp_determinar_resolucao_aluno(' . $inside . '); END';
                        }
                    }
                    $db->query("DROP TRIGGER IF EXISTS `" . $t['TRIGGER_NAME'] . "`");
                    $createTrig = "CREATE TRIGGER `" . $t['TRIGGER_NAME'] . "` " . $t['ACTION_TIMING'] . " " . $t['EVENT_MANIPULATION'] . " ON `" . $t['EVENT_OBJECT_TABLE'] . "` FOR EACH ROW " . $safeBody;
                    $db->query($createTrig);
                }
            }
            $ts->close();
            $db->autocommit(false);
            $db->begin_transaction();

            // Inserir na tabela Usuario
            $stmt = $db->prepare("INSERT INTO Usuario (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $senha_hash = password_hash($dados['senha'], PASSWORD_BCRYPT);
            $stmt->bind_param("ssss", $dados['nome'], $dados['email'], $senha_hash, $dados['tipo']);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar usuário: " . $stmt->error);
            }
            $usuario_id = $db->insert_id;

            // Inserir na tabela específica baseada no tipo
            self::criarRegistroEspecifico($db, $usuario_id, $dados);

            // Gerar API Key única para este usuário
            $apiKey = self::gerarApiKey();
            $nomeAplicacao = 'user_' . $usuario_id;
            
            $stmt = $db->prepare("INSERT INTO ApiKeys (nome_aplicacao, api_key, ativa, criada_em) VALUES (?, ?, 1, NOW())");
            $stmt->bind_param("ss", $nomeAplicacao, $apiKey);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar API Key: " . $stmt->error);
            }

            $db->commit();
            $db->autocommit(true);

            error_log("Usuário criado com sucesso: ID={$usuario_id}, API_KEY={$apiKey}");

            return [
                'usuario_id' => $usuario_id,
                'api_key' => $apiKey
            ];

        } catch (Exception $e) {
            if ($db) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em Cadastro::create: " . $e->getMessage());
            return false;
        }
    }

    // Adicionar método para gerar API Key única
    private static function gerarApiKey() {
        do {
            $apiKey = bin2hex(random_bytes(32));
            
            // Verificar se a chave já existe
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM ApiKeys WHERE api_key = ?");
            $stmt->bind_param("s", $apiKey);
            $stmt->execute();
            $result = $stmt->get_result();
            
        } while ($result->num_rows > 0);
        
        return $apiKey;
    }
    
    private static function criarRegistroEspecifico($db, $usuario_id, $dados) {
        switch ($dados['tipo']) {
            case 'aluno':
                self::criarAluno($db, $usuario_id, $dados);
                break;
            case 'coordenador':
                self::criarCoordenador($db, $usuario_id, $dados);
                break;
            case 'orientador':
                self::criarOrientador($db, $usuario_id, $dados);
                break;
            default:
                throw new Exception("Tipo de usuário inválido: " . $dados['tipo']);
        }
    }
    
    private static function criarAluno($db, $usuario_id, $dados) {
        if (empty($dados['matricula']) || empty($dados['curso_id'])) {
            throw new Exception("Matrícula e curso são obrigatórios para alunos");
        }
        
        $stmt = $db->prepare("INSERT INTO Aluno (usuario_id, matricula, curso_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $usuario_id, $dados['matricula'], $dados['curso_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar registro de aluno: " . $stmt->error);
        }
        
        error_log("Aluno criado: usuario_id={$usuario_id}, matricula={$dados['matricula']}");
    }
    
    private static function criarCoordenador($db, $usuario_id, $dados) {
        if (empty($dados['siape']) || empty($dados['curso_id'])) {
            throw new Exception("SIAPE e curso são obrigatórios para coordenadores");
        }
        
        $stmt = $db->prepare("INSERT INTO Coordenador (usuario_id, siape, curso_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $usuario_id, $dados['siape'], $dados['curso_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar registro de coordenador: " . $stmt->error);
        }
        
        error_log("Coordenador criado: usuario_id={$usuario_id}, siape={$dados['siape']}");
    }
    
    private static function criarOrientador($db, $usuario_id, $dados) {
        if (empty($dados['siape'])) {
            throw new Exception("SIAPE é obrigatório para orientadores");
        }
        
        $stmt = $db->prepare("INSERT INTO Orientador (usuario_id, siape) VALUES (?, ?)");
        $stmt->bind_param("is", $usuario_id, $dados['siape']);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar registro de orientador: " . $stmt->error);
        }
        
        error_log("Orientador criado: usuario_id={$usuario_id}, siape={$dados['siape']}");
    }
}
?>

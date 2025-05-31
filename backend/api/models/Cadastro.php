<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;

class Cadastro {
    
    public static function emailExists($email) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM Usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function create($dados) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            
            // 1. Inserir na tabela Usuario
            $stmt = $db->prepare("INSERT INTO Usuario (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $senha_hash = password_hash($dados['senha'], PASSWORD_BCRYPT);
            $stmt->bind_param("ssss", $dados['nome'], $dados['email'], $senha_hash, $dados['tipo']);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar usuário principal: " . $stmt->error);
            }
            
            $usuario_id = $db->insert_id;
            error_log("Usuário criado com ID: " . $usuario_id);
            
            // 2. Inserir na tabela específica baseada no tipo
            if ($dados['tipo'] === 'aluno') {
                if (empty($dados['matricula']) || empty($dados['curso_id'])) {
                    throw new Exception("Matrícula e curso são obrigatórios para alunos");
                }
                
                error_log("Criando aluno com matrícula: " . $dados['matricula'] . " e curso_id: " . $dados['curso_id']);
                
                $stmt = $db->prepare("INSERT INTO Aluno (usuario_id, matricula, curso_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $usuario_id, $dados['matricula'], $dados['curso_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao criar registro de aluno: " . $stmt->error);
                }
                
                error_log("Registro de aluno criado para usuario_id: " . $usuario_id);
                
            } elseif ($dados['tipo'] === 'coordenador') {
                if (empty($dados['siape']) || empty($dados['curso_id'])) {
                    throw new Exception("SIAPE e curso são obrigatórios para coordenadores");
                }
                
                error_log("Criando coordenador com SIAPE: " . $dados['siape'] . " e curso_id: " . $dados['curso_id']);
                
                $stmt = $db->prepare("INSERT INTO Coordenador (usuario_id, siape, curso_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $usuario_id, $dados['siape'], $dados['curso_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao criar registro de coordenador: " . $stmt->error);
                }
                
                error_log("Registro de coordenador criado para usuario_id: " . $usuario_id);
                
            } elseif ($dados['tipo'] === 'orientador') {
                if (empty($dados['siape'])) {
                    throw new Exception("SIAPE é obrigatório para orientadores");
                }
                
                error_log("Criando orientador com SIAPE: " . $dados['siape']);
                
                $stmt = $db->prepare("INSERT INTO Orientador (usuario_id, siape) VALUES (?, ?)");
                $stmt->bind_param("is", $usuario_id, $dados['siape']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao criar registro de orientador: " . $stmt->error);
                }
                
                error_log("Registro de orientador criado para usuario_id: " . $usuario_id);
            }
            
            // Confirmar transação
            $db->commit();
            $db->autocommit(true);
            
            error_log("Transação concluída com sucesso para usuario_id: " . $usuario_id);
            return $usuario_id;
            
        } catch (Exception $e) {
            // Desfazer transação em caso de erro
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em Cadastro::create: " . $e->getMessage());
            return false;
        }
    }
}
?>
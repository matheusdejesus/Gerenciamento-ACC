<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;

class AtividadeComplementarEnsino {
    
    public static function create($dados) {
        try {
            // Validar dados obrigatórios básicos
            $camposObrigatorios = ['aluno_id', 'categoria_id'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }

            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            // Campos básicos sempre presentes
            $campos = "aluno_id, categoria_id";
            $placeholders = "?, ?";
            $tipos = "ii";
            $valores = [
                $dados['aluno_id'],
                $dados['categoria_id']
            ];

            // Campos específicos para Disciplinas em outras IES
            if (!empty($dados['nome_disciplina'])) {
                $campos .= ", nome_disciplina";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_disciplina'];
            }
            
            if (!empty($dados['nome_instituicao'])) {
                $campos .= ", nome_instituicao";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_instituicao'];
            }
            
            if (!empty($dados['carga_horaria'])) {
                $campos .= ", carga_horaria";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['carga_horaria'];
            }

            // Campos específicos para Monitoria
            if (!empty($dados['nome_disciplina_laboratorio'])) {
                $campos .= ", nome_disciplina_laboratorio";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_disciplina_laboratorio'];
            }
            
            if (!empty($dados['monitor'])) {
                $campos .= ", monitor";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['monitor'];
            }
            
            if (!empty($dados['data_inicio'])) {
                $campos .= ", data_inicio";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['data_inicio'];
            }
            
            if (!empty($dados['data_fim'])) {
                $campos .= ", data_fim";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['data_fim'];
            }

            // Campo para arquivo de declaração/comprovante
            if (!empty($dados['declaracao_caminho'])) {
                $campos .= ", declaracao_caminho";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['declaracao_caminho'];
            }

            $sql = "INSERT INTO AtividadeComplementarEnsino ($campos) VALUES ($placeholders)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            $stmt->bind_param($tipos, ...$valores);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $atividade_id = $db->insert_id;

            $db->commit();
            $db->autocommit(true);

            error_log("Atividade complementar de ensino criada: ID={$atividade_id}, Aluno={$dados['aluno_id']}");

            return $atividade_id;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro ao criar atividade complementar de ensino: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ace.id,
                        ace.aluno_id,
                        ace.categoria_id,
                        ace.nome_disciplina,
                        ace.nome_instituicao,
                        ace.carga_horaria,
                        ace.nome_disciplina_laboratorio,
                        ace.monitor,
                        ace.data_inicio,
                        ace.data_fim,
                        ace.declaracao_caminho,
                        ca.descricao AS categoria_nome
                    FROM AtividadeComplementarEnsino ace
                    INNER JOIN CategoriaAtividade ca ON ace.categoria_id = ca.id
                    WHERE ace.aluno_id = ?
                    ORDER BY ace.id DESC";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_id' => (int)$row['aluno_id'],
                    'categoria_id' => (int)$row['categoria_id'],
                    'nome_disciplina' => $row['nome_disciplina'],
                    'nome_instituicao' => $row['nome_instituicao'],
                    'carga_horaria' => $row['carga_horaria'] ? (int)$row['carga_horaria'] : null,
                    'nome_disciplina_laboratorio' => $row['nome_disciplina_laboratorio'],
                    'monitor' => $row['monitor'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'declaracao_caminho' => $row['declaracao_caminho'],
                    'categoria_nome' => $row['categoria_nome']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEnsino::buscarPorAluno: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ace.*,
                        ca.descricao AS categoria_nome
                    FROM AtividadeComplementarEnsino ace
                    INNER JOIN CategoriaAtividade ca ON ace.categoria_id = ca.id
                    WHERE ace.id = ?";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return [
                    'id' => (int)$row['id'],
                    'aluno_id' => (int)$row['aluno_id'],
                    'categoria_id' => (int)$row['categoria_id'],
                    'nome_disciplina' => $row['nome_disciplina'],
                    'nome_instituicao' => $row['nome_instituicao'],
                    'carga_horaria' => $row['carga_horaria'] ? (int)$row['carga_horaria'] : null,
                    'nome_disciplina_laboratorio' => $row['nome_disciplina_laboratorio'],
                    'monitor' => $row['monitor'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'declaracao_caminho' => $row['declaracao_caminho'],
                    'categoria_nome' => $row['categoria_nome']
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementarEnsino::buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }
}
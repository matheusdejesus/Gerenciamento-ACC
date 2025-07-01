<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;


class AtividadeComplementar {
    public static function create($dados) {
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            $sql = "INSERT INTO AtividadeComplementar (
                        aluno_id, 
                        categoria_id, 
                        titulo, 
                        descricao, 
                        data_inicio, 
                        data_fim, 
                        carga_horaria_solicitada, 
                        declaracao,
                        declaracao_mime,
                        orientador_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            // Correção: usar 'b' para BLOB e 's' para string
            $stmt->bind_param(
                "iisssisbsi",
                $dados['aluno_id'],
                $dados['categoria_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['data_inicio'],
                $dados['data_fim'],
                $dados['carga_horaria_solicitada'],
                $dados['declaracao'], // BLOB
                $dados['declaracao_mime'],
                $dados['orientador_id']
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $atividade_id = $db->insert_id;

            $db->commit();
            $db->autocommit(true);

            error_log("Atividade complementar criada: ID={$atividade_id}, Aluno={$dados['aluno_id']}");

            return $atividade_id;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro ao criar atividade complementar: " . $e->getMessage());
            return false;
        }
    }
    
    public static function buscarPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        ac.carga_horaria_solicitada,
                        ac.carga_horaria_aprovada,
                        ac.status,
                        ac.data_submissao,
                        ac.data_avaliacao,
                        ac.observacoes_Analise,
                        ca.descricao AS categoria_nome,
                        u.nome AS orientador_nome,
                        CASE WHEN ac.declaracao IS NOT NULL THEN 1 ELSE 0 END AS tem_declaracao
                    FROM AtividadeComplementar ac
                    INNER JOIN CategoriaAtividade ca ON ac.categoria_id = ca.id
                    LEFT JOIN Usuario u ON ac.orientador_id = u.id
                    WHERE ac.aluno_id = ?
                    ORDER BY ac.data_submissao DESC";
            
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
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'carga_horaria_aprovada' => $row['carga_horaria_aprovada'] ? (int)$row['carga_horaria_aprovada'] : null,
                    'status' => $row['status'],
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_Analise' => $row['observacoes_Analise'],
                    'categoria_nome' => $row['categoria_nome'],
                    'orientador_nome' => $row['orientador_nome'],
                    'tem_declaracao' => (bool)$row['tem_declaracao']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPorAluno: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarDeclaracaoBlob($atividade_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT declaracao, declaracao_mime FROM AtividadeComplementar WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $atividade_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row && !empty($row['declaracao'])) {
                return [
                    'blob' => $row['declaracao'],
                    'mime' => $row['declaracao_mime'] ?: 'application/octet-stream'
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarDeclaracaoBlob: " . $e->getMessage());
            throw $e;
        }
    }

    public static function listarOrientadores() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT u.id, u.nome, u.email, o.siape
                    FROM Usuario u
                    INNER JOIN Orientador o ON u.id = o.usuario_id
                    WHERE u.tipo = 'orientador'
                    ORDER BY u.nome";
            
            $result = $db->query($sql);
            $orientadores = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $orientadores[] = [
                        'id' => (int)$row['id'],
                        'nome' => $row['nome'],
                        'email' => $row['email'],
                        'siape' => $row['siape'] ?? null
                    ];
                }
            }
            
            return $orientadores;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::listarOrientadores: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarPendentesOrientador($orientador_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        ac.carga_horaria_solicitada,
                        ac.data_submissao,
                        u.nome AS aluno_nome,
                        a.matricula AS aluno_matricula,
                        u.email AS aluno_email,
                        c.nome AS curso_nome
                    FROM AtividadeComplementar ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u ON a.usuario_id = u.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.orientador_id = ? AND ac.status = 'Pendente'
                    ORDER BY ac.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $orientador_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'data_submissao' => $row['data_submissao'],
                    'nome_aluno' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'aluno_email' => $row['aluno_email'],
                    'curso_nome' => $row['curso_nome']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarPendentesOrientador: " . $e->getMessage());
            throw $e;
        }
    }

    public static function buscarAvaliadasOrientador($orientador_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ac.id,
                        ac.titulo,
                        ac.descricao,
                        ac.data_inicio,
                        ac.data_fim,
                        ac.carga_horaria_solicitada,
                        ac.carga_horaria_aprovada,
                        ac.status,
                        ac.data_submissao,
                        ac.data_avaliacao,
                        ac.observacoes_Analise,
                        u.nome AS aluno_nome,
                        a.matricula AS aluno_matricula,
                        u.email AS aluno_email,
                        c.nome AS curso_nome
                    FROM AtividadeComplementar ac
                    INNER JOIN Aluno a ON ac.aluno_id = a.usuario_id
                    INNER JOIN Usuario u ON a.usuario_id = u.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    WHERE ac.orientador_id = ? AND ac.status IN ('Aprovada', 'Rejeitada')
                    ORDER BY ac.data_avaliacao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $orientador_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'carga_horaria_solicitada' => (int)$row['carga_horaria_solicitada'],
                    'carga_horaria_aprovada' => $row['carga_horaria_aprovada'] ? (int)$row['carga_horaria_aprovada'] : null,
                    'status' => $row['status'],
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_Analise' => $row['observacoes_Analise'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'aluno_email' => $row['aluno_email'],
                    'curso_nome' => $row['curso_nome']
                ];
            }
            
            return $atividades;
        } catch (Exception $e) {
            error_log("Erro em AtividadeComplementar::buscarAvaliadasOrientador: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
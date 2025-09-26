<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;

class AtividadeComplementarACC {
    
    /**
     * Criar nova atividade complementar de extensão
     */
    public static function create($dados) {
        try {
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'atividade_disponivel_id', 'horas_realizadas', 'data_inicio', 'data_fim', 'local_instituicao', 'declaracao_caminho', 'curso_evento_nome'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }

            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            $sql = "INSERT INTO atividadecomplementaracc 
                    (aluno_id, atividade_disponivel_id, categoria_id, curso_evento_nome, horas_realizadas, data_inicio, data_fim, local_instituicao, observacoes, declaracao_caminho) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            // Buscar categoria_id da atividade disponível
            $stmt_categoria = $db->prepare("SELECT categoria_id FROM atividadesdisponiveisbcc23 WHERE id = ?");
            $stmt_categoria->bind_param("i", $dados['atividade_disponivel_id']);
            $stmt_categoria->execute();
            $result_categoria = $stmt_categoria->get_result();
            $categoria_data = $result_categoria->fetch_assoc();
            $categoria_id = $categoria_data ? $categoria_data['categoria_id'] : null;
            
            // Preparar variáveis para bind_param (não pode passar expressões por referência)
            $aluno_id = $dados['aluno_id'];
            $atividade_disponivel_id = $dados['atividade_disponivel_id'];
            $horas_realizadas = $dados['horas_realizadas'];
            $data_inicio = $dados['data_inicio'];
            $data_fim = $dados['data_fim'];
            $local_instituicao = $dados['local_instituicao'];
            $observacoes = $dados['observacoes'] ?? null;
            $declaracao_caminho = $dados['declaracao_caminho'];
            
            // Usar campo unificado curso_evento_nome
            $curso_evento_nome = $dados['curso_evento_nome'] ?? null;

            $stmt->bind_param(
                "iissssssss",
                $aluno_id,
                $atividade_disponivel_id,
                $categoria_id,
                $curso_evento_nome,
                $horas_realizadas,
                $data_inicio,
                $data_fim,
                $local_instituicao,
                $observacoes,
                $declaracao_caminho
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $atividade_id = $db->insert_id;

            $db->commit();
            $db->autocommit(true);

            error_log("Atividade complementar ACC criada: ID={$atividade_id}, Aluno={$dados['aluno_id']}");

            return $atividade_id;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro ao criar atividade complementar ACC: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar atividades por aluno
     */
    public static function buscarPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        acc.id,
                        acc.curso_evento_nome,
                        acc.horas_realizadas,
                        acc.data_inicio,
                        acc.data_fim,
                        acc.local_instituicao,
                        acc.observacoes,
                        acc.declaracao_caminho,
                        acc.status,
                        acc.data_submissao,
                        acc.data_avaliacao,
                        acc.observacoes_avaliacao,
                        ad.titulo as atividade_nome,
                        acc.curso_evento_nome as titulo,
                        ad.carga_horaria_maxima_por_atividade as horas_maximas,
                        ca.descricao as categoria_nome,
                        u.nome as avaliador_nome
                    FROM atividadecomplementaracc acc
                    INNER JOIN atividadesdisponiveisbcc23 ad ON acc.atividade_disponivel_id = ad.id
                    INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    LEFT JOIN Coordenador c ON acc.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE acc.aluno_id = ?
                    ORDER BY acc.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            $stmt->bind_param("i", $aluno_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                // Adicionar campo atividade_titulo para compatibilidade
                $row['atividade_titulo'] = $row['atividade_nome'];
                $atividades[] = $row;
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar atividades por aluno: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar atividade por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        acc.*,
                        ad.titulo as atividade_nome,
                        ad.carga_horaria_maxima_por_atividade as horas_maximas,
                        ca.descricao as categoria_nome,
                        u.nome as avaliador_nome,
                        al.matricula,
                        ua.nome as aluno_nome
                    FROM atividadecomplementaracc acc
                    INNER JOIN atividadesdisponiveisbcc23 ad ON acc.atividade_disponivel_id = ad.id
                    INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    INNER JOIN Aluno al ON acc.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON acc.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE acc.id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Erro ao buscar atividade por ID: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Atualizar status da atividade
     */
    public static function atualizarStatus($id, $status, $avaliador_id, $observacoes_avaliacao = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE atividadecomplementaracc 
                    SET status = ?, avaliador_id = ?, observacoes_avaliacao = ?, data_avaliacao = NOW() 
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            $stmt->bind_param("sisi", $status, $avaliador_id, $observacoes_avaliacao, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da atividade: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Listar todas as atividades (para coordenadores)
     */
    public static function listarTodas($filtros = []) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        acc.id,
                        acc.curso_evento_nome,
                        acc.horas_realizadas,
                        acc.data_inicio,
                        acc.data_fim,
                        acc.local_instituicao,
                        acc.status,
                        acc.data_submissao,
                        acc.data_avaliacao,
                        ad.titulo as atividade_nome,
                        ca.descricao as categoria_nome,
                        ua.nome as aluno_nome,
                        al.matricula,
                        u.nome as avaliador_nome
                    FROM atividadecomplementaracc acc
                    INNER JOIN atividadesdisponiveisbcc23 ad ON acc.atividade_disponivel_id = ad.id
                    INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    INNER JOIN Aluno al ON acc.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON acc.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE 1=1";
            
            $params = [];
            $types = "";
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                $sql .= " AND acc.status = ?";
                $params[] = $filtros['status'];
                $types .= "s";
            }
            
            if (!empty($filtros['aluno_id'])) {
                $sql .= " AND acc.aluno_id = ?";
                $params[] = $filtros['aluno_id'];
                $types .= "i";
            }
            
            $sql .= " ORDER BY acc.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro ao listar atividades: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Excluir atividade
     */
    public static function excluir($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "DELETE FROM atividadecomplementaracc WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir atividade: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
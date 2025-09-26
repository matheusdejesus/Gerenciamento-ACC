<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;

class AtividadeComplementarEstagio {
    
    /**
     * Criar nova atividade complementar de estágio
     */
    public static function create($dados) {
        try {
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'atividade_disponivel_id', 'empresa', 'area', 'data_inicio', 'data_fim', 'horas', 'declaracao_caminho'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }

            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            $sql = "INSERT INTO atividadecomplementarestagio 
                    (aluno_id, atividade_disponivel_id, empresa, area, data_inicio, data_fim, horas, declaracao_caminho, status, data_submissao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Aguardando avaliação', NOW())";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            // Preparar variáveis para bind_param (não pode passar expressões por referência)
            $aluno_id = $dados['aluno_id'];
            $atividade_disponivel_id = $dados['atividade_disponivel_id'];
            $empresa = $dados['empresa'];
            $area = $dados['area'];
            $data_inicio = $dados['data_inicio'];
            $data_fim = $dados['data_fim'];
            $horas = $dados['horas'];
            $declaracao_caminho = $dados['declaracao_caminho'];

            $stmt->bind_param(
                "iissssss",
                $aluno_id,
                $atividade_disponivel_id,
                $empresa,
                $area,
                $data_inicio,
                $data_fim,
                $horas,
                $declaracao_caminho
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $atividade_id = $db->insert_id;

            $db->commit();
            $db->autocommit(true);

            error_log("Atividade complementar de estágio criada: ID={$atividade_id}, Aluno={$dados['aluno_id']}");

            return $atividade_id;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro ao criar atividade complementar de estágio: " . $e->getMessage());
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
                        est.id,
                        est.empresa,
                        est.area,
                        est.data_inicio,
                        est.data_fim,
                        est.horas,
                        est.declaracao_caminho,
                        est.status,
                        est.data_submissao,
                        est.data_avaliacao,
                        est.observacoes_avaliacao,
                        u.nome as avaliador_nome,
                        CONCAT('Estágio - ', est.empresa, ' - ', est.area) as atividade_titulo
                    FROM atividadecomplementarestagio est
                    LEFT JOIN Coordenador c ON est.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE est.aluno_id = ?
                    ORDER BY est.data_submissao DESC";
            
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
                $atividades[] = $row;
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar atividades de estágio por aluno: " . $e->getMessage());
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
                        est.*,
                        u.nome as avaliador_nome,
                        al.matricula,
                        ua.nome as aluno_nome
                    FROM atividadecomplementarestagio est
                    INNER JOIN Aluno al ON est.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON est.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE est.id = ?";
            
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
            error_log("Erro ao buscar atividade de estágio por ID: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Atualizar status da atividade
     */
    public static function atualizarStatus($id, $status, $avaliador_id, $observacoes_avaliacao = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE atividadecomplementarestagio 
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
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Atividade não encontrada ou não foi possível atualizar");
            }
            
            error_log("Status da atividade de estágio atualizado: ID={$id}, Status={$status}");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da atividade de estágio: " . $e->getMessage());
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
                        est.*,
                        ua.nome as aluno_nome,
                        al.matricula,
                        u.nome as avaliador_nome
                    FROM atividadecomplementarestagio est
                    INNER JOIN Aluno al ON est.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON est.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id";
            
            $where = [];
            $params = [];
            $types = "";
            
            if (isset($filtros['status'])) {
                $where[] = "est.status = ?";
                $params[] = $filtros['status'];
                $types .= "s";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY est.data_submissao DESC";
            
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
            error_log("Erro ao listar todas as atividades de estágio: " . $e->getMessage());
            throw $e;
        }
    }
}
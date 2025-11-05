<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;
use Exception;

class CadastrarAtividadesModel {
    
    public static function cadastrarAtividade($dados) {
        $db = null;
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();
            
            // Preparar query de inserção
            $sql = "INSERT INTO atividade_enviada (
                        aluno_id, 
                        atividades_por_resolucao_id, 
                        titulo, 
                        descricao, 
                        ch_solicitada, 
                        ch_atribuida, 
                        caminho_declaracao, 
                        status, 
                        observacoes_avaliador, 
                        avaliado_por, 
                        data_avaliacao, 
                        avaliado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar statement: " . $db->error);
            }
            
            // Bind dos parâmetros
            $stmt->bind_param(
                "iissiissssii",
                $dados['aluno_id'],
                $dados['atividades_por_resolucao_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['ch_solicitada'],
                $dados['ch_atribuida'],
                $dados['caminho_declaracao'],
                $dados['status'],
                $dados['observacoes_avaliador'],
                $dados['avaliado_por'],
                $dados['data_avaliacao'],
                $dados['avaliado']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar inserção: " . $stmt->error);
            }
            
            $atividadeId = $db->insert_id;
            
            // Commit da transação
            $db->commit();
            $db->autocommit(true);
            
            return $atividadeId;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            if ($db) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em CadastrarAtividadesModel::cadastrarAtividade: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function verificarAtividadeExiste($aluno_id, $atividades_por_resolucao_id, $titulo) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT id FROM atividade_enviada 
                    WHERE aluno_id = ? 
                    AND atividades_por_resolucao_id = ? 
                    AND titulo = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("iis", $aluno_id, $atividades_por_resolucao_id, $titulo);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
            
        } catch (Exception $e) {
            error_log("Erro em CadastrarAtividadesModel::verificarAtividadeExiste: " . $e->getMessage());
            return false;
        }
    }
    
    public static function obterAtividadesPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT ae.*, 
                           ac.titulo as atividade_titulo,
                           ac.descricao as atividade_descricao
                    FROM atividade_enviada ae
                    INNER JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                    INNER JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    WHERE ae.aluno_id = ?
                    ORDER BY ae.id DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em CadastrarAtividadesModel::obterAtividadesPorAluno: " . $e->getMessage());
            return [];
        }
    }
    
    public static function obterAtividadePorId($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT ae.*, 
                           ac.titulo as atividade_titulo,
                           ac.descricao as atividade_descricao,
                           u.nome as avaliador_nome
                    FROM atividade_enviada ae
                    INNER JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                    INNER JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN usuario u ON ae.avaliado_por = u.id
                    WHERE ae.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log("Erro em CadastrarAtividadesModel::obterAtividadePorId: " . $e->getMessage());
            return null;
        }
    }
    
    public static function editarAtividade($id, $dados) {
        $db = null;
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();
            
            // Preparar query de atualização
            $sql = "UPDATE atividade_enviada SET 
                        atividades_por_resolucao_id = ?, 
                        titulo = ?, 
                        descricao = ?, 
                        ch_solicitada = ?";
            
            $params = [
                $dados['atividades_por_resolucao_id'],
                $dados['titulo'],
                $dados['descricao'],
                $dados['ch_solicitada']
            ];
            $types = "issi";
            
            // Adicionar caminho da declaração se fornecido
            if (isset($dados['caminho_declaracao']) && !empty($dados['caminho_declaracao'])) {
                $sql .= ", caminho_declaracao = ?";
                $params[] = $dados['caminho_declaracao'];
                $types .= "s";
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar statement: " . $db->error);
            }
            
            // Bind dos parâmetros
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar atualização: " . $stmt->error);
            }
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Nenhuma atividade foi atualizada. Verifique se o ID existe.");
            }
            
            // Commit da transação
            $db->commit();
            $db->autocommit(true);
            
            return true;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            if ($db) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em CadastrarAtividadesModel::editarAtividade: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
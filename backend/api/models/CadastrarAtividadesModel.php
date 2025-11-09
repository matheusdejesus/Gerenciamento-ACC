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

            // Garantir que a coluna data_submissao exista
            try {
                $checkCol = $db->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'atividade_enviada' AND COLUMN_NAME = 'data_submissao'");
                if ($checkCol) {
                    $rowCol = $checkCol->fetch_assoc();
                    if ((int)$rowCol['cnt'] === 0) {
                        $db->query("ALTER TABLE atividade_enviada ADD COLUMN data_submissao datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
                    }
                }
            } catch (Exception $e) {
                // Não bloquear fluxo caso ocorra erro nesta verificação
                error_log("[WARN] Falha ao garantir coluna data_submissao: " . $e->getMessage());
            }
            
            // Mapear o ID de atividades_por_resolucao (APR) para os campos reais da tabela atividade_enviada
            if (!isset($dados['atividades_por_resolucao_id']) || empty($dados['atividades_por_resolucao_id'])) {
                throw new Exception("ID de atividades_por_resolucao é obrigatório");
            }

            $sqlApr = "SELECT resolucao_id, tipo_atividade_id, atividades_complementares_id FROM atividades_por_resolucao WHERE id = ?";
            $stmtApr = $db->prepare($sqlApr);
            if (!$stmtApr) {
                throw new Exception("Erro ao preparar consulta APR: " . $db->error);
            }
            $stmtApr->bind_param("i", $dados['atividades_por_resolucao_id']);
            if (!$stmtApr->execute()) {
                throw new Exception("Erro ao buscar APR: " . $stmtApr->error);
            }
            $resultApr = $stmtApr->get_result();
            if ($resultApr->num_rows === 0) {
                throw new Exception("Atividade por resolução não encontrada");
            }
            $apr = $resultApr->fetch_assoc();
            $stmtApr->close();

            // Preparar query de inserção
            $sql = "INSERT INTO atividade_enviada (
                        aluno_id,
                        resolucao_id,
                        tipo_atividade_id,
                        atividades_complementares_id,
                        titulo,
                        descricao,
                        ch_solicitada,
                        ch_atribuida,
                        caminho_declaracao,
                        data_submissao,
                        status,
                        observacoes_avaliador,
                        avaliado_por,
                        data_avaliacao,
                        avaliado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar statement: " . $db->error);
            }
            
            // Bind dos parâmetros
            $stmt->bind_param(
                "iiiissiisssssi",
                $dados['aluno_id'],
                $apr['resolucao_id'],
                $apr['tipo_atividade_id'],
                $apr['atividades_complementares_id'],
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
            
            // Obter mapeamento do APR para verificar duplicidade
            $sqlApr = "SELECT resolucao_id, tipo_atividade_id, atividades_complementares_id FROM atividades_por_resolucao WHERE id = ?";
            $stmtApr = $db->prepare($sqlApr);
            if (!$stmtApr) {
                throw new Exception("Erro ao preparar consulta APR: " . $db->error);
            }
            $stmtApr->bind_param("i", $atividades_por_resolucao_id);
            $stmtApr->execute();
            $resApr = $stmtApr->get_result();
            if ($resApr->num_rows === 0) {
                return false;
            }
            $apr = $resApr->fetch_assoc();
            $stmtApr->close();

            $sql = "SELECT id FROM atividade_enviada 
                    WHERE aluno_id = ? 
                    AND resolucao_id = ?
                    AND tipo_atividade_id = ?
                    AND atividades_complementares_id = ?
                    AND titulo = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("iiiis", $aluno_id, $apr['resolucao_id'], $apr['tipo_atividade_id'], $apr['atividades_complementares_id'], $titulo);
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
                           ac.descricao as atividade_descricao,
                           apr.id as atividades_por_resolucao_id
                    FROM atividade_enviada ae
                    INNER JOIN atividades_por_resolucao apr 
                        ON apr.resolucao_id = ae.resolucao_id
                        AND apr.tipo_atividade_id = ae.tipo_atividade_id
                        AND apr.atividades_complementares_id = ae.atividades_complementares_id
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
                           u.nome as avaliador_nome,
                           apr.id as atividades_por_resolucao_id
                    FROM atividade_enviada ae
                    INNER JOIN atividades_por_resolucao apr 
                        ON apr.resolucao_id = ae.resolucao_id
                        AND apr.tipo_atividade_id = ae.tipo_atividade_id
                        AND apr.atividades_complementares_id = ae.atividades_complementares_id
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
            
            // Mapear APR id para os campos reais caso tenha sido alterado
            $apr = null;
            if (isset($dados['atividades_por_resolucao_id']) && !empty($dados['atividades_por_resolucao_id'])) {
                $sqlApr = "SELECT resolucao_id, tipo_atividade_id, atividades_complementares_id FROM atividades_por_resolucao WHERE id = ?";
                $stmtApr = $db->prepare($sqlApr);
                if (!$stmtApr) {
                    throw new Exception("Erro ao preparar consulta APR: " . $db->error);
                }
                $stmtApr->bind_param("i", $dados['atividades_por_resolucao_id']);
                if (!$stmtApr->execute()) {
                    throw new Exception("Erro ao buscar APR: " . $stmtApr->error);
                }
                $resApr = $stmtApr->get_result();
                if ($resApr->num_rows > 0) {
                    $apr = $resApr->fetch_assoc();
                }
                $stmtApr->close();
            }

            // Preparar query de atualização
            $sql = "UPDATE atividade_enviada SET 
                        titulo = ?, 
                        descricao = ?, 
                        ch_solicitada = ?";
            
            $params = [
                $dados['titulo'],
                $dados['descricao'],
                $dados['ch_solicitada']
            ];
            $types = "ssi";

            // Se APR foi fornecido, atualizar os campos correspondentes
            if ($apr) {
                $sql .= ", resolucao_id = ?, tipo_atividade_id = ?, atividades_complementares_id = ?";
                $params[] = $apr['resolucao_id'];
                $params[] = $apr['tipo_atividade_id'];
                $params[] = $apr['atividades_complementares_id'];
                $types .= "iii";
            }
            
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
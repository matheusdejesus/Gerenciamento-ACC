<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;
use Exception;

class AvaliarAtividadeModel {
    
    /**
     * Listar certificados processados (aprovados/rejeitados)
     */
    public static function listarCertificadosProcessados($coordenadorId) {
        try {
            $conn = Database::getInstance()->getConnection();
            
            // Query para buscar atividades processadas (aprovadas ou rejeitadas)
            $sql = "SELECT 
                        ae.id,
                        ae.titulo,
                        ae.descricao,
                        ae.ch_solicitada,
                        ae.ch_atribuida,
                        ae.status,
                        ae.observacoes_avaliador,
                        ae.data_avaliacao,
                        ae.data_envio,
                        ae.caminho_declaracao,
                        u.nome as aluno_nome,
                        al.matricula as aluno_matricula,
                        c.nome as curso_nome,
                        ac.titulo as atividade_titulo,
                        ta.nome as categoria_nome,
                        coord.nome as avaliador_nome
                    FROM atividade_enviada ae
                    LEFT JOIN aluno al ON ae.aluno_id = al.usuario_id
                    LEFT JOIN usuario u ON al.usuario_id = u.id
                    LEFT JOIN curso c ON al.curso_id = c.id
                    LEFT JOIN atividades_por_resolucao apr ON ae.atividades_por_resolucao_id = apr.id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    LEFT JOIN usuario coord ON ae.avaliado_por = coord.id
                    WHERE ae.status IN ('aprovado', 'rejeitado')
                    AND ae.avaliado = 1
                    ORDER BY ae.data_avaliacao DESC";
            
            error_log("SQL Query (Certificados Processados): " . $sql);
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conn->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $certificados = [];
            
            while ($row = $result->fetch_assoc()) {
                $certificados[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'descricao' => $row['descricao'],
                    'ch_solicitada' => (int)$row['ch_solicitada'],
                    'ch_atribuida' => (int)$row['ch_atribuida'],
                    'status' => $row['status'],
                    'observacoes_avaliador' => $row['observacoes_avaliador'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'data_envio' => $row['data_envio'],
                    'caminho_declaracao' => $row['caminho_declaracao'],
                    'aluno_nome' => $row['aluno_nome'],
                    'aluno_matricula' => $row['aluno_matricula'],
                    'curso_nome' => $row['curso_nome'],
                    'atividade_titulo' => $row['atividade_titulo'],
                    'categoria_nome' => $row['categoria_nome'],
                    'avaliador_nome' => $row['avaliador_nome']
                ];
            }
            
            error_log("Certificados processados encontrados: " . count($certificados));
            return $certificados;
            
        } catch (Exception $e) {
            error_log("Erro em AvaliarAtividadeModel::listarCertificadosProcessados: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Aprovar certificado
     */
    public static function aprovarCertificado($atividadeId, $observacoes, $coordenadorId, $chAtribuida) {
        $db = null;
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();
            
            // Verificar se a atividade existe e está pendente
            $sqlCheck = "SELECT id, status FROM atividade_enviada WHERE id = ? AND status = 'Aguardando avaliação'";
            $stmtCheck = $db->prepare($sqlCheck);
            if (!$stmtCheck) {
                throw new Exception("Erro ao preparar verificação: " . $db->error);
            }
            
            $stmtCheck->bind_param("i", $atividadeId);
            if (!$stmtCheck->execute()) {
                throw new Exception("Erro ao verificar atividade: " . $stmtCheck->error);
            }
            
            $result = $stmtCheck->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Atividade não encontrada ou já foi avaliada");
            }
            
            // Validar carga horária atribuída
            if (!is_numeric($chAtribuida) || $chAtribuida <= 0) {
                throw new Exception("Carga horária atribuída deve ser um número positivo");
            }
            
            // Atualizar status para aprovado e definir carga horária atribuída
            $sql = "UPDATE atividade_enviada SET 
                        status = 'aprovado',
                        ch_atribuida = ?,
                        observacoes_avaliador = ?,
                        avaliado_por = ?,
                        data_avaliacao = NOW(),
                        avaliado = 1
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar atualização: " . $db->error);
            }
            
            $stmt->bind_param("isii", $chAtribuida, $observacoes, $coordenadorId, $atividadeId);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao aprovar certificado: " . $stmt->error);
            }
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Nenhuma atividade foi atualizada");
            }
            
            // Commit da transação
            $db->commit();
            $db->autocommit(true);
            
            error_log("Certificado ID $atividadeId aprovado com sucesso pelo coordenador $coordenadorId com {$chAtribuida}h atribuídas");
            return true;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            if ($db) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em AvaliarAtividadeModel::aprovarCertificado: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Rejeitar certificado
     */
    public static function rejeitarCertificado($atividadeId, $observacoes, $coordenadorId) {
        $db = null;
        try {
            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();
            
            // Verificar se a atividade existe e está pendente
            $sqlCheck = "SELECT id, status FROM atividade_enviada WHERE id = ? AND status = 'Aguardando avaliação'";
            $stmtCheck = $db->prepare($sqlCheck);
            if (!$stmtCheck) {
                throw new Exception("Erro ao preparar verificação: " . $db->error);
            }
            
            $stmtCheck->bind_param("i", $atividadeId);
            if (!$stmtCheck->execute()) {
                throw new Exception("Erro ao verificar atividade: " . $stmtCheck->error);
            }
            
            $result = $stmtCheck->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Atividade não encontrada ou já foi avaliada");
            }
            
            // Atualizar status para rejeitado
            $sql = "UPDATE atividade_enviada SET 
                        status = 'rejeitado',
                        observacoes_avaliador = ?,
                        avaliado_por = ?,
                        data_avaliacao = NOW(),
                        avaliado = 1,
                        ch_atribuida = 0
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar atualização: " . $db->error);
            }
            
            $stmt->bind_param("sii", $observacoes, $coordenadorId, $atividadeId);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao rejeitar certificado: " . $stmt->error);
            }
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Nenhuma atividade foi atualizada");
            }
            
            // Commit da transação
            $db->commit();
            $db->autocommit(true);
            
            error_log("Certificado ID $atividadeId rejeitado com sucesso pelo coordenador $coordenadorId");
            return true;
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            if ($db) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro em AvaliarAtividadeModel::rejeitarCertificado: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
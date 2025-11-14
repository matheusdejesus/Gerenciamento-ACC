<?php

namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;
use Exception;

class AvaliarAtividadeModel
{

    /**
     * Listar certificados processados (aprovados/rejeitados)
     */
    public static function listarCertificadosProcessados($coordenadorId)
    {
        try {
            $conn = Database::getInstance()->getConnection();

            try {
                $checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'atividade_enviada' AND COLUMN_NAME = 'data_submissao'");
                if ($checkCol) {
                    $rowCol = $checkCol->fetch_assoc();
                    if ((int)$rowCol['cnt'] === 0) {
                        $conn->query("ALTER TABLE atividade_enviada ADD COLUMN data_submissao datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
                    }
                }
            } catch (\Exception $e) {
                error_log("[WARN] Falha ao garantir coluna data_submissao em listarCertificadosProcessados: " . $e->getMessage());
            }

            // Buscar curso do coordenador para filtragem
            $cursoId = null;
            if (!empty($coordenadorId)) {
                $stmtCurso = $conn->prepare("SELECT curso_id FROM coordenador WHERE usuario_id = ? LIMIT 1");
                if ($stmtCurso) {
                    $stmtCurso->bind_param("i", $coordenadorId);
                    if ($stmtCurso->execute()) {
                        $resCurso = $stmtCurso->get_result();
                        $row = $resCurso->fetch_assoc();
                        if ($row && isset($row['curso_id'])) {
                            $cursoId = (int)$row['curso_id'];
                            error_log("AvaliarAtividadeModel::listarCertificadosProcessados - curso_id do coordenador: " . $cursoId);
                        }
                    } else {
                        error_log("Erro ao obter curso do coordenador: " . $stmtCurso->error);
                    }
                    $stmtCurso->close();
                } else {
                    error_log("Erro ao preparar consulta de curso do coordenador: " . $conn->error);
                }
            }

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
                        ae.data_submissao,
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
                    LEFT JOIN atividades_por_resolucao apr 
                        ON apr.resolucao_id = ae.resolucao_id
                        AND apr.tipo_atividade_id = ae.tipo_atividade_id
                        AND apr.atividades_complementares_id = ae.atividades_complementares_id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    LEFT JOIN usuario coord ON ae.avaliado_por = coord.id
                    WHERE ae.status IN ('aprovado', 'rejeitado')
                    AND ae.avaliado = 1";

            // Parâmetros dinâmicos
            $params = [];
            $types = '';
            if (!empty($cursoId)) {
                $sql .= " AND al.curso_id = ?";
                $params[] = $cursoId;
                $types .= 'i';
            }

            $sql .= " ORDER BY ae.data_avaliacao DESC";

            error_log("SQL Query (Certificados Processados): " . $sql);

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Falha ao preparar consulta de certificados processados: " . $conn->error);
                return [];
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                error_log("Falha ao executar consulta de certificados processados: " . $stmt->error);
                return [];
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
                    'data_submissao' => $row['data_submissao'],
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
            return [];
        }
    }

    /**
     * Obter horas aprovadas de um aluno, agregadas por categoria
     */
    public static function obterHorasAprovadasAluno($alunoId) {
        try {
            $conn = Database::getInstance()->getConnection();

            // Query base similar ao listar certificados, mas agregando horas por categoria
            $sql = "SELECT 
                        CASE 
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%estagio%' OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%estágio%' THEN 'estagio'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%ensino%' THEN 'ensino'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%pesquisa%' THEN 'pesquisa'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%social%' 
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%comunit%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%acao%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%ação%'
                              THEN 'acao_social'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%extracurricular%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%extracurriculares%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%extensao%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%acc%'
                              THEN 'acc'
                            ELSE COALESCE(ta_dir.nome, ta.nome)
                        END AS categoria_nome,
                        SUM(COALESCE(ae.ch_atribuida, ae.ch_solicitada, 0)) AS horas
                    FROM atividade_enviada ae
                    LEFT JOIN atividades_por_resolucao apr 
                        ON apr.resolucao_id = ae.resolucao_id
                        AND apr.tipo_atividade_id = ae.tipo_atividade_id
                        AND apr.atividades_complementares_id = ae.atividades_complementares_id
                    LEFT JOIN atividades_complementares ac ON apr.atividades_complementares_id = ac.id
                    LEFT JOIN tipo_atividade ta ON ac.tipo_atividade_id = ta.id
                    LEFT JOIN tipo_atividade ta_dir ON ae.tipo_atividade_id = ta_dir.id
                    WHERE ae.aluno_id = ?
                      AND (LOWER(ae.status) IN ('aprovado','aprovada'))
                      AND ae.avaliado = 1
                      AND COALESCE(ae.ch_atribuida, ae.ch_solicitada, 0) > 0
                    GROUP BY 
                        CASE 
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%estagio%' OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%estágio%' THEN 'estagio'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%ensino%' THEN 'ensino'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%pesquisa%' THEN 'pesquisa'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%social%' 
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%comunit%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%acao%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%ação%'
                              THEN 'acao_social'
                            WHEN LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%extracurricular%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%extracurriculares%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%extensao%'
                              OR LOWER(COALESCE(ta_dir.nome, ta.nome)) LIKE '%acc%'
                              THEN 'acc'
                            ELSE COALESCE(ta_dir.nome, ta.nome)
                        END";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta de horas aprovadas: " . $conn->error);
            }

            $stmt->bind_param('i', $alunoId);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta de horas aprovadas: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $categorias = [];
            $totalHoras = 0;

            while ($row = $result->fetch_assoc()) {
                $horas = (int)($row['horas'] ?? 0);
                $totalHoras += $horas;
                $categorias[] = [
                    'categoria_nome' => $row['categoria_nome'] ?? 'N/A',
                    'horas' => $horas
                ];
            }

            return [
                'total_horas' => $totalHoras,
                'categorias' => $categorias
            ];

        } catch (Exception $e) {
            error_log("Erro em AvaliarAtividadeModel::obterHorasAprovadasAluno: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Aprovar certificado
     */
    public static function aprovarCertificado($atividadeId, $observacoes, $coordenadorId, $chAtribuida)
    {
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

            $rowAe = null;
            $resRow = $db->query("SELECT aluno_id, resolucao_id, tipo_atividade_id, atividades_complementares_id FROM atividade_enviada WHERE id = " . intval($atividadeId) . " LIMIT 1");
            if ($resRow && $resRow->num_rows > 0) { $rowAe = $resRow->fetch_assoc(); }
            if (!$rowAe) { throw new Exception("Falha ao obter contexto da atividade"); }

            $maxTotal = 0;
            $stmtMax = $db->prepare("SELECT carga_horaria_maxima_por_atividade FROM atividades_por_resolucao WHERE resolucao_id = ? AND tipo_atividade_id = ? AND atividades_complementares_id = ? LIMIT 1");
            if ($stmtMax) {
                $stmtMax->bind_param("iii", $rowAe['resolucao_id'], $rowAe['tipo_atividade_id'], $rowAe['atividades_complementares_id']);
                if ($stmtMax->execute()) {
                    $rMax = $stmtMax->get_result();
                    if ($rMax && $rMax->num_rows > 0) {
                        $maxTotal = (int)$rMax->fetch_assoc()['carga_horaria_maxima_por_atividade'];
                    }
                }
                $stmtMax->close();
            }
            if ($maxTotal <= 0) { $maxTotal = (int)$chAtribuida; }

            $stmtSum = $db->prepare("SELECT SUM(COALESCE(ch_atribuida,0)) AS total FROM atividade_enviada WHERE aluno_id = ? AND resolucao_id = ? AND tipo_atividade_id = ? AND atividades_complementares_id = ? AND LOWER(status) IN ('aprovado','aprovada') AND avaliado = 1 AND id <> ?");
            if ($stmtSum) {
                $stmtSum->bind_param("iiiii", $rowAe['aluno_id'], $rowAe['resolucao_id'], $rowAe['tipo_atividade_id'], $rowAe['atividades_complementares_id'], $atividadeId);
                if ($stmtSum->execute()) {
                    $rSum = $stmtSum->get_result();
                    $aprovadas = 0;
                    if ($rSum && $rSum->num_rows > 0) { $aprovadas = (int)($rSum->fetch_assoc()['total'] ?? 0); }
                    $restante = max(0, $maxTotal - $aprovadas);
                    if ($chAtribuida > $restante) {
                        throw new Exception("Carga aprovada excede o máximo permitido para esta atividade. Restante: {$restante}h.");
                    }
                }
                $stmtSum->close();
            }

            // Limite total do curso
            $cursoId = null; $matricula = null;
            $resAluno = $db->query("SELECT curso_id, matricula FROM aluno WHERE usuario_id = " . intval($rowAe['aluno_id']) . " LIMIT 1");
            if ($resAluno && $resAluno->num_rows > 0) { $ra = $resAluno->fetch_assoc(); $cursoId = isset($ra['curso_id']) ? (int)$ra['curso_id'] : null; $matricula = isset($ra['matricula']) ? (string)$ra['matricula'] : null; }
            $anoMatricula = $matricula ? (int)substr($matricula, 0, 4) : null;
            $limiteTotal = ($cursoId === 2) ? 300 : (($anoMatricula && $anoMatricula >= 2023) ? 120 : 240);
            $totGeral = 0;
            $stmtAll = $db->prepare("SELECT SUM(COALESCE(ch_atribuida,0)) AS total FROM atividade_enviada WHERE aluno_id = ? AND LOWER(status) IN ('aprovado','aprovada') AND avaliado = 1 AND id <> ?");
            if ($stmtAll) {
                $stmtAll->bind_param("ii", $rowAe['aluno_id'], $atividadeId);
                if ($stmtAll->execute()) {
                    $rAll = $stmtAll->get_result();
                    if ($rAll && $rAll->num_rows > 0) { $totGeral = (int)($rAll->fetch_assoc()['total'] ?? 0); }
                }
                $stmtAll->close();
            }
            $restanteTotal = max(0, $limiteTotal - $totGeral);
            if ($chAtribuida > $restanteTotal) { throw new Exception("Carga aprovada excede o restante total do curso. Restante: {$restanteTotal}h."); }

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
    public static function rejeitarCertificado($atividadeId, $observacoes, $coordenadorId)
    {
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

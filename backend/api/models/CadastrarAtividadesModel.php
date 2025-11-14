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

            $sqlApr = "SELECT resolucao_id, tipo_atividade_id, atividades_complementares_id, carga_horaria_maxima_por_atividade FROM atividades_por_resolucao WHERE id = ?";
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

            $totalSolicitado = 0;
            $sqlSoma = "SELECT SUM(COALESCE(ch_atribuida,0)) AS total FROM atividade_enviada WHERE aluno_id = ? AND resolucao_id = ? AND tipo_atividade_id = ? AND atividades_complementares_id = ? AND LOWER(status) IN ('aprovado','aprovada') AND avaliado = 1";
            $stmtSoma = $db->prepare($sqlSoma);
            if (!$stmtSoma) {
                throw new Exception("Erro ao preparar soma de horas: " . $db->error);
            }
            $stmtSoma->bind_param("iiii", $dados['aluno_id'], $apr['resolucao_id'], $apr['tipo_atividade_id'], $apr['atividades_complementares_id']);
            if (!$stmtSoma->execute()) {
                throw new Exception("Erro ao executar soma de horas: " . $stmtSoma->error);
            }
            $resSoma = $stmtSoma->get_result();
            if ($resSoma && $resSoma->num_rows > 0) {
                $rowSoma = $resSoma->fetch_assoc();
                $totalSolicitado = (int)($rowSoma['total'] ?? 0);
            }
            $stmtSoma->close();

            $maxTotal = (int)$apr['carga_horaria_maxima_por_atividade'];
            $restante = max(0, $maxTotal - $totalSolicitado);
            if ($restante <= 0) {
                throw new Exception("Limite atingido para esta atividade. Máximo: {$maxTotal}h.");
            }

            // Validação por categoria (somatório aprovado na categoria não pode ultrapassar o limite)
            $stmtAluno = $db->prepare("SELECT curso_id, matricula FROM aluno WHERE usuario_id = ? LIMIT 1");
            $cursoId = null; $matricula = null;
            if ($stmtAluno) {
                $stmtAluno->bind_param("i", $dados['aluno_id']);
                if ($stmtAluno->execute()) {
                    $resAluno = $stmtAluno->get_result();
                    if ($resAluno && $resAluno->num_rows > 0) {
                        $rowAluno = $resAluno->fetch_assoc();
                        $cursoId = isset($rowAluno['curso_id']) ? (int)$rowAluno['curso_id'] : null;
                        $matricula = isset($rowAluno['matricula']) ? (string)$rowAluno['matricula'] : null;
                    }
                }
                $stmtAluno->close();
            }

            $anoMatricula = $matricula ? (int)substr($matricula, 0, 4) : null;
            $isBSI = ($cursoId === 2);
            $limitesCategoria = [];
            if ($isBSI) {
                $limitesCategoria = [ 1 => 80, 2 => 80, 3 => 80, 4 => 100, 5 => 30 ];
            } elseif ($anoMatricula && $anoMatricula >= 2023) {
                $limitesCategoria = [ 1 => 40, 2 => 40, 3 => 40, 4 => 90 ];
            } else {
                $limitesCategoria = [ 1 => 80, 2 => 80, 3 => 80, 4 => 100, 5 => 30 ];
            }

            $tipoId = (int)$apr['tipo_atividade_id'];
            $limiteCategoria = isset($limitesCategoria[$tipoId]) ? (int)$limitesCategoria[$tipoId] : 0;

            if ($limiteCategoria > 0) {
                $totalCategoriaAprovada = 0;
                $stmtCat = $db->prepare("SELECT SUM(COALESCE(ch_atribuida,0)) AS total FROM atividade_enviada WHERE aluno_id = ? AND resolucao_id = ? AND tipo_atividade_id = ? AND LOWER(status) IN ('aprovado','aprovada') AND avaliado = 1");
                if ($stmtCat) {
                    $stmtCat->bind_param("iii", $dados['aluno_id'], $apr['resolucao_id'], $tipoId);
                    if ($stmtCat->execute()) {
                        $resCat = $stmtCat->get_result();
                        if ($resCat && $resCat->num_rows > 0) {
                            $totalCategoriaAprovada = (int)($resCat->fetch_assoc()['total'] ?? 0);
                        }
                    }
                    $stmtCat->close();
                }
                $restanteCategoria = max(0, $limiteCategoria - $totalCategoriaAprovada);
                if ($restanteCategoria <= 0) {
                    throw new Exception("Limite da categoria atingido. Máximo: {$limiteCategoria}h.");
                }
                if ($dados['ch_solicitada'] > $restanteCategoria) {
                    throw new Exception("Horas excedem o restante da categoria. Restante: {$restanteCategoria}h.");
                }
            }

            // Validação de limite TOTAL do curso
            $limiteTotal = 0;
            if ($isBSI) { $limiteTotal = 300; }
            elseif ($anoMatricula && $anoMatricula >= 2023) { $limiteTotal = 120; }
            else { $limiteTotal = 240; }

            $totalAprovadoGeral = 0;
            $stmtTot = $db->prepare("SELECT SUM(COALESCE(ch_atribuida,0)) AS total FROM atividade_enviada WHERE aluno_id = ? AND LOWER(status) IN ('aprovado','aprovada') AND avaliado = 1");
            if ($stmtTot) {
                $stmtTot->bind_param("i", $dados['aluno_id']);
                if ($stmtTot->execute()) {
                    $resTot = $stmtTot->get_result();
                    if ($resTot && $resTot->num_rows > 0) { $totalAprovadoGeral = (int)($resTot->fetch_assoc()['total'] ?? 0); }
                }
                $stmtTot->close();
            }
            $restanteTotal = max(0, $limiteTotal - $totalAprovadoGeral);
            if ($restanteTotal <= 0) { throw new Exception("Limite total atingido. Máximo: {$limiteTotal}h."); }
            if ($dados['ch_solicitada'] > $restanteTotal) { throw new Exception("Horas excedem o restante total. Restante: {$restanteTotal}h."); }

            $minCadastro = 1;
            $maxCadastro = $restante;

            if ($dados['ch_solicitada'] <= 0 || $dados['ch_solicitada'] > $maxCadastro || ($dados['ch_solicitada'] > $restante)) {
                throw new Exception("Horas fora dos limites. Restante: {$restante}h.");
            }

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

    private static function gerarSugestoes($restante, $minCadastro, $maxCadastro) {
        $restante = (int)$restante;
        $minCadastro = max(1, (int)$minCadastro);
        $maxCadastro = max($minCadastro, (int)$maxCadastro);
        $sugs = [];
        $seen = [];
        $add = function($s) use (&$sugs, &$seen) {
            if ($s === '') return;
            if (!isset($seen[$s])) { $seen[$s] = true; $sugs[] = $s; }
        };
        if ($restante <= 0) return "";
        if ($restante >= $minCadastro && $restante <= $maxCadastro) {
            $add("1x" . $restante . "h");
        }
        if ($restante % $maxCadastro === 0) { $add(($restante / $maxCadastro) . "x" . $maxCadastro . "h"); }
        if ($restante % $minCadastro === 0) { $add(($restante / $minCadastro) . "x" . $minCadastro . "h"); }
        for ($k = intdiv($restante, $maxCadastro); $k >= 1 && count($sugs) < 3; $k--) {
            $left = $restante - $k * $maxCadastro;
            if ($left > 0 && $left % $minCadastro === 0) {
                $add($k . "x" . $maxCadastro . "h + " . ($left / $minCadastro) . "x" . $minCadastro . "h");
            } elseif ($left === 0) {
                $add($k . "x" . $maxCadastro . "h");
            }
        }
        if (empty($sugs)) { $add($restante . "h"); }
        return implode(", ", $sugs);
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
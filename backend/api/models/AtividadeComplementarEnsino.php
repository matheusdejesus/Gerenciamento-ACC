<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;

class AtividadeComplementarEnsino {
    
    public static function create($dados) {
        try {
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'categoria_id', 'atividade_disponivel_id'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }

            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            // Campos básicos sempre presentes
            $campos = "aluno_id, categoria_id, atividade_disponivel_id";
            $placeholders = "?, ?, ?";
            $tipos = "iii";
            $valores = [
                $dados['aluno_id'],
                $dados['categoria_id'],
                $dados['atividade_disponivel_id']
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

            // Campos de controle (status padrão é 'Aguardando avaliação')
            $campos .= ", status, data_submissao";
            $placeholders .= ", ?, NOW()";
            $tipos .= "s";
            $valores[] = 'Aguardando avaliação';

            $sql = "INSERT INTO atividadecomplementarensino ($campos) VALUES ($placeholders)";

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
            
            // Buscar a matrícula do aluno para determinar as tabelas corretas
            $sqlMatricula = "SELECT a.matricula FROM aluno a WHERE a.usuario_id = ?";
            $stmtMatricula = $db->prepare($sqlMatricula);
            $stmtMatricula->bind_param("i", $aluno_id);
            $stmtMatricula->execute();
            $resultMatricula = $stmtMatricula->get_result();
            $matricula = $resultMatricula->fetch_assoc()['matricula'] ?? null;
            
            // Usar as tabelas corretas baseado na matrícula
            $sql = "SELECT DISTINCT
                        ace.id,
                        ace.aluno_id,
                        ace.categoria_id,
                        ace.atividade_disponivel_id,
                        ace.nome_disciplina,
                        ace.nome_instituicao,
                        ace.carga_horaria,
                        ace.nome_disciplina_laboratorio,
                        ace.monitor,
                        ace.data_inicio,
                        ace.data_fim,
                        ace.declaracao_caminho,
                        ace.status,
                        ace.data_submissao,
                        ace.data_avaliacao,
                        ace.observacoes_avaliacao,
                        CASE 
                            WHEN SUBSTR(?, 1, 4) >= '2023' THEN ca23.descricao
                            ELSE COALESCE(ca23.descricao, ca17.descricao)
                        END AS categoria_nome,
                        CASE 
                            WHEN SUBSTR(?, 1, 4) >= '2023' THEN ad23.titulo
                            ELSE COALESCE(ad23.titulo, ad17.titulo)
                        END AS atividade_nome,
                        CASE 
                            WHEN ace.nome_disciplina IS NOT NULL THEN ace.nome_disciplina
                            WHEN ace.nome_disciplina_laboratorio IS NOT NULL THEN ace.nome_disciplina_laboratorio
                            ELSE 'Sem título'
                        END AS titulo_personalizado
                    FROM atividadecomplementarensino ace
                    LEFT JOIN categoriaatividadebcc17 ca17 ON ace.categoria_id = ca17.id
                    LEFT JOIN categoriaatividadebcc23 ca23 ON ace.categoria_id = ca23.id
                    LEFT JOIN atividadesdisponiveisbcc17 ad17 ON ace.atividade_disponivel_id = ad17.id
                    LEFT JOIN atividadesdisponiveisbcc23 ad23 ON ace.atividade_disponivel_id = ad23.id
                    WHERE ace.aluno_id = ?
                    ORDER BY ace.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("ssi", $matricula, $matricula, $aluno_id);
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
                    'categoria_nome' => $row['categoria_nome'],
                    'atividade_nome' => $row['atividade_nome'],
                    'titulo' => $row['titulo_personalizado'],
                    'atividade_titulo' => $row['atividade_nome'], // Para compatibilidade
                    'status' => $row['status'],
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_avaliacao' => $row['observacoes_avaliacao']
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
            
            // Buscar a matrícula do aluno para determinar as tabelas corretas
            $sqlMatricula = "SELECT a.matricula FROM atividadecomplementarensino ace 
                            INNER JOIN aluno a ON ace.aluno_id = a.usuario_id 
                            WHERE ace.id = ?";
            $stmtMatricula = $db->prepare($sqlMatricula);
            $stmtMatricula->bind_param("i", $id);
            $stmtMatricula->execute();
            $resultMatricula = $stmtMatricula->get_result();
            $matricula = $resultMatricula->fetch_assoc()['matricula'] ?? null;
            
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
                        ace.status,
                        ace.data_submissao,
                        ace.data_avaliacao,
                        ace.observacoes_avaliacao,
                        CASE 
                            WHEN SUBSTR(?, 1, 4) >= '2023' THEN ca23.descricao
                            ELSE COALESCE(ca23.descricao, ca17.descricao)
                        END AS categoria_nome
                    FROM atividadecomplementarensino ace
                    LEFT JOIN categoriaatividadebcc17 ca17 ON ace.categoria_id = ca17.id
                    LEFT JOIN categoriaatividadebcc23 ca23 ON ace.categoria_id = ca23.id
                    WHERE ace.id = ?";
            
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("si", $matricula, $id);
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

    public static function buscarPendentes() {
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
                        ace.status,
                        ace.data_submissao,
                        ace.data_avaliacao,
                        ace.observacoes_avaliacao,
                        CASE 
                            WHEN SUBSTR(a.matricula, 1, 4) >= '2023' THEN ca23.descricao
                            ELSE COALESCE(ca23.descricao, ca17.descricao)
                        END AS categoria_nome,
                        u.nome AS aluno_nome,
                        c.nome AS curso_nome
                    FROM atividadecomplementarensino ace
                    LEFT JOIN categoriaatividadebcc17 ca17 ON ace.categoria_id = ca17.id
                    LEFT JOIN categoriaatividadebcc23 ca23 ON ace.categoria_id = ca23.id
                    INNER JOIN aluno a ON ace.aluno_id = a.usuario_id
                    INNER JOIN usuario u ON a.usuario_id = u.id
                    INNER JOIN curso c ON a.curso_id = c.id
                    WHERE ace.status = 'Aguardando avaliação'
                    ORDER BY ace.data_submissao ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar atividades pendentes: " . $e->getMessage());
            return [];
        }
    }

    public static function avaliar($id, $status, $observacoes = null, $avaliador_id = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE atividadecomplementarensino 
                    SET status = ?, 
                        data_avaliacao = NOW(), 
                        observacoes_avaliacao = ?, 
                        avaliador_id = ?
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssis", $status, $observacoes, $avaliador_id, $id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro ao avaliar atividade: " . $e->getMessage());
            return false;
        }
    }
}
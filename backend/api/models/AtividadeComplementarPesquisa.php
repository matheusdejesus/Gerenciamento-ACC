<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;

class AtividadeComplementarPesquisa {
    
    /**
     * Criar nova atividade de pesquisa
     */
    public static function create($dados) {
        try {
            error_log("=== INÍCIO CREATE ATIVIDADE PESQUISA ===");
            error_log("Dados recebidos no model: " . json_encode($dados));
            
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'atividade_disponivel_id', 'tipo_atividade', 'horas_realizadas', 'declaracao_caminho'];
            
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    error_log("ERRO: Campo obrigatório vazio: " . $campo);
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }
            error_log("Validação de campos obrigatórios concluída");

            $db = Database::getInstance()->getConnection();
            if (!$db) {
                error_log("ERRO: Falha ao obter conexão com banco de dados");
                throw new Exception("Erro de conexão com banco de dados");
            }
            error_log("Conexão com banco de dados obtida");
            
            // Buscar matrícula do aluno para determinar categoria correta
            $sqlMatricula = "SELECT matricula FROM Aluno WHERE usuario_id = ?";
            $stmtMatricula = $db->prepare($sqlMatricula);
            $stmtMatricula->bind_param("i", $dados['aluno_id']);
            $stmtMatricula->execute();
            $resultMatricula = $stmtMatricula->get_result();
            $matricula = '';
            if ($row = $resultMatricula->fetch_assoc()) {
                $matricula = $row['matricula'];
            }
            error_log("Matrícula do aluno: " . $matricula);
            
            // Determinar categoria_id baseado no currículo (sempre categoria 2 = Pesquisa)
            $categoria_id = 2; // Pesquisa é sempre categoria 2 em ambas as tabelas
            error_log("Categoria determinada: " . $categoria_id);
            
            // Campos base incluindo categoria_id
            $campos = "aluno_id, atividade_disponivel_id, categoria_id, tipo_atividade, horas_realizadas, declaracao_caminho";
            $placeholders = "?, ?, ?, ?, ?, ?";
            $tipos = "iiiiss";
            $valores = [
                $dados['aluno_id'],
                $dados['atividade_disponivel_id'],
                $categoria_id,
                $dados['tipo_atividade'],
                $dados['horas_realizadas'],
                $dados['declaracao_caminho']
            ];
            
            error_log("Campos base preparados: " . $campos);
            error_log("Valores base: " . json_encode($valores));

            // Adicionar local_instituicao se fornecido e não vazio
            if (!empty($dados['local_instituicao']) && trim($dados['local_instituicao']) !== '') {
                $campos .= ", local_instituicao";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['local_instituicao'];
                error_log("Adicionado local_instituicao: " . $dados['local_instituicao']);
            }

            // Campos opcionais específicos por tipo de atividade
            if (!empty($dados['tema'])) {
                $campos .= ", tema";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['tema'];
                error_log("Adicionado tema: " . $dados['tema']);
            }

            if (!empty($dados['quantidade_apresentacoes'])) {
                $campos .= ", quantidade_apresentacoes";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['quantidade_apresentacoes'];
                error_log("Adicionado quantidade_apresentacoes: " . $dados['quantidade_apresentacoes']);
            }

            if (!empty($dados['nome_evento'])) {
                $campos .= ", nome_evento";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_evento'];
                error_log("Adicionado nome_evento: " . $dados['nome_evento']);
            }

            if (!empty($dados['nome_projeto'])) {
                $campos .= ", nome_projeto";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_projeto'];
                error_log("Adicionado nome_projeto: " . $dados['nome_projeto']);
            }

            if (!empty($dados['data_inicio'])) {
                $campos .= ", data_inicio";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['data_inicio'];
                error_log("Adicionado data_inicio: " . $dados['data_inicio']);
            }

            if (!empty($dados['data_fim'])) {
                $campos .= ", data_fim";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['data_fim'];
                error_log("Adicionado data_fim: " . $dados['data_fim']);
            }

            if (!empty($dados['nome_artigo'])) {
                $campos .= ", nome_artigo";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_artigo'];
                error_log("Adicionado nome_artigo: " . $dados['nome_artigo']);
            }

            if (!empty($dados['quantidade_publicacoes'])) {
                $campos .= ", quantidade_publicacoes";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['quantidade_publicacoes'];
                error_log("Adicionado quantidade_publicacoes: " . $dados['quantidade_publicacoes']);
            }

            $sql = "INSERT INTO atividadecomplementarpesquisa ($campos) VALUES ($placeholders)";
            
            error_log("SQL preparado: " . $sql);
            error_log("Tipos de parâmetros: " . $tipos);
            error_log("Valores finais: " . json_encode($valores));
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                error_log("ERRO ao preparar consulta: " . $db->error);
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }

            $stmt->bind_param($tipos, ...$valores);
            
            if (!$stmt->execute()) {
                error_log("ERRO ao executar consulta: " . $stmt->error);
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            $id = $db->insert_id;
            error_log("Atividade criada com sucesso. ID: " . $id);
            
            return $id;

        } catch (Exception $e) {
            error_log("ERRO no model create: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Buscar atividades por aluno
     */
    public static function buscarPorAluno($aluno_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Primeiro, buscar a matrícula do aluno
            $sqlMatricula = "SELECT matricula FROM Aluno WHERE usuario_id = ?";
            $stmtMatricula = $db->prepare($sqlMatricula);
            $stmtMatricula->bind_param("i", $aluno_id);
            $stmtMatricula->execute();
            $resultMatricula = $stmtMatricula->get_result();
            $matricula = $resultMatricula->fetch_assoc()['matricula'] ?? '';
            
            $sql = "SELECT 
                        acp.id,
                        acp.aluno_id,
                        acp.atividade_disponivel_id,
                        acp.tipo_atividade,
                        acp.horas_realizadas,
                        acp.local_instituicao,
                        acp.declaracao_caminho,
                        acp.tema,
                        acp.quantidade_apresentacoes,
                        acp.nome_evento,
                        acp.nome_projeto,
                        acp.data_inicio,
                        acp.data_fim,
                        acp.nome_artigo,
                        acp.quantidade_publicacoes,
                        acp.status,
                        acp.data_submissao,
                        acp.data_avaliacao,
                        acp.observacoes_avaliacao,
                        acp.avaliador_id,
                        CASE 
                            WHEN SUBSTRING(?, 1, 4) >= '2023' THEN ad23.titulo
                            ELSE ad17.titulo
                        END as atividade_titulo,
                        CASE 
                            WHEN acp.nome_evento IS NOT NULL AND acp.nome_evento != '' THEN acp.nome_evento
                            WHEN acp.nome_projeto IS NOT NULL AND acp.nome_projeto != '' THEN acp.nome_projeto
                            WHEN acp.nome_artigo IS NOT NULL AND acp.nome_artigo != '' THEN acp.nome_artigo
                            ELSE 'Sem título'
                        END as titulo_atividade,
                        u.nome as avaliador_nome
                    FROM atividadecomplementarpesquisa acp
                    LEFT JOIN atividadesdisponiveisbcc23 ad23 ON acp.atividade_disponivel_id = ad23.id
                    LEFT JOIN atividadesdisponiveisbcc17 ad17 ON acp.atividade_disponivel_id = ad17.id
                    LEFT JOIN Usuario u ON acp.avaliador_id = u.id
                    WHERE acp.aluno_id = ?
                    ORDER BY acp.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("si", $matricula, $aluno_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividades = [];
            
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'aluno_id' => (int)$row['aluno_id'],
                    'atividade_disponivel_id' => (int)$row['atividade_disponivel_id'],
                    'tipo_atividade' => $row['tipo_atividade'],
                    'horas_realizadas' => (int)$row['horas_realizadas'],
                    'local_instituicao' => $row['local_instituicao'],
                    'declaracao_caminho' => $row['declaracao_caminho'],
                    'tema' => $row['tema'],
                    'quantidade_apresentacoes' => $row['quantidade_apresentacoes'] ? (int)$row['quantidade_apresentacoes'] : null,
                    'nome_evento' => $row['nome_evento'],
                    'nome_projeto' => $row['nome_projeto'],
                    'data_inicio' => $row['data_inicio'],
                    'data_fim' => $row['data_fim'],
                    'nome_artigo' => $row['nome_artigo'],
                    'quantidade_publicacoes' => $row['quantidade_publicacoes'] ? (int)$row['quantidade_publicacoes'] : null,
                    'status' => $row['status'],
                    'data_submissao' => $row['data_submissao'],
                    'data_avaliacao' => $row['data_avaliacao'],
                    'observacoes_avaliacao' => $row['observacoes_avaliacao'],
                    'avaliador_id' => $row['avaliador_id'] ? (int)$row['avaliador_id'] : null,
                    'atividade_titulo' => $row['atividade_titulo'],
                    'titulo_atividade' => $row['titulo_atividade'],
                    'avaliador_nome' => $row['avaliador_nome']
                ];
            }
            
            return $atividades;

        } catch (Exception $e) {
            error_log("Erro ao buscar atividades de pesquisa por aluno: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar atividade por ID
     */
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Primeiro, buscar a matrícula do aluno associado à atividade
            $sqlMatricula = "SELECT a.matricula FROM atividadecomplementarpesquisa acp 
                            INNER JOIN Aluno a ON acp.aluno_id = a.usuario_id 
                            WHERE acp.id = ?";
            $stmtMatricula = $db->prepare($sqlMatricula);
            $stmtMatricula->bind_param("i", $id);
            $stmtMatricula->execute();
            $resultMatricula = $stmtMatricula->get_result();
            $matricula = $resultMatricula->fetch_assoc()['matricula'] ?? '';
            
            $sql = "SELECT 
                        acp.*,
                        CASE 
                            WHEN SUBSTRING(?, 1, 4) >= '2023' THEN ad23.titulo
                            ELSE ad17.titulo
                        END as atividade_titulo,
                        u.nome as avaliador_nome
                    FROM atividadecomplementarpesquisa acp
                    LEFT JOIN atividadesdisponiveisbcc23 ad23 ON acp.atividade_disponivel_id = ad23.id
                    LEFT JOIN atividadesdisponiveisbcc17 ad17 ON acp.atividade_disponivel_id = ad17.id
                    LEFT JOIN Usuario u ON acp.avaliador_id = u.id
                    WHERE acp.id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("si", $matricula, $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Erro ao buscar atividade de pesquisa por ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar status da atividade
     */
    public static function atualizarStatus($id, $status, $observacoes_avaliacao = null, $avaliador_id = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE atividadecomplementarpesquisa 
                    SET status = ?, 
                        observacoes_avaliacao = ?, 
                        avaliador_id = ?, 
                        data_avaliacao = NOW() 
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("ssii", $status, $observacoes_avaliacao, $avaliador_id, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            error_log("Erro ao atualizar status da atividade de pesquisa: " . $e->getMessage());
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
                        acp.id,
                        acp.tipo_atividade,
                        acp.horas_realizadas,
                        acp.local_instituicao,
                        acp.tema,
                        acp.nome_evento,
                        acp.nome_projeto,
                        acp.nome_artigo,
                        acp.status,
                        acp.data_submissao,
                        acp.data_avaliacao,
                        CASE 
                            WHEN SUBSTRING(a.matricula, 1, 4) >= '2023' THEN ad23.titulo
                            ELSE ad17.titulo
                        END as atividade_titulo,
                        u.nome as aluno_nome,
                        u.email as aluno_email,
                        c.nome as curso_nome,
                        av.nome as avaliador_nome
                    FROM atividadecomplementarpesquisa acp
                    INNER JOIN Aluno a ON acp.aluno_id = a.usuario_id
                    INNER JOIN Usuario u ON a.usuario_id = u.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    LEFT JOIN atividadesdisponiveisbcc23 ad23 ON acp.atividade_disponivel_id = ad23.id
                    LEFT JOIN atividadesdisponiveisbcc17 ad17 ON acp.atividade_disponivel_id = ad17.id
                    LEFT JOIN Usuario av ON acp.avaliador_id = av.id";
            
            $condicoes = [];
            $parametros = [];
            $tipos = "";
            
            if (!empty($filtros['status'])) {
                $condicoes[] = "acp.status = ?";
                $parametros[] = $filtros['status'];
                $tipos .= "s";
            }
            
            if (!empty($filtros['curso_id'])) {
                $condicoes[] = "a.curso_id = ?";
                $parametros[] = $filtros['curso_id'];
                $tipos .= "i";
            }
            
            if (!empty($filtros['tipo_atividade'])) {
                $condicoes[] = "acp.tipo_atividade = ?";
                $parametros[] = $filtros['tipo_atividade'];
                $tipos .= "s";
            }
            
            if (!empty($condicoes)) {
                $sql .= " WHERE " . implode(" AND ", $condicoes);
            }
            
            $sql .= " ORDER BY acp.data_submissao DESC";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            if (!empty($parametros)) {
                $stmt->bind_param($tipos, ...$parametros);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = $row;
            }
            
            return $atividades;

        } catch (Exception $e) {
            error_log("Erro ao listar atividades de pesquisa: " . $e->getMessage());
            throw $e;
        }
    }
}
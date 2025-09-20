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
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'atividade_disponivel_id', 'tipo_atividade', 'horas_realizadas', 'declaracao_caminho'];
            
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }

            $db = Database::getInstance()->getConnection();
            
            // Campos base
            $campos = "aluno_id, atividade_disponivel_id, tipo_atividade, horas_realizadas, declaracao_caminho";
            $placeholders = "?, ?, ?, ?, ?";
            $tipos = "iisis";
            $valores = [
                $dados['aluno_id'],
                $dados['atividade_disponivel_id'],
                $dados['tipo_atividade'],
                $dados['horas_realizadas'],
                $dados['declaracao_caminho']
            ];

            // Adicionar local_instituicao se fornecido e não vazio
            if (!empty($dados['local_instituicao']) && trim($dados['local_instituicao']) !== '') {
                $campos .= ", local_instituicao";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['local_instituicao'];
            }

            // Campos opcionais específicos por tipo de atividade
            if (!empty($dados['tema'])) {
                $campos .= ", tema";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['tema'];
            }

            if (!empty($dados['quantidade_apresentacoes'])) {
                $campos .= ", quantidade_apresentacoes";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['quantidade_apresentacoes'];
            }

            if (!empty($dados['nome_evento'])) {
                $campos .= ", nome_evento";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_evento'];
            }

            if (!empty($dados['nome_projeto'])) {
                $campos .= ", nome_projeto";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_projeto'];
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

            if (!empty($dados['nome_artigo'])) {
                $campos .= ", nome_artigo";
                $placeholders .= ", ?";
                $tipos .= "s";
                $valores[] = $dados['nome_artigo'];
            }

            if (!empty($dados['quantidade_publicacoes'])) {
                $campos .= ", quantidade_publicacoes";
                $placeholders .= ", ?";
                $tipos .= "i";
                $valores[] = $dados['quantidade_publicacoes'];
            }

            $sql = "INSERT INTO atividadecomplementarpesquisa ($campos) VALUES ($placeholders)";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }

            $stmt->bind_param($tipos, ...$valores);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }

            return $db->insert_id;

        } catch (Exception $e) {
            error_log("Erro ao criar atividade de pesquisa: " . $e->getMessage());
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
                        ad.titulo as atividade_titulo,
                        u.nome as avaliador_nome
                    FROM atividadecomplementarpesquisa acp
                    LEFT JOIN AtividadesDisponiveis ad ON acp.atividade_disponivel_id = ad.id
                    LEFT JOIN Usuario u ON acp.avaliador_id = u.id
                    WHERE acp.aluno_id = ?
                    ORDER BY acp.data_submissao DESC";
            
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
            
            $sql = "SELECT 
                        acp.*,
                        ad.titulo as atividade_titulo,
                        u.nome as avaliador_nome
                    FROM atividadecomplementarpesquisa acp
                    LEFT JOIN AtividadesDisponiveis ad ON acp.atividade_disponivel_id = ad.id
                    LEFT JOIN Usuario u ON acp.avaliador_id = u.id
                    WHERE acp.id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("i", $id);
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
                        ad.titulo as atividade_titulo,
                        u.nome as aluno_nome,
                        u.email as aluno_email,
                        c.nome as curso_nome,
                        av.nome as avaliador_nome
                    FROM atividadecomplementarpesquisa acp
                    INNER JOIN Aluno a ON acp.aluno_id = a.usuario_id
                    INNER JOIN Usuario u ON a.usuario_id = u.id
                    INNER JOIN Curso c ON a.curso_id = c.id
                    LEFT JOIN AtividadesDisponiveis ad ON acp.atividade_disponivel_id = ad.id
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
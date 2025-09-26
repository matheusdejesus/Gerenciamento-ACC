<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/database.php';

use backend\api\config\Database;
use Exception;

class AtividadeSocialComunitaria {
    
    /**
     * Criar nova atividade social comunitária
     */
    public static function create($dados) {
        try {
            // Validar dados obrigatórios
            $camposObrigatorios = ['aluno_id', 'nome_projeto', 'instituicao', 'carga_horaria', 'descricao_atividades'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($dados[$campo])) {
                    throw new Exception("Campo obrigatório não informado: $campo");
                }
            }

            $db = Database::getInstance()->getConnection();
            $db->autocommit(false);
            $db->begin_transaction();

            $sql = "INSERT INTO atividadessociaiscomunitarias 
                    (aluno_id, nome_projeto, instituicao, atividade_disponivel_id, categoria_id, carga_horaria, local_realizacao, descricao_atividades, declaracao_caminho) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            // Preparar variáveis para bind_param
            $aluno_id = $dados['aluno_id'];
            $nome_projeto = $dados['nome_projeto'];
            $instituicao = $dados['instituicao'];
            $atividade_disponivel_id = $dados['atividade_disponivel_id'] ?? 1; // Default para ação social
            $categoria_id = 5; // ID da categoria Ação Social
            $carga_horaria = $dados['carga_horaria'];
            $local_realizacao = $dados['local_realizacao'] ?? $dados['instituicao'];
            $descricao_atividades = $dados['descricao_atividades'];
            $declaracao_caminho = $dados['declaracao_caminho'] ?? null;

            $stmt->bind_param(
                "issisisss",
                $aluno_id,
                $nome_projeto,
                $instituicao,
                $atividade_disponivel_id,
                $categoria_id,
                $carga_horaria,
                $local_realizacao,
                $descricao_atividades,
                $declaracao_caminho
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $atividade_id = $db->insert_id;

            $db->commit();
            $db->autocommit(true);

            error_log("Atividade social comunitária criada: ID={$atividade_id}, Aluno={$dados['aluno_id']}");

            return $atividade_id;

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            error_log("Erro ao criar atividade social comunitária: " . $e->getMessage());
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
                        asc.id,
                        asc.nome_projeto,
                        asc.instituicao,
                        asc.carga_horaria,
                        asc.local_realizacao,
                        asc.descricao_atividades,
                        asc.declaracao_caminho,
                        asc.status,
                        asc.data_submissao,
                        asc.data_avaliacao,
                        asc.observacoes_avaliacao,
                        'Atividade Social Comunitária' as atividade_nome,
                        asc.nome_projeto as titulo,
                        30 as horas_maximas,
                        'Atividades sociais e comunitárias' as categoria_nome,
                        u.nome as avaliador_nome
                    FROM atividadessociaiscomunitarias asc
                    LEFT JOIN Coordenador c ON asc.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE asc.aluno_id = ?
                    ORDER BY asc.data_submissao DESC";
            
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
                $row['horas_realizadas'] = $row['carga_horaria']; // Compatibilidade
                $atividades[] = $row;
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar atividades sociais por aluno: " . $e->getMessage());
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
                        asc.*,
                        'Atividade Social Comunitária' as atividade_nome,
                        30 as horas_maximas,
                        'Atividades sociais e comunitárias' as categoria_nome,
                        u.nome as avaliador_nome,
                        al.matricula,
                        ua.nome as aluno_nome
                    FROM atividadessociaiscomunitarias asc
                    INNER JOIN Aluno al ON asc.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON asc.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE asc.id = ?";
            
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
            error_log("Erro ao buscar atividade social por ID: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Atualizar status da atividade
     */
    public static function atualizarStatus($id, $status, $observacoes_avaliacao = null, $avaliador_id = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE atividadessociaiscomunitarias 
                    SET status = ?, observacoes_avaliacao = ?, avaliador_id = ?, data_avaliacao = NOW() 
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            $stmt->bind_param("ssii", $status, $observacoes_avaliacao, $avaliador_id, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da atividade social: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar atividades disponíveis da categoria 5 (ação social)
     */
    public static function buscarAtividadesDisponiveis() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $categoria_id = 5; // Categoria de ação social
            error_log("[DEBUG] Model - Buscando atividades disponíveis para categoria_id = {$categoria_id}");
            
            // Primeiro tentar a tabela atividadesdisponiveisbcc17 com categoriaatividadebcc17 (tabela correta para 2017)
            error_log("[DEBUG] Model - Tentando buscar na tabela atividadesdisponiveisbcc17 com categoriaatividadebcc17");
            $sql = "SELECT 
                        ad.id,
                        ad.titulo,
                        ad.carga_horaria_maxima_por_atividade,
                        ad.observacoes,
                        ca.descricao as categoria_nome
                    FROM atividadesdisponiveisbcc17 ad
                    LEFT JOIN categoriaatividadebcc17 ca ON ad.categoria_id = ca.id
                    WHERE ad.categoria_id = ?
                    ORDER BY ad.titulo";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }
            
            $stmt->bind_param("i", $categoria_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            error_log("[DEBUG] Model - Resultados encontrados na atividadesdisponiveisbcc17: " . $result->num_rows);
            
            // Se não retornou resultados, tentar com atividadesdisponiveisbcc23
            if ($result->num_rows === 0) {
                error_log("[DEBUG] Model - Nenhum resultado com atividadesdisponiveisbcc17, tentando atividadesdisponiveisbcc23");
                $sql = "SELECT 
                            ad.id,
                            ad.titulo,
                            ad.carga_horaria_maxima_por_atividade,
                            ad.observacoes,
                            ca.descricao as categoria_nome
                        FROM atividadesdisponiveisbcc23 ad
                        LEFT JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                        WHERE ad.categoria_id = ?
                        ORDER BY ad.titulo";
                
                $stmt = $db->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Erro ao preparar query: " . $db->error);
                }
                
                $stmt->bind_param("i", $categoria_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao executar query: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                
                error_log("[DEBUG] Model - Resultados encontrados na atividadesdisponiveisbcc23: " . $result->num_rows);
                
                // Se ainda não retornou resultados, tentar com AtividadesDisponiveis
                if ($result->num_rows === 0) {
                    error_log("[DEBUG] Model - Nenhum resultado com atividadesdisponiveisbcc23, tentando AtividadesDisponiveis");
                    $sql = "SELECT 
                                ad.id,
                                ad.titulo,
                                ad.carga_horaria_maxima_por_atividade,
                                ad.observacoes,
                                ca.descricao as categoria_nome
                            FROM AtividadesDisponiveis ad
                            LEFT JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                            WHERE ad.categoria_id = ?
                            ORDER BY ad.titulo";
                    
                    $stmt = $db->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Erro ao preparar query: " . $db->error);
                    }
                    
                    $stmt->bind_param("i", $categoria_id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao executar query: " . $stmt->error);
                    }
                    
                    $result = $stmt->get_result();
                    
                    error_log("[DEBUG] Model - Resultados encontrados na AtividadesDisponiveis: " . $result->num_rows);
                }
            }
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                error_log("[DEBUG] Model - Atividade encontrada: " . $row['titulo']);
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'carga_horaria_maxima_por_atividade' => (int)$row['carga_horaria_maxima_por_atividade'],
                    'observacoes' => $row['observacoes'] ?? '',
                    'categoria_nome' => $row['categoria_nome']
                ];
            }
            
            error_log("[DEBUG] Model - Total de atividades retornadas: " . count($atividades));
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitaria::buscarAtividadesDisponiveis: " . $e->getMessage());
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
                        asc.*,
                        'Atividade Social Comunitária' as atividade_nome,
                        'Atividades sociais e comunitárias' as categoria_nome,
                        u.nome as avaliador_nome,
                        al.matricula,
                        ua.nome as aluno_nome
                    FROM atividadessociaiscomunitarias asc
                    INNER JOIN Aluno al ON asc.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON asc.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id";
            
            $where = [];
            $params = [];
            $types = "";
            
            if (!empty($filtros['status'])) {
                $where[] = "asc.status = ?";
                $params[] = $filtros['status'];
                $types .= "s";
            }
            
            if (!empty($filtros['aluno_id'])) {
                $where[] = "asc.aluno_id = ?";
                $params[] = $filtros['aluno_id'];
                $types .= "i";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY asc.data_submissao DESC";
            
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
            error_log("Erro ao listar atividades sociais: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
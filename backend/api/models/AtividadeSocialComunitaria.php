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
                    (aluno_id, nome_projeto, local_instituicao, atividade_disponivel_id, categoria_id, horas_realizadas, descricao_atividades) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            // Preparar variáveis para bind_param
            $aluno_id = $dados['aluno_id'];
            $nome_projeto = $dados['nome_projeto'];
            $local_instituicao = $dados['instituicao'];
            // Buscar dinamicamente o ID da atividade de ação social na tabela BCC17
            $sql_atividade = "SELECT id FROM atividadesdisponiveisbcc17 WHERE titulo LIKE '%ação social%' OR titulo LIKE '%Ação social%' LIMIT 1";
            $stmt_atividade = $db->prepare($sql_atividade);
            if ($stmt_atividade) {
                $stmt_atividade->execute();
                $result_atividade = $stmt_atividade->get_result();
                if ($row_atividade = $result_atividade->fetch_assoc()) {
                    $atividade_disponivel_id = (int)$row_atividade['id'];
                } else {
                    $atividade_disponivel_id = $dados['atividade_disponivel_id'] ?? 34; // ID da atividade de ação social na tabela BCC17
                }
            } else {
                $atividade_disponivel_id = $dados['atividade_disponivel_id'] ?? 34; // Fallback para ID 34 (ação social na BCC17)
            }
            // Buscar dinamicamente o ID da categoria social/comunitária
            $sql_categoria = "SELECT id FROM categoriaatividadebcc23 WHERE descricao LIKE '%social%' OR descricao LIKE '%comunitaria%' OR descricao LIKE '%acao%' OR descricao LIKE '%Ação%' LIMIT 1";
            $stmt_categoria = $db->prepare($sql_categoria);
            if ($stmt_categoria) {
                $stmt_categoria->execute();
                $result_categoria = $stmt_categoria->get_result();
                if ($row_categoria = $result_categoria->fetch_assoc()) {
                    $categoria_id = (int)$row_categoria['id'];
                } else {
                    $categoria_id = 5; // Fallback
                }
            } else {
                $categoria_id = 5; // Fallback se erro na query
            }
            $horas_realizadas = $dados['carga_horaria'];
            $descricao = $dados['descricao_atividades'];

            $stmt->bind_param(
                "issiiss",
                $aluno_id,
                $nome_projeto,
                $local_instituicao,
                $atividade_disponivel_id,
                $categoria_id,
                $horas_realizadas,
                $descricao
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
                        atividade.id,
                        atividade.nome_projeto,
                        atividade.local_instituicao as instituicao,
                        atividade.horas_realizadas,
                        atividade.local_realizacao,
                        atividade.descricao_atividades,
                        atividade.declaracao_caminho,
                        atividade.status,
                        atividade.data_submissao,
                        atividade.data_avaliacao,
                        atividade.observacoes_avaliacao,
                        'Atividade Social Comunitária' as atividade_nome,
                        atividade.nome_projeto as titulo,
                        30 as horas_maximas,
                        'Atividades sociais e comunitárias' as categoria_nome,
                        u.nome as avaliador_nome
                    FROM atividadessociaiscomunitarias atividade
                    LEFT JOIN Coordenador c ON atividade.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE atividade.aluno_id = ?
                    ORDER BY atividade.data_submissao DESC";
            
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
                $row['carga_horaria'] = $row['horas_realizadas']; // Compatibilidade
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
                        atividade.*,
                        'Atividade Social Comunitária' as atividade_nome,
                        30 as horas_maximas,
                        'Atividades sociais e comunitárias' as categoria_nome,
                        u.nome as avaliador_nome,
                        al.matricula,
                        ua.nome as aluno_nome
                    FROM atividadessociaiscomunitarias atividade
                    INNER JOIN Aluno al ON atividade.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON atividade.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id
                    WHERE atividade.id = ?";
            
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
     * Busca em ambas as tabelas (BCC17 e BCC23) para garantir compatibilidade
     */
    public static function buscarAtividadesDisponiveis($aluno_id = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            error_log("[DEBUG] Model - Iniciando busca de atividades disponíveis");
            
            $atividades = [];
            
            // Buscar atividades de ambas as tabelas para garantir compatibilidade
            $tabelas = [
                ['atividades' => 'atividadesdisponiveisbcc17', 'categoria' => 'categoriaatividadebcc17'],
                ['atividades' => 'atividadesdisponiveisbcc23', 'categoria' => 'categoriaatividadebcc23']
            ];
            
            foreach ($tabelas as $tabela_info) {
                $tabela_atividades = $tabela_info['atividades'];
                $tabela_categoria = $tabela_info['categoria'];
                
                error_log("[DEBUG] Model - Buscando em tabelas: {$tabela_atividades} e {$tabela_categoria}");

                // Buscar dinamicamente o ID da categoria social/comunitária
                $sql_categoria = "SELECT id FROM {$tabela_categoria} WHERE descricao LIKE '%social%' OR descricao LIKE '%comunitaria%' OR descricao LIKE '%acao%' OR descricao LIKE '%Ação%' LIMIT 1";
                $stmt_categoria = $db->prepare($sql_categoria);
                if ($stmt_categoria) {
                    $stmt_categoria->execute();
                    $result_categoria = $stmt_categoria->get_result();
                    if ($row_categoria = $result_categoria->fetch_assoc()) {
                        $categoria_id = (int)$row_categoria['id'];
                    } else {
                        $categoria_id = 5; // Fallback
                    }
                } else {
                    $categoria_id = 5; // Fallback se erro na query
                }
                
                $sql = "SELECT 
                            ad.id,
                            ad.titulo,
                            ad.carga_horaria_maxima_por_atividade,
                            ad.observacoes,
                            ca.descricao as categoria_nome
                        FROM {$tabela_atividades} ad
                        LEFT JOIN {$tabela_categoria} ca ON ad.categoria_id = ca.id
                        WHERE ad.categoria_id = ?
                        ORDER BY ad.titulo";
                
                $stmt = $db->prepare($sql);
                if (!$stmt) {
                    error_log("[DEBUG] Model - Erro ao preparar query para {$tabela_atividades}: " . $db->error);
                    continue; // Pular para próxima tabela se houver erro
                }
                
                $stmt->bind_param("i", $categoria_id);
                
                if (!$stmt->execute()) {
                    error_log("[DEBUG] Model - Erro ao executar query para {$tabela_atividades}: " . $stmt->error);
                    continue; // Pular para próxima tabela se houver erro
                }
                
                $result = $stmt->get_result();
                
                error_log("[DEBUG] Model - Resultados encontrados em {$tabela_atividades}: " . $result->num_rows);
                
                while ($row = $result->fetch_assoc()) {
                    error_log("[DEBUG] Model - Atividade encontrada: " . $row['titulo']);
                    
                    // Limpar título primeiro
                    $titulo_limpo = self::limparTitulo($row['titulo']);
                    
                    // Normalizar título para comparação (remover acentos e caracteres especiais)
                    $titulo_normalizado = self::normalizarTitulo($titulo_limpo);
                    
                    error_log("[DEBUG] Model - Título original: '{$row['titulo']}', Limpo: '{$titulo_limpo}', Normalizado: '{$titulo_normalizado}'");
                    
                    // Evitar duplicatas baseado no título normalizado
                    $ja_existe = false;
                    foreach ($atividades as $atividade_existente) {
                        $titulo_existente_normalizado = self::normalizarTitulo($atividade_existente['titulo']);
                        error_log("[DEBUG] Model - Comparando '{$titulo_normalizado}' com '{$titulo_existente_normalizado}'");
                        if ($titulo_existente_normalizado === $titulo_normalizado) {
                            $ja_existe = true;
                            error_log("[DEBUG] Model - Duplicata detectada: '{$row['titulo']}' já existe como '{$atividade_existente['titulo']}'");
                            break;
                        }
                    }
                    
                    if (!$ja_existe) {
                        $atividades[] = [
                            'id' => (int)$row['id'],
                            'titulo' => $titulo_limpo,
                            'carga_horaria_maxima_por_atividade' => (int)$row['carga_horaria_maxima_por_atividade'],
                            'observacoes' => $row['observacoes'] ?? '',
                            'categoria_nome' => $row['categoria_nome']
                        ];
                        
                        error_log("[DEBUG] Model - Atividade adicionada: " . $titulo_limpo);
                    }
                }
            }
            
            error_log("[DEBUG] Model - Total de atividades únicas retornadas: " . count($atividades));
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadeSocialComunitaria::buscarAtividadesDisponiveis: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Normalizar título para comparação (remove acentos, caracteres especiais e converte para minúsculas)
     */
    private static function normalizarTitulo($titulo) {
        // Remover caracteres corrompidos e normalizar
        $titulo = trim($titulo);
        $titulo = mb_strtolower($titulo, 'UTF-8');
        
        // Remover acentos
        $titulo = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $titulo);
        
        // Remover caracteres especiais e espaços extras
        $titulo = preg_replace('/[^a-z0-9\s]/', '', $titulo);
        $titulo = preg_replace('/\s+/', ' ', $titulo);
        
        return trim($titulo);
    }
    
    /**
     * Limpar título para exibição (corrige caracteres corrompidos)
     */
    private static function limparTitulo($titulo) {
        // Corrigir caracteres corrompidos comuns
        $titulo = str_replace(['├º├úo', '├íria'], ['ção', 'ária'], $titulo);
        
        // Se ainda houver caracteres corrompidos, usar versão padrão
        if (preg_match('/[^\x20-\x7E\xC0-\xFF]/', $titulo)) {
            return 'Ação social e comunitária';
        }
        
        return trim($titulo);
    }
    
    /**
     * Listar todas as atividades (para coordenadores)
     */
    public static function listarTodas($filtros = []) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        atividade.*,
                        'Atividade Social Comunitária' as atividade_nome,
                        'Atividades sociais e comunitárias' as categoria_nome,
                        u.nome as avaliador_nome,
                        al.matricula,
                        ua.nome as aluno_nome
                    FROM atividadessociaiscomunitarias atividade
                    INNER JOIN Aluno al ON atividade.aluno_id = al.usuario_id
                    INNER JOIN Usuario ua ON al.usuario_id = ua.id
                    LEFT JOIN Coordenador c ON atividade.avaliador_id = c.usuario_id
                    LEFT JOIN Usuario u ON c.usuario_id = u.id";
            
            $where = [];
            $params = [];
            $types = "";
            
            if (!empty($filtros['status'])) {
                $where[] = "atividade.status = ?";
                $params[] = $filtros['status'];
                $types .= "s";
            }
            
            if (!empty($filtros['aluno_id'])) {
                $where[] = "atividade.aluno_id = ?";
                $params[] = $filtros['aluno_id'];
                $types .= "i";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY atividade.data_submissao DESC";
            
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
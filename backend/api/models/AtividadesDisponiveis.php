<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;
use Exception;

class AtividadesDisponiveis {
    
    /**
     * Determina qual tabela de atividades usar baseado no ano de matrícula
     * @param string $matricula - Matrícula do aluno (formato: YYYYXXXXXX)
     * @return array - Array com nomes das tabelas [atividades, categoria]
     */
    public static function determinarTabelasPorMatricula($matricula) {
        // Extrair o ano da matrícula (primeiros 4 dígitos)
        $anoMatricula = (int)substr($matricula, 0, 4);
        
        // Para alunos de 2017 a 2022: usar tabelas bcc17
        if ($anoMatricula >= 2017 && $anoMatricula <= 2022) {
            return [
                'atividades' => 'atividadesdisponiveisbcc17',
                'categoria' => 'categoriaatividadebcc17'
            ];
        }
        // Para alunos de 2023 em diante: usar tabelas bcc23
        else if ($anoMatricula >= 2023) {
            return [
                'atividades' => 'atividadesdisponiveisbcc23',
                'categoria' => 'categoriaatividadebcc23'
            ];
        }
        // Fallback para casos não previstos: usar tabelas bcc23
        else {
            return [
                'atividades' => 'atividadesdisponiveisbcc23',
                'categoria' => 'categoriaatividadebcc23'
            ];
        }
    }
    
    /**
     * Busca atividades usando a tabela apropriada baseada na matrícula do aluno
     * @param string $matricula - Matrícula do aluno
     * @return array - Lista de atividades
     */
    public static function listarPorMatricula($matricula) {
        try {
            $db = Database::getInstance()->getConnection();
            $tabelas = self::determinarTabelasPorMatricula($matricula);
            
            $sql = "SELECT 
                        ad.id,
                        ad.titulo as nome,
                        ad.carga_horaria_maxima_por_atividade as horas_max,
                        ca.descricao as categoria,
                        'Atividade Complementar' as tipo,
                        ad.observacoes as descricao
                    FROM {$tabelas['atividades']} ad
                    INNER JOIN {$tabelas['categoria']} ca ON ad.categoria_id = ca.id
                    ORDER BY ca.descricao, ad.titulo";
            
            $result = $db->query($sql);
            
            // Se a query falhar, usar fallback com tabelas padrão
            if (!$result) {
                error_log("Tabela {$tabelas['atividades']} não encontrada, usando fallback");
                return self::listarTodas();
            }
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'horas_max' => (int)$row['horas_max'],
                    'categoria' => $row['categoria'],
                    'tipo' => $row['tipo'],
                    'descricao' => $row['descricao'] ?? ''
                ];
            }
            
            error_log("AtividadesDisponiveis::listarPorMatricula - Encontradas " . count($atividades) . " atividades para matrícula $matricula");
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::listarPorMatricula: " . $e->getMessage());
            // Em caso de erro, usar o método padrão como fallback
            return self::listarTodas();
        }
    }
    
    public static function listarTodas() {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Primeiro tentar a tabela atividadesdisponiveisbcc23 com categoriaatividadebcc23
            $sql = "SELECT 
                        ad.id,
                        ad.titulo as nome,
                        ad.carga_horaria_maxima_por_atividade as horas_max,
                        ca.descricao as categoria,
                        'Atividade Complementar' as tipo,
                        ad.observacoes as descricao
                    FROM atividadesdisponiveisbcc23 ad
                    INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    ORDER BY ca.descricao, ad.titulo";
            
            $result = $db->query($sql);
            
            // Se a query falhar, tentar a tabela padrão AtividadesDisponiveis
            if (!$result) {
                error_log("Tabela atividadesdisponiveisbcc23 não encontrada, usando AtividadesDisponiveis");
                $sql = "SELECT 
                            ad.id,
                            ad.titulo as nome,
                            ad.carga_horaria_maxima_por_atividade as horas_max,
                            ca.descricao as categoria,
                            'Atividade Complementar' as tipo,
                            ad.observacoes as descricao
                        FROM AtividadesDisponiveis ad
                        INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                        ORDER BY ca.descricao, ad.titulo";
                
                $result = $db->query($sql);
                
                // Se ainda falhar, tentar com categoriaatividade
                if (!$result) {
                    error_log("Usando tabela categoriaatividade como fallback");
                    $sql = "SELECT 
                                ad.id,
                                ad.titulo as nome,
                                ad.carga_horaria_maxima_por_atividade as horas_max,
                                ca.descricao as categoria,
                                'Atividade Complementar' as tipo,
                                ad.observacoes as descricao
                            FROM AtividadesDisponiveis ad
                            INNER JOIN categoriaatividade ca ON ad.categoria_id = ca.id
                            ORDER BY ca.descricao, ad.titulo";
                    
                    $result = $db->query($sql);
                }
            }
            
            if (!$result) {
                error_log("Erro na consulta SQL: " . $db->error);
                throw new Exception("Erro na consulta: " . $db->error);
            }
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'horas_max' => (int)$row['horas_max'],
                    'categoria' => $row['categoria'],
                    'tipo' => $row['tipo'],
                    'descricao' => $row['descricao'] ?? ''
                ];
            }
            
            error_log("AtividadesDisponiveis::listarTodas - Encontradas " . count($atividades) . " atividades");
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::listarTodas: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Busca atividade por ID usando a tabela apropriada baseada na matrícula
     * @param int $id - ID da atividade
     * @param string $matricula - Matrícula do aluno (opcional, para determinar tabela)
     * @return array|null - Dados da atividade ou null se não encontrada
     */
    public static function buscarPorId($id, $matricula = null) {
        try {
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
            // Se matrícula foi fornecida, usar tabela específica
            if ($matricula) {
                $tabelas = self::determinarTabelasPorMatricula($matricula);
                
                $sql = "SELECT ad.*, ca.descricao as categoria_nome 
                        FROM {$tabelas['atividades']} ad
                        LEFT JOIN {$tabelas['categoria']} ca ON ad.categoria_id = ca.id
                        WHERE ad.id = ?";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $atividade = $result->fetch_assoc();
                
                if ($atividade) {
                    return $atividade;
                }
            }
            
            // Fallback: tentar nas tabelas na ordem de prioridade
            // Primeiro tentar a tabela atividadesdisponiveisbcc23 com categoriaatividadebcc23
            $sql = "SELECT ad.*, ca.descricao as categoria_nome 
                    FROM atividadesdisponiveisbcc23 ad
                    LEFT JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    WHERE ad.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $atividade = $result->fetch_assoc();
            
            // Se não encontrou, tentar com atividadesdisponiveisbcc17
            if (!$atividade) {
                error_log("Tentando buscar na tabela atividadesdisponiveisbcc17");
                $sql = "SELECT ad.*, ca.descricao as categoria_nome 
                        FROM atividadesdisponiveisbcc17 ad
                        LEFT JOIN categoriaatividadebcc17 ca ON ad.categoria_id = ca.id
                        WHERE ad.id = ?";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $atividade = $result->fetch_assoc();
            }
            
            // Se ainda não encontrou, tentar com AtividadesDisponiveis
            if (!$atividade) {
                error_log("Tentando buscar na tabela AtividadesDisponiveis");
                $sql = "SELECT ad.*, ca.descricao as categoria_nome 
                        FROM AtividadesDisponiveis ad
                        LEFT JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                        WHERE ad.id = ?";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $atividade = $result->fetch_assoc();
                
                // Se ainda não encontrou ou categoria_nome é null, tentar com categoriaatividade
                if (!$atividade || !$atividade['categoria_nome']) {
                    error_log("Tentando buscar categoria na tabela categoriaatividade");
                    $sql = "SELECT ad.*, ca.descricao as categoria_nome 
                            FROM AtividadesDisponiveis ad
                            LEFT JOIN categoriaatividade ca ON ad.categoria_id = ca.id
                            WHERE ad.id = ?";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    
                    $result = $stmt->get_result();
                    $atividade = $result->fetch_assoc();
                }
            }
            
            return $atividade;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::buscarPorId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Busca atividades por categoria usando a tabela apropriada baseada na matrícula
     * @param string $categoria - Nome da categoria
     * @param string $matricula - Matrícula do aluno (opcional, para determinar tabela)
     * @return array - Lista de atividades da categoria
     */
    public static function buscarPorCategoria($categoria, $matricula = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $categoria_like = "%{$categoria}%";
            
            // Se matrícula foi fornecida, usar tabela específica
            if ($matricula) {
                $tabelas = self::determinarTabelasPorMatricula($matricula);
                
                // Verificar se é aluno com matrícula 2023+ e categoria é "Atividades sociais e comunitárias"
                $anoMatricula = (int)substr($matricula, 0, 4);
                if ($anoMatricula >= 2023 && stripos($categoria, 'sociais e comunitárias') !== false) {
                    error_log("Filtro aplicado: Aluno 2023+ não pode ver atividades sociais e comunitárias");
                    return []; // Retorna array vazio para alunos 2023+
                }
                
                $sql = "SELECT 
                            ad.id,
                            ad.titulo as nome,
                            ad.carga_horaria_maxima_por_atividade as horas_max,
                            ca.descricao as categoria,
                            'Atividade Complementar' as tipo
                        FROM {$tabelas['atividades']} ad
                        INNER JOIN {$tabelas['categoria']} ca ON ad.categoria_id = ca.id
                        WHERE ca.descricao LIKE ?
                        ORDER BY ad.titulo";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("s", $categoria_like);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $atividades = [];
                    while ($row = $result->fetch_assoc()) {
                        $atividades[] = [
                            'id' => (int)$row['id'],
                            'nome' => $row['nome'],
                            'descricao' => $row['descricao'] ?? '',
                            'horas_max' => (int)$row['horas_max'],
                            'categoria' => $row['categoria'],
                            'tipo' => $row['tipo']
                        ];
                    }
                    return $atividades;
                }
            }
            
            // Fallback: tentar nas tabelas na ordem de prioridade
            // Primeiro tentar a tabela atividadesdisponiveisbcc23 com categoriaatividadebcc23
            $sql = "SELECT 
                        ad.id,
                        ad.titulo as nome,
                        ad.carga_horaria_maxima_por_atividade as horas_max,
                        ca.descricao as categoria,
                        'Atividade Complementar' as tipo
                    FROM atividadesdisponiveisbcc23 ad
                    INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    WHERE ca.descricao LIKE ?
                    ORDER BY ad.titulo";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("s", $categoria_like);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Se não retornou resultados, tentar com atividadesdisponiveisbcc17
            if ($result->num_rows === 0) {
                error_log("Nenhum resultado com atividadesdisponiveisbcc23, tentando atividadesdisponiveisbcc17");
                $sql = "SELECT 
                            ad.id,
                            ad.titulo as nome,
                            ad.carga_horaria_maxima_por_atividade as horas_max,
                            ca.descricao as categoria,
                            'Atividade Complementar' as tipo
                        FROM atividadesdisponiveisbcc17 ad
                        INNER JOIN categoriaatividadebcc17 ca ON ad.categoria_id = ca.id
                        WHERE ca.descricao LIKE ?
                        ORDER BY ad.titulo";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("s", $categoria_like);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            
            // Se ainda não retornou resultados, tentar com AtividadesDisponiveis
            if ($result->num_rows === 0) {
                error_log("Nenhum resultado com atividadesdisponiveisbcc17, tentando AtividadesDisponiveis");
                $sql = "SELECT 
                            ad.id,
                            ad.titulo as nome,
                            ad.carga_horaria_maxima_por_atividade as horas_max,
                            ca.descricao as categoria,
                            'Atividade Complementar' as tipo
                        FROM AtividadesDisponiveis ad
                        INNER JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                        WHERE ca.descricao LIKE ?
                        ORDER BY ad.titulo";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("s", $categoria_like);
                $stmt->execute();
                $result = $stmt->get_result();
                
                // Se ainda não retornou resultados, tentar com categoriaatividade
                if ($result->num_rows === 0) {
                    error_log("Nenhum resultado com categoriaatividadebcc23, tentando categoriaatividade");
                    $sql = "SELECT 
                                ad.id,
                                ad.titulo as nome,
                                ad.carga_horaria_maxima_por_atividade as horas_max,
                                ca.descricao as categoria,
                                'Atividade Complementar' as tipo
                            FROM AtividadesDisponiveis ad
                            INNER JOIN categoriaatividade ca ON ad.categoria_id = ca.id
                            WHERE ca.descricao LIKE ?
                            ORDER BY ad.titulo";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("s", $categoria_like);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
            }
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'] ?? '',
                    'horas_max' => (int)$row['horas_max'],
                    'categoria' => $row['categoria'],
                    'tipo' => $row['tipo']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::buscarPorCategoria: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function editar($id, $titulo, $descricao, $categoria_id, $carga_horaria) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE AtividadesDisponiveis 
                    SET titulo = ?, categoria_id = ?, carga_horaria_maxima_por_atividade = ?, observacoes = ?
                    WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("siis", $titulo, $categoria_id, $carga_horaria, $descricao, $id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::editar: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function remover($id) {
        try {
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("DELETE FROM AtividadesDisponiveis WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            $sucesso = $stmt->execute();
            $linhas_afetadas = $stmt->affected_rows;
            
            error_log("Remoção - Linhas afetadas: " . $linhas_afetadas);
            
            return $sucesso && $linhas_afetadas > 0;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::remover: " . $e->getMessage());
            return false;
        }
    }
    
    public static function adicionar($titulo, $categoria_id, $carga_horaria) {
        try {
            error_log("=== AtividadesDisponiveis::adicionar ===");
            error_log("Parâmetros: título=$titulo, categoria_id=$categoria_id, carga_horaria=$carga_horaria");
            
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
            // Verificar se a categoria existe
            // Verificar se a categoria existe (primeiro em categoriaatividadebcc23, depois em categoriaatividade)
            $checkCat = $db->prepare("SELECT id FROM categoriaatividadebcc23 WHERE id = ?");
            $checkCat->bind_param("i", $categoria_id);
            $checkCat->execute();
            if ($checkCat->get_result()->num_rows === 0) {
                // Tentar na tabela padrão
                $checkCat = $db->prepare("SELECT id FROM categoriaatividade WHERE id = ?");
                $checkCat->bind_param("i", $categoria_id);
                $checkCat->execute();
                if ($checkCat->get_result()->num_rows === 0) {
                    throw new \Exception("Categoria não encontrada: $categoria_id");
                }
            }
            
            $stmt = $db->prepare("INSERT INTO AtividadesDisponiveis (titulo, categoria_id, carga_horaria_maxima_por_atividade) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $titulo, $categoria_id, $carga_horaria);
            
            if (!$stmt->execute()) {
                throw new \Exception("Erro ao inserir atividade: " . $stmt->error);
            }
            
            $id = $db->insert_id;
            error_log("Atividade inserida com ID: $id");
            return $id;
            
        } catch (\Exception $e) {
            error_log("Erro em AtividadesDisponiveis::adicionar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca atividades por ID da categoria usando a tabela apropriada baseada na matrícula
     * @param int $categoria_id - ID da categoria
     * @param string $matricula - Matrícula do aluno (opcional, para determinar tabela)
     * @return array - Lista de atividades da categoria
     */
    public static function buscarPorCategoriaId($categoria_id, $matricula = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Se matrícula foi fornecida, usar tabela específica
            if ($matricula) {
                $tabelas = self::determinarTabelasPorMatricula($matricula);
                
                // Primeiro, verificar se a categoria é "Atividades sociais e comunitárias"
                $sqlCategoria = "SELECT descricao FROM {$tabelas['categoria']} WHERE id = ?";
                $stmtCategoria = $db->prepare($sqlCategoria);
                $stmtCategoria->bind_param("i", $categoria_id);
                $stmtCategoria->execute();
                $resultCategoria = $stmtCategoria->get_result();
                
                if ($resultCategoria->num_rows > 0) {
                    $categoria = $resultCategoria->fetch_assoc();
                    $nomeCategoria = $categoria['descricao'];
                    
                    // Verificar se é aluno com matrícula 2023+ e categoria é "Atividades sociais e comunitárias"
                    $anoMatricula = (int)substr($matricula, 0, 4);
                    if ($anoMatricula >= 2023 && stripos($nomeCategoria, 'sociais e comunitárias') !== false) {
                        error_log("Filtro aplicado por categoria ID: Aluno 2023+ não pode ver atividades sociais e comunitárias");
                        return []; // Retorna array vazio para alunos 2023+
                    }
                }
                
                $sql = "SELECT 
                            ad.id,
                            ad.titulo,
                            ad.carga_horaria_maxima_por_atividade,
                            ad.observacoes,
                            ca.descricao as categoria_nome
                        FROM {$tabelas['atividades']} ad
                        LEFT JOIN {$tabelas['categoria']} ca ON ad.categoria_id = ca.id
                        WHERE ad.categoria_id = ?
                        ORDER BY ad.titulo";
                
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $categoria_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $atividades = [];
                    while ($row = $result->fetch_assoc()) {
                        $atividades[] = [
                            'id' => (int)$row['id'],
                            'titulo' => $row['titulo'],
                            'carga_horaria_maxima_por_atividade' => (int)$row['carga_horaria_maxima_por_atividade'],
                            'observacoes' => $row['observacoes'] ?? '',
                            'categoria_nome' => $row['categoria_nome']
                        ];
                    }
                    return $atividades;
                }
            }
            
            // Fallback: tentar nas tabelas na ordem de prioridade
            // Primeiro tentar a tabela atividadesdisponiveisbcc23 com categoriaatividadebcc23
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
            $stmt->bind_param("i", $categoria_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Se não retornou resultados, tentar com atividadesdisponiveisbcc17
            if ($result->num_rows === 0) {
                error_log("Nenhum resultado com atividadesdisponiveisbcc23, tentando atividadesdisponiveisbcc17");
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
                $stmt->bind_param("i", $categoria_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                // Se ainda não retornou resultados, tentar com AtividadesDisponiveis
                if ($result->num_rows === 0) {
                    error_log("Nenhum resultado com atividadesdisponiveisbcc17, tentando AtividadesDisponiveis");
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
                    $stmt->bind_param("i", $categoria_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
            }
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'titulo' => $row['titulo'],
                    'carga_horaria_maxima_por_atividade' => (int)$row['carga_horaria_maxima_por_atividade'],
                    'observacoes' => $row['observacoes'] ?? '',
                    'categoria_nome' => $row['categoria_nome']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::buscarPorCategoriaId: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
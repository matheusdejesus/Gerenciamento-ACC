<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;
use Exception;

class AtividadesDisponiveis {
    
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
    
    public static function buscarPorId($id) {
        try {
            $db = \backend\api\config\Database::getInstance()->getConnection();
            
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
            
            // Se não encontrou, tentar com AtividadesDisponiveis
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
    
    public static function buscarPorCategoria($categoria) {
        try {
            $db = Database::getInstance()->getConnection();
            
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
            $categoria_like = "%{$categoria}%";
            $stmt->bind_param("s", $categoria_like);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Se não retornou resultados, tentar com AtividadesDisponiveis
            if ($result->num_rows === 0) {
                error_log("Nenhum resultado com atividadesdisponiveisbcc23, tentando AtividadesDisponiveis");
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

    public static function buscarPorCategoriaId($categoria_id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Primeiro tentar a tabela atividadesdisponiveisbcc17 com categoriaatividadebcc23
            $sql = "SELECT 
                        ad.id,
                        ad.titulo,
                        ad.carga_horaria_maxima_por_atividade,
                        ad.observacoes,
                        ca.descricao as categoria_nome
                    FROM atividadesdisponiveisbcc17 ad
                    LEFT JOIN categoriaatividadebcc23 ca ON ad.categoria_id = ca.id
                    WHERE ad.categoria_id = ?
                    ORDER BY ad.titulo";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $categoria_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Se não retornou resultados, tentar com atividadesdisponiveisbcc23
            if ($result->num_rows === 0) {
                error_log("Nenhum resultado com atividadesdisponiveisbcc17, tentando atividadesdisponiveisbcc23");
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
                
                // Se ainda não retornou resultados, tentar com AtividadesDisponiveis
                if ($result->num_rows === 0) {
                    error_log("Nenhum resultado com atividadesdisponiveisbcc23, tentando AtividadesDisponiveis");
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
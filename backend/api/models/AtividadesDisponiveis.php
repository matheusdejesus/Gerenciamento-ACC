<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;
use Exception;

class AtividadesDisponiveis {
    
    public static function listarTodas() {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ad.id,
                        ad.titulo as nome,
                        ad.descricao,
                        ad.carga_horaria as horas_max,
                        ca.descricao as categoria,
                        'Atividade Complementar' as tipo
                    FROM AtividadesDisponiveis ad
                    INNER JOIN CategoriaAtividade ca ON ad.categoria_id = ca.id
                    ORDER BY ca.descricao, ad.titulo";
            
            $result = $db->query($sql);
            
            if (!$result) {
                throw new Exception("Erro na consulta: " . $db->error);
            }
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'horas_max' => (int)$row['horas_max'],
                    'categoria' => $row['categoria'],
                    'tipo' => $row['tipo']
                ];
            }
            
            return $atividades;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::listarTodas: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarPorId($id) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ad.id,
                        ad.titulo as nome,
                        ad.descricao,
                        ad.carga_horaria as horas_max,
                        ca.descricao as categoria,
                        ad.categoria_id,
                        'Atividade Complementar' as tipo
                    FROM AtividadesDisponiveis ad
                    INNER JOIN CategoriaAtividade ca ON ad.categoria_id = ca.id
                    WHERE ad.id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
                    'horas_max' => (int)$row['horas_max'],
                    'categoria' => $row['categoria'],
                    'categoria_id' => (int)$row['categoria_id'],
                    'tipo' => $row['tipo']
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erro em AtividadesDisponiveis::buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function buscarPorCategoria($categoria) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        ad.id,
                        ad.titulo as nome,
                        ad.descricao,
                        ad.carga_horaria as horas_max,
                        ca.descricao as categoria,
                        'Atividade Complementar' as tipo
                    FROM AtividadesDisponiveis ad
                    INNER JOIN CategoriaAtividade ca ON ad.categoria_id = ca.id
                    WHERE ca.descricao LIKE ?
                    ORDER BY ad.titulo";
            
            $stmt = $db->prepare($sql);
            $categoria_like = "%{$categoria}%";
            $stmt->bind_param("s", $categoria_like);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $atividades = [];
            while ($row = $result->fetch_assoc()) {
                $atividades[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao'],
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
}
?>
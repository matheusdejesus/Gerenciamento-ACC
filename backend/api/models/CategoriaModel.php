<?php

namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;
use Exception;

class CategoriaModel
{

    /**
     * Buscar todas as categorias (tipos de atividade) ativas
     * @return array Lista de categorias
     */
    public static function listarTodas()
    {
        try {
            error_log("[DEBUG] CategoriaModel::listarTodas - Iniciando busca de categorias");
            
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT 
                        id,
                        nome,
                        descricao
                    FROM tipo_atividade 
                    ORDER BY nome ASC";

            error_log("[DEBUG] SQL Query: " . $sql);

            $stmt = $db->prepare($sql);

            if (!$stmt) {
                error_log("[ERROR] Erro ao preparar query: " . $db->error);
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            if (!$stmt->execute()) {
                error_log("[ERROR] Erro ao executar query: " . $stmt->error);
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $categorias = [];

            while ($row = $result->fetch_assoc()) {
                $categorias[] = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao']
                ];
            }

            $stmt->close();

            error_log("[DEBUG] Categorias encontradas: " . count($categorias));
            error_log("[DEBUG] Dados das categorias: " . json_encode($categorias));

            return $categorias;
        } catch (Exception $e) {
            error_log("[ERROR] Erro ao listar categorias: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Buscar categoria por ID
     * @param int $id ID da categoria
     * @return array|null Dados da categoria ou null se não encontrada
     */
    public static function buscarPorId($id)
    {
        try {
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT 
                        id,
                        nome,
                        descricao
                    FROM tipo_atividade 
                    WHERE id = ?";

            $stmt = $db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $db->error);
            }

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $categoria = null;

            if ($row = $result->fetch_assoc()) {
                $categoria = [
                    'id' => (int)$row['id'],
                    'nome' => $row['nome'],
                    'descricao' => $row['descricao']
                ];
            }

            $stmt->close();

            return $categoria;
        } catch (Exception $e) {
            error_log("Erro ao buscar categoria por ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar se uma categoria existe
     * @param int $id ID da categoria
     * @return bool True se existe, false caso contrário
     */
    public static function existe($id)
    {
        try {
            $categoria = self::buscarPorId($id);
            return $categoria !== null;
        } catch (Exception $e) {
            error_log("Erro ao verificar existência da categoria: " . $e->getMessage());
            return false;
        }
    }
}
?>

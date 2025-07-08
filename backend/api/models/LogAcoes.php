<?php
namespace backend\api\models;

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;
use Exception;

class LogAcoes {
    
    
    // Registrar uma ação no log de auditoria
    
    public static function registrarAcao($usuario_id, $acao, $descricao = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "INSERT INTO LogAcoes (usuario_id, acao, descricao) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $db->error);
            }
            
            $stmt->bind_param("iss", $usuario_id, $acao, $descricao);
            $resultado = $stmt->execute();
            
            if (!$resultado) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            return $db->insert_id;
            
        } catch (Exception $e) {
            error_log("Erro ao registrar ação no log: " . $e->getMessage());
            throw $e;
        }
    }

    // Buscar logs por usuário

    public static function buscarPorUsuario($usuario_id, $limite = 50, $offset = 0) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT 
                        l.id,
                        l.acao,
                        l.data_hora,
                        l.descricao,
                        u.nome as usuario_nome,
                        u.email as usuario_email,
                        u.tipo as usuario_tipo
                    FROM LogAcoes l
                    INNER JOIN Usuario u ON l.usuario_id = u.id
                    WHERE l.usuario_id = ?
                    ORDER BY l.data_hora DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param("iii", $usuario_id, $limite, $offset);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = [
                    'id' => (int)$row['id'],
                    'acao' => $row['acao'],
                    'data_hora' => $row['data_hora'],
                    'descricao' => $row['descricao'],
                    'usuario' => [
                        'nome' => $row['usuario_nome'],
                        'email' => $row['usuario_email'],
                        'tipo' => $row['usuario_tipo']
                    ]
                ];
            }
            
            return $logs;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar logs por usuário: " . $e->getMessage());
            throw $e;
        }
    }
    
    
    // Buscar todos os logs (para administradores)
    
    public static function buscarTodos($filtros = [], $limite = 50, $offset = 0) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $whereClause = "WHERE 1=1";
            $params = [];
            $types = "";
            
            // Filtro por período
            if (!empty($filtros['data_inicio'])) {
                $whereClause .= " AND l.data_hora >= ?";
                $params[] = $filtros['data_inicio'];
                $types .= "s";
            }
            
            if (!empty($filtros['data_fim'])) {
                $whereClause .= " AND l.data_hora <= ?";
                $params[] = $filtros['data_fim'];
                $types .= "s";
            }
            
            // Filtro por tipo de usuário
            if (!empty($filtros['tipo_usuario'])) {
                $whereClause .= " AND u.tipo = ?";
                $params[] = $filtros['tipo_usuario'];
                $types .= "s";
            }
            
            // Filtro por ação
            if (!empty($filtros['acao'])) {
                $whereClause .= " AND l.acao LIKE ?";
                $params[] = "%" . $filtros['acao'] . "%";
                $types .= "s";
            }
            
            // Filtro por usuário específico
            if (!empty($filtros['usuario_id'])) {
                $whereClause .= " AND l.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
                $types .= "i";
            }
            
            $sql = "SELECT 
                        l.id,
                        l.acao,
                        l.data_hora,
                        l.descricao,
                        u.nome as usuario_nome,
                        u.email as usuario_email,
                        u.tipo as usuario_tipo
                    FROM LogAcoes l
                    INNER JOIN Usuario u ON l.usuario_id = u.id
                    $whereClause
                    ORDER BY l.data_hora DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limite;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = $db->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            $result = $stmt->get_result();
            $logs = [];
            
            while ($row = $result->fetch_assoc()) {
                $logs[] = [
                    'id' => (int)$row['id'],
                    'acao' => $row['acao'],
                    'data_hora' => $row['data_hora'],
                    'descricao' => $row['descricao'],
                    'usuario' => [
                        'nome' => $row['usuario_nome'],
                        'email' => $row['usuario_email'],
                        'tipo' => $row['usuario_tipo']
                    ]
                ];
            }
            
            return $logs;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar todos os logs: " . $e->getMessage());
            throw $e;
        }
    }
    

    // Contar total de logs para paginação
    
    public static function contarLogs($filtros = []) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $whereClause = "WHERE 1=1";
            $params = [];
            $types = "";
            
            // Aplicar os mesmos filtros da busca
            if (!empty($filtros['data_inicio'])) {
                $whereClause .= " AND l.data_hora >= ?";
                $params[] = $filtros['data_inicio'];
                $types .= "s";
            }
            
            if (!empty($filtros['data_fim'])) {
                $whereClause .= " AND l.data_hora <= ?";
                $params[] = $filtros['data_fim'];
                $types .= "s";
            }
            
            if (!empty($filtros['tipo_usuario'])) {
                $whereClause .= " AND u.tipo = ?";
                $params[] = $filtros['tipo_usuario'];
                $types .= "s";
            }
            
            if (!empty($filtros['acao'])) {
                $whereClause .= " AND l.acao LIKE ?";
                $params[] = "%" . $filtros['acao'] . "%";
                $types .= "s";
            }
            
            if (!empty($filtros['usuario_id'])) {
                $whereClause .= " AND l.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
                $types .= "i";
            }
            
            $sql = "SELECT COUNT(*) as total
                    FROM LogAcoes l
                    INNER JOIN Usuario u ON l.usuario_id = u.id
                    $whereClause";
            
            $stmt = $db->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)$row['total'];
            
        } catch (Exception $e) {
            error_log("Erro ao contar logs: " . $e->getMessage());
            throw $e;
        }
    }
    
    
    // Buscar estatísticas de ações
     
    public static function buscarEstatisticas($filtros = []) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $whereClause = "WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($filtros['data_inicio'])) {
                $whereClause .= " AND l.data_hora >= ?";
                $params[] = $filtros['data_inicio'];
                $types .= "s";
            }
            
            if (!empty($filtros['data_fim'])) {
                $whereClause .= " AND l.data_hora <= ?";
                $params[] = $filtros['data_fim'];
                $types .= "s";
            }
            
            // Estatísticas por ação
            $sql = "SELECT 
                        l.acao,
                        COUNT(*) as quantidade,
                        u.tipo as tipo_usuario
                    FROM LogAcoes l
                    INNER JOIN Usuario u ON l.usuario_id = u.id
                    $whereClause
                    GROUP BY l.acao, u.tipo
                    ORDER BY quantidade DESC";
            
            $stmt = $db->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            $result = $stmt->get_result();
            $estatisticas = [];
            
            while ($row = $result->fetch_assoc()) {
                $estatisticas[] = [
                    'acao' => $row['acao'],
                    'quantidade' => (int)$row['quantidade'],
                    'tipo_usuario' => $row['tipo_usuario']
                ];
            }
            
            return $estatisticas;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            throw $e;
        }
    }
    
    
    // Limpar logs antigos
    
    public static function limparLogsAntigos($dias = 365) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "DELETE FROM LogAcoes WHERE data_hora < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $dias);
            $stmt->execute();
            
            return $stmt->affected_rows;
            
        } catch (Exception $e) {
            error_log("Erro ao limpar logs antigos: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
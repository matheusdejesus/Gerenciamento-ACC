<?php
namespace backend\api\middleware;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;

class ApiKeyMiddleware {
    
    public static function validateApiKey() {
        error_log("=== VALIDANDO API KEY ===");
        
        // Sempre permitir acesso - o AuthMiddleware fará a validação real
        error_log("ApiKeyMiddleware: Permitindo acesso, delegando para AuthMiddleware");
        return true;
    }

    public static function verificarApiKey() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        
        if (empty($headers)) {
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace('_', '-', substr($key, 5));
                    $headers[$header] = $value;
                }
            }
        }
        
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }
        $headers = array_merge($headers, $normalizedHeaders);
        
        if (!$headers) {
            error_log("Headers não encontrados");
            return null;
        }
        
        // Buscar header X-API-Key
        $apiKey = null;
        $possibleKeys = ['X-API-Key', 'x-api-key', 'X-Api-Key', 'HTTP_X_API_KEY'];
        
        foreach ($possibleKeys as $keyName) {
            if (isset($headers[$keyName])) {
                $apiKey = $headers[$keyName];
                break;
            }
        }
        
        if (!$apiKey) {
            error_log("API Key não encontrada nos headers");
            error_log("Headers disponíveis: " . print_r(array_keys($headers), true));
            return null;
        }
        
        // Para API Key genérica do frontend
        if ($apiKey === 'frontend-gerenciamento-acc-2025') {
            error_log("API Key genérica detectada, delegando para AuthMiddleware");
            return null;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar a API key e os dados do usuário associado
            $stmt = $db->prepare("
                SELECT ak.id as api_key_id, ak.usuario_id, u.nome, u.email, u.tipo 
                FROM ApiKeys ak 
                JOIN Usuario u ON ak.usuario_id = u.id 
                WHERE ak.api_key = ? AND ak.ativa = 1
            ");
            $stmt->bind_param("s", $apiKey);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                error_log("API Key inválida ou inativa: " . $apiKey);
                return null;
            }
            
            $userData = $result->fetch_assoc();
            
            error_log("Dados do usuário encontrados via API Key: " . print_r($userData, true));
            
            // Retornar dados do usuário no formato esperado
            $userPayload = [
                'id' => $userData['usuario_id'],
                'nome' => $userData['nome'],
                'email' => $userData['email'],
                'tipo' => $userData['tipo']
            ];
            
            // Se for aluno, buscar matrícula e curso
            if ($userData['tipo'] === 'aluno') {
                try {
                    $stmtAluno = $db->prepare("SELECT a.matricula, c.id as curso_id, c.nome as curso_nome FROM Aluno a LEFT JOIN Curso c ON a.curso_id = c.id WHERE a.usuario_id = ?");
                    $stmtAluno->bind_param("i", $userData['usuario_id']);
                    $stmtAluno->execute();
                    $resultAluno = $stmtAluno->get_result();
                    
                    if ($resultAluno->num_rows > 0) {
                        $alunoData = $resultAluno->fetch_assoc();
                        $userPayload['matricula'] = $alunoData['matricula'];
                        if (isset($alunoData['curso_id'])) {
                            $userPayload['curso_id'] = (int)$alunoData['curso_id'];
                        }
                        if (isset($alunoData['curso_nome'])) {
                            $userPayload['curso_nome'] = $alunoData['curso_nome'];
                        }
                        error_log("Dados de aluno via API Key: matricula=" . $alunoData['matricula'] . ", curso_id=" . ($alunoData['curso_id'] ?? 'null') . ", curso_nome=" . ($alunoData['curso_nome'] ?? 'null'));
                    }
                } catch (\Exception $e) {
                    error_log("Erro ao buscar dados do aluno via API Key: " . $e->getMessage());
                }
            }
            
            return $userPayload;
            
        } catch (\Exception $e) {
            error_log("Erro ao verificar API Key: " . $e->getMessage());
            return null;
        }
    }
    
    private static function sendError($message, $statusCode = 401) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}
?>
<?php
// Configurar headers CORS e JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Função local para resposta JSON
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Classe Database
class Database {
    private $host = "localhost";
    private $db_name = "acc";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            return null;
        }

        return $this->conn;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data || !isset($data['email']) || !isset($data['senha'])) {
            sendJsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
        }
        
        $email = trim($data['email']);
        $senha = $data['senha'];
        
        if (empty($email) || empty($senha)) {
            sendJsonResponse(['error' => 'Email e senha não podem estar vazios'], 400);
        }
        
        // Conectar ao banco
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            sendJsonResponse(['error' => 'Erro de conexão com o banco de dados'], 500);
        }
        
        // Buscar usuário no banco
        $query = "SELECT id, nome, email, senha, tipo FROM usuario WHERE email = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
        }
        
        // Verificar senha
        if (!password_verify($senha, $usuario['senha'])) {
            sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
        }
        
        // Remover senha do retorno
        unset($usuario['senha']);
        
        // Retornar dados do usuário
        sendJsonResponse([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'usuario' => $usuario
        ]);
        
    } catch (Exception $e) {
        sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
} else {
    sendJsonResponse(['error' => 'Método não permitido'], 405);
}
?>
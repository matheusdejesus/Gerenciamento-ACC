<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Controller.php';

use backend\api\config\Database;

class UsuarioController extends Controller {
    
    public function create() {
        $data = $this->getRequestData();
        
        // Validar dados de entrada
        if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
            jsonResponse(['error' => 'Nome, email e senha são obrigatórios.'], 400);
            return;
        }

        // Cria um novo usuário
        $usuario = new Usuario();
        $usuario->setNome($data['nome']);
        $usuario->setEmail($data['email']);
        $usuario->setSenha(password_hash($data['senha'], PASSWORD_BCRYPT));

        if ($usuario->save()) {
            jsonResponse(['message' => 'Usuário criado com sucesso.'], 201);
        } else {
            jsonResponse(['error' => 'Erro ao criar usuário.'], 500);
        }
    }

    public function update($id) {
        $data = $this->getRequestData();
        
        // Validar dados de entrada
        if (empty($data['nome']) || empty($data['email'])) {
            jsonResponse(['error' => 'Nome e email são obrigatórios.'], 400);
            return;
        }

        // Atualiza o usuário existente
        $usuario = new Usuario();
        $usuario->setId($id);
        $usuario->setNome($data['nome']);
        $usuario->setEmail($data['email']);

        if ($usuario->update()) {
            jsonResponse(['message' => 'Usuário atualizado com sucesso.']);
        } else {
            jsonResponse(['error' => 'Erro ao atualizar usuário.'], 500);
        }
    }

    public function delete($id) {
        $usuario = new Usuario();
        $usuario->setId($id);

        if ($usuario->delete()) {
            jsonResponse(['message' => 'Usuário excluído com sucesso.']);
        } else {
            jsonResponse(['error' => 'Erro ao excluir usuário.'], 500);
        }
    }

    public function show($id) {
        $usuario = new Usuario();
        $result = $usuario->findById($id);
        
        if ($result) {
            jsonResponse($result);
        } else {
            jsonResponse(['error' => 'Usuário não encontrado.'], 404);
        }
    }

    public function index() {
        $usuario = new Usuario();
        $result = $usuario->findAll();
        jsonResponse($result);
    }

    public function login() {
        try {
            // Pegar dados do POST
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['email']) || !isset($data['senha'])) {
                jsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
                return;
            }
            
            $email = trim($data['email']);
            $senha = $data['senha'];
            
            if (empty($email) || empty($senha)) {
                jsonResponse(['error' => 'Email e senha não podem estar vazios'], 400);
                return;
            }
            
            // Conectar ao banco
            $database = new Database();
            $db = $database->getConnection();
            
            // Buscar usuário
            $query = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ? AND ativo = 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $email);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                jsonResponse(['error' => 'Email ou senha inválidos'], 401);
                return;
            }
            
            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                jsonResponse(['error' => 'Email ou senha inválidos'], 401);
                return;
            }
            
            // Remover senha do retorno
            unset($usuario['senha']);
            
            jsonResponse([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'usuario' => $usuario
            ], 200);
            
        } catch (Exception $e) {
            jsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    private function registrarTentativaLogin($email, $sucesso, $motivo = '') {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Usar os nomes corretos das colunas conforme o banco
            $stmt = $db->prepare("INSERT INTO TentativasLogin (email, sucesso, data_hora, ip_address) VALUES (?, ?, NOW(), ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->bind_param("sis", $email, $sucesso, $ip);
            $stmt->execute();
        } catch (Exception $e) {

        }
    }
}
?>
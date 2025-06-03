<?php
require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;

class Usuario {
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $tipo;
    private $matricula;
    private $curso_id;
    private $siape;

    public function __construct($nome = null, $email = null, $senha = null, $tipo = null, $matricula = null, $curso_id = null, $siape = null) {
        $this->nome = $nome;
        $this->email = $email;
        if ($senha) {
            $this->senha = password_hash($senha, PASSWORD_BCRYPT);
        }
        $this->tipo = $tipo;
        $this->matricula = $matricula;
        $this->curso_id = $curso_id;
        $this->siape = $siape;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function save() {
        $dados = [
            'nome' => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha,
            'tipo' => $this->tipo,
            'matricula' => $this->matricula,
            'curso_id' => $this->curso_id,
            'siape' => $this->siape
        ];
        
        return self::create($dados);
    }

    public function update() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE Usuario SET nome = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $this->nome, $this->email, $this->id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM Usuario WHERE id = ?");
            $stmt->bind_param("i", $this->id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function findAll() {
        try {
            $db = Database::getInstance()->getConnection();
            $result = $db->query("SELECT * FROM Usuario");
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function findByEmail($email) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM Usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public static function findById($id) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM Usuario WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }
    
    public static function create($dados) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Iniciar transação
            $db->autocommit(false);
            
            // 1. Inserir na tabela Usuario
            $stmt = $db->prepare("INSERT INTO Usuario (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $senha_hash = password_hash($dados['senha'], PASSWORD_BCRYPT);
            $stmt->bind_param("ssss", $dados['nome'], $dados['email'], $senha_hash, $dados['tipo']);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao criar usuário principal");
            }
            
            $usuario_id = $db->insert_id;
            
            // 2. Inserir na tabela específica baseada no tipo
            if ($dados['tipo'] === 'aluno') {
                if (empty($dados['matricula']) || empty($dados['curso_id'])) {
                    throw new Exception("Matrícula e curso são obrigatórios para alunos");
                }
                
                $stmt = $db->prepare("INSERT INTO Aluno (usuario_id, matricula, curso_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $usuario_id, $dados['matricula'], $dados['curso_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao criar registro de aluno");
                }
                
            } elseif ($dados['tipo'] === 'coordenador') {
                if (empty($dados['siape']) || empty($dados['curso_id'])) {
                    throw new Exception("SIAPE e curso são obrigatórios para coordenadores");
                }
                
                $stmt = $db->prepare("INSERT INTO Coordenador (usuario_id, siape, curso_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $usuario_id, $dados['siape'], $dados['curso_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao criar registro de coordenador");
                }
                
            } elseif ($dados['tipo'] === 'orientador') {
                if (empty($dados['siape'])) {
                    throw new Exception("SIAPE é obrigatório para orientadores");
                }
                
                $stmt = $db->prepare("INSERT INTO Orientador (usuario_id, siape) VALUES (?, ?)");
                $stmt->bind_param("is", $usuario_id, $dados['siape']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao criar registro de orientador");
                }
            }
            
            $db->commit();
            $db->autocommit(true);
            
            return $usuario_id;
            
        } catch (Exception $e) {
            
            if (isset($db)) {
                $db->rollback();
                $db->autocommit(true);
            }
            return false;
        }
    }

    public static function findByEmailForLogin($email) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, nome, email, senha, tipo FROM Usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function registrarTentativaLogin($email, $sucesso, $db) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        try {
            $stmt = $db->prepare("INSERT INTO TentativasLogin (email, ip_address, sucesso) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $email, $ip_address, $sucesso);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
        }
    }

    public static function verificarBloqueio($email) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar tentativas dos últimos 5 minutos
            $stmt = $db->prepare("
                SELECT COUNT(*) as tentativas_falhadas 
                FROM TentativasLogin 
                WHERE email = ? 
                AND sucesso = 0 
                AND data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['tentativas_falhadas'] >= 3;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar bloqueio: " . $e->getMessage());
            return false;
        }
    }
    
    public static function obterTempoRestanteBloqueio($email) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar a última tentativa falhada dentro dos últimos 5 minutos
            $stmt = $db->prepare("
                SELECT TIMESTAMPDIFF(SECOND, data_hora, DATE_ADD(data_hora, INTERVAL 5 MINUTE)) as segundos_restantes
                FROM TentativasLogin 
                WHERE email = ? 
                AND sucesso = 0 
                AND data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ORDER BY data_hora DESC 
                LIMIT 1
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return max(0, $row['segundos_restantes']);
            }
            
            return 0;
            
        } catch (Exception $e) {
            error_log("Erro ao obter tempo restante: " . $e->getMessage());
            return 0;
        }
    }
    
    public static function limparTentativasAntigas($email) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Limpar tentativas antigas (mais de 5 minutos) em caso de login bem-sucedido
            $stmt = $db->prepare("
                DELETE FROM TentativasLogin 
                WHERE email = ? 
                AND data_hora < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro ao limpar tentativas antigas: " . $e->getMessage());
        }
    }

    public function buscarPorEmail($email) {
        $stmt = $this->db->prepare("SELECT id, nome, email FROM Usuario WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function atualizarSenha($id, $senha_hash) {
        $stmt = $this->db->prepare("UPDATE Usuario SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $senha_hash, $id);
        return $stmt->execute();
    }
}
?>
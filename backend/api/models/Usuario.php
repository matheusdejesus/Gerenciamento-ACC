<?php
require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;

class Usuario {
    
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

    // Registrar tentativa de login
    public static function registrarTentativaLogin($email, $sucesso) {
        try {
            $db = Database::getInstance()->getConnection();
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            
            $stmt = $db->prepare("INSERT INTO TentativasLogin (email, ip_address, sucesso) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $email, $ip_address, $sucesso);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
        }
    }

     // Verificar se usuário está bloqueado
    public static function verificarBloqueio($email) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar tentativas dos últimos 5 minutos
            $stmt = $db->prepare("
                SELECT COUNT(*) as tentativas 
                FROM TentativasLogin 
                WHERE email = ? 
                AND sucesso = 0 
                AND data_hora > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['tentativas'] >= 5;
        } catch (Exception $e) {
            error_log("Erro ao verificar bloqueio: " . $e->getMessage());
            return false;
        }
    }

    // Obter tempo restante de bloqueio
    public static function obterTempoRestanteBloqueio($email) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT TIMESTAMPDIFF(SECOND, MAX(data_hora), DATE_ADD(MAX(data_hora), INTERVAL 5 MINUTE)) as tempo_restante
                FROM TentativasLogin 
                WHERE email = ? 
                AND sucesso = 0 
                AND data_hora > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return max(0, $row['tempo_restante'] ?? 0);
        } catch (Exception $e) {
            error_log("Erro ao obter tempo restante: " . $e->getMessage());
            return 0;
        }
    }


    // Limpar tentativas antigas
    public static function limparTentativasAntigas($email) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Limpar tentativas antigas (mais de 5 minutos)
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

    
    // Buscar usuário para login
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
            error_log("Erro em findByEmailForLogin: " . $e->getMessage());
            return null;
        }
    }

    // Buscar dados completos de um usuário por ID
    public static function buscarDadosCompletosPorId($id, $tipo) {
        try {
            error_log("=== buscarDadosCompletosPorId ===");
            error_log("ID: " . $id);
            error_log("Tipo: " . $tipo);
            
            $db = Database::getInstance()->getConnection();
            
            switch ($tipo) {
                case 'aluno':
                    $sql = "SELECT 
                                u.nome, 
                                u.email, 
                                a.matricula, 
                                c.nome as curso_nome,
                                c.id as curso_id
                            FROM Usuario u
                            INNER JOIN Aluno a ON a.usuario_id = u.id
                            INNER JOIN Curso c ON c.id = a.curso_id
                            WHERE u.id = ?";
                    break;
                    
                case 'coordenador':
                    $sql = "SELECT 
                                u.nome, 
                                u.email, 
                                coord.siape, 
                                c.nome as curso_nome,
                                c.id as curso_id
                            FROM Usuario u
                            INNER JOIN Coordenador coord ON coord.usuario_id = u.id
                            INNER JOIN Curso c ON c.id = coord.curso_id
                            WHERE u.id = ?";
                    break;
                    
                case 'orientador':
                    $sql = "SELECT 
                                u.nome, 
                                u.email, 
                                o.siape
                            FROM Usuario u
                            INNER JOIN Orientador o ON o.usuario_id = u.id
                            WHERE u.id = ?";
                    break;
                    
                default:
                    $sql = "SELECT nome, email FROM Usuario WHERE id = ?";
                    break;
            }
            
            error_log("SQL: " . $sql);
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                error_log("Erro ao preparar statement: " . $db->error);
                return null;
            }
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $dados = $result->fetch_assoc();
            
            error_log("Dados encontrados: " . json_encode($dados));
            
            return $dados;
            
        } catch (Exception $e) {
            error_log("Erro em Usuario::buscarDadosCompletosPorId: " . $e->getMessage());
            return null;
        }
    }

    
    // Atualizar dados básicos do usuário
    public static function atualizarDadosBasicos($id, $dados) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "UPDATE Usuario SET ";
            $params = [];
            $types = "";
            $updates = [];
            
            if (isset($dados['nome'])) {
                $updates[] = "nome = ?";
                $params[] = $dados['nome'];
                $types .= "s";
            }
            
            if (isset($dados['email'])) {
                $updates[] = "email = ?";
                $params[] = $dados['email'];
                $types .= "s";
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $sql .= implode(", ", $updates) . " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro em Usuario::atualizarDadosBasicos: " . $e->getMessage());
            return false;
        }
    }

    
    // Alterar senha do usuário
    public static function alterarSenha($id, $senhaAtual, $novaSenha) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar senha atual
            $stmt = $db->prepare("SELECT senha FROM Usuario WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'error' => 'Usuário não encontrado'];
            }
            
            $usuario = $result->fetch_assoc();
            
            if (!password_verify($senhaAtual, $usuario['senha'])) {
                return ['success' => false, 'error' => 'Senha atual incorreta'];
            }
            
            // Atualizar senha
            $novaSenhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE Usuario SET senha = ? WHERE id = ?");
            $stmt->bind_param("si", $novaSenhaHash, $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Senha alterada com sucesso'];
            } else {
                return ['success' => false, 'error' => 'Erro ao alterar senha'];
            }
            
        } catch (Exception $e) {
            error_log("Erro em Usuario::alterarSenha: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno do servidor'];
        }
    }

    
    // Método principal de autenticação - centraliza toda lógica de login
    public static function autenticar($email, $senha) {
        try {
            // Validações básicas
            if (empty($email) || empty($senha)) {
                return [
                    'success' => false, 
                    'error' => 'Email e senha são obrigatórios',
                    'status_code' => 400
                ];
            }
            
            $email = trim($email);
            
            // Verificar bloqueio
            if (self::verificarBloqueio($email)) {
                $tempoRestante = self::obterTempoRestanteBloqueio($email);
                $minutosRestantes = ceil($tempoRestante / 60);
                return [
                    'success' => false, 
                    'error' => "Muitas tentativas de login falhadas. Tente novamente em $minutosRestantes minuto(s).",
                    'bloqueado' => true,
                    'tempo_restante' => $tempoRestante,
                    'status_code' => 429
                ];
            }
            
            // Buscar usuário
            $usuario = self::findByEmailForLogin($email);
            if (!$usuario) {
                self::registrarTentativaLogin($email, 0);
                return [
                    'success' => false, 
                    'error' => 'Email ou senha inválidos',
                    'status_code' => 401
                ];
            }
            
            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                self::registrarTentativaLogin($email, 0);
                return [
                    'success' => false, 
                    'error' => 'Email ou senha inválidos',
                    'status_code' => 401
                ];
            }
            
            // Sucesso - limpar dados de segurança
            self::registrarTentativaLogin($email, 1);
            self::limparTentativasAntigas($email);
            
            // Remover senha dos dados retornados
            unset($usuario['senha']);
            
            return [
                'success' => true, 
                'usuario' => $usuario,
                'status_code' => 200
            ];
            
        } catch (Exception $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            if (isset($email)) {
                self::registrarTentativaLogin($email, 0);
            }
            return [
                'success' => false, 
                'error' => 'Erro interno do servidor',
                'status_code' => 500
            ];
        }
    }

    // Buscar dados para configuração com validações
    public static function buscarDadosConfiguracao($userId, $userType) {
        try {
            if (empty($userId) || empty($userType)) {
                return [
                    'success' => false,
                    'error' => 'Parâmetros inválidos',
                    'status_code' => 400
                ];
            }

            $dados = self::buscarDadosCompletosPorId($userId, $userType);
            
            if (!$dados) {
                return [
                    'success' => false,
                    'error' => 'Dados do usuário não encontrados',
                    'status_code' => 404
                ];
            }

            return [
                'success' => true,
                'data' => $dados,
                'status_code' => 200
            ];

        } catch (Exception $e) {
            error_log("Erro em buscarDadosConfiguracao: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor',
                'status_code' => 500
            ];
        }
    }


    // Atualizar dados pessoais com validações
    public static function atualizarDadosPessoaisCompleto($userId, $dados) {
        try {
            // Validações
            if (empty($userId)) {
                return [
                    'success' => false,
                    'error' => 'ID do usuário inválido',
                    'status_code' => 400
                ];
            }

            if (empty($dados) || (!isset($dados['nome']) && !isset($dados['email']))) {
                return [
                    'success' => false,
                    'error' => 'Nenhum dado para atualizar',
                    'status_code' => 400
                ];
            }

            // Validar email se fornecido
            if (isset($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'error' => 'Email inválido',
                    'status_code' => 400
                ];
            }

            // Verificar se email já existe (se estiver sendo alterado)
            if (isset($dados['email'])) {
                $emailExiste = self::findByEmail($dados['email']);
                if ($emailExiste && $emailExiste['id'] != $userId) {
                    return [
                        'success' => false,
                        'error' => 'Este email já está em uso',
                        'status_code' => 409
                    ];
                }
            }

            $resultado = self::atualizarDadosBasicos($userId, $dados);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Dados atualizados com sucesso',
                    'status_code' => 200
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Erro ao atualizar dados',
                    'status_code' => 500
                ];
            }

        } catch (Exception $e) {
            error_log("Erro em atualizarDadosPessoaisCompleto: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor',
                'status_code' => 500
            ];
        }
    }

    
    // Alterar senha com validações completas
    public static function alterarSenhaCompleta($userId, $senhaAtual, $novaSenha) {
        try {
            // Validações
            if (empty($userId) || empty($senhaAtual) || empty($novaSenha)) {
                return [
                    'success' => false,
                    'error' => 'Todos os campos são obrigatórios',
                    'status_code' => 400
                ];
            }

            if (strlen($novaSenha) < 6) {
                return [
                    'success' => false,
                    'error' => 'A nova senha deve ter pelo menos 6 caracteres',
                    'status_code' => 400
                ];
            }

            if ($senhaAtual === $novaSenha) {
                return [
                    'success' => false,
                    'error' => 'A nova senha deve ser diferente da atual',
                    'status_code' => 400
                ];
            }

            return self::alterarSenha($userId, $senhaAtual, $novaSenha);

        } catch (Exception $e) {
            error_log("Erro em alterarSenhaCompleta: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro interno do servidor',
                'status_code' => 500
            ];
        }
    }

    public static function listarTodos() {
        $db = \backend\api\config\Database::getInstance()->getConnection();
        // Remover o filtro para incluir admin
        $res = $db->query("SELECT id, nome, email, tipo FROM Usuario ORDER BY nome");
        $usuarios = [];
        while ($row = $res->fetch_assoc()) {
            // Buscar dados adicionais conforme o tipo
            if ($row['tipo'] === 'aluno') {
                $stmt = $db->prepare("SELECT matricula, curso_id FROM Aluno WHERE usuario_id = ?");
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();
                $dadosAluno = $stmt->get_result()->fetch_assoc();
                $row['matricula'] = $dadosAluno['matricula'] ?? null;
                $row['curso_id'] = $dadosAluno['curso_id'] ?? null;
                $stmt->close();
            } elseif ($row['tipo'] === 'coordenador' || $row['tipo'] === 'orientador') {
                $stmt = $db->prepare("SELECT siape FROM " . ucfirst($row['tipo']) . " WHERE usuario_id = ?");
                $stmt->bind_param("i", $row['id']);
                $stmt->execute();
                $dados = $stmt->get_result()->fetch_assoc();
                $row['siape'] = $dados['siape'] ?? null;
                $stmt->close();
            }
            $usuarios[] = $row;
        }
        return $usuarios;
    }
}
?>
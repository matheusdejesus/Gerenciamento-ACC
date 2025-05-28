<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController extends Controller {
    private $requiredFields = ['nome', 'email', 'senha', 'tipo'];

    public function __construct() {
        parent::__construct(new Usuario());
    }

    public function login() {
        $data = $this->getRequestData();
        $this->validateRequiredFields($data, ['email', 'senha']);

        $usuario = $this->model->login($data['email'], $data['senha']);
        if ($usuario) {
            // Aqui você pode gerar um token JWT
            jsonResponse([
                'message' => 'Login realizado com sucesso',
                'usuario' => $usuario
            ]);
        }
        jsonResponse(['error' => 'Email ou senha inválidos'], 401);
    }

    public function store() {
        $data = $this->getRequestData();
        $this->validateRequiredFields($data, $this->requiredFields);

        // Validar tipo de usuário
        $tiposValidos = ['aluno', 'coordenador', 'orientador', 'admin'];
        if (!in_array($data['tipo'], $tiposValidos)) {
            jsonResponse(['error' => 'Tipo de usuário inválido'], 400);
        }

        // Verificar se email já existe
        $existe = $this->model->getByEmail($data['email']);
        if (!empty($existe)) {
            jsonResponse(['error' => 'Email já cadastrado'], 400);
        }

        parent::store();
    }

    public function update($id) {
        $data = $this->getRequestData();
        
        // Se estiver atualizando o email, verificar se já existe
        if (isset($data['email'])) {
            $existe = $this->model->getByEmail($data['email']);
            if (!empty($existe) && $existe[0]['id'] != $id) {
                jsonResponse(['error' => 'Email já cadastrado'], 400);
            }
        }

        // Se estiver atualizando o tipo, validar
        if (isset($data['tipo'])) {
            $tiposValidos = ['aluno', 'coordenador', 'orientador', 'admin'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                jsonResponse(['error' => 'Tipo de usuário inválido'], 400);
            }
        }

        parent::update($id);
    }

    public function getByTipo($tipo) {
        $tiposValidos = ['aluno', 'coordenador', 'orientador', 'admin'];
        if (!in_array($tipo, $tiposValidos)) {
            jsonResponse(['error' => 'Tipo de usuário inválido'], 400);
        }

        $usuarios = $this->model->getByTipo($tipo);
        jsonResponse($usuarios);
    }
} 
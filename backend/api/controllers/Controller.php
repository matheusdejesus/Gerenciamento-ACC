<?php
require_once __DIR__ . '/../config/config.php';

abstract class Controller {
    protected $model;

    public function __construct($model) {
        $this->model = $model;
    }

    protected function getRequestData() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonResponse(['error' => 'JSON inválido'], 400);
        }
        return $data;
    }

    protected function validateRequiredFields($data, $requiredFields) {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            jsonResponse(['error' => 'Campos obrigatórios faltando: ' . implode(', ', $missing)], 400);
        }
        return true;
    }

    public function index() {
        $result = $this->model->findAll();
        jsonResponse($result);
    }

    public function show($id) {
        $result = $this->model->findById($id);
        if (!$result) {
            jsonResponse(['error' => 'Registro não encontrado'], 404);
        }
        jsonResponse($result);
    }

    public function store() {
        $data = $this->getRequestData();
        $id = $this->model->create($data);
        if ($id) {
            jsonResponse(['id' => $id, 'message' => 'Registro criado com sucesso'], 201);
        }
        jsonResponse(['error' => 'Erro ao criar registro'], 500);
    }

    public function update($id) {
        $data = $this->getRequestData();
        if ($this->model->update($id, $data)) {
            jsonResponse(['message' => 'Registro atualizado com sucesso']);
        }
        jsonResponse(['error' => 'Erro ao atualizar registro'], 500);
    }

    public function delete($id) {
        if ($this->model->delete($id)) {
            jsonResponse(['message' => 'Registro excluído com sucesso']);
        }
        jsonResponse(['error' => 'Erro ao excluir registro'], 500);
    }
} 
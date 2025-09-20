<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AtividadeComplementarPesquisa.php';
require_once __DIR__ . '/../models/AtividadesDisponiveis.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\config\Database;
use backend\api\models\AtividadeComplementarPesquisa;
use backend\api\models\AtividadesDisponiveis;
use backend\api\controllers\Controller;
use backend\api\middleware\AuthMiddleware;
use Exception;

class AtividadeComplementarPesquisaController extends Controller {

    /**
     * Cadastrar nova atividade de pesquisa
     */
    public function cadastrar($dados = null) {
        try {
            // Verificar autenticação
            $usuario = AuthMiddleware::validateToken();
            
            if (!$usuario) {
                http_response_code(403);
                echo json_encode(['erro' => 'Acesso negado']);
                return;
            }

            // Se não foram passados dados, pegar do POST
            if ($dados === null) {
                $dados = $_POST;
            }

            // Adicionar o ID do aluno logado
            $dados['aluno_id'] = $usuario['id'];

            // Validar dados obrigatórios
            $this->validarDadosCadastro($dados);

            // Validar se a atividade disponível existe
            $atividadeDisponivel = AtividadesDisponiveis::buscarPorId($dados['atividade_disponivel_id']);
            if (!$atividadeDisponivel) {
                throw new Exception("Atividade disponível não encontrada");
            }

            // Validar horas realizadas não excedem o máximo permitido
            if ($dados['horas_realizadas'] > $atividadeDisponivel['carga_horaria_maxima_por_atividade']) {
                throw new Exception("Horas realizadas excedem o máximo permitido para esta atividade");
            }

            // Processar upload do arquivo se necessário
            if (isset($_FILES['declaracao']) && $_FILES['declaracao']['error'] === UPLOAD_ERR_OK) {
                $dados['declaracao_caminho'] = $this->processarUploadArquivo($_FILES['declaracao']);
            } elseif (empty($dados['declaracao_caminho'])) {
                throw new Exception("Declaração/Certificado é obrigatório");
            }

            // Criar a atividade
            $id = AtividadeComplementarPesquisa::create($dados);

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Atividade de pesquisa cadastrada com sucesso',
                'id' => $id
            ]);

        } catch (Exception $e) {
            error_log("Erro ao cadastrar atividade de pesquisa: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Listar atividades por aluno
     */
    public function listarPorAluno($aluno_id = null) {
        try {
            if ($aluno_id === null) {
                $aluno_id = $_GET['aluno_id'] ?? null;
            }

            if (empty($aluno_id)) {
                throw new Exception("ID do aluno é obrigatório");
            }

            $atividades = AtividadeComplementarPesquisa::buscarPorAluno($aluno_id);

            echo json_encode([
                'success' => true,
                'data' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro ao listar atividades de pesquisa por aluno: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Buscar atividade por ID
     */
    public function buscarPorId($id = null) {
        try {
            if ($id === null) {
                $id = $_GET['id'] ?? null;
            }

            if (empty($id)) {
                throw new Exception("ID da atividade é obrigatório");
            }

            $atividade = AtividadeComplementarPesquisa::buscarPorId($id);

            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }

            $this->sendJsonResponse([
                'success' => true,
                'atividade' => $atividade
            ]);

        } catch (Exception $e) {
            error_log("Erro ao buscar atividade de pesquisa por ID: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Listar todas as atividades (para coordenadores)
     */
    public function listarTodas() {
        try {
            $filtros = [];
            
            if (!empty($_GET['status'])) {
                $filtros['status'] = $_GET['status'];
            }
            
            if (!empty($_GET['curso_id'])) {
                $filtros['curso_id'] = $_GET['curso_id'];
            }
            
            if (!empty($_GET['tipo_atividade'])) {
                $filtros['tipo_atividade'] = $_GET['tipo_atividade'];
            }

            $atividades = AtividadeComplementarPesquisa::listarTodas($filtros);

            $this->sendJsonResponse([
                'success' => true,
                'atividades' => $atividades
            ]);

        } catch (Exception $e) {
            error_log("Erro ao listar todas as atividades de pesquisa: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Avaliar atividade (aprovar/rejeitar)
     */
    public function avaliarAtividade($dados = null) {
        try {
            if ($dados === null) {
                $dados = $_POST;
            }

            // Validar dados obrigatórios
            if (empty($dados['id']) || !is_numeric($dados['id'])) {
                throw new Exception("ID da atividade é obrigatório");
            }

            if (empty($dados['status']) || !in_array($dados['status'], ['aprovada', 'rejeitada'])) {
                throw new Exception("Status deve ser 'aprovada' ou 'rejeitada'");
            }

            if (empty($dados['avaliador_id']) || !is_numeric($dados['avaliador_id'])) {
                throw new Exception("ID do avaliador é obrigatório");
            }

            $observacoes = $dados['observacoes_avaliacao'] ?? null;

            // Verificar se a atividade existe
            $atividade = AtividadeComplementarPesquisa::buscarPorId($dados['id']);
            if (!$atividade) {
                throw new Exception("Atividade não encontrada");
            }

            // Verificar se a atividade ainda está pendente
            if ($atividade['status'] !== 'pendente') {
                throw new Exception("Esta atividade já foi avaliada");
            }

            // Atualizar status
            $sucesso = AtividadeComplementarPesquisa::atualizarStatus(
                $dados['id'],
                $dados['status'],
                $observacoes,
                $dados['avaliador_id']
            );

            if (!$sucesso) {
                throw new Exception("Erro ao atualizar status da atividade");
            }

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Atividade avaliada com sucesso'
            ]);

        } catch (Exception $e) {
            error_log("Erro ao avaliar atividade de pesquisa: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validar dados do cadastro
     */
    private function validarDadosCadastro($dados) {
        if (empty($dados['aluno_id']) || !is_numeric($dados['aluno_id'])) {
            throw new Exception("ID do aluno é obrigatório");
        }

        if (empty($dados['atividade_disponivel_id']) || !is_numeric($dados['atividade_disponivel_id'])) {
            throw new Exception("ID da atividade disponível é obrigatório");
        }

        if (empty($dados['tipo_atividade'])) {
            throw new Exception("Tipo de atividade é obrigatório");
        }

        $tiposValidos = ['apresentacao_evento', 'membro_evento', 'iniciacao_cientifica', 'publicacao_artigo'];
        if (!in_array($dados['tipo_atividade'], $tiposValidos)) {
            throw new Exception("Tipo de atividade inválido");
        }

        if (empty($dados['horas_realizadas']) || !is_numeric($dados['horas_realizadas']) || $dados['horas_realizadas'] <= 0) {
            throw new Exception("Horas realizadas deve ser um número positivo");
        }

        // Validar local_instituicao apenas para tipos que requerem
        $tiposQueRequeremLocal = ['apresentacao_evento', 'membro_evento'];
        if (in_array($dados['tipo_atividade'], $tiposQueRequeremLocal)) {
            if (empty($dados['local_instituicao']) || strlen(trim($dados['local_instituicao'])) < 3) {
                throw new Exception("Local/Instituição deve ter pelo menos 3 caracteres");
            }
        }

        // Validações específicas por tipo de atividade
        switch ($dados['tipo_atividade']) {
            case 'apresentacao_evento':
                if (empty($dados['nome_evento'])) {
                    throw new Exception("Nome do evento é obrigatório para apresentação em evento");
                }
                // Tema da apresentação é opcional
                break;

            case 'membro_evento':
                if (empty($dados['nome_evento'])) {
                    throw new Exception("Nome do evento é obrigatório");
                }
                break;

            case 'iniciacao_cientifica':
                if (empty($dados['nome_projeto'])) {
                    throw new Exception("Nome do projeto é obrigatório para iniciação científica");
                }
                if (empty($dados['data_inicio']) || empty($dados['data_fim'])) {
                    throw new Exception("Datas de início e fim são obrigatórias para iniciação científica");
                }
                break;

            case 'publicacao_artigo':
                if (empty($dados['nome_artigo'])) {
                    throw new Exception("Nome do artigo é obrigatório para publicação");
                }
                break;
        }
    }

    /**
     * Processar upload de arquivo
     */
    private function processarUploadArquivo($arquivo) {
        $diretorioUpload = __DIR__ . '/../../../uploads/atividades_pesquisa/';
        
        // Criar diretório se não existir
        if (!is_dir($diretorioUpload)) {
            mkdir($diretorioUpload, 0755, true);
        }

        // Validar tipo de arquivo
        $tiposPermitidos = ['pdf', 'jpg', 'jpeg', 'png'];
        $tiposMime = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extensao, $tiposPermitidos) && !in_array($arquivo['type'], $tiposMime)) {
            throw new Exception("Tipo de arquivo não permitido. Use PDF, JPG ou PNG");
        }

        // Validar tamanho (máximo 5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("Arquivo muito grande. Máximo 5MB");
        }

        // Gerar nome único
        $nomeArquivo = uniqid() . '_' . time() . '.' . $extensao;
        $caminhoCompleto = $diretorioUpload . $nomeArquivo;

        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception("Erro ao fazer upload do arquivo");
        }

        return 'uploads/atividades_pesquisa/' . $nomeArquivo;
    }
}